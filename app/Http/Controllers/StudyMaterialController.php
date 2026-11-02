<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StudyMaterialController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('study_materials as sm')
            ->join('subjects as sub', 'sub.id', '=', 'sm.subject_id')
            ->join('sections as sec', 'sec.id', '=', 'sm.section_id')
            ->join('school_classes as sc', 'sc.id', '=', 'sec.school_class_id')
            ->leftJoin('staff_profiles as sp', 'sp.user_id', '=', 'sm.uploaded_by')
            ->select('sm.*', 'sub.name as subject_name', 'sec.name as section_name',
                'sc.name as class_name', 'sp.first_name as teacher_first_name',
                'sp.last_name as teacher_last_name');
        $query->when($request->filled('search'), fn($q) => $q->where('sm.title', 'like', '%' . $request->search . '%'));
        $query->when($request->filled('subject_id'), fn($q) => $q->where('sm.subject_id', $request->subject_id));
        $query->when($request->filled('material_type'), fn($q) => $q->where('sm.material_type', $request->material_type));
        $materials = $query->orderByDesc('sm.created_at')->paginate(10)->withQueryString();
        $subjects = DB::table('subjects')->where('is_active', 1)->orderBy('name')->get();
        $stats = [
            'total' => DB::table('study_materials')->count(),
            'published' => DB::table('study_materials')->where('is_published', 1)->count(),
            'draft' => DB::table('study_materials')->where('is_published', 0)->count(),
        ];
        return view('study-materials.index', compact('materials', 'subjects', 'stats'));
    }

    public function create()
    {
        return view('study-materials.form', $this->formData() + [
            'material' => null, 'pageTitle' => 'Upload Study Material',
            'formAction' => route('study-materials.store'), 'formMethod' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['file_path'] = $request->file('file')->store('study-materials', 'public');
        unset($data['file']);
        $data['uploaded_by'] = auth()->id();
        $data['created_at'] = now();
        $data['updated_at'] = now();
        $id = DB::table('study_materials')->insertGetId($data);
        return redirect()->route('study-materials.show', $id)->with('success', 'Study material uploaded successfully.');
    }

    public function show(string $id)
    {
        $material = DB::table('study_materials')->where('id', $id)->first();
        abort_unless($material, 404);
        return view('study-materials.show', compact('material'));
    }

    public function edit(string $id)
    {
        $material = DB::table('study_materials')->where('id', $id)->first();
        abort_unless($material, 404);
        return view('study-materials.form', $this->formData() + [
            'material' => $material, 'pageTitle' => 'Edit Study Material',
            'formAction' => route('study-materials.update', $id), 'formMethod' => 'PUT',
        ]);
    }

    public function update(Request $request, string $id)
    {
        $material = DB::table('study_materials')->where('id', $id)->first();
        abort_unless($material, 404);
        $data = $this->validated($request, false);
        if ($request->hasFile('file')) {
            if ($material->file_path) Storage::disk('public')->delete($material->file_path);
            $data['file_path'] = $request->file('file')->store('study-materials', 'public');
        }
        unset($data['file']);
        $data['updated_at'] = now();
        DB::table('study_materials')->where('id', $id)->update($data);
        return redirect()->route('study-materials.show', $id)->with('success', 'Study material updated successfully.');
    }

    public function destroy(string $id)
    {
        $material = DB::table('study_materials')->where('id', $id)->first();
        abort_unless($material, 404);
        if ($material->file_path) Storage::disk('public')->delete($material->file_path);
        DB::table('study_materials')->where('id', $id)->delete();
        return redirect()->route('study-materials.index')->with('success', 'Study material deleted successfully.');
    }

    private function validated(Request $request, bool $fileRequired = true): array
    {
        return $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'section_id' => ['required', 'exists:sections,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'material_type' => ['required', Rule::in(['notes', 'worksheet', 'presentation', 'video', 'reference', 'other'])],
            'is_published' => ['required', 'boolean'],
            'file' => [$fileRequired ? 'required' : 'nullable', 'file', 'max:20480',
                'mimes:pdf,doc,docx,ppt,pptx,jpg,jpeg,png,mp4,webm'],
        ]);
    }

    private function formData(): array
    {
        return [
            'schools' => DB::table('schools')->orderBy('name')->get(),
            'sections' => DB::table('sections')->join('school_classes', 'school_classes.id', '=', 'sections.school_class_id')
                ->select('sections.*', 'school_classes.name as class_name', 'school_classes.school_id')
                ->where('sections.is_active', 1)->get(),
            'subjects' => DB::table('subjects')->where('is_active', 1)->orderBy('name')->get(),
        ];
    }
}
