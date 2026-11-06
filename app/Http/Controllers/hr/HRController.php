<?php
namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\{StaffProfile,EmployeeAttendance,AttendanceRule,LeaveType,LeaveBalance,LeaveRequest,RecruitmentJob,JobApplication,PayrollPeriod,PayrollRecord};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class HRController extends Controller
{
 private function schoolId(): int { return (int) session('school_id',1); }
 private function staff() { return StaffProfile::where('school_id',$this->schoolId())->where('status','active'); }

 public function dashboard() {
  $s=$this->schoolId(); $today=today()->toDateString();
  return view('hr.dashboard',[
   'employees'=>StaffProfile::where('school_id',$s)->where('status','active')->count(),
   'present'=>EmployeeAttendance::whereDate('attendance_date',$today)->whereHas('staff',fn($q)=>$q->where('school_id',$s))->where('status','present')->count(),
   'pending'=>LeaveRequest::where('status','pending')->whereHas('staff',fn($q)=>$q->where('school_id',$s))->count(),
   'jobs'=>RecruitmentJob::where('school_id',$s)->whereIn('status',['open','published'])->count(),
   'leaves'=>LeaveRequest::with('staff','leaveType')->where('status','pending')->whereHas('staff',fn($q)=>$q->where('school_id',$s))->latest()->take(6)->get(),
   'latestPayroll'=>PayrollPeriod::where('school_id',$s)->latest('start_date')->first()
  ]);
 }
 public function recruitment() { $jobs=RecruitmentJob::withCount('applications')->where('school_id',$this->schoolId())->latest()->paginate(10); return view('hr.recruitment',compact('jobs')); }
 public function storeJob(Request $r) { $d=$r->validate(['title'=>'required|string|max:255','description'=>'nullable|string','vacancies'=>'required|integer|min:1','application_deadline'=>'nullable|date','status'=>[Rule::in(['draft','open','published','closed'])]]); $d['school_id']=$this->schoolId(); RecruitmentJob::create($d); return back()->with('success','Job created.'); }
 public function job(RecruitmentJob $job) { abort_unless((int)$job->school_id===$this->schoolId(),404); $job->load('applications'); return view('hr.job',compact('job')); }
 public function storeApplication(Request $r,RecruitmentJob $job) { abort_unless((int)$job->school_id===$this->schoolId(),404); $job->applications()->create($r->validate(['applicant_name'=>'required|string|max:255','email'=>'nullable|email','phone'=>'nullable|string|max:30','cover_letter'=>'nullable|string'])); return back()->with('success','Applicant added.'); }
 public function updateApplication(Request $r,JobApplication $application) { abort_unless((int)$application->job->school_id===$this->schoolId(),404); $application->update($r->validate(['status'=>[Rule::in(['received','screening','shortlisted','interview','offered','hired','rejected'])]])); return back()->with('success','Applicant updated.'); }

 public function attendance(Request $r) {
  $date=$r->input('date',today()->toDateString());
  $records=EmployeeAttendance::with('staff')->whereDate('attendance_date',$date)->whereHas('staff',fn($q)=>$q->where('school_id',$this->schoolId()))->orderBy('check_in')->paginate(15)->withQueryString();
  $staff=$this->staff()->orderBy('first_name')->get(); return view('hr.attendance',compact('records','staff','date'));
 }
 public function storeAttendance(Request $r) {
  $d=$r->validate(['staff_profile_id'=>'required|integer','attendance_date'=>'required|date','check_in'=>'nullable|date_format:H:i','check_out'=>'nullable|date_format:H:i','status'=>[Rule::in(['present','absent','late','half_day','leave'])],'remarks'=>'nullable|string']);
  abort_unless($this->staff()->whereKey($d['staff_profile_id'])->exists(),404); $d['recorded_by']=auth()->id();
  EmployeeAttendance::updateOrCreate(['staff_profile_id'=>$d['staff_profile_id'],'attendance_date'=>$d['attendance_date']],$d); return back()->with('success','Attendance saved.');
 }
 public function attendanceRules() { $rules=AttendanceRule::where('school_id',$this->schoolId())->latest()->paginate(10); return view('hr.attendance-rules',compact('rules')); }
 public function storeAttendanceRule(Request $r) { $d=$r->validate(['name'=>'required|string|max:255','code'=>'nullable|string|max:30','shift_start'=>'nullable|date_format:H:i','shift_end'=>'nullable|date_format:H:i','grace_minutes'=>'required|integer|min:0|max:180','minimum_hours'=>'required|numeric|min:0|max:24']); $d['school_id']=$this->schoolId(); $d['is_active']=$r->boolean('is_active'); AttendanceRule::create($d); return back()->with('success','Rule saved.'); }

 public function leave() {
  $s=$this->schoolId(); $types=LeaveType::where('school_id',$s)->orderBy('name')->get();
  $requests=LeaveRequest::with('staff','leaveType')->whereHas('staff',fn($q)=>$q->where('school_id',$s))->latest()->paginate(12);
  $staff=$this->staff()->orderBy('first_name')->get(); return view('hr.leave',compact('types','requests','staff'));
 }
 public function storeLeaveType(Request $r) { $d=$r->validate(['name'=>'required|string|max:255','annual_allowance'=>'nullable|integer|min:0|max:366']); $d['school_id']=$this->schoolId(); $d['carry_forward_allowed']=$r->boolean('carry_forward_allowed'); $d['is_paid']=$r->boolean('is_paid'); LeaveType::create($d); return back()->with('success','Leave type created.'); }
 public function storeLeaveRequest(Request $r) {
  $d=$r->validate(['staff_profile_id'=>'required|integer','leave_type_id'=>'required|integer','start_date'=>'required|date','end_date'=>'required|date|after_or_equal:start_date','total_days'=>'required|numeric|min:.5|max:366','reason'=>'nullable|string']);
  abort_unless($this->staff()->whereKey($d['staff_profile_id'])->exists(),404); abort_unless(LeaveType::where('school_id',$this->schoolId())->whereKey($d['leave_type_id'])->exists(),404);
  LeaveRequest::create($d); return back()->with('success','Leave request submitted.');
 }
 public function reviewLeaveRequest(Request $r,LeaveRequest $leaveRequest) {
  abort_unless($leaveRequest->staff && (int)$leaveRequest->staff->school_id===$this->schoolId(),404);
  $d=$r->validate(['status'=>[Rule::in(['approved','rejected'])],'review_comment'=>'nullable|string']);
  DB::transaction(function() use($leaveRequest,$d) {
   $leaveRequest->update($d+['reviewed_by'=>auth()->id(),'reviewed_at'=>now()]);
   if($d['status']==='approved') {
    $yearId=DB::table('academic_years')->where('school_id',$this->schoolId())->value('id');
    if($yearId) { $b=LeaveBalance::firstOrCreate(['staff_profile_id'=>$leaveRequest->staff_profile_id,'leave_type_id'=>$leaveRequest->leave_type_id,'academic_year_id'=>$yearId],['allocated_days'=>0,'used_days'=>0,'adjusted_days'=>0]); $b->increment('used_days',$leaveRequest->total_days); }
   }
  }); return back()->with('success','Leave request reviewed.');
 }

 public function payroll() { $periods=PayrollPeriod::withCount('records')->where('school_id',$this->schoolId())->latest('start_date')->paginate(10); return view('hr.payroll',compact('periods')); }
 public function storePayrollPeriod(Request $r) { $d=$r->validate(['name'=>'required|string|max:255','start_date'=>'required|date','end_date'=>'required|date|after_or_equal:start_date','payment_date'=>'nullable|date']); $d['school_id']=$this->schoolId(); PayrollPeriod::create($d); return back()->with('success','Payroll period created.'); }
 public function payrollPeriod(PayrollPeriod $period) { abort_unless((int)$period->school_id===$this->schoolId(),404); $period->load('records.staff'); $staff=$this->staff()->orderBy('first_name')->get(); return view('hr.payroll-period',compact('period','staff')); }
 public function generatePayroll(Request $r,PayrollPeriod $period) {
  abort_unless((int)$period->school_id===$this->schoolId(),404); $d=$r->validate(['salaries'=>'required|array','salaries.*'=>'nullable|numeric|min:0']);
  DB::transaction(function() use($period,$d) { foreach($d['salaries'] as $id=>$salary) { $person=$this->staff()->whereKey($id)->first(); if(!$person) continue; $v=(float)$salary; PayrollRecord::updateOrCreate(['payroll_period_id'=>$period->id,'staff_profile_id'=>$id],['basic_salary'=>$v,'allowances'=>0,'deductions'=>0,'gross_salary'=>$v,'net_salary'=>$v,'payment_status'=>'pending']); } });
  return back()->with('success','Payroll records generated. Verify each amount before payment.');
 }
 public function updatePayrollRecord(Request $r,PayrollRecord $record) {
  abort_unless($record->payrollPeriod && (int)$record->payrollPeriod->school_id===$this->schoolId(),404);
  $d=$r->validate(['basic_salary'=>'required|numeric|min:0','allowances'=>'required|numeric|min:0','deductions'=>'required|numeric|min:0','payment_status'=>[Rule::in(['pending','paid','failed'])],'payment_reference'=>'nullable|string|max:255']);
  $gross=(float)$d['basic_salary']+(float)$d['allowances']; $d['gross_salary']=$gross; $d['net_salary']=max(0,$gross-(float)$d['deductions']); $d['paid_at']=$d['payment_status']==='paid'?today():null; $record->update($d); return back()->with('success','Payroll record updated.');
 }
 public function payslip(PayrollRecord $record) { abort_unless($record->payrollPeriod && (int)$record->payrollPeriod->school_id===$this->schoolId(),404); $record->load('staff','payrollPeriod','components'); return view('hr.payslip',compact('record')); }
}
