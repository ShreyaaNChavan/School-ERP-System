<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CampusController extends Controller
{
    public function index(Request $request)
    {
        $campuses = Campus::with('school.organization')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhereHas('school', function ($school) use ($search) {
                            $school->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('school.organization', function ($organization) use ($search) {
                            $organization->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->school_id, function ($query, $schoolId) {
                $query->where('school_id', $schoolId);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10);

        $schools = School::where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('campuses.index', compact('campuses', 'schools'));
    }

    public function create()
    {
        $schools = School::where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('campuses.create', compact('schools'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateCampus($request);

        $validated['status'] = 'active';

        Campus::create($validated);

        return redirect()
            ->route('campuses.index')
            ->with('success', 'Campus created successfully.');
    }

    public function edit(Campus $campus)
    {
        $schools = School::orderBy('name')->get();

        return view('campuses.edit', compact('campus', 'schools'));
    }

    public function update(Request $request, Campus $campus)
    {
        $validated = $this->validateCampus($request, $campus);

        $campus->update($validated);

        return redirect()
            ->route('campuses.index')
            ->with('success', 'Campus updated successfully.');
    }

    public function toggleStatus(Campus $campus)
    {
        $campus->status = $campus->status === 'active'
            ? 'inactive'
            : 'active';

        $campus->save();

        return redirect()
            ->route('campuses.index')
            ->with('success', 'Campus status updated successfully.');
    }

    private function validateCampus(
        Request $request,
        ?Campus $campus = null
    ): array {
        return $request->validate([
            'school_id' => [
                'required',
                'exists:schools,id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('campuses', 'code')
                    ->where('school_id', $request->school_id)
                    ->ignore($campus?->id),
            ],

            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],

            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],

            'phone' => ['nullable', 'string', 'max:30'],

            'timezone' => [
                'required',
                'string',
                'max:100',
            ],
        ]);
    }
}