<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CommunicationController extends Controller
{
    private function schoolId(): int
    {
        return (int) session('school_id', 1);
    }

    public function dashboard()
    {
        $schoolId = $this->schoolId();
        $announcements = DB::table('announcements')->where('school_id', $schoolId);

        $stats = [
            'total' => (clone $announcements)->count(),
            'published' => (clone $announcements)->where('status', 'published')->count(),
            'scheduled' => (clone $announcements)->where('status', 'scheduled')->count(),
            'drafts' => (clone $announcements)->where('status', 'draft')->count(),
            'unread' => DB::table('announcement_reads')
                ->join('announcements', 'announcements.id', '=', 'announcement_reads.announcement_id')
                ->where('announcements.school_id', $schoolId)
                ->whereNull('announcement_reads.read_at')->count(),
            'conversations' => DB::table('conversations')->where('school_id', $schoolId)->count(),
        ];

        $recent = (clone $announcements)->latest('created_at')->limit(8)->get();
        return view('communication.dashboard', compact('stats', 'recent'));
    }

    public function index(Request $request)
    {
        $query = DB::table('announcements')->where('school_id', $this->schoolId());

        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('content', 'like', "%{$term}%");
            });
        }
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('priority')) $query->where('priority', $request->priority);

        $items = $query->latest('created_at')->paginate(15)->withQueryString();
        return view('communication.index', compact('items'));
    }

    public function create()
    {
        $campuses = DB::table('campuses')->where('school_id', $this->schoolId())->orderBy('name')->get();
        return view('communication.create', compact('campuses'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedAnnouncement($request);
        $intent = $request->input('intent', 'draft');
        $data['school_id'] = $this->schoolId();
        $data['created_by'] = auth()->id();
        $data['status'] = $intent === 'publish' ? 'published' : ($intent === 'schedule' ? 'scheduled' : 'draft');
        $data['published_at'] = $intent === 'publish' ? now() : ($intent === 'schedule' ? $data['published_at'] : null);
        $data['audience_rules'] = json_encode($data['audience_rules'] ?? []);
        $data['created_at'] = now();
        $data['updated_at'] = now();

        $id = DB::table('announcements')->insertGetId($data);
        return redirect()->route('communication.show', $id)->with('success', 'Announcement saved successfully.');
    }

    public function show(int $id)
    {
        $item = DB::table('announcements')->where('school_id', $this->schoolId())->find($id);
        abort_unless($item, 404);
        $reads = DB::table('announcement_reads')->where('announcement_id', $id)->count();
        $deliveryRows = DB::table('communication_deliveries')->where('announcement_id', $id)->latest()->get();
        return view('communication.show', compact('item', 'reads', 'deliveryRows'));
    }

    public function edit(int $id)
    {
        $item = DB::table('announcements')->where('school_id', $this->schoolId())->find($id);
        abort_unless($item, 404);
        $campuses = DB::table('campuses')->where('school_id', $this->schoolId())->orderBy('name')->get();
        return view('communication.edit', compact('item', 'campuses'));
    }

    public function update(Request $request, int $id)
    {
        $item = DB::table('announcements')->where('school_id', $this->schoolId())->find($id);
        abort_unless($item, 404);
        $data = $this->validatedAnnouncement($request);
        $intent = $request->input('intent', 'draft');
        $data['status'] = $intent === 'publish' ? 'published' : ($intent === 'schedule' ? 'scheduled' : 'draft');
        $data['published_at'] = $intent === 'publish' ? ($item->published_at ?: now()) : ($intent === 'schedule' ? $data['published_at'] : null);
        $data['audience_rules'] = json_encode($data['audience_rules'] ?? []);
        $data['updated_at'] = now();
        DB::table('announcements')->where('id', $id)->where('school_id', $this->schoolId())->update($data);
        return redirect()->route('communication.show', $id)->with('success', 'Announcement updated successfully.');
    }

    public function publish(int $id)
    {
        $updated = DB::table('announcements')->where('school_id', $this->schoolId())->where('id', $id)
            ->update(['status' => 'published', 'published_at' => now(), 'updated_at' => now()]);
        abort_unless($updated, 404);
        return back()->with('success', 'Announcement published.');
    }

    public function archive(int $id)
    {
        $updated = DB::table('announcements')->where('school_id', $this->schoolId())->where('id', $id)
            ->update(['status' => 'archived', 'updated_at' => now()]);
        abort_unless($updated, 404);
        return back()->with('success', 'Announcement archived.');
    }

    public function destroy(int $id)
    {
        $item = DB::table('announcements')->where('school_id', $this->schoolId())->find($id);
        abort_unless($item, 404);
        DB::transaction(function () use ($id) {
            DB::table('communication_deliveries')->where('announcement_id', $id)->delete();
            DB::table('announcement_reads')->where('announcement_id', $id)->delete();
            DB::table('announcements')->where('id', $id)->where('school_id', $this->schoolId())->delete();
        });
        return redirect()->route('communication.index')->with('success', 'Announcement deleted.');
    }

    public function messages()
    {
        $items = DB::table('conversations')
            ->where('conversations.school_id', $this->schoolId())
            ->leftJoin('conversation_messages', 'conversation_messages.conversation_id', '=', 'conversations.id')
            ->select('conversations.*', DB::raw('MAX(conversation_messages.created_at) as last_message_at'))
            ->groupBy('conversations.id', 'conversations.school_id', 'conversations.subject', 'conversations.created_by', 'conversations.conversation_type', 'conversations.created_at', 'conversations.updated_at')
            ->orderByDesc('last_message_at')->paginate(15);
        return view('communication.messages', compact('items'));
    }

    public function compose()
    {
        return view('communication.compose');
    }

    public function sendMessage(Request $request)
    {
        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:10000',
            'recipient_ids' => 'required|array|min:1',
            'recipient_ids.*' => 'integer|exists:users,id',
            'conversation_type' => 'nullable|in:direct,group',
        ]);

        $recipientIds = collect($data['recipient_ids'])->unique()->reject(fn ($id) => (int) $id === (int) auth()->id())->values();
        abort_if($recipientIds->isEmpty(), 422, 'Select at least one recipient other than yourself.');

        $conversationId = DB::transaction(function () use ($data, $recipientIds) {
            $id = DB::table('conversations')->insertGetId([
                'school_id' => $this->schoolId(),
                'subject' => $data['subject'],
                'created_by' => auth()->id(),
                'conversation_type' => $data['conversation_type'] ?? ($recipientIds->count() > 1 ? 'group' : 'direct'),
                'created_at' => now(), 'updated_at' => now(),
            ]);

            $participants = $recipientIds->push(auth()->id())->unique();
            foreach ($participants as $userId) {
                DB::table('conversation_participants')->insert([
                    'conversation_id' => $id, 'user_id' => $userId,
                    'last_read_at' => (int) $userId === (int) auth()->id() ? now() : null,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }

            DB::table('conversation_messages')->insert([
                'conversation_id' => $id, 'sender_id' => auth()->id(),
                'body' => $data['message'], 'created_at' => now(), 'updated_at' => now(),
            ]);
            return $id;
        });

        return redirect()->route('communication.message.show', $conversationId)->with('success', 'Message sent.');
    }

    public function messageShow(int $id)
    {
        $conversation = DB::table('conversations')->where('school_id', $this->schoolId())->find($id);
        abort_unless($conversation, 404);
        $isParticipant = DB::table('conversation_participants')->where('conversation_id', $id)->where('user_id', auth()->id())->exists();
        abort_unless($isParticipant || (int) $conversation->created_by === (int) auth()->id(), 403);

        $messages = DB::table('conversation_messages')->where('conversation_id', $id)->orderBy('created_at')->get();
        DB::table('conversation_participants')->where('conversation_id', $id)->where('user_id', auth()->id())->update(['last_read_at' => now(), 'updated_at' => now()]);
        return view('communication.message-show', compact('conversation', 'messages'));
    }

    public function reply(Request $request, int $id)
    {
        $conversation = DB::table('conversations')->where('school_id', $this->schoolId())->find($id);
        abort_unless($conversation, 404);
        $participant = DB::table('conversation_participants')->where('conversation_id', $id)->where('user_id', auth()->id())->exists();
        abort_unless($participant, 403);
        $data = $request->validate(['message' => 'required|string|max:10000']);
        DB::table('conversation_messages')->insert([
            'conversation_id' => $id, 'sender_id' => auth()->id(), 'body' => $data['message'],
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('conversations')->where('id', $id)->update(['updated_at' => now()]);
        return back()->with('success', 'Reply sent.');
    }

    public function templates()
    {
        $items = DB::table('communication_templates')->where('school_id', $this->schoolId())->latest()->paginate(15);
        return view('communication.templates', compact('items'));
    }

    public function storeTemplate(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:150', 'category' => 'required|string|max:80', 'subject' => 'nullable|string|max:255', 'body' => 'required|string|max:10000']);
        DB::table('communication_templates')->insert($data + ['school_id' => $this->schoolId(), 'created_by' => auth()->id(), 'created_at' => now(), 'updated_at' => now()]);
        return back()->with('success', 'Template created.');
    }

    public function deliveries()
    {
        $items = DB::table('communication_deliveries')
            ->join('announcements', 'announcements.id', '=', 'communication_deliveries.announcement_id')
            ->where('announcements.school_id', $this->schoolId())
            ->select('communication_deliveries.*', 'announcements.title')
            ->latest('communication_deliveries.created_at')->paginate(20);
        return view('communication.deliveries', compact('items'));
    }

    private function validatedAnnouncement(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:30000',
            'campus_id' => 'nullable|integer',
            'audience_type' => 'required|in:all_school,campus,staff,students,parents,selected',
            'priority' => 'required|in:normal,high,urgent',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:published_at',
        ]);
        if (!empty($data['campus_id'])) {
            $validCampus = DB::table('campuses')->where('school_id', $this->schoolId())->where('id', $data['campus_id'])->exists();
            abort_unless($validCampus, 422, 'Selected campus does not belong to this school.');
        }
        $data['audience_rules'] = ['campus_id' => $data['campus_id'] ?? null];
        unset($data['campus_id']);
        return $data;
    }
}
