<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkflowBuilderController extends Controller
{
    private function schoolId(): int { return (int) session('school_id', 1); }

    public function dashboard()
    {
        $q = DB::table('caspa_approval_workflows')->where('school_id', $this->schoolId());
        $total = (clone $q)->count();
        $active = (clone $q)->where('is_active', 1)->count();
        $inactive = $total - $active;
        $recent = (clone $q)->orderByDesc('updated_at')->limit(8)->get();
        $runs = DB::table('caspa_workflow_runs')->where('school_id', $this->schoolId())->count();
        $recentRuns = DB::table('caspa_workflow_runs')->where('school_id', $this->schoolId())->orderByDesc('created_at')->limit(6)->get();
        return view('workflow-builder.dashboard', compact('total','active','inactive','recent','runs','recentRuns'));
    }

    public function index()
    {
        $workflows = DB::table('caspa_approval_workflows')->where('school_id',$this->schoolId())->orderByDesc('updated_at')->paginate(15);
        $runCounts = DB::table('caspa_workflow_runs')->where('school_id',$this->schoolId())->select('workflow_id',DB::raw('COUNT(*) as total'))->groupBy('workflow_id')->pluck('total','workflow_id');
        return view('workflow-builder.index',compact('workflows','runCounts'));
    }

    public function create()
    {
        return view('workflow-builder.form',['workflow'=>null,'definition'=>['nodes'=>[['id'=>'start','type'=>'start','label'=>'Start'],['id'=>'approval','type'=>'approval','label'=>'Approval'],['id'=>'end','type'=>'end','label'=>'End']],'edges'=>[['from'=>'start','to'=>'approval'],['from'=>'approval','to'=>'end']]]]);
    }

    public function store(Request $request)
    {
        $data=$request->validate(['name'=>'required|string|max:150','definition'=>'required|json']);
        $id=DB::table('caspa_approval_workflows')->insertGetId(['school_id'=>$this->schoolId(),'name'=>$data['name'],'definition'=>$data['definition'],'is_active'=>0,'created_by'=>auth()->id(),'created_at'=>now(),'updated_at'=>now()]);
        return redirect()->route('workflow-builder.show',$id)->with('success','Workflow saved as inactive draft. Review and activate it when ready.');
    }

    public function show(int $id)
    {
        $workflow=DB::table('caspa_approval_workflows')->where('school_id',$this->schoolId())->where('id',$id)->firstOrFail();
        $definition=json_decode($workflow->definition,true) ?: ['nodes'=>[],'edges'=>[]];
        $history=DB::table('caspa_workflow_runs')->where('school_id',$this->schoolId())->where('workflow_id',$id)->orderByDesc('created_at')->limit(30)->get();
        return view('workflow-builder.show',compact('workflow','definition','history'));
    }

    public function edit(int $id)
    {
        $workflow=DB::table('caspa_approval_workflows')->where('school_id',$this->schoolId())->where('id',$id)->firstOrFail();
        return view('workflow-builder.form',['workflow'=>$workflow,'definition'=>json_decode($workflow->definition,true) ?: ['nodes'=>[],'edges'=>[]]]);
    }

    public function update(Request $request,int $id)
    {
        $data=$request->validate(['name'=>'required|string|max:150','definition'=>'required|json']);
        DB::table('caspa_approval_workflows')->where('school_id',$this->schoolId())->where('id',$id)->update(['name'=>$data['name'],'definition'=>$data['definition'],'updated_at'=>now()]);
        return redirect()->route('workflow-builder.show',$id)->with('success','Workflow updated.');
    }

    public function toggle(int $id)
    {
        $w=DB::table('caspa_approval_workflows')->where('school_id',$this->schoolId())->where('id',$id)->firstOrFail();
        DB::table('caspa_approval_workflows')->where('id',$id)->update(['is_active'=>!$w->is_active,'updated_at'=>now()]);
        return back()->with('success','Workflow status updated.');
    }

    public function run(Request $request,int $id)
    {
        $w=DB::table('caspa_approval_workflows')->where('school_id',$this->schoolId())->where('id',$id)->where('is_active',1)->firstOrFail();
        DB::table('caspa_workflow_runs')->insert(['school_id'=>$this->schoolId(),'workflow_id'=>$id,'workflow_name'=>$w->name,'status'=>'started','triggered_by'=>auth()->id(),'context'=>json_encode($request->only(['reference','notes'])),'started_at'=>now(),'created_at'=>now(),'updated_at'=>now()]);
        return back()->with('success','A workflow test run was recorded. This does not execute external actions.');
    }
}
