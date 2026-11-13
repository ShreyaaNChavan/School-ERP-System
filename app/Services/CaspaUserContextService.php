<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CaspaUserContextService
{
    public function build(): array
    {
        $user = auth()->user();
        $schoolId = (int) session('school_id', 1);
        $assignments = DB::table('user_role_assignments as ura')
            ->join('roles as r', 'r.id', '=', 'ura.role_id')
            ->where('ura.user_id', $user->id)
            ->where('ura.is_active', 1)
            ->where(function ($q) {
                $q->whereNull('ura.starts_at')->orWhere('ura.starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ura.expires_at')->orWhere('ura.expires_at', '>=', now());
            })
            ->where(function ($q) use ($schoolId) {
                $q->whereNull('ura.school_id')->orWhere('ura.school_id', $schoolId);
            })
            ->select('r.name', 'r.code', 'r.scope_type', 'ura.school_id', 'ura.campus_id')
            ->get();

        $roleText = strtolower($assignments->pluck('code')->merge($assignments->pluck('name'))->implode(' '));
        $role = 'general';
        foreach (['student','teacher','parent','guardian','principal','admin','administrator'] as $candidate) {
            if (str_contains($roleText, $candidate)) { $role = $candidate; break; }
        }

        $context = [
            'user_id' => (int) $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'role' => $role,
            'role_assignments' => $assignments->map(fn ($a) => ['name'=>$a->name,'code'=>$a->code,'scope'=>$a->scope_type,'school_id'=>$a->school_id,'campus_id'=>$a->campus_id])->all(),
            'school_id' => $schoolId,
            'campus_id' => session('campus_id'),
        ];

        if ($role === 'student') {
            $student = DB::table('student_profiles')->where('user_id',$user->id)->where('school_id',$schoolId)->whereNull('deleted_at')->first();
            if ($student) {
                $enrollment = DB::table('student_enrollments as e')->leftJoin('school_classes as c','c.id','=','e.school_class_id')->leftJoin('sections as s','s.id','=','e.section_id')
                    ->where('e.student_id',$student->id)->where('e.status','active')->orderByDesc('e.id')->select('e.*','c.name as class_name','s.name as section_name')->first();
                $attendance = DB::table('student_attendance as sa')->join('attendance_sessions as ats','ats.id','=','sa.attendance_session_id')->where('sa.student_id',$student->id)->where('ats.school_id',$schoolId)->select('sa.status','ats.attendance_date')->orderByDesc('ats.attendance_date')->limit(30)->get();
                $results = DB::table('exam_results as er')->join('exam_subjects as es','es.id','=','er.exam_subject_id')->join('examinations as ex','ex.id','=','es.examination_id')->where('er.student_id',$student->id)->where('ex.school_id',$schoolId)->select('ex.name as examination','er.marks_obtained','er.grade','er.remarks')->orderByDesc('er.created_at')->limit(20)->get();
                $context['student'] = ['profile_id'=>$student->id,'admission_number'=>$student->admission_number,'name'=>trim($student->first_name.' '.$student->middle_name.' '.$student->last_name),'class'=>$enrollment->class_name??null,'section'=>$enrollment->section_name??null,'attendance'=>$attendance->toArray(),'exam_results'=>$results->toArray()];
            }
        } elseif ($role === 'teacher') {
            $staff = DB::table('staff_profiles')->where('user_id',$user->id)->where('school_id',$schoolId)->whereNull('deleted_at')->first();
            if ($staff) $context['staff'] = ['profile_id'=>$staff->id,'employee_number'=>$staff->employee_number,'name'=>trim($staff->first_name.' '.$staff->middle_name.' '.$staff->last_name),'designation'=>$staff->designation,'department'=>$staff->department,'campus_id'=>$staff->campus_id];
            $context['teacher_notice'] = 'Only the authenticated teacher profile is included. Class and student records are not included until teaching-assignment relationships are explicitly mapped.';
        } elseif (in_array($role,['parent','guardian'],true)) {
            $guardian = DB::table('guardians')->where('user_id',$user->id)->first();
            if ($guardian) {
                $linked = DB::table('student_guardians as sg')->join('student_profiles as sp','sp.id','=','sg.student_id')->where('sg.guardian_id',$guardian->id)->where('sp.school_id',$schoolId)->whereNull('sp.deleted_at')->select('sp.id','sp.first_name','sp.middle_name','sp.last_name','sp.admission_number')->get();
                $context['linked_students'] = $linked->toArray();
            }
        } elseif (in_array($role,['principal','admin','administrator'],true)) {
            $context['school_summary'] = [
                'active_students'=>DB::table('student_profiles')->where('school_id',$schoolId)->whereNull('deleted_at')->where('status','active')->count(),
                'active_staff'=>DB::table('staff_profiles')->where('school_id',$schoolId)->whereNull('deleted_at')->where('status','active')->count(),
                'active_classes'=>DB::table('school_classes')->where('school_id',$schoolId)->where('is_active',1)->count(),
                'attendance_sessions_30d'=>DB::table('attendance_sessions')->where('school_id',$schoolId)->where('attendance_date','>=',now()->subDays(30)->toDateString())->count(),
                'examinations'=>DB::table('examinations')->where('school_id',$schoolId)->count(),
            ];
        }

        return $context;
    }

    public function assertScreenAccess(string $screen): array
    {
        $context = $this->build();
        $role = $context['role'];
        $allowed = match ($screen) {
            'chat' => ['student','teacher','parent','guardian','principal','admin','administrator','general'],
            'student-learning' => ['student','parent','guardian','teacher','principal','admin','administrator'],
            'teacher-assistant' => ['teacher','principal','admin','administrator'],
            'principal-intelligence' => ['principal','admin','administrator'],
            default => [],
        };
        abort_unless(in_array($role,$allowed,true),403,'Your assigned role does not have access to this CASPA module.');
        return $context;
    }
}
