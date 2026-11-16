<?php

namespace App\Http\Controllers\Parent;

use Illuminate\Support\Facades\DB;

class ChildrenController extends ParentController
{
    public function index()
    {
        $context = $this->parentContext();

        $children = $context['children']->map(function ($child) use ($context) {
            $enrollment = $context['enrollments']->where('student_id', $child->id)
                ->sortByDesc('academic_year_id')->first();

            $child->class_name = $enrollment->class_name ?? '—';
            $child->section_name = $enrollment->section_name ?? '—';
            return $child;
        });

        return view('parent.children.index', compact('children'));
    }
}
