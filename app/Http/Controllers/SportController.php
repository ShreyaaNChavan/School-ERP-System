<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SportController extends Controller
{
    private function schoolId(): int
    {
        return (int) session('school_id', 1);
    }

    public function dashboard()
    {
        $q = DB::table('sports')
            ->where('school_id', $this->schoolId());

        return view('sports.dashboard', [
            'total' => (clone $q)->count(),
            'active' => (clone $q)->where('is_active', 1)->count(),
            'recent' => (clone $q)->latest()->limit(6)->get(),
        ]);
    }

    public function index(Request $request)
    {
        $q = DB::table('sports')
            ->where('school_id', $this->schoolId());

        if ($request->filled('search')) {
            $q->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $q->where(
                'is_active',
                $request->status === 'active' ? 1 : 0
            );
        }

        $items = $q->select('sports.*')
            ->selectRaw("IF(is_active=1,'active','inactive') as status")
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('sports.index', compact('items'));
    }

    public function create()
    {
        $campuses = DB::table('campuses')
            ->where('school_id', $this->schoolId())
            ->orderBy('name')
            ->get();

        $staff = DB::table('staff_profiles')
            ->where('school_id', $this->schoolId())
            ->whereNull('deleted_at')
            ->orderBy('first_name')
            ->get();

        return view('sports.create', compact('campuses', 'staff'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:80',
            'description' => 'nullable|string',
            'campus_id' => [
                'nullable',
                Rule::exists('campuses', 'id')
                    ->where('school_id', $this->schoolId()),
            ],
            'is_active' => 'nullable|boolean',
        ]);

        $data['school_id'] = $this->schoolId();
        $data['is_active'] = $request->boolean('is_active');

        if (!$request->filled('campus_id')) {
            $data['campus_id'] = null;
        }

        DB::table('sports')->insert(
            $data + [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return redirect()
            ->route('sports.index')
            ->with('success', 'Sport created successfully.');
    }

    public function show(int $id)
    {
        $item = DB::table('sports')
            ->where('school_id', $this->schoolId())
            ->select('sports.*')
            ->selectRaw("IF(is_active=1,'active','inactive') as status")
            ->find($id);

        abort_unless($item, 404);

        return view('sports.show', compact('item'));
    }

    public function edit(int $id)
    {
        $item = DB::table('sports')
            ->where('school_id', $this->schoolId())
            ->select('sports.*')
            ->selectRaw("IF(is_active=1,'active','inactive') as status")
            ->find($id);

        abort_unless($item, 404);

        $campuses = DB::table('campuses')
            ->where('school_id', $this->schoolId())
            ->orderBy('name')
            ->get();

        $staff = DB::table('staff_profiles')
            ->where('school_id', $this->schoolId())
            ->whereNull('deleted_at')
            ->orderBy('first_name')
            ->get();

        return view('sports.edit', compact('item', 'campuses', 'staff'));
    }

    public function update(Request $request, int $id)
    {
        $item = DB::table('sports')
            ->where('school_id', $this->schoolId())
            ->find($id);

        abort_unless($item, 404);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:80',
            'description' => 'nullable|string',
            'campus_id' => [
                'nullable',
                Rule::exists('campuses', 'id')
                    ->where('school_id', $this->schoolId()),
            ],
            'is_active' => 'nullable|boolean',
        ]);

        // FIX: Use the controller's schoolId() method.
        $data['school_id'] = $this->schoolId();
        $data['is_active'] = $request->boolean('is_active');

        if (!$request->filled('campus_id')) {
            $data['campus_id'] = null;
        }

        $data['updated_at'] = now();

        DB::table('sports')
            ->where('id', $id)
            ->where('school_id', $this->schoolId())
            ->update($data);

        return redirect()
            ->route('sports.show', $id)
            ->with('success', 'Sport updated successfully.');
    }

    public function destroy(int $id)
    {
        $deleted = DB::table('sports')
            ->where('school_id', $this->schoolId())
            ->where('id', $id)
            ->delete();

        abort_unless($deleted, 404);

        return redirect()
            ->route('sports.index')
            ->with('success', 'Sport deleted successfully.');
    }
}
