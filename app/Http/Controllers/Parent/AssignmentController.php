<?php

namespace App\Http\Controllers\Parent;

use Illuminate\Support\Facades\DB;

class AssignmentController extends ParentController
{
    public function index()
    {
        $context = $this->parentContext();

        $records = DB::table('assignments as a')
            ->join('student_enrollments as se', 'se.section_id', '=', 'a.section_id')
            ->join('student_profiles as sp', 'sp.id', '=', 'se.student_id')
            ->leftJoin('subjects as sub', 'sub.id', '=', 'a.subject_id')
            ->leftJoin('assignment_submissions as ass', function ($join) {
                $join->on('ass.assignment_id', '=', 'a.id')
                    ->on('ass.student_id', '=', 'sp.id');
            })
            ->whereIn('se.student_id', $context['childIds'] ?: [0])
            ->where('se.status', 'active')
            ->where('a.status', 'published')
            ->select(
                'sp.first_name',
                'sp.last_name',
                'a.title',
                'sub.name as subject',
                'a.description',
                'a.assignment_type',
                'a.maximum_marks',
                'a.passing_marks',
                'a.start_date',
                'a.due_date',
                'a.attachment_path',
                'ass.status as submission_status',
                'ass.submitted_at',
                'ass.marks_obtained',
                'ass.teacher_feedback'
            )
            ->orderByDesc('a.due_date')
            ->get();

        return $this->parentView('assignments', 'Children Assignments', $context, $records);
    }
}