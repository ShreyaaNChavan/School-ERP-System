<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class IntegrationHubController extends Controller
{
    private function schoolId(): int { return (int) session('school_id',1); }
    public function dashboard()
    {
        $q=DB::table('caspa_integrations')->where('school_id',$this->schoolId());
        $total=(clone $q)->count(); $connected=(clone $q)->where('status','connected')->count(); $attention=(clone $q)->where('status','attention')->count();
        $items=(clone $q)->orderByDesc('updated_at')->limit(8)->get();
        $logs=DB::table('caspa_integration_logs')->where('school_id',$this->schoolId())->orderByDesc('created_at')->limit(8)->get();
        return view('integration-hub.dashboard',compact('total','connected','attention','items','logs'));
    }
    public function index(Request $request)
    {
        $q=DB::table('caspa_integrations')->where('school_id',$this->schoolId());
        if($request->filled('search'))$q->where('name','like','%'.trim($request->input('search')).'%');
        if($request->filled('status'))$q->where('status',$request->input('status'));
        $integrations=$q->orderBy('name')->paginate(15)->withQueryString();
        return view('integration-hub.index',compact('integrations'));
    }
    public function create(){return view('integration-hub.form',['integration'=>null]);}
    public function store(Request $request)
    {
        $data=$request->validate(['name'=>'required|string|max:150','provider'=>'required|string|max:100','category'=>'required|string|max:80','endpoint_url'=>'nullable|url|max:2000','api_key'=>'nullable|string|max:4000','description'=>'nullable|string|max:1000']);
        $id=DB::table('caspa_integrations')->insertGetId(['school_id'=>$this->schoolId(),'name'=>$data['name'],'provider'=>$data['provider'],'category'=>$data['category'],'endpoint_url'=>$data['endpoint_url']??null,'credentials'=>!empty($data['api_key'])?Crypt::encryptString($data['api_key']):null,'description'=>$data['description']??null,'status'=>'disconnected','created_by'=>auth()->id(),'created_at'=>now(),'updated_at'=>now()]);
        $this->log($id,'created','Integration configuration created.');
        return redirect()->route('integration-hub.show',$id)->with('success','Integration added. Configure and test it before use.');
    }
    public function show(int $id)
    {
        $integration=DB::table('caspa_integrations')->where('school_id',$this->schoolId())->where('id',$id)->firstOrFail();
        $logs=DB::table('caspa_integration_logs')->where('integration_id',$id)->orderByDesc('created_at')->limit(30)->get();
        return view('integration-hub.show',compact('integration','logs'));
    }
    public function edit(int $id)
    {
        $integration=DB::table('caspa_integrations')->where('school_id',$this->schoolId())->where('id',$id)->firstOrFail();
        return view('integration-hub.form',compact('integration'));
    }
    public function update(Request $request,int $id)
    {
        $integration=DB::table('caspa_integrations')->where('school_id',$this->schoolId())->where('id',$id)->firstOrFail();
        $data=$request->validate(['name'=>'required|string|max:150','provider'=>'required|string|max:100','category'=>'required|string|max:80','endpoint_url'=>'nullable|url|max:2000','api_key'=>'nullable|string|max:4000','description'=>'nullable|string|max:1000']);
        $update=['name'=>$data['name'],'provider'=>$data['provider'],'category'=>$data['category'],'endpoint_url'=>$data['endpoint_url']??null,'description'=>$data['description']??null,'updated_at'=>now()];
        if(!empty($data['api_key']))$update['credentials']=Crypt::encryptString($data['api_key']);
        DB::table('caspa_integrations')->where('id',$id)->update($update); $this->log($id,'updated','Integration configuration updated.');
        return redirect()->route('integration-hub.show',$id)->with('success','Integration updated.');
    }
    public function test(int $id)
    {
        $item=DB::table('caspa_integrations')->where('school_id',$this->schoolId())->where('id',$id)->firstOrFail();
        if(!$item->endpoint_url){$this->log($id,'test_failed','No endpoint URL configured.');return back()->withErrors(['endpoint_url'=>'Add an endpoint URL before testing.']);}
        // Safe configuration check only. No outbound request is made by this starter module.
        $this->log($id,'test_configured','Endpoint is configured. Live provider authentication/handshake is not implemented.');
        return back()->with('success','Configuration check recorded. A live provider connection test is not yet implemented.');
    }
    public function toggle(int $id)
    {
        $item=DB::table('caspa_integrations')->where('school_id',$this->schoolId())->where('id',$id)->firstOrFail();
        $status=$item->status==='disabled'?'disconnected':'disabled';
        DB::table('caspa_integrations')->where('id',$id)->update(['status'=>$status,'updated_at'=>now()]);
        $this->log($id,'status_changed','Status changed to '.$status.'.');
        return back()->with('success','Integration status updated.');
    }
    private function log(int $id,string $event,string $message):void
    {
        DB::table('caspa_integration_logs')->insert(['school_id'=>$this->schoolId(),'integration_id'=>$id,'event'=>$event,'message'=>$message,'created_at'=>now()]);
    }
}
