<?php

namespace App\Http\Controllers\Parent;

use Illuminate\Support\Facades\DB;

class HomeworkController extends ParentController
{
    public function index()
    {
        $context = $this->parentContext();

        $records = DB::table('homework_assignments as h')
            ->join('student_enrollments as se', 'se.section_id', '=', 'h.section_id')
            ->join('student_profiles as sp', 'sp.id', '=', 'se.student_id')
            ->leftJoin('subjects as sub', 'sub.id', '=', 'h.subject_id')
            ->leftJoin('homework_submissions as hs', function ($join) {
                $join->on('hs.homework_assignment_id', '=', 'h.id')
                    ->on('hs.student_id', '=', 'sp.id');
            })
            ->whereIn('se.student_id', $context['childIds'] ?: [0])
            ->where('se.status', 'active')
            ->where('h.status', 'published')
            ->select(
                'sp.first_name',
                'sp.last_name',
                'h.title',
                'sub.name as subject',
                'h.instructions',
                'h.assigned_date',
                'h.due_date',
                'h.attachment_path',
                'hs.status as submission_status',
                'hs.submitted_at',
                'hs.marks_obtained',
                'hs.teacher_feedback'
            )
            ->orderByDesc('h.due_date')
            ->get();

        return $this->parentView('homework', 'Children Homework', $context, $records);
    }
}