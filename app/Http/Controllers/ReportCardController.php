<?php

namespace App\Http\Controllers;

use App\Models\ReportCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReportCardController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('report_cards as rc')
            ->join('student_profiles as sp', 'sp.id', '=', 'rc.student_id')
            ->join('examinations as e', 'e.id', '=', 'rc.examination_id')
            ->leftJoin('student_enrollments as se', function ($join) {
                $join->on('se.student_id', '=', 'sp.id')
                    ->on('se.academic_year_id', '=', 'e.academic_year_id');
            })
            ->leftJoin('school_classes as sc', 'sc.id', '=', 'se.school_class_id')
            ->leftJoin('sections as sec', 'sec.id', '=', 'se.section_id')
            ->select(
                'rc.*',
                'sp.first_name',
                'sp.middle_name',
                'sp.last_name',
                'sp.admission_number',
                'e.name as examination_name',
                'e.academic_year_id',
                'sc.name as class_name',
                'sec.name as section_name'
            );

        if ($request->filled('examination_id')) {
            $query->where('rc.examination_id', $request->examination_id);
        }

        if ($request->filled('school_class_id')) {
            $query->where('se.school_class_id', $request->school_class_id);
        }

        if ($request->filled('section_id')) {
            $query->where('se.section_id', $request->section_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('sp.first_name', 'like', "%{$search}%")
                    ->orWhere('sp.last_name', 'like', "%{$search}%")
                    ->orWhere('sp.admission_number', 'like', "%{$search}%");
            });
        }

        $reportCards = $query
            ->orderBy('sc.name')
            ->orderBy('sec.name')
            ->orderBy('sp.first_name')
            ->paginate(15)
            ->withQueryString();

        $examinations = DB::table('examinations')
            ->orderByDesc('start_date')
            ->get();

        $classes = DB::table('school_classes')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();

        $sections = DB::table('sections')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();

        $stats = [
            'total' => DB::table('report_cards')->count(),
            'published' => DB::table('report_cards')
                ->whereNotNull('published_at')->count(),
            'pending' => DB::table('report_cards')
                ->whereNull('published_at')->count(),
            'average' => round(
                DB::table('report_cards')->avg('percentage') ?? 0,
                2
            ),
        ];

        return view('report-cards.index', compact(
            'reportCards',
            'examinations',
            'classes',
            'sections',
            'stats'
        ));
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'examination_id' => ['required', 'exists:examinations,id'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'section_id' => ['required', 'exists:sections,id'],
        ]);

        $exam = DB::table('examinations')
            ->where('id', $validated['examination_id'])
            ->first();

        $section = DB::table('sections')
            ->where('id', $validated['section_id'])
            ->where('school_class_id', $validated['school_class_id'])
            ->first();

        if (!$section) {
            throw ValidationException::withMessages([
                'section_id' => 'The selected section does not belong to this class.',
            ]);
        }

        $examSubjects = DB::table('exam_subjects')
            ->where('examination_id', $exam->id)
            ->where('school_class_id', $validated['school_class_id'])
            ->where('section_id', $validated['section_id'])
            ->get();

        if ($examSubjects->isEmpty()) {
            return back()->with('error', 'No examination subjects are configured for this class and section.');
        }

        $students = DB::table('student_enrollments as se')
            ->join('student_profiles as sp', 'sp.id', '=', 'se.student_id')
            ->where('se.academic_year_id', $exam->academic_year_id)
            ->where('se.school_class_id', $validated['school_class_id'])
            ->where('se.section_id', $validated['section_id'])
            ->where('se.status', 'active')
            ->where('sp.status', 'active')
            ->select('sp.id')
            ->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'No active students found in the selected section.');
        }

        $generated = 0;
        $pending = 0;

        DB::transaction(function () use (
            $students,
            $exam,
            $examSubjects,
            &$generated,
            &$pending
        ) {
            $rankCandidates = [];

            foreach ($students as $student) {
                $results = DB::table('exam_results')
                    ->where('student_id', $student->id)
                    ->whereIn('exam_subject_id', $examSubjects->pluck('id'))
                    ->get()
                    ->keyBy('exam_subject_id');

                $complete = true;
                $totalMarks = 0;
                $maximumMarks = 0;

                foreach ($examSubjects as $subject) {
                    $maximumMarks += (float) $subject->maximum_marks;

                    $result = $results->get($subject->id);

                    if (!$result || (
                        $result->marks_obtained === null &&
                        $result->grade !== 'AB'
                    )) {
                        $complete = false;
                        break;
                    }

                    if ($result->grade !== 'AB') {
                        $totalMarks += (float) $result->marks_obtained;
                    }
                }

                if (!$complete || $maximumMarks <= 0) {
                    $pending++;
                    continue;
                }

                $percentage = round(
                    ($totalMarks / $maximumMarks) * 100,
                    2
                );

                $grade = $this->gradeFromPercentage($percentage);

                $card = ReportCard::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'examination_id' => $exam->id,
                    ],
                    [
                        'total_marks' => $totalMarks,
                        'percentage' => $percentage,
                        'overall_grade' => $grade,
                        'rank' => null,
                        'published_at' => null,
                    ]
                );

                $rankCandidates[] = [
                    'id' => $card->id,
                    'percentage' => $percentage,
                ];

                $generated++;
            }

            usort($rankCandidates, function ($a, $b) {
                return $b['percentage'] <=> $a['percentage'];
            });

            $rank = 0;
            $previousPercentage = null;

            foreach ($rankCandidates as $index => $candidate) {
                if ($previousPercentage === null ||
                    $candidate['percentage'] < $previousPercentage) {
                    $rank = $index + 1;
                }

                DB::table('report_cards')
                    ->where('id', $candidate['id'])
                    ->update([
                        'rank' => $rank,
                        'updated_at' => now(),
                    ]);

                $previousPercentage = $candidate['percentage'];
            }
        });

        return redirect()
            ->route('report-cards.index', [
                'examination_id' => $exam->id,
                'school_class_id' => $validated['school_class_id'],
                'section_id' => $validated['section_id'],
            ])
            ->with(
                'success',
                "{$generated} report cards generated. {$pending} students still have incomplete marks."
            );
    }

    public function show(ReportCard $reportCard)
    {
        $reportCard->load(['student', 'examination']);

        $student = $reportCard->student;
        $exam = $reportCard->examination;

        $enrollment = DB::table('student_enrollments as se')
            ->join('school_classes as sc', 'sc.id', '=', 'se.school_class_id')
            ->join('sections as sec', 'sec.id', '=', 'se.section_id')
            ->where('se.student_id', $student->id)
            ->where('se.academic_year_id', $exam->academic_year_id)
            ->select(
                'se.school_class_id',
                'se.section_id',
                'sc.name as class_name',
                'sec.name as section_name'
            )
            ->first();

        abort_unless($enrollment, 404);

        $subjects = DB::table('exam_subjects as es')
            ->join('subjects as s', 's.id', '=', 'es.subject_id')
            ->leftJoin('exam_results as er', function ($join) use ($student) {
                $join->on('er.exam_subject_id', '=', 'es.id')
                    ->where('er.student_id', '=', $student->id);
            })
            ->where('es.examination_id', $exam->id)
            ->where('es.school_class_id', $enrollment->school_class_id)
            ->where('es.section_id', $enrollment->section_id)
            ->select(
                's.name as subject_name',
                's.code as subject_code',
                'es.maximum_marks',
                'es.passing_marks',
                'er.marks_obtained',
                'er.grade',
                'er.remarks'
            )
            ->orderBy('s.name')
            ->get();

        $guardian = DB::table('student_guardians as sg')
            ->join('guardians as g', 'g.id', '=', 'sg.guardian_id')
            ->where('sg.student_id', $student->id)
            ->orderByDesc('sg.is_primary')
            ->select('g.full_name', 'g.relationship', 'g.phone')
            ->first();

        $teacher = DB::table('teaching_assignments as ta')
            ->join('class_subjects as cs', 'cs.id', '=', 'ta.class_subject_id')
            ->join('staff_profiles as sp', 'sp.id', '=', 'ta.staff_profile_id')
            ->where('ta.section_id', $enrollment->section_id)
            ->where('ta.academic_year_id', $exam->academic_year_id)
            ->where('cs.school_class_id', $enrollment->school_class_id)
            ->whereNull('sp.deleted_at')
            ->select(
                'sp.first_name',
                'sp.middle_name',
                'sp.last_name',
                'sp.designation'
            )
            ->first();

        $attendance = $this->attendancePercentage(
            $student->id,
            $enrollment->section_id,
            $exam->academic_year_id
        );

        $school = DB::table('schools')
            ->where('id', $exam->school_id)
            ->first();

        $rankCount = DB::table('report_cards as rc')
            ->join('student_enrollments as se', function ($join) use ($exam) {
                $join->on('se.student_id', '=', 'rc.student_id')
                    ->where('se.academic_year_id', '=', $exam->academic_year_id);
            })
            ->where('rc.examination_id', $exam->id)
            ->where('se.school_class_id', $enrollment->school_class_id)
            ->where('se.section_id', $enrollment->section_id)
            ->count();

        return view('report-cards.show', compact(
            'reportCard',
            'student',
            'exam',
            'enrollment',
            'subjects',
            'guardian',
            'teacher',
            'attendance',
            'school',
            'rankCount'
        ));
    }

    public function publish(Request $request, ReportCard $reportCard)
    {
        if ($reportCard->published_at) {
            return back()->with('error', 'This report card has already been published.');
        }

        $reportCard->update([
            'published_at' => now(),
        ]);

        return back()->with('success', 'Report card published successfully.');
    }

    public function publishSection(Request $request)
    {
        $validated = $request->validate([
            'examination_id' => ['required', 'exists:examinations,id'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'section_id' => ['required', 'exists:sections,id'],
        ]);

        $exam = DB::table('examinations')
            ->where('id', $validated['examination_id'])
            ->first();

        $section = DB::table('sections')
            ->where('id', $validated['section_id'])
            ->where('school_class_id', $validated['school_class_id'])
            ->first();

        if (!$section) {
            return back()->with('error', 'Invalid class and section selection.');
        }

        $studentIds = DB::table('student_enrollments')
            ->where('academic_year_id', $exam->academic_year_id)
            ->where('school_class_id', $validated['school_class_id'])
            ->where('section_id', $validated['section_id'])
            ->where('status', 'active')
            ->pluck('student_id');

        $cards = ReportCard::where('examination_id', $exam->id)
            ->whereIn('student_id', $studentIds)
            ->get();

        if ($cards->isEmpty()) {
            return back()->with('error', 'Generate report cards before publishing.');
        }

        $missing = $studentIds->diff($cards->pluck('student_id'));

        if ($missing->isNotEmpty()) {
            return back()->with('error', 'Some students do not have generated report cards. Generate all cards first.');
        }

        ReportCard::whereIn('id', $cards->pluck('id'))
            ->whereNull('published_at')
            ->update(['published_at' => now()]);

        return back()->with('success', $cards->count() . ' report cards published successfully.');
    }

    private function gradeFromPercentage(float $percentage): string
    {
        return match (true) {
            $percentage >= 91 => 'A1',
            $percentage >= 81 => 'A2',
            $percentage >= 71 => 'B1',
            $percentage >= 61 => 'B2',
            $percentage >= 51 => 'C1',
            $percentage >= 41 => 'C2',
            $percentage >= 33 => 'D',
            default => 'E',
        };
    }

    private function attendancePercentage(
        int $studentId,
        int $sectionId,
        int $academicYearId
    ): float {
        $records = DB::table('student_attendance as sa')
            ->join('attendance_sessions as ats', 'ats.id', '=', 'sa.attendance_session_id')
            ->where('sa.student_id', $studentId)
            ->where('ats.section_id', $sectionId)
            ->where('ats.academic_year_id', $academicYearId)
            ->whereNotIn('sa.status', ['leave', 'holiday'])
            ->select('sa.status')
            ->get();

        $eligible = $records->count();

        if ($eligible === 0) {
            return 0;
        }

        $attended = $records->sum(function ($record) {
            return match ($record->status) {
                'present', 'late' => 1,
                'halfday' => 0.5,
                default => 0,
            };
        });

        return round(($attended / $eligible) * 100, 2);
    }
}
