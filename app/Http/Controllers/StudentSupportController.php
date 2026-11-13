<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentSupportController extends Controller
{
    private function schoolId(): int { return (int) session('school_id', 1); }

    private function modules(): array
    {
        return [
            'complaints' => ['title' => 'Complaint / Grievance', 'table' => 'complaints', 'kind' => 'complaint', 'description' => 'Register, assign and resolve student, parent and staff grievances.'],
            'anti-ragging' => ['title' => 'Anti-Ragging Report', 'table' => 'safeguarding_cases', 'kind' => 'safeguarding', 'category' => 'anti_ragging', 'description' => 'Confidential reporting and follow-up of suspected ragging incidents.'],
            'safety-incidents' => ['title' => 'Safety Incident Case', 'table' => 'safety_incidents', 'kind' => 'incident', 'description' => 'Record campus accidents, hazards, injuries and corrective actions.'],
            'child-protection' => ['title' => 'Child Protection', 'table' => 'safeguarding_cases', 'kind' => 'safeguarding', 'category' => 'child_protection', 'description' => 'Restricted safeguarding case records, referrals and follow-ups.'],
            'counselling' => ['title' => 'Counselling', 'table' => 'counselling_records', 'kind' => 'counselling', 'description' => 'Manage student counselling sessions, action plans and follow-ups.'],
            'emergency-control' => ['title' => 'Emergency Control Center', 'table' => 'emergency_control_logs', 'kind' => 'emergency', 'description' => 'Coordinate emergency alerts, response teams and incident closure.'],
        ];
    }

    private function module(string $module): array
    {
        $modules = $this->modules();
        abort_unless(isset($modules[$module]), 404);
        return $modules[$module];
    }

    private function query(string $module)
    {
        $config = $this->module($module);
        $query = DB::table($config['table'])->where('school_id', $this->schoolId());
        if ($config['kind'] === 'safeguarding') $query->where('category', $config['category']);
        return $query;
    }

    public function dashboard()
    {
        $cards = [];
        foreach ($this->modules() as $slug => $config) {
            $count = $this->query($slug)->count();
            $open = $this->query($slug)->whereNotIn('status', ['resolved', 'closed', 'completed'])->count();
            $cards[] = ['slug' => $slug, 'title' => $config['title'], 'description' => $config['description'], 'total' => $count, 'open' => $open];
        }
        return view('student-support.dashboard', compact('cards'));
    }

    public function index(Request $request, string $module)
    {
        $config = $this->module($module);
        $q = $this->query($module);
        if ($request->filled('search')) {
            $term = '%'.$request->string('search').'%';
            $q->where(function ($w) use ($term, $config) {
                foreach ($this->searchableColumns($config['kind']) as $column) $w->orWhere($column, 'like', $term);
            });
        }
        if ($request->filled('status')) $q->where('status', $request->input('status'));
        $items = $q->latest('created_at')->paginate(15)->withQueryString();
        return view('student-support.index', compact('module', 'config', 'items'));
    }

    private function searchableColumns(string $kind): array
    {
        return match ($kind) {
            'complaint' => ['subject', 'reference_number', 'category', 'description'],
            'safeguarding' => ['case_reference', 'category', 'restricted_summary'],
            'counselling' => ['summary', 'session_type', 'action_plan'],
            'incident' => ['reference_number', 'title', 'incident_type', 'location'],
            'emergency' => ['reference_number', 'title', 'emergency_type', 'location'],
            default => ['id'],
        };
    }

    public function create(string $module)
    {
        $config = $this->module($module);
        $students = DB::table('student_profiles')->where('school_id', $this->schoolId())->whereNull('deleted_at')->orderBy('first_name')->get();
        $users = DB::table('users')->orderBy('name')->get(['id', 'name']);
        return view('student-support.create', compact('module', 'config', 'students', 'users'));
    }

    public function store(Request $request, string $module)
    {
        $config = $this->module($module);
        $kind = $config['kind'];
        $rules = match ($kind) {
            'complaint' => ['subject'=>'required|string|max:255','category'=>'required|string|max:100','description'=>'required|string','priority'=>'required|in:low,medium,high,critical','assigned_to'=>'nullable|integer|exists:users,id'],
            'safeguarding' => ['student_id'=>'nullable|integer|exists:student_profiles,id','severity'=>'required|in:low,medium,high,critical','restricted_summary'=>'required|string','case_owner'=>'nullable|integer|exists:users,id'],
            'counselling' => ['student_id'=>'required|integer|exists:student_profiles,id','session_at'=>'required|date','session_type'=>'required|string|max:100','summary'=>'required|string','action_plan'=>'nullable|string','next_session_at'=>'nullable|date'],
            'incident' => ['student_id'=>'nullable|integer|exists:student_profiles,id','title'=>'required|string|max:255','incident_type'=>'required|string|max:100','severity'=>'required|in:low,medium,high,critical','incident_at'=>'required|date','location'=>'required|string|max:255','description'=>'required|string','action_taken'=>'nullable|string'],
            'emergency' => ['title'=>'required|string|max:255','emergency_type'=>'required|string|max:100','severity'=>'required|in:medium,high,critical','location'=>'required|string|max:255','description'=>'required|string','response_lead'=>'nullable|string|max:180'],
        };
        $data = $request->validate($rules);
        if (isset($data['student_id'])) abort_unless(DB::table('student_profiles')->where('id', $data['student_id'])->where('school_id', $this->schoolId())->exists(), 422);
        $now = now();
        $row = ['school_id'=>$this->schoolId(), 'created_at'=>$now, 'updated_at'=>$now];

        if ($kind === 'complaint') {
            $row += ['reported_by'=>auth()->id(),'reference_number'=>$this->reference('GRV'),'category'=>$data['category'],'subject'=>$data['subject'],'description'=>$data['description'],'priority'=>$data['priority'],'status'=>'submitted','assigned_to'=>$data['assigned_to'] ?? null];
        } elseif ($kind === 'safeguarding') {
            $row += ['case_reference'=>$this->reference($module === 'anti-ragging' ? 'RAG' : 'CP'),'student_id'=>$data['student_id'] ?? null,'reported_by'=>auth()->id(),'case_owner'=>$data['case_owner'] ?? null,'category'=>$config['category'],'severity'=>$data['severity'],'status'=>'open','restricted_summary'=>$data['restricted_summary'],'reported_at'=>$now];
        } elseif ($kind === 'counselling') {
            $row += ['student_id'=>$data['student_id'],'admission_application_id'=>null,'counsellor_id'=>auth()->id(),'session_at'=>$data['session_at'],'session_type'=>$data['session_type'],'summary'=>$data['summary'],'action_plan'=>$data['action_plan'] ?? null,'next_session_at'=>$data['next_session_at'] ?? null,'status'=>'scheduled'];
        } elseif ($kind === 'incident') {
            $row += ['reference_number'=>$this->reference('INC'),'reported_by'=>auth()->id(),'student_id'=>$data['student_id'] ?? null,'title'=>$data['title'],'incident_type'=>$data['incident_type'],'severity'=>$data['severity'],'incident_at'=>$data['incident_at'],'location'=>$data['location'],'description'=>$data['description'],'action_taken'=>$data['action_taken'] ?? null,'status'=>'reported'];
        } else {
            $row += ['reference_number'=>$this->reference('EMG'),'reported_by'=>auth()->id(),'title'=>$data['title'],'emergency_type'=>$data['emergency_type'],'severity'=>$data['severity'],'location'=>$data['location'],'description'=>$data['description'],'response_lead'=>$data['response_lead'] ?? null,'status'=>'active','started_at'=>$now];
        }
        $id = DB::table($config['table'])->insertGetId($row);
        return redirect()->route('student-support.show', [$module, $id])->with('success', 'Record created successfully.');
    }

    private function reference(string $prefix): string
    {
        return $prefix.'-'.now()->format('ymd').'-'.Str::upper(Str::random(5));
    }

    public function show(string $module, int $id)
    {
        $config = $this->module($module);
        $item = $this->query($module)->where('id', $id)->firstOrFail();
        $student = isset($item->student_id) ? DB::table('student_profiles')->where('id', $item->student_id)->first() : null;
        $notes = $config['kind'] === 'safeguarding'
            ? DB::table('safeguarding_case_notes')->where('safeguarding_case_id', $id)->latest()->get()
            : collect();
        return view('student-support.show', compact('module', 'config', 'item', 'student', 'notes'));
    }

    public function updateStatus(Request $request, string $module, int $id)
    {
        $config = $this->module($module);
        $data = $request->validate(['status'=>'required|string|max:30']);
        $allowed = ['submitted','open','under_review','investigating','in_progress','scheduled','completed','resolved','closed','cancelled','active','responding'];
        abort_unless(in_array($data['status'], $allowed, true), 422);
        $row = ['status'=>$data['status'], 'updated_at'=>now()];
        if (in_array($data['status'], ['resolved','closed','completed'], true)) {
            if ($config['kind'] === 'complaint') $row['resolved_at'] = now();
            if ($config['kind'] === 'safeguarding') $row['closed_at'] = now();
            if ($config['kind'] === 'emergency') $row['closed_at'] = now();
        }
        $updated = $this->query($module)->where('id', $id)->update($row);
        abort_unless($updated, 404);
        return back()->with('success', 'Status updated successfully.');
    }

    public function addNote(Request $request, string $module, int $id)
    {
        abort_unless(in_array($module, ['anti-ragging','child-protection'], true), 404);
        $this->query($module)->where('id', $id)->firstOrFail();
        $data = $request->validate(['note'=>'required|string|max:5000','is_restricted'=>'nullable|boolean']);
        DB::table('safeguarding_case_notes')->insert(['safeguarding_case_id'=>$id,'author_id'=>auth()->id(),'note'=>$data['note'],'is_restricted'=>$request->boolean('is_restricted'),'created_at'=>now(),'updated_at'=>now()]);
        return back()->with('success', 'Case note added.');
    }
}
