<?php

namespace App\Http\Controllers\Teacher\Concerns;

use App\Models\StaffProfile;
use Illuminate\Support\Facades\DB;

trait ResolvesTeacher
{
    protected function currentTeacher(): StaffProfile
    {
        $teacher = StaffProfile::query()
            ->where('user_id', auth()->id())
            ->where('status', 'active')
            ->first();

        abort_unless($teacher, 403, 'Your teacher profile is not linked to this login.');

        $hasActiveTeacherRole = DB::table('user_role_assignments as ura')
            ->join('roles as r', 'r.id', '=', 'ura.role_id')
            ->where('ura.user_id', auth()->id())
            ->where('ura.school_id', $teacher->school_id)
            ->where('ura.is_active', true)
            ->where('r.code', 'TEACHER')
            ->where('r.is_active', true)
            ->where(function ($q) {
                $q->whereNull('ura.starts_at')->orWhere('ura.starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ura.expires_at')->orWhere('ura.expires_at', '>=', now());
            })
            ->exists();

        abort_unless($hasActiveTeacherRole, 403, 'This area is available to active teacher accounts only.');

        return $teacher->loadMissing(['school', 'campus', 'user']);
    }

    protected function assignedTeachingRows(StaffProfile $teacher)
    {
        return DB::table('teaching_assignments as ta')
            ->join('class_subjects as cs', 'cs.id', '=', 'ta.class_subject_id')
            ->join('school_classes as sc', 'sc.id', '=', 'cs.school_class_id')
            ->join('sections as sec', 'sec.id', '=', 'ta.section_id')
            ->leftJoin('subjects as sub', 'sub.id', '=', 'cs.subject_id')
            ->where('ta.staff_profile_id', $teacher->id)
            ->where('ta.academic_year_id', function ($q) use ($teacher) {
                $q->select('id')
                    ->from('academic_years')
                    ->where('school_id', $teacher->school_id)
                    ->where('status', 'active')
                    ->orderByDesc('start_date')
                    ->limit(1);
            })
            ->select(
                'ta.id as assignment_id',
                'ta.academic_year_id',
                'ta.section_id',
                'cs.id as class_subject_id',
                'cs.school_class_id',
                'cs.subject_id',
                'sc.name as class_name',
                'sec.name as section_name',
                'sub.name as subject_name'
            );
    }
}
