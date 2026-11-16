<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

abstract class ParentController extends Controller
{
    protected function parentContext(): array
    {
        $userId = auth()->id();

        $guardian = DB::table('guardians')
            ->where('user_id', $userId)
            ->first();

        if (!$guardian) {
            abort(403, 'No parent profile is linked to this account.');
        }

        $children = DB::table('student_profiles as sp')
            ->join('student_guardians as sg', 'sg.student_id', '=', 'sp.id')
            ->where('sg.guardian_id', $guardian->id)
            ->whereNull('sp.deleted_at')
            ->select(
                'sp.id',
                'sp.school_id',
                'sp.campus_id',
                'sp.admission_number',
                'sp.first_name',
                'sp.middle_name',
                'sp.last_name',
                'sp.status'
            )
            ->distinct()
            ->get();

        $childIds = $children->pluck('id')->all();

        $enrollments = DB::table('student_enrollments as se')
            ->join('sections as sec', 'sec.id', '=', 'se.section_id')
            ->join('school_classes as sc', 'sc.id', '=', 'se.school_class_id')
            ->whereIn('se.student_id', $childIds ?: [0])
            ->where('se.status', 'active')
            ->where('sec.is_active', true)
            ->where('sc.is_active', true)
            ->select(
                'se.student_id',
                'se.academic_year_id',
                'se.school_class_id',
                'se.section_id',
                'sc.name as class_name',
                'sec.name as section_name'
            )
            ->get();

        return [
            'guardian' => $guardian,
            'children' => $children,
            'childIds' => $childIds,
            'enrollments' => $enrollments,
            'sectionIds' => $enrollments->pluck('section_id')->unique()->values()->all(),
        ];
    }

    protected function parentView(
        string $module,
        string $title,
        array $context,
        $records
    ) {
        return view('parent.services.index', array_merge($context, [
            'module' => $module,
            'title' => $title,
            'records' => $records,
        ]));
    }
}