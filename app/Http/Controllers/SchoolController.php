<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SchoolController extends Controller
{
    public function index(Request $request)
    {
        $schools = School::with('organization')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhereHas('organization', function ($org) use ($search) {
                            $org->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->organization_id, function ($query, $organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10);

        $organizations = Organization::orderBy('name')->get();

        return view('schools.index', compact('schools', 'organizations'));
    }

    public function create()
    {
        $organizations = Organization::where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('schools.create', compact('organizations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'organization_id' => ['required', 'exists:organizations,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('schools', 'code')
                    ->where('organization_id', $request->organization_id),
            ],
            'school_type' => ['nullable', 'string', 'max:100'],
            'affiliation_number' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'timezone' => ['required', 'string', 'max:100'],
            'default_currency' => ['required', 'string', 'size:3'],
        ]);

        $validated['slug'] = $this->generateSlug($validated['name']);
        $validated['status'] = 'active';

        School::create($validated);

        return redirect()
            ->route('schools.index')
            ->with('success', 'School created successfully.');
    }

    public function edit(School $school)
    {
        $organizations = Organization::orderBy('name')->get();

        return view('schools.edit', compact('school', 'organizations'));
    }

    public function update(Request $request, School $school)
    {
        $validated = $request->validate([
            'organization_id' => ['required', 'exists:organizations,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('schools', 'code')
                    ->where('organization_id', $request->organization_id)
                    ->ignore($school->id),
            ],
            'school_type' => ['nullable', 'string', 'max:100'],
            'affiliation_number' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'timezone' => ['required', 'string', 'max:100'],
            'default_currency' => ['required', 'string', 'size:3'],
        ]);

        if ($school->name !== $validated['name']) {
            $validated['slug'] = $this->generateSlug(
                $validated['name'],
                $school->id
            );
        }

        $school->update($validated);

        return redirect()
            ->route('schools.index')
            ->with('success', 'School updated successfully.');
    }

    public function toggleStatus(School $school)
    {
        $school->status = $school->status === 'active'
            ? 'inactive'
            : 'active';

        $school->save();

        return redirect()
            ->route('schools.index')
            ->with('success', 'School status updated successfully.');
    }

    private function generateSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (
            School::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}