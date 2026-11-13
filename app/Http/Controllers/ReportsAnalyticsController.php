<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportsAnalyticsController extends Controller
{
    private function schoolId(): int
    {
        return (int) session('school_id', 1);
    }

    private function reports(): array
    {
        return [
            'dashboard'   => ['Reports Dashboard', 'School-wide reporting overview'],
            'attendance'  => ['Attendance Reports', 'Daily and period-wise student attendance'],
            'academic'    => ['Academic Reports', 'Examination and assessment overview'],
            'performance' => ['Student Performance', 'Student-wise examination performance'],
            'classes'     => ['Class Analytics', 'Enrollment and class distribution'],
            'grades'      => ['Grade Reports', 'Grade distribution from published results'],
            'fees'        => ['Fee Collection', 'Invoice, collection and outstanding summary'],
            'teachers'    => ['Teacher Performance', 'Staff attendance and recorded assessment activity'],
            'custom'      => ['Custom Reports', 'Build a report using available report categories'],
        ];
    }

    public function index(Request $request, string $report = 'dashboard')
    {
        abort_unless(array_key_exists($report, $this->reports()), 404);

        $schoolId = $this->schoolId();

        $filters = $request->only([
            'from',
            'to',
            'academic_year_id',
            'class_id',
            'section_id',
            'q'
        ]);

        $years = DB::table('academic_years')
            ->where('school_id', $schoolId)
            ->orderByDesc('start_date')
            ->get(['id', 'name', 'status']);

        $classes = DB::table('school_classes')
            ->where('school_id', $schoolId)
            ->orderBy('name')
            ->get(['id', 'name']);

        $sections = DB::table('sections')
            ->whereIn('school_class_id', $classes->pluck('id'))
            ->orderBy('name')
            ->get(['id', 'school_class_id', 'name']);

        $meta = $this->reports()[$report];

        $data = $this->buildReport($report, $filters, $schoolId);

        $rows = collect($data['rows']);

        $page = LengthAwarePaginator::resolveCurrentPage();

        $data['rows'] = new LengthAwarePaginator(
            $rows->forPage($page, 25)->values(),
            $rows->count(),
            25,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query()
            ]
        );

        return view(
            'caspa.reports.' . $report,
            compact('report', 'meta', 'filters', 'years', 'classes', 'sections', 'data')
        );
    }

    public function export(Request $request, string $report, string $format)
    {
        abort_unless(
            array_key_exists($report, $this->reports()) &&
            in_array($format, ['csv', 'pdf'], true),
            404
        );

        $data = $this->buildReport(
            $report,
            $request->only([
                'from',
                'to',
                'academic_year_id',
                'class_id',
                'section_id',
                'q'
            ]),
            $this->schoolId()
        );

        $title = $this->reports()[$report][0];

        if ($format === 'pdf') {
            $pdf = Pdf::loadView(
                'caspa.reports.export',
                compact('data', 'title', 'report')
            )->setPaper('a4', 'landscape');

            return $pdf->download(Str::slug($title) . '.pdf');
        }

        $filename = Str::slug($title) . '.csv';

        return response()->streamDownload(function () use ($data, $title) {
            $out = fopen('php://output', 'w');

            fprintf($out, "\xEF\xBB\xBF");

            fputcsv($out, [$title]);

            if (!empty($data['rows'])) {
                fputcsv($out, array_keys((array) $data['rows'][0]));

                foreach ($data['rows'] as $row) {
                    fputcsv($out, (array) $row);
                }
            } else {
                fputcsv($out, ['No records found']);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8'
        ]);
    }

    private function buildReport(string $report, array $f, int $schoolId): array
    {
        $data = [
            'cards' => [],
            'rows' => [],
            'chart' => [],
            'notes' => []
        ];

        $from = $f['from'] ?? null;
        $to = $f['to'] ?? null;

        $dateRange = function ($query, string $column) use ($from, $to) {
            if ($from) {
                $query->whereDate($column, '>=', $from);
            }

            if ($to) {
                $query->whereDate($column, '<=', $to);
            }

            return $query;
        };

        $studentName = "TRIM(CONCAT(
            COALESCE(sp.first_name, ''),
            ' ',
            COALESCE(sp.middle_name, ''),
            ' ',
            COALESCE(sp.last_name, '')
        ))";

        /*
        |--------------------------------------------------------------------------
        | REPORT DASHBOARD
        |--------------------------------------------------------------------------
        */

        if ($report === 'dashboard') {

            $students = DB::table('student_profiles')
                ->where('school_id', $schoolId)
                ->whereNull('deleted_at')
                ->count();

            $staff = DB::table('staff_profiles')
                ->where('school_id', $schoolId)
                ->whereNull('deleted_at')
                ->count();

            $invoices = DB::table('fee_invoices')
                ->where('school_id', $schoolId);

            $invoicedAmount = (float) (clone $invoices)->sum('total_amount');
            $outstandingAmount = (float) (clone $invoices)->sum('balance_amount');

            $data['cards'] = [
                ['label' => 'Students', 'value' => $students],
                ['label' => 'Staff', 'value' => $staff],
                ['label' => 'Invoiced', 'value' => '₹' . number_format($invoicedAmount, 2)],
                ['label' => 'Outstanding', 'value' => '₹' . number_format($outstandingAmount, 2)]
            ];

            $data['rows'] = DB::table('academic_years')
                ->where('school_id', $schoolId)
                ->orderByDesc('start_date')
                ->get([
                    'name',
                    'start_date',
                    'end_date',
                    'status'
                ])
                ->all();

            $data['notes'][] = 'Overview uses the records currently stored for the selected school.';
        }

        /*
        |--------------------------------------------------------------------------
        | ATTENDANCE REPORT
        |--------------------------------------------------------------------------
        */

        elseif ($report === 'attendance') {

            $q = DB::table('student_attendance as sa')
                ->join('attendance_sessions as ats', 'ats.id', '=', 'sa.attendance_session_id')
                ->join('student_profiles as sp', 'sp.id', '=', 'sa.student_id')
                ->where('ats.school_id', $schoolId)
                ->whereNull('sp.deleted_at');

            $dateRange($q, 'ats.attendance_date');

            if (!empty($f['academic_year_id'])) {
                $q->where('ats.academic_year_id', $f['academic_year_id']);
            }

            if (!empty($f['section_id'])) {
                $q->where('ats.section_id', $f['section_id']);
            }

            if (!empty($f['class_id'])) {
                $q->whereExists(function ($sq) use ($f) {
                    $sq->selectRaw('1')
                        ->from('sections as s')
                        ->whereColumn('s.id', 'ats.section_id')
                        ->where('s.school_class_id', $f['class_id']);
                });
            }

            if (!empty($f['q'])) {
                $q->whereRaw($studentName . ' LIKE ?', ['%' . $f['q'] . '%']);
            }

            $total = (clone $q)->count();

            $present = (clone $q)
                ->whereIn(DB::raw('LOWER(sa.status)'), ['present', 'late'])
                ->count();

            $absent = (clone $q)
                ->whereIn(DB::raw('LOWER(sa.status)'), ['absent'])
                ->count();

            $data['cards'] = [
                ['label' => 'Attendance records', 'value' => $total],
                ['label' => 'Present / Late', 'value' => $present],
                ['label' => 'Absent', 'value' => $absent],
                [
                    'label' => 'Attendance rate',
                    'value' => $total
                        ? number_format(($present / $total) * 100, 1) . '%'
                        : '—'
                ]
            ];

            $rowsQuery = clone $q;

            $data['rows'] = $rowsQuery
                ->selectRaw("
                    ats.attendance_date,
                    ats.session_name,
                    {$studentName} as student,
                    sp.admission_number,
                    sa.status,
                    sa.check_in_time,
                    sa.check_out_time
                ")
                ->orderByDesc('ats.attendance_date')
                ->limit(1000)
                ->get()
                ->all();

            $data['notes'][] = 'Attendance rate treats present and late as attended; other statuses remain visible in the detail table.';
        }

        /*
        |--------------------------------------------------------------------------
        | ACADEMIC / PERFORMANCE / GRADES
        |--------------------------------------------------------------------------
        */

        elseif (in_array($report, ['academic', 'performance', 'grades'], true)) {

            $q = DB::table('exam_results as er')
                ->join('exam_subjects as es', 'es.id', '=', 'er.exam_subject_id')
                ->join('examinations as ex', 'ex.id', '=', 'es.examination_id')
                ->join('student_profiles as sp', 'sp.id', '=', 'er.student_id')
                ->where('ex.school_id', $schoolId)
                ->whereNull('sp.deleted_at');

            $dateRange($q, 'es.exam_date');

            if (!empty($f['academic_year_id'])) {
                $q->where('ex.academic_year_id', $f['academic_year_id']);
            }

            if (!empty($f['class_id'])) {
                $q->where('es.school_class_id', $f['class_id']);
            }

            if (!empty($f['section_id'])) {
                $q->where('es.section_id', $f['section_id']);
            }

            if (!empty($f['q'])) {
                $q->whereRaw($studentName . ' LIKE ?', ['%' . $f['q'] . '%']);
            }

            if ($report === 'performance') {

                $resultEntries = (clone $q)->count('er.id');

                $studentsAssessed = (clone $q)
                    ->distinct()
                    ->count('er.student_id');

                $avg = (clone $q)
                    ->selectRaw("
                        AVG(
                            CASE
                                WHEN es.maximum_marks > 0
                                THEN er.marks_obtained / es.maximum_marks * 100
                            END
                        ) as v
                    ")
                    ->value('v');

                $rowsQuery = clone $q;

                $data['rows'] = $rowsQuery
                    ->selectRaw("
                        {$studentName} as student,
                        sp.admission_number,
                        COUNT(er.id) as assessments,
                        ROUND(
                            AVG(
                                CASE
                                    WHEN es.maximum_marks > 0
                                    THEN er.marks_obtained / es.maximum_marks * 100
                                END
                            ),
                            2
                        ) as average_percentage,
                        MAX(er.grade) as grade
                    ")
                    ->groupBy(
                        'sp.id',
                        'sp.first_name',
                        'sp.middle_name',
                        'sp.last_name',
                        'sp.admission_number'
                    )
                    ->orderByDesc('average_percentage')
                    ->limit(1000)
                    ->get()
                    ->all();

                $data['cards'] = [
                    ['label' => 'Students assessed', 'value' => $studentsAssessed],
                    ['label' => 'Result entries', 'value' => $resultEntries],
                    [
                        'label' => 'Average score',
                        'value' => $avg !== null
                            ? number_format((float) $avg, 1) . '%'
                            : '—'
                    ]
                ];

            } elseif ($report === 'grades') {

                $rowsQuery = clone $q;

                $data['rows'] = $rowsQuery
                    ->selectRaw("
                        er.grade,
                        COUNT(*) as students,
                        ROUND(AVG(er.marks_obtained), 2) as average_marks
                    ")
                    ->groupBy('er.grade')
                    ->orderBy('er.grade')
                    ->get()
                    ->all();

                $publishedCount = (clone $q)
                    ->whereNotNull('er.published_at')
                    ->count();

                $allCount = (clone $q)->count();

                $data['cards'] = [
                    ['label' => 'Grade categories', 'value' => count($data['rows'])],
                    ['label' => 'Published result entries', 'value' => $publishedCount],
                    ['label' => 'All result entries', 'value' => $allCount]
                ];

            } else {

                $rowsQuery = clone $q;

                $data['rows'] = $rowsQuery
                    ->selectRaw("
                        ex.name as examination,
                        ex.exam_type,
                        ex.start_date,
                        COUNT(er.id) as result_entries,
                        ROUND(
                            AVG(
                                CASE
                                    WHEN es.maximum_marks > 0
                                    THEN er.marks_obtained / es.maximum_marks * 100
                                END
                            ),
                            2
                        ) as average_percentage
                    ")
                    ->groupBy(
                        'ex.id',
                        'ex.name',
                        'ex.exam_type',
                        'ex.start_date'
                    )
                    ->orderByDesc('ex.start_date')
                    ->get()
                    ->all();

                $examinationCount = DB::table('examinations')
                    ->where('school_id', $schoolId)
                    ->count();

                $resultCount = (clone $q)->count();

                $avg = (clone $q)
                    ->selectRaw("
                        AVG(
                            CASE
                                WHEN es.maximum_marks > 0
                                THEN er.marks_obtained / es.maximum_marks * 100
                            END
                        ) as v
                    ")
                    ->value('v');

                $data['cards'] = [
                    ['label' => 'Examinations', 'value' => $examinationCount],
                    ['label' => 'Result entries', 'value' => $resultCount],
                    [
                        'label' => 'Average score',
                        'value' => $avg !== null
                            ? number_format((float) $avg, 1) . '%'
                            : '—'
                    ]
                ];
            }

            $data['notes'][] = 'Academic results are calculated from exam_results joined to exam_subjects and examinations.';
        }

        /*
        |--------------------------------------------------------------------------
        | CLASS ANALYTICS
        |--------------------------------------------------------------------------
        */

        elseif ($report === 'classes') {

            $q = DB::table('school_classes as sc')
                ->leftJoin('sections as s', 's.school_class_id', '=', 'sc.id')
                ->leftJoin('student_enrollments as se', function ($join) {
                    $join->on('se.section_id', '=', 's.id')
                        ->where('se.status', '=', 'active');
                })
                ->where('sc.school_id', $schoolId);

            if (!empty($f['class_id'])) {
                $q->where('sc.id', $f['class_id']);
            }

            $rowsQuery = clone $q;

            $data['rows'] = $rowsQuery
                ->selectRaw("
                    sc.name as class_name,
                    sc.code,
                    s.name as section,
                    COUNT(DISTINCT se.student_id) as enrolled_students
                ")
                ->groupBy(
                    'sc.id',
                    'sc.name',
                    'sc.code',
                    's.id',
                    's.name'
                )
                ->orderBy('sc.name')
                ->orderBy('s.name')
                ->get()
                ->all();

            $data['cards'] = [
                [
                    'label' => 'Classes',
                    'value' => DB::table('school_classes')
                        ->where('school_id', $schoolId)
                        ->count()
                ],
                [
                    'label' => 'Sections',
                    'value' => DB::table('sections')
                        ->whereIn(
                            'school_class_id',
                            DB::table('school_classes')
                                ->select('id')
                                ->where('school_id', $schoolId)
                        )
                        ->count()
                ],
                [
                    'label' => 'Active enrollments',
                    'value' => DB::table('student_enrollments')
                        ->where('status', 'active')
                        ->whereIn(
                            'school_class_id',
                            DB::table('school_classes')
                                ->select('id')
                                ->where('school_id', $schoolId)
                        )
                        ->count()
                ]
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | FEE COLLECTION
        |--------------------------------------------------------------------------
        */

        elseif ($report === 'fees') {

            $q = DB::table('fee_invoices as fi')
                ->join('student_profiles as sp', 'sp.id', '=', 'fi.student_id')
                ->where('fi.school_id', $schoolId)
                ->whereNull('sp.deleted_at');

            $dateRange($q, 'fi.invoice_date');

            if (!empty($f['academic_year_id'])) {
                $q->where('fi.academic_year_id', $f['academic_year_id']);
            }

            if (!empty($f['q'])) {
                $q->where(function ($w) use ($f, $studentName) {
                    $w->where('fi.invoice_number', 'like', '%' . $f['q'] . '%')
                        ->orWhereRaw(
                            $studentName . ' LIKE ?',
                            ['%' . $f['q'] . '%']
                        );
                });
            }

            $invoiceCount = (clone $q)->count();

            $invoiced = (float) (clone $q)->sum('fi.total_amount');
            $collected = (float) (clone $q)->sum('fi.paid_amount');
            $outstanding = (float) (clone $q)->sum('fi.balance_amount');

            $data['cards'] = [
                ['label' => 'Invoices', 'value' => $invoiceCount],
                ['label' => 'Invoiced amount', 'value' => '₹' . number_format($invoiced, 2)],
                ['label' => 'Collected', 'value' => '₹' . number_format($collected, 2)],
                ['label' => 'Outstanding', 'value' => '₹' . number_format($outstanding, 2)]
            ];

            $rowsQuery = clone $q;

            $data['rows'] = $rowsQuery
                ->selectRaw("
                    fi.invoice_number,
                    fi.invoice_date,
                    fi.due_date,
                    {$studentName} as student,
                    fi.total_amount,
                    fi.paid_amount,
                    fi.balance_amount,
                    fi.status
                ")
                ->orderByDesc('fi.invoice_date')
                ->limit(1000)
                ->get()
                ->all();

            $data['notes'][] = 'Collected and outstanding amounts use the invoice paid_amount and balance_amount fields to avoid double-counting payment transactions.';
        }

        /*
        |--------------------------------------------------------------------------
        | TEACHER PERFORMANCE / STAFF ATTENDANCE
        |--------------------------------------------------------------------------
        */

        elseif ($report === 'teachers') {

            $q = DB::table('employee_attendance as ea')
                ->join('staff_profiles as st', 'st.id', '=', 'ea.staff_profile_id')
                ->where('st.school_id', $schoolId)
                ->whereNull('st.deleted_at');

            $dateRange($q, 'ea.attendance_date');

            if (!empty($f['q'])) {
                $q->whereRaw(
                    "CONCAT_WS(' ', st.first_name, st.middle_name, st.last_name) LIKE ?",
                    ['%' . $f['q'] . '%']
                );
            }

            // Calculate counts from the original ungrouped query.
            $attendanceEntries = (clone $q)->count('ea.id');

            $attendedDays = (clone $q)
                ->whereIn(DB::raw('LOWER(ea.status)'), ['present', 'late'])
                ->count('ea.id');

            // Use a separate clone for the grouped teacher table.
            $rowsQuery = clone $q;

            $data['rows'] = $rowsQuery
                ->selectRaw("
                    CONCAT_WS(
                        ' ',
                        st.first_name,
                        st.middle_name,
                        st.last_name
                    ) as teacher,
                    st.employee_number,
                    st.designation,
                    COUNT(ea.id) as attendance_days,
                    SUM(
                        CASE
                            WHEN LOWER(ea.status) IN ('present', 'late')
                            THEN 1
                            ELSE 0
                        END
                    ) as attended_days
                ")
                ->groupBy(
                    'st.id',
                    'st.first_name',
                    'st.middle_name',
                    'st.last_name',
                    'st.employee_number',
                    'st.designation'
                )
                ->orderBy('teacher')
                ->get()
                ->all();

            $staffCount = DB::table('staff_profiles')
                ->where('school_id', $schoolId)
                ->whereNull('deleted_at')
                ->count();

            $data['cards'] = [
                ['label' => 'Staff members', 'value' => $staffCount],
                ['label' => 'Attendance entries', 'value' => $attendanceEntries],
                ['label' => 'Staff attendance days', 'value' => $attendedDays]
            ];

            $data['notes'][] = 'This page reports staff attendance. Assessment attribution is not inferred because the supplied schema does not establish a teacher-to-subject assignment for every result.';
        }

        /*
        |--------------------------------------------------------------------------
        | CUSTOM REPORTS
        |--------------------------------------------------------------------------
        */

        else {

            $academicYearsCount = DB::table('academic_years')
                ->where('school_id', $schoolId)
                ->count();

            $data['cards'] = [
                ['label' => 'Available categories', 'value' => 7],
                ['label' => 'Academic years', 'value' => $academicYearsCount]
            ];

            $data['rows'] = [
                (object) ['report' => 'Attendance', 'route' => 'attendance'],
                (object) ['report' => 'Academic', 'route' => 'academic'],
                (object) ['report' => 'Student Performance', 'route' => 'performance'],
                (object) ['report' => 'Class Analytics', 'route' => 'classes'],
                (object) ['report' => 'Grade', 'route' => 'grades'],
                (object) ['report' => 'Fee Collection', 'route' => 'fees'],
                (object) ['report' => 'Teacher Performance', 'route' => 'teachers']
            ];
        }

        return $data;
    }
}
