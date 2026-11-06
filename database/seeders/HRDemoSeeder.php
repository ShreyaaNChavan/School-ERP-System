<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

class HRDemoSeeder extends Seeder
{
    public function run(): void
    {
        $schoolId = (int) (DB::table('schools')->orderBy('id')->value('id') ?? 1);
        $campusId = DB::table('campuses')->where('school_id', $schoolId)->orderBy('id')->value('id');

        if (!$campusId) {
            $this->command?->error("No campus found for school {$schoolId}. Create a campus first; no demo data inserted.");
            return;
        }

        $staffRows = [
            ['employee_number'=>'DEMO-HR-001','first_name'=>'Aarav','middle_name'=>null,'last_name'=>'Sharma','designation'=>'HR Manager','department'=>'Human Resources','employment_type'=>'full_time','phone'=>'9000001001'],
            ['employee_number'=>'DEMO-HR-002','first_name'=>'Isha','middle_name'=>null,'last_name'=>'Patil','designation'=>'Senior Teacher','department'=>'Academics','employment_type'=>'full_time','phone'=>'9000001002'],
            ['employee_number'=>'DEMO-HR-003','first_name'=>'Rohan','middle_name'=>null,'last_name'=>'Kulkarni','designation'=>'Accountant','department'=>'Finance','employment_type'=>'full_time','phone'=>'9000001003'],
            ['employee_number'=>'DEMO-HR-004','first_name'=>'Meera','middle_name'=>null,'last_name'=>'Deshmukh','designation'=>'Science Teacher','department'=>'Academics','employment_type'=>'full_time','phone'=>'9000001004'],
            ['employee_number'=>'DEMO-HR-005','first_name'=>'Kabir','middle_name'=>null,'last_name'=>'Joshi','designation'=>'IT Support','department'=>'Information Technology','employment_type'=>'full_time','phone'=>'9000001005'],
            ['employee_number'=>'DEMO-HR-006','first_name'=>'Anaya','middle_name'=>null,'last_name'=>'More','designation'=>'Librarian','department'=>'Library','employment_type'=>'full_time','phone'=>'9000001006'],
            ['employee_number'=>'DEMO-HR-007','first_name'=>'Vihaan','middle_name'=>null,'last_name'=>'Shinde','designation'=>'Transport Supervisor','department'=>'Transport','employment_type'=>'full_time','phone'=>'9000001007'],
            ['employee_number'=>'DEMO-HR-008','first_name'=>'Sara','middle_name'=>null,'last_name'=>'Kadam','designation'=>'Office Assistant','department'=>'Administration','employment_type'=>'contract','phone'=>'9000001008'],
        ];

        $staffIds = [];
        foreach ($staffRows as $row) {
            $existing = DB::table('staff_profiles')->where('school_id',$schoolId)->where('employee_number',$row['employee_number'])->first();
            if ($existing) {
                $staffIds[] = $existing->id;
                continue;
            }
            $staffIds[] = DB::table('staff_profiles')->insertGetId($row + [
                'school_id'=>$schoolId, 'campus_id'=>$campusId, 'user_id'=>null,
                'joining_date'=>Carbon::now()->subMonths(rand(8,48))->toDateString(),
                'status'=>'active', 'metadata'=>json_encode(['demo_record'=>true]),
                'created_at'=>now(), 'updated_at'=>now(),
            ]);
        }

        $leaveTypeIds = [];
        foreach ([
            ['name'=>'Casual Leave','annual_allowance'=>12,'carry_forward_allowed'=>false,'is_paid'=>true],
            ['name'=>'Earned Leave','annual_allowance'=>18,'carry_forward_allowed'=>true,'is_paid'=>true],
            ['name'=>'Medical Leave','annual_allowance'=>10,'carry_forward_allowed'=>false,'is_paid'=>true],
        ] as $type) {
            $id = DB::table('leave_types')->where('school_id',$schoolId)->where('name',$type['name'])->value('id');
            if (!$id) $id = DB::table('leave_types')->insertGetId($type + ['school_id'=>$schoolId,'is_active'=>true,'created_at'=>now(),'updated_at'=>now()]);
            $leaveTypeIds[] = $id;
        }

        $today = today();
        foreach ($staffIds as $i=>$staffId) {
            DB::table('employee_attendance')->updateOrInsert(
                ['staff_profile_id'=>$staffId,'attendance_date'=>$today->toDateString()],
                ['check_in'=>($i===2?'09:24:00':'08:'.str_pad((string)(45+$i),2,'0',STR_PAD_LEFT).':00'),
                 'check_out'=>'16:30:00','status'=>($i===2?'late':'present'),
                 'remarks'=>'DEMO DATA — safe to remove','recorded_by'=>null,'updated_at'=>now(),'created_at'=>now()]
            );
        }

        $leaveSamples = [
            [$staffIds[1],$leaveTypeIds[0],$today->copy()->addDays(3),$today->copy()->addDays(4),2,'Family function','pending'],
            [$staffIds[3],$leaveTypeIds[2],$today->copy()->subDays(5),$today->copy()->subDays(4),2,'Medical appointment','approved'],
            [$staffIds[5],$leaveTypeIds[1],$today->copy()->addDays(10),$today->copy()->addDays(12),3,'Personal work','pending'],
        ];
        foreach ($leaveSamples as [$staffId,$typeId,$start,$end,$days,$reason,$status]) {
            $exists=DB::table('leave_requests')->where('staff_profile_id',$staffId)->where('start_date',$start->toDateString())->exists();
            if (!$exists) DB::table('leave_requests')->insert([
                'staff_profile_id'=>$staffId,'leave_type_id'=>$typeId,'start_date'=>$start->toDateString(),
                'end_date'=>$end->toDateString(),'total_days'=>$days,'reason'=>$reason,'status'=>$status,
                'reviewed_by'=>null,'reviewed_at'=>null,'review_comment'=>null,'created_at'=>now(),'updated_at'=>now()
            ]);
        }

        $jobId=DB::table('recruitment_jobs')->where('school_id',$schoolId)->where('title','Demo — Mathematics Teacher')->value('id');
        if (!$jobId) $jobId=DB::table('recruitment_jobs')->insertGetId([
            'school_id'=>$schoolId,'campus_id'=>$campusId,'designation_id'=>null,'title'=>'Demo — Mathematics Teacher',
            'description'=>'Demonstration vacancy for testing the recruitment workflow.','vacancies'=>2,
            'application_deadline'=>$today->copy()->addDays(30)->toDateString(),'status'=>'open','created_at'=>now(),'updated_at'=>now()
        ]);
        foreach ([
            ['Demo Candidate One','candidate.one@example.test','9000010001','screening'],
            ['Demo Candidate Two','candidate.two@example.test','9000010002','shortlisted'],
            ['Demo Candidate Three','candidate.three@example.test','9000010003','interview'],
        ] as [$name,$email,$phone,$status]) {
            if (!DB::table('job_applications')->where('recruitment_job_id',$jobId)->where('email',$email)->exists()) {
                DB::table('job_applications')->insert([
                    'recruitment_job_id'=>$jobId,'applicant_name'=>$name,'email'=>$email,'phone'=>$phone,
                    'resume_path'=>null,'cover_letter'=>'Demo application for testing only.','status'=>$status,
                    'reviewed_by'=>null,'created_at'=>now(),'updated_at'=>now()
                ]);
            }
        }

        if (Schema::hasTable('attendance_rules') && !DB::table('attendance_rules')->where('school_id',$schoolId)->where('code','DEMO-ATR-01')->exists()) {
            DB::table('attendance_rules')->insert([
                'school_id'=>$schoolId,'name'=>'Demo Standard Working Hours','code'=>'DEMO-ATR-01',
                'shift_start'=>'09:00:00','shift_end'=>'16:30:00','grace_minutes'=>15,
                'minimum_hours'=>6.50,'is_active'=>true,'created_at'=>now(),'updated_at'=>now()
            ]);
        }

        $periodId=DB::table('payroll_periods')->where('school_id',$schoolId)->where('name','Demo Payroll — '.now()->format('M Y'))->value('id');
        if (!$periodId) $periodId=DB::table('payroll_periods')->insertGetId([
            'school_id'=>$schoolId,'name'=>'Demo Payroll — '.now()->format('M Y'),
            'start_date'=>$today->copy()->startOfMonth()->toDateString(),'end_date'=>$today->copy()->endOfMonth()->toDateString(),
            'payment_date'=>$today->copy()->endOfMonth()->toDateString(),'status'=>'draft','processed_by'=>null,'processed_at'=>null,
            'created_at'=>now(),'updated_at'=>now()
        ]);
        $sampleSalaries=[42000,36500,31000,34000,28000,26500,30000,22000];
        foreach ($staffIds as $i=>$staffId) {
            if (!DB::table('payroll_records')->where('payroll_period_id',$periodId)->where('staff_profile_id',$staffId)->exists()) {
                $basic=$sampleSalaries[$i]; $allowance=[3000,2500,1500,2000,1200,1000,1800,800][$i]; $deduction=[1800,1200,900,1100,800,700,900,500][$i];
                DB::table('payroll_records')->insert([
                    'payroll_period_id'=>$periodId,'staff_profile_id'=>$staffId,'basic_salary'=>$basic,
                    'allowances'=>$allowance,'deductions'=>$deduction,'gross_salary'=>$basic+$allowance,
                    'net_salary'=>$basic+$allowance-$deduction,'payment_status'=>$i<5?'paid':'pending',
                    'paid_at'=>$i<5?$today->toDateString():null,'payment_reference'=>$i<5?'DEMO-'.Str::upper(Str::random(8)):null,
                    'notes'=>'DEMO DATA — safe to remove','created_at'=>now(),'updated_at'=>now()
                ]);
            }
        }

        $this->command?->info('HR demo data inserted/checked for school '.$schoolId.'. Existing demo records were reused.');
    }
}
