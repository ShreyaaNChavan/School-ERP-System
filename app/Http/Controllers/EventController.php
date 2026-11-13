<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    private function schoolId(): int
    {
        return (int) session('school_id', 1);
    }

    private function eventTypes(): array
    {
        return [
            'Academic',
            'Cultural',
            'Sports',
            'Celebration',
            'Workshop',
            'Other',
        ];
    }

    private function statuses(): array
    {
        return [
            'planned',
            'ongoing',
            'completed',
            'cancelled',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Events Dashboard
    |--------------------------------------------------------------------------
    */

    public function dashboard()
    {
        $schoolId = $this->schoolId();

        $total = DB::table('events')
            ->where('school_id', $schoolId)
            ->count();

        $upcoming = DB::table('events')
            ->where('school_id', $schoolId)
            ->where('starts_at', '>', now())
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->count();

        $ongoing = DB::table('events')
            ->where('school_id', $schoolId)
            ->where('status', 'ongoing')
            ->count();

        $completed = DB::table('events')
            ->where('school_id', $schoolId)
            ->where('status', 'completed')
            ->count();

        $recent = DB::table('events')
            ->where('school_id', $schoolId)
            ->orderByDesc('starts_at')
            ->take(8)
            ->get();

        return view('events.dashboard', compact(
            'total',
            'upcoming',
            'ongoing',
            'completed',
            'recent'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Event Listing
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $events = DB::table('events as e')
            ->leftJoin('campuses as c', 'c.id', '=', 'e.campus_id')
            ->where('e.school_id', $this->schoolId())

            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);

                $query->where(function ($q) use ($search) {
                    $q->where('e.title', 'like', "%{$search}%")
                        ->orWhere('e.venue', 'like', "%{$search}%")
                        ->orWhere('e.description', 'like', "%{$search}%");
                });
            })

            ->when($request->filled('type'), function ($query) use ($request) {
                $query->where('e.event_type', $request->type);
            })

            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('e.status', $request->status);
            })

            ->select(
                'e.*',
                'c.name as campus_name'
            )
            ->orderByDesc('e.starts_at')
            ->paginate(15)
            ->withQueryString();

        $types = $this->eventTypes();
        $statuses = $this->statuses();

        return view('events.index', compact(
            'events',
            'types',
            'statuses'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Create Event Form
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $campuses = DB::table('campuses')
            ->where('school_id', $this->schoolId())
            ->orderBy('name')
            ->get();

        $types = $this->eventTypes();
        $statuses = $this->statuses();

        return view('events.create', compact(
            'campuses',
            'types',
            'statuses'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Store Event
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_type' => [
                'nullable',
                'string',
                'max:50',
                Rule::in($this->eventTypes()),
            ],
            'campus_id' => [
                'nullable',
                'integer',
                Rule::exists('campuses', 'id')
                    ->where('school_id', $this->schoolId()),
            ],
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'venue' => 'nullable|string|max:255',
            'status' => [
                'required',
                'string',
                'max:30',
                Rule::in($this->statuses()),
            ],
        ]);

        $eventId = DB::table('events')->insertGetId([
            'school_id' => $this->schoolId(),
            'campus_id' => $data['campus_id'] ?? null,
            'created_by' => auth()->id(),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'event_type' => $data['event_type'] ?? null,
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'] ?? null,
            'venue' => $data['venue'] ?? null,
            'status' => $data['status'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('events.show', $eventId)
            ->with('success', 'Event created successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Event Details
    |--------------------------------------------------------------------------
    */

    public function show(int $event)
    {
        $record = $this->findEvent($event);

        $campus = $record->campus_id
            ? DB::table('campuses')
                ->where('id', $record->campus_id)
                ->where('school_id', $this->schoolId())
                ->first()
            : null;

        $registrationCount = DB::table('event_registrations')
            ->where('event_id', $record->id)
            ->count();

        $studentRegistrations = DB::table('event_registrations')
            ->where('event_id', $record->id)
            ->whereNotNull('student_id')
            ->count();

        $staffRegistrations = DB::table('event_registrations')
            ->where('event_id', $record->id)
            ->whereNotNull('staff_profile_id')
            ->count();

        $registrations = DB::table('event_registrations')
            ->where('event_id', $record->id)
            ->orderByDesc('registered_at')
            ->take(20)
            ->get();

        return view('events.show', compact(
            'record',
            'campus',
            'registrationCount',
            'studentRegistrations',
            'staffRegistrations',
            'registrations'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Edit Event Form
    |--------------------------------------------------------------------------
    */

    public function edit(int $event)
    {
        $record = $this->findEvent($event);

        $campuses = DB::table('campuses')
            ->where('school_id', $this->schoolId())
            ->orderBy('name')
            ->get();

        $types = $this->eventTypes();
        $statuses = $this->statuses();

        return view('events.edit', compact(
            'record',
            'campuses',
            'types',
            'statuses'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Update Event
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, int $event)
    {
        $record = $this->findEvent($event);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_type' => [
                'nullable',
                'string',
                'max:50',
                Rule::in($this->eventTypes()),
            ],
            'campus_id' => [
                'nullable',
                'integer',
                Rule::exists('campuses', 'id')
                    ->where('school_id', $this->schoolId()),
            ],
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'venue' => 'nullable|string|max:255',
            'status' => [
                'required',
                'string',
                'max:30',
                Rule::in($this->statuses()),
            ],
        ]);

        DB::table('events')
            ->where('id', $record->id)
            ->where('school_id', $this->schoolId())
            ->update([
                'campus_id' => $data['campus_id'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'event_type' => $data['event_type'] ?? null,
                'starts_at' => $data['starts_at'],
                'ends_at' => $data['ends_at'] ?? null,
                'venue' => $data['venue'] ?? null,
                'status' => $data['status'],
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('events.show', $record->id)
            ->with('success', 'Event updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Event
    |--------------------------------------------------------------------------
    */

    public function destroy(int $event)
    {
        $record = $this->findEvent($event);

        DB::transaction(function () use ($record) {
            DB::table('event_registrations')
                ->where('event_id', $record->id)
                ->delete();

            DB::table('events')
                ->where('id', $record->id)
                ->where('school_id', $this->schoolId())
                ->delete();
        });

        return redirect()
            ->route('events.index')
            ->with('success', 'Event deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Find Event Within Current School
    |--------------------------------------------------------------------------
    */

    private function findEvent(int $id): object
    {
        $record = DB::table('events')
            ->where('school_id', $this->schoolId())
            ->where('id', $id)
            ->first();

        abort_unless($record, 404, 'Event not found.');

        return $record;
    }
}
