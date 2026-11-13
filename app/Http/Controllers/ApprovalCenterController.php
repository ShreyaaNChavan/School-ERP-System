<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApprovalCenterController extends Controller
{
    private function schoolId(): int { return (int) session('school_id', 1); }
    private function userId(): int { return (int) auth()->id(); }

    public function dashboard()
    {
        $base = DB::table('caspa_approval_requests')->where('school_id', $this->schoolId());
        $stats = [
            'total' => (clone $base)->count(),
            'pending' => (clone $base)->where('status', 'pending')->count(),
            'approved_today' => (clone $base)->where('status', 'approved')->whereDate('updated_at', today())->count(),
            'rejected' => (clone $base)->where('status', 'rejected')->count(),
            'overdue' => (clone $base)->where('status', 'pending')->whereNotNull('due_at')->where('due_at', '<', now())->count(),
        ];
        $recent = (clone $base)->latest()->limit(8)->get();
        $statusCounts = (clone $base)->select('status', DB::raw('COUNT(*) as total'))->groupBy('status')->pluck('total', 'status');
        return view('approvals.dashboard', compact('stats', 'recent', 'statusCounts'));
    }

    public function requests(Request $request)
    {
        $query = DB::table('caspa_approval_requests')->where('school_id', $this->schoolId());
        if ($request->filled('status')) $query->where('status', $request->string('status'));
        if ($request->filled('type')) $query->where('request_type', $request->string('type'));
        if ($request->filled('priority')) $query->where('priority', $request->string('priority'));
        if ($request->filled('q')) $query->where(function ($q) use ($request) {
            $term = '%'.$request->q.'%';
            $q->where('request_number', 'like', $term)->orWhere('title', 'like', $term)->orWhere('submitted_name', 'like', $term);
        });
        $requests = $query->latest()->paginate(15)->withQueryString();
        return view('approvals.requests', compact('requests'));
    }

    public function create()
    {
        return view('approvals.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:190',
            'request_type' => 'required|string|max:80',
            'department' => 'nullable|string|max:100',
            'description' => 'required|string|max:5000',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_at' => 'nullable|date',
            'assigned_to' => 'nullable|integer',
        ]);
        $id = DB::table('caspa_approval_requests')->insertGetId([
            'school_id' => $this->schoolId(),
            'request_number' => 'APR-'.now()->format('ymd').'-'.Str::upper(Str::random(5)),
            'title' => $data['title'],
            'request_type' => $data['request_type'],
            'department' => $data['department'] ?? null,
            'description' => $data['description'],
            'priority' => $data['priority'],
            'status' => 'pending',
            'submitted_by' => $this->userId(),
            'submitted_name' => auth()->user()->name ?? 'User',
            'assigned_to' => $data['assigned_to'] ?? null,
            'due_at' => $data['due_at'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->logAction($id, 'submitted', 'Request submitted.');
        return redirect()->route('approvals.show', $id)->with('success', 'Approval request submitted.');
    }

    public function show(int $id)
    {
        $approval = DB::table('caspa_approval_requests')->where('school_id', $this->schoolId())->where('id', $id)->firstOrFail();
        $actions = DB::table('caspa_approval_actions')->where('approval_request_id', $id)->latest()->get();
        return view('approvals.show', compact('approval', 'actions'));
    }

    public function decide(Request $request, int $id)
    {
        $data = $request->validate([
            'decision' => 'required|in:approved,rejected,returned,forwarded',
            'remarks' => 'nullable|string|max:3000',
            'assigned_to' => 'nullable|integer',
        ]);
        $approval = DB::table('caspa_approval_requests')->where('school_id', $this->schoolId())->where('id', $id)->firstOrFail();
        $status = match ($data['decision']) {
            'approved' => 'approved',
            'rejected' => 'rejected',
            'returned' => 'returned',
            default => 'pending',
        };
        DB::table('caspa_approval_requests')->where('id', $id)->update([
            'status' => $status,
            'assigned_to' => $data['decision'] === 'forwarded' ? ($data['assigned_to'] ?? null) : $approval->assigned_to,
            'updated_at' => now(),
        ]);
        $this->logAction($id, $data['decision'], $data['remarks'] ?? null);
        return back()->with('success', 'Approval decision recorded.');
    }

    public function myApprovals()
    {
        $requests = DB::table('caspa_approval_requests')->where('school_id', $this->schoolId())
            ->where('assigned_to', $this->userId())->where('status', 'pending')->latest()->paginate(15);
        return view('approvals.my', compact('requests'));
    }

    public function history()
    {
        $actions = DB::table('caspa_approval_actions as a')
            ->join('caspa_approval_requests as r', 'r.id', '=', 'a.approval_request_id')
            ->where('r.school_id', $this->schoolId())
            ->select('a.*', 'r.request_number', 'r.title')->latest('a.created_at')->paginate(20);
        return view('approvals.history', compact('actions'));
    }

    public function workflow()
    {
        $workflow = DB::table('caspa_approval_workflows')->where('school_id', $this->schoolId())->latest()->first();
        return view('approvals.workflow', compact('workflow'));
    }

    public function saveWorkflow(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'definition' => 'required|json',
        ]);
        $definition = json_decode($data['definition'], true);
        if (!is_array($definition) || empty($definition['nodes']) || empty($definition['edges'])) {
            return back()->withErrors(['definition' => 'Workflow must contain nodes and edges.'])->withInput();
        }
        DB::table('caspa_approval_workflows')->insert([
            'school_id' => $this->schoolId(), 'name' => $data['name'],
            'definition' => json_encode($definition), 'is_active' => false,
            'created_by' => $this->userId(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        return back()->with('success', 'Workflow saved as draft.');
    }

    public function delegations()
    {
        $delegations = DB::table('caspa_approval_delegations')->where('school_id', $this->schoolId())->latest()->paginate(15);
        return view('approvals.delegations', compact('delegations'));
    }

    public function storeDelegation(Request $request)
    {
        $data = $request->validate([
            'delegate_user_id' => 'required|integer',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after_or_equal:starts_at',
            'reason' => 'nullable|string|max:500',
        ]);
        DB::table('caspa_approval_delegations')->insert([
            'school_id' => $this->schoolId(), 'user_id' => $this->userId(),
            'delegate_user_id' => $data['delegate_user_id'], 'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'], 'reason' => $data['reason'] ?? null,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return back()->with('success', 'Delegation saved.');
    }

    private function logAction(int $id, string $action, ?string $remarks): void
    {
        DB::table('caspa_approval_actions')->insert([
            'approval_request_id' => $id, 'user_id' => $this->userId(),
            'action' => $action, 'remarks' => $remarks,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
