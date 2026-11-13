<?php

namespace App\Http\Controllers;

use App\Services\CaspaAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class CaspaIntelligenceController extends Controller
{
    /**
     * Current school context.
     */
    private function schoolId(): int
    {
        return (int) session('school_id', 1);
    }

    /**
     * Authenticated user ID.
     */
    private function userId(): int
    {
        return (int) auth()->id();
    }

    /**
     * CASPA screen definitions.
     */
    private array $screens = [
        'chat' => [
            '65',
            'CASPA Chat',
            'Role-aware school assistant',
            'Ask questions, draft communications and work with authorized school context.'
        ],

        'student-learning' => [
            '66',
            'CASPA Student Learning',
            'Personalized student tutor',
            'Explain topics, create practice questions and build revision plans.'
        ],

        'teacher-assistant' => [
            '67',
            'CASPA Teacher Assistant',
            'AI teaching copilot',
            'Draft lesson plans, worksheets, quizzes and question papers for teacher review.'
        ],

        'principal-intelligence' => [
            '68',
            'CASPA Principal Intelligence',
            'School-wide decision support',
            'Review verified school indicators and request management summaries.'
        ],

        'reports' => [
            '69',
            'Reports & Analytics',
            'Reporting workspace',
            'Create, save and review report definitions and export history.'
        ],

        'approvals' => [
            '70',
            'Approval Center',
            'Centralized approvals',
            'Review submitted requests, record decisions and inspect approval history.'
        ],

        'roles' => [
            '71',
            'Role Builder',
            'Custom role administration',
            'Review existing roles and document proposed role configurations.'
        ],

        'permissions' => [
            '72',
            'Permission Matrix',
            'Access control visibility',
            'Inspect configured roles and permissions.'
        ],

        'workflows' => [
            '73',
            'Workflow Builder',
            'No-code process configuration',
            'Create draft workflow definitions and manage activation status.'
        ],

        'audit' => [
            '74',
            'Audit Logs',
            'Accountability and activity',
            'Review platform activity recorded by this module.'
        ],

        'settings' => [
            '75',
            'System Settings',
            'School configuration',
            'Manage module-level settings and AI configuration status.'
        ],

        'integrations' => [
            '76',
            'Integration Hub',
            'External connectivity',
            'Register integration metadata and monitor connection configuration.'
        ],
    ];

    /**
     * Legacy CASPA landing route.
     *
     * The generic caspa.workspace Blade view has been removed.
     * Redirect to the actual SchoolNexa dashboard.
     */
    public function index(string $screen = 'chat')
    {
        return redirect()->route('dashboard');
    }

    /**
     * CASPA AI chat endpoint.
     */
    public function chat(Request $request, CaspaAIService $ai)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:12000',
            'screen' => 'required|in:chat,student-learning,teacher-assistant,principal-intelligence',
            'conversation_id' => 'nullable|integer',
        ]);

        $role = match ($validated['screen']) {
            'student-learning' => 'student',
            'teacher-assistant' => 'teacher',
            'principal-intelligence' => 'principal',
            default => 'general',
        };

        $schoolId = $this->schoolId();
        $userId = $this->userId();

        $conversation = null;

        if (!empty($validated['conversation_id'])) {
            $conversation = DB::table('caspa_ai_conversations')
                ->where('id', $validated['conversation_id'])
                ->where('school_id', $schoolId)
                ->where('user_id', $userId)
                ->first();
        }

        if (!$conversation) {
            $id = DB::table('caspa_ai_conversations')->insertGetId([
                'school_id' => $schoolId,
                'user_id' => $userId,
                'role_context' => $role,
                'title' => Str::limit($validated['message'], 70),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $conversation = DB::table('caspa_ai_conversations')
                ->find($id);
        }

        DB::table('caspa_ai_messages')->insert([
            'conversation_id' => $conversation->id,
            'sender' => 'user',
            'content' => $validated['message'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            /*
             * No student, counselling, safeguarding, fee or other
             * private ERP records are sent to AI by default.
             */
            $result = $ai->respond(
                $validated['message'],
                $role,
                [
                    'school_id' => $schoolId,
                    'data_access' => 'No ERP records attached. Only user-provided prompt is sent.',
                ]
            );

            DB::table('caspa_ai_messages')->insert([
                'conversation_id' => $conversation->id,
                'sender' => 'assistant',
                'content' => $result['text'],
                'provider_response_id' => $result['response_id'],
                'model' => $result['model'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'ok' => true,
                'answer' => $result['text'],
                'conversation_id' => $conversation->id,
            ]);

        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create CASPA work items.
     */
    public function store(Request $request, string $screen)
    {
        abort_unless(
            isset($this->screens[$screen]) &&
            !in_array($screen, [
                'chat',
                'student-learning',
                'teacher-assistant',
                'principal-intelligence',
                'permissions',
                'audit',
            ], true),
            404
        );

        $data = $request->validate([
            'title' => 'required|string|max:190',
            'description' => 'nullable|string|max:5000',
            'status' => 'nullable|in:draft,pending,active,inactive',
            'due_at' => 'nullable|date',
        ]);

        if ($screen === 'approvals') {
            $data['status'] = 'pending';
        }

        $id = DB::table('caspa_work_items')->insertGetId([
            'school_id' => $this->schoolId(),
            'created_by' => $this->userId(),
            'module' => $screen,
            'title' => $data['title'],
            'payload' => json_encode([
                'description' => $data['description'] ?? null,
            ]),
            'status' => $data['status'] ?? 'draft',
            'due_at' => $data['due_at'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('caspa_platform_records')->insert([
            'school_id' => $this->schoolId(),
            'user_id' => $this->userId(),
            'record_type' => 'audit',
            'record_key' => 'work_item:' . $id,
            'payload' => json_encode([
                'action' => 'created',
                'module' => $screen,
                'title' => $data['title'],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with(
            'success',
            'Record created successfully.'
        );
    }

    /**
     * Approve or reject an approval request.
     */
    public function decide(Request $request, int $id)
    {
        $data = $request->validate([
            'decision' => 'required|in:approved,rejected,pending',
            'remarks' => 'nullable|string|max:3000',
        ]);

        $item = DB::table('caspa_work_items')
            ->where('id', $id)
            ->where('school_id', $this->schoolId())
            ->where('module', 'approvals')
            ->first();

        abort_unless($item, 404);

        DB::table('caspa_work_items')
            ->where('id', $id)
            ->update([
                'status' => $data['decision'],
                'remarks' => $data['remarks'] ?? null,
                'updated_at' => now(),
            ]);

        DB::table('caspa_platform_records')->insert([
            'school_id' => $this->schoolId(),
            'user_id' => $this->userId(),
            'record_type' => 'audit',
            'record_key' => 'approval:' . $id,
            'payload' => json_encode([
                'decision' => $data['decision'],
                'remarks' => $data['remarks'] ?? null,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with(
            'success',
            'Approval decision saved.'
        );
    }

    /**
     * Save CASPA configuration records.
     */
    public function saveRecord(Request $request, string $screen)
    {
        abort_unless(isset($this->screens[$screen]), 404);

        $data = $request->validate([
            'key' => 'required|string|max:190',
            'payload' => 'nullable|string|max:12000',
        ]);

        $decoded = [];

        if (!empty($data['payload'])) {
            $decoded = json_decode($data['payload'], true);

            if (!is_array($decoded)) {
                return back()->withErrors([
                    'payload' => 'Payload must be valid JSON.',
                ]);
            }
        }

        DB::table('caspa_platform_records')->insert([
            'school_id' => $this->schoolId(),
            'user_id' => $this->userId(),
            'record_type' => $screen,
            'record_key' => $data['key'],
            'payload' => json_encode($decoded),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with(
            'success',
            'Configuration saved.'
        );
    }
}
