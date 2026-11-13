<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemSettingsController extends Controller
{
    private function schoolId(): int { return (int) session('school_id',1); }
    private function keys(): array { return ['general'=>['school_name','school_email','school_phone','school_website','timezone','date_format','currency'],'academic'=>['current_academic_year','default_term','grading_system'],'attendance'=>['attendance_start_time','late_after_minutes','allow_backdated_attendance'],'notifications'=>['email_notifications','sms_notifications','push_notifications'],'security'=>['session_timeout','password_min_length','two_factor_required'],'branding'=>['primary_color','secondary_color','logo_url','language']]; }
    public function dashboard()
    {
        $count=DB::table('system_settings')->where('school_id',$this->schoolId())->count();
        $schoolCount=DB::table('school_settings')->where('school_id',$this->schoolId())->count();
        $campusCount=DB::table('campus_settings')->whereIn('campus_id',function($q){$q->select('id')->from('campuses')->where('school_id',$this->schoolId());})->count();
        $updated=DB::table('system_settings')->where('school_id',$this->schoolId())->max('updated_at');
        return view('system-settings.dashboard',compact('count','schoolCount','campusCount','updated'));
    }
    public function index(string $section='general')
    {
        abort_unless(array_key_exists($section,$this->keys()),404);
        $settings=DB::table('system_settings')->where('school_id',$this->schoolId())->whereNull('campus_id')->whereIn('setting_key',$this->keys()[$section])->pluck('setting_value','setting_key')->all();
        return view('system-settings.section',compact('section','settings'));
    }
    public function update(Request $request,string $section)
    {
        abort_unless(array_key_exists($section,$this->keys()),404);
        $rules=[]; foreach($this->keys()[$section] as $key)$rules[$key]=['nullable','string','max:2000'];
        $data=$request->validate($rules); $now=now();
        DB::transaction(function()use($data,$now){
            foreach($data as $key=>$value){
                DB::table('system_settings')->updateOrInsert(
                    ['school_id'=>$this->schoolId(),'campus_id'=>null,'setting_key'=>$key],
                    ['setting_value'=>$value,'value_type'=>'string','updated_at'=>$now,'created_at'=>$now]
                );
            }
        });
        if(DB::getSchemaBuilder()->hasTable('audit_logs'))DB::table('audit_logs')->insert(['user_id'=>auth()->id(),'school_id'=>$this->schoolId(),'action'=>'system_settings.updated','entity_type'=>'system_settings','entity_id'=>null,'old_values'=>null,'new_values'=>json_encode(['section'=>$section,'keys'=>array_keys($data)]),'ip_address'=>$request->ip(),'user_agent'=>substr((string)$request->userAgent(),0,1000),'request_id'=>$request->attributes->get('request_id'),'created_at'=>$now]);
        return redirect()->route('system-settings.section',$section)->with('success','Settings saved successfully.');
    }
}
