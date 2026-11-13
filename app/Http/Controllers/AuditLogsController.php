<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogsController extends Controller
{
    private function query(Request $request)
    {
        $q=DB::table('audit_logs as a')->leftJoin('users as u','u.id','=','a.user_id')->where(function($w){
            $w->whereNull('a.school_id')->orWhere('a.school_id',session('school_id',1));
        })->select('a.*','u.name as actor_name','u.email as actor_email');
        if($request->filled('search')){ $s=trim($request->input('search')); $q->where(function($x)use($s){$x->where('a.action','like',"%$s%")->orWhere('a.entity_type','like',"%$s%")->orWhere('a.entity_id','like',"%$s%")->orWhere('u.name','like',"%$s%")->orWhere('u.email','like',"%$s%");});}
        if($request->filled('user_id'))$q->where('a.user_id',$request->input('user_id'));
        if($request->filled('entity_type'))$q->where('a.entity_type',$request->input('entity_type'));
        if($request->filled('action'))$q->where('a.action','like','%'.$request->input('action').'%');
        if($request->filled('from'))$q->whereDate('a.created_at','>=',$request->input('from'));
        if($request->filled('to'))$q->whereDate('a.created_at','<=',$request->input('to'));
        return $q;
    }
    public function dashboard()
    {
        $base=DB::table('audit_logs')->where(function($w){$w->whereNull('school_id')->orWhere('school_id',session('school_id',1));});
        $total=(clone $base)->count(); $today=(clone $base)->whereDate('created_at',today())->count();
        $actors=(clone $base)->whereNotNull('user_id')->distinct('user_id')->count('user_id');
        $entities=(clone $base)->distinct('entity_type')->count('entity_type');
        $recent=$this->query(new Request())->orderByDesc('a.created_at')->limit(10)->get();
        $actions=(clone $base)->select('action',DB::raw('COUNT(*) as total'))->groupBy('action')->orderByDesc('total')->limit(8)->get();
        return view('audit-logs.dashboard',compact('total','today','actors','entities','recent','actions'));
    }
    public function index(Request $request)
    {
        $logs=$this->query($request)->orderByDesc('a.created_at')->paginate(30)->withQueryString();
        $users=DB::table('users')->orderBy('name')->get(['id','name']);
        $entityTypes=DB::table('audit_logs')->whereNotNull('entity_type')->distinct()->orderBy('entity_type')->pluck('entity_type');
        return view('audit-logs.index',compact('logs','users','entityTypes'));
    }
    public function show(int $id)
    {
        $log=DB::table('audit_logs as a')->leftJoin('users as u','u.id','=','a.user_id')->where('a.id',$id)->where(function($w){$w->whereNull('a.school_id')->orWhere('a.school_id',session('school_id',1));})->select('a.*','u.name as actor_name','u.email as actor_email')->firstOrFail();
        return view('audit-logs.show',compact('log'));
    }
    public function export(Request $request): StreamedResponse
    {
        $rows=$this->query($request)->orderByDesc('a.created_at')->limit(10000)->get();
        return response()->streamDownload(function()use($rows){
            $out=fopen('php://output','w'); fputcsv($out,['ID','Date','User','Email','Action','Entity Type','Entity ID','IP Address','Old Values','New Values']);
            foreach($rows as $r)fputcsv($out,[$r->id,$r->created_at,$r->actor_name,$r->actor_email,$r->action,$r->entity_type,$r->entity_id,$r->ip_address,$r->old_values,$r->new_values]);
            fclose($out);
        },'schoolnexa-audit-logs.csv',['Content-Type'=>'text/csv']);
    }
}
