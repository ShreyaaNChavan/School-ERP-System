<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Campus;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\StaffProfile;
use App\Models\ClassSubject;
use App\Models\Timetable;
use App\Models\TimetablePeriod;
use App\Models\TimetableEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;

class TimetableController extends Controller
{
    private array $days = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
    ];

    public function index(Request $request)
    {
        $query = Timetable::with([
            'schoolClass',
            'section',
            'academicYear',
            'campus',
        ])->latest();

        if ($request->filled('academic_year_id')) {
            $query->where(
                'academic_year_id',
                $request->academic_year_id
            );
        }

        if ($request->filled('school_class_id')) {
            $query->where(
                'school_class_id',
                $request->school_class_id
            );
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('schoolClass', function ($class) use ($search) {
                        $class->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('section', function ($section) use ($search) {
                        $section->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $timetables = $query->paginate(12)->withQueryString();

        $stats = [
            'total' => Timetable::count(),
            'published' => Timetable::where('status', 'published')->count(),
            'draft' => Timetable::where('status', 'draft')->count(),
            'archived' => Timetable::where('status', 'archived')->count(),
        ];

        return view('timetables.index', array_merge(
            compact('timetables', 'stats'),
            $this->formData()
        ));
    }

    public function create()
    {
        return view(
            'timetables.create',
            $this->formData()
        );
    }

    public function store(Request $request)
    {
        $data = $this->validateTimetable($request);

        $timetable = DB::transaction(function () use ($data) {
            $version = Timetable::where('academic_year_id', $data['academic_year_id'])
                ->where('section_id', $data['section_id'])
                ->max('version') + 1;

            $timetable = Timetable::create([
                'school_id' => $data['school_id'],
                'campus_id' => $data['campus_id'],
                'academic_year_id' => $data['academic_year_id'],
                'school_class_id' => $data['school_class_id'],
                'section_id' => $data['section_id'],
                'created_by' => auth()->id(),
                'name' => $data['name'],
                'version' => $version,
                'effective_from' => $data['effective_from'] ?? null,
                'effective_until' => $data['effective_until'] ?? null,
                'status' => 'draft',
            ]);

            $this->savePeriods($timetable, $data['periods']);

            return $timetable;
        });

        return redirect()
            ->route('timetables.show', $timetable)
            ->with('success', 'Timetable created successfully.');
    }

    public function show(Timetable $timetable)
    {
        $timetable->load([
            'school',
            'campus',
            'academicYear',
            'schoolClass',
            'section',
            'periods.entries.classSubject.subject',
            'periods.entries.teacher',
        ]);

        return view('timetables.show', compact('timetable'));
    }

    public function edit(Timetable $timetable)
    {
        abort_unless($timetable->status === 'draft', 403);

        $timetable->load([
            'periods.entries',
        ]);

        return view(
            'timetables.edit',
            array_merge(
                $this->formData(),
                compact('timetable')
            )
        );
    }

    public function update(Request $request, Timetable $timetable)
    {
        abort_unless($timetable->status === 'draft', 403);

        $data = $this->validateTimetable($request, $timetable);

        DB::transaction(function () use ($data, $timetable) {
            $timetable->update([
                'school_id' => $data['school_id'],
                'campus_id' => $data['campus_id'],
                'academic_year_id' => $data['academic_year_id'],
                'school_class_id' => $data['school_class_id'],
                'section_id' => $data['section_id'],
                'name' => $data['name'],
                'effective_from' => $data['effective_from'] ?? null,
                'effective_until' => $data['effective_until'] ?? null,
            ]);

            // Replace the draft schedule's periods and entries.
            $timetable->periods()->delete();

            $this->savePeriods($timetable, $data['periods']);
        });

        return redirect()
            ->route('timetables.show', $timetable)
            ->with('success', 'Timetable updated successfully.');
    }

    public function destroy(Timetable $timetable)
    {
        abort_unless($timetable->status !== 'published', 403);

        $timetable->delete();

        return redirect()
            ->route('timetables.index')
            ->with('success', 'Timetable deleted successfully.');
    }

    public function publish(Timetable $timetable)
    {
        abort_unless($timetable->status === 'draft', 403);

        $alreadyPublished = Timetable::where(
            'academic_year_id',
            $timetable->academic_year_id
        )
            ->where('section_id', $timetable->section_id)
            ->where('status', 'published')
            ->exists();

        if ($alreadyPublished) {
            throw ValidationException::withMessages([
                'timetable' => 'Another timetable is already published for this section and academic year. Archive it before publishing this version.',
            ]);
        }

        $conflicts = $this->findConflicts($timetable);

        if (count($conflicts)) {
            throw ValidationException::withMessages([
                'conflicts' => $conflicts,
            ]);
        }

        $timetable->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return back()->with(
            'success',
            'Timetable published successfully.'
        );
    }

    public function archive(Timetable $timetable)
    {
        abort_unless($timetable->status === 'published', 403);

        $timetable->update([
            'status' => 'archived',
        ]);

        return back()->with(
            'success',
            'Timetable archived successfully.'
        );
    }

    private function formData(): array
    {
        return [
            'schools' => School::orderBy('name')->get(),
            'campuses' => Campus::where('status', 'active')
                ->orderBy('name')->get(),
            'academicYears' => AcademicYear::orderByDesc('start_date')->get(),
            'classes' => SchoolClass::where('is_active', 1)
                ->orderBy('grade_level')->get(),
            'sections' => Section::where('is_active', 1)
                ->orderBy('name')->get(),
            'teachers' => StaffProfile::where('status', 'active')
                ->whereNull('deleted_at')
                ->orderBy('first_name')
                ->get(),
            'classSubjects' => ClassSubject::with([
                'subject',
                'schoolClass',
            ])->get(),
            'days' => $this->days,
        ];
    }

    private function validateTimetable(
        Request $request,
        ?Timetable $timetable = null
    ): array {
        $data = $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'campus_id' => ['required', 'exists:campuses,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'section_id' => ['required', 'exists:sections,id'],

            'name' => ['required', 'string', 'max:255'],
            'effective_from' => ['nullable', 'date'],
            'effective_until' => ['nullable', 'date', 'after_or_equal:effective_from'],

            'periods' => ['required', 'array', 'min:1'],
            'periods.*.period_number' => ['required', 'integer', 'min:1'],
            'periods.*.name' => ['required', 'string', 'max:255'],
            'periods.*.period_type' => [
                'required',
                Rule::in(['teaching', 'break']),
            ],
            'periods.*.start_time' => ['required', 'date_format:H:i'],
            'periods.*.end_time' => ['required', 'date_format:H:i'],
            'periods.*.entries' => ['nullable', 'array'],

            'periods.*.entries.*.class_subject_id' => [
                'nullable',
                'exists:class_subjects,id',
            ],
            'periods.*.entries.*.staff_profile_id' => [
                'nullable',
                'exists:staff_profiles,id',
            ],
            'periods.*.entries.*.room_number' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $section = Section::with('schoolClass')
            ->findOrFail($data['section_id']);

        if (
            $section->school_class_id != $data['school_class_id'] ||
            $section->campus_id != $data['campus_id'] ||
            $section->schoolClass->school_id != $data['school_id']
        ) {
            throw ValidationException::withMessages([
                'section_id' => 'The selected section does not belong to the selected school, campus and class.',
            ]);
        }

        if (
            $timetable &&
            (
                $timetable->academic_year_id != $data['academic_year_id'] ||
                $timetable->section_id != $data['section_id']
            )
        ) {
            $versionExists = Timetable::where(
                'academic_year_id',
                $data['academic_year_id']
            )
                ->where('section_id', $data['section_id'])
                ->where('version', $timetable->version)
                ->where('id', '!=', $timetable->id)
                ->exists();

            if ($versionExists) {
                throw ValidationException::withMessages([
                    'section_id' => 'This timetable version number is already in use for the selected section and academic year.',
                ]);
            }
        }

        $periods = $data['periods'];

        usort(
            $periods,
            fn($a, $b) =>
                $a['start_time'] <=> $b['start_time']
        );

        for ($i = 0; $i < count($periods); $i++) {
            if ($periods[$i]['end_time'] <= $periods[$i]['start_time']) {
                throw ValidationException::withMessages([
                    'periods' => 'Period end time must be later than its start time.',
                ]);
            }

            if (
                $i > 0 &&
                $periods[$i]['start_time'] < $periods[$i - 1]['end_time']
            ) {
                throw ValidationException::withMessages([
                    'periods' => 'Two periods cannot overlap in the timetable.',
                ]);
            }
        }

        $numbers = array_column($data['periods'], 'period_number');

        if (count($numbers) !== count(array_unique($numbers))) {
            throw ValidationException::withMessages([
                'periods' => 'Period numbers must be unique.',
            ]);
        }

        foreach ($data['periods'] as $period) {
            if ($period['period_type'] === 'break') {
                continue;
            }

            foreach ($period['entries'] ?? [] as $day => $entry) {
                if (!in_array($day, $this->days, true)) {
                    throw ValidationException::withMessages([
                        'periods' => 'Invalid day of the week.',
                    ]);
                }

                $subjectId = $entry['class_subject_id'] ?? null;
                $teacherId = $entry['staff_profile_id'] ?? null;

                if (!$subjectId && !$teacherId) {
                    continue;
                }

                if (!$subjectId || !$teacherId) {
                    throw ValidationException::withMessages([
                        'periods' => 'Both subject and teacher are required for a teaching period.',
                    ]);
                }

                $classSubject = ClassSubject::with('subject')
                    ->findOrFail($subjectId);

                if (
                    $classSubject->school_class_id != $data['school_class_id'] ||
                    $classSubject->subject->school_id != $data['school_id']
                ) {
                    throw ValidationException::withMessages([
                        'periods' => 'The selected subject is not assigned to this class.',
                    ]);
                }

                $assignmentExists = DB::table('teaching_assignments')
                    ->where('staff_profile_id', $teacherId)
                    ->where('class_subject_id', $subjectId)
                    ->where('section_id', $data['section_id'])
                    ->where('academic_year_id', $data['academic_year_id'])
                    ->exists();

                if (!$assignmentExists) {
                    throw ValidationException::withMessages([
                        'periods' => 'The selected teacher is not assigned to teach this subject in this section.',
                    ]);
                }
            }
        }

        return $data;
    }

    private function savePeriods(
        Timetable $timetable,
        array $periods
    ): void {
        foreach ($periods as $periodData) {
            $period = $timetable->periods()->create([
                'period_number' => $periodData['period_number'],
                'name' => $periodData['name'],
                'period_type' => $periodData['period_type'],
                'start_time' => $periodData['start_time'],
                'end_time' => $periodData['end_time'],
                'is_active' => true,
            ]);

            if ($periodData['period_type'] === 'break') {
                continue;
            }

            foreach ($periodData['entries'] ?? [] as $day => $entry) {
                if (
                    empty($entry['class_subject_id']) ||
                    empty($entry['staff_profile_id'])
                ) {
                    continue;
                }

                $period->entries()->create([
                    'timetable_id' => $timetable->id,
                    'class_subject_id' => $entry['class_subject_id'],
                    'staff_profile_id' => $entry['staff_profile_id'],
                    'day_of_week' => $day,
                    'room_number' => $entry['room_number'] ?? null,
                    'notes' => null,
                ]);
            }
        }
    }

    private function findConflicts(Timetable $timetable): array
    {
        $conflicts = [];

        $currentEntries = TimetableEntry::with('period')
            ->where('timetable_id', $timetable->id)
            ->get();

        $otherEntries = DB::table('timetable_entries as e')
            ->join('timetable_periods as p', 'p.id', '=', 'e.timetable_period_id')
            ->join('timetables as t', 't.id', '=', 'e.timetable_id')
            ->where('t.status', 'published')
            ->where('t.id', '!=', $timetable->id)
            ->where('t.school_id', $timetable->school_id)
            ->select(
                'e.*',
                'p.start_time',
                'p.end_time',
                't.section_id',
                't.campus_id'
            )
            ->get();

        foreach ($currentEntries as $entry) {
            foreach ($otherEntries as $other) {
                if ($entry->day_of_week !== $other->day_of_week) {
                    continue;
                }

                $start = $entry->period->start_time;
                $end = $entry->period->end_time;

                $overlap = $start < $other->end_time &&
                    $end > $other->start_time;

                if (!$overlap) {
                    continue;
                }

                if (
                    $entry->staff_profile_id &&
                    $entry->staff_profile_id == $other->staff_profile_id
                ) {
                    $conflicts[] = "Teacher conflict on {$entry->day_of_week}: staff ID {$entry->staff_profile_id}.";
                }

                if ($timetable->section_id == $other->section_id) {
                    $conflicts[] = "Class/section conflict on {$entry->day_of_week}.";
                }

                if (
                    $entry->room_number &&
                    $other->room_number &&
                    $entry->room_number === $other->room_number &&
                    $timetable->campus_id == $other->campus_id
                ) {
                    $conflicts[] = "Room conflict on {$entry->day_of_week}: room {$entry->room_number}.";
                }
            }
        }

        return array_values(array_unique($conflicts));
    }
}
