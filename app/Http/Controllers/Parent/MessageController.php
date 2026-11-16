<?php

namespace App\Http\Controllers\Parent;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MessageController extends ParentController
{
    private function participantConversation(int $id)
    {
        $conversation = DB::table('conversations')->where('id', $id)->first();
        abort_unless($conversation, 404);

        $participant = DB::table('conversation_participants')
            ->where('conversation_id', $id)->where('user_id', auth()->id())->exists();

        abort_unless($participant, 403);
        return $conversation;
    }

    private function teacherRecipients(array $context)
    {
        $sectionIds = collect($context['enrollments'])->pluck('section_id')->filter()->unique()->values()->all();
        if (!$sectionIds) return collect();

        return DB::table('teaching_assignments as ta')
            ->join('staff_profiles as sp', 'sp.id', '=', 'ta.staff_profile_id')
            ->join('sections as sec', 'sec.id', '=', 'ta.section_id')
            ->whereIn('ta.section_id', $sectionIds)
            ->whereNull('sp.deleted_at')
            ->where('sp.status', 'active')
            ->whereNotNull('sp.user_id')
            ->select('sp.user_id', 'sp.id as staff_profile_id', 'sp.first_name', 'sp.last_name', 'sec.name as section_name')
            ->distinct()->orderBy('sp.first_name')->get();
    }

    public function index()
    {
        $this->parentContext();

        $items = DB::table('conversations as c')
            ->join('conversation_participants as cp', 'cp.conversation_id', '=', 'c.id')
            ->where('cp.user_id', auth()->id())
            ->leftJoin('conversation_messages as cm', function ($join) {
                $join->on('cm.conversation_id', '=', 'c.id')
                    ->whereRaw('cm.id = (SELECT MAX(cm2.id) FROM conversation_messages cm2 WHERE cm2.conversation_id = c.id)');
            })
            ->select('c.*', 'cm.body as last_message', 'cm.created_at as last_message_at')
            ->orderByDesc('cm.created_at')->paginate(15);

        return view('parent.messages.index', compact('items'));
    }

    public function create()
    {
        $context = $this->parentContext();
        $teachers = $this->teacherRecipients($context);
        return view('parent.messages.create', compact('teachers', 'context'));
    }

    public function store(Request $request)
    {
        $context = $this->parentContext();
        $data = $request->validate([
            'recipient_user_id' => 'required|integer',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:10000',
        ]);

        $allowed = $this->teacherRecipients($context)
            ->firstWhere('user_id', (int) $data['recipient_user_id']);
        if (!$allowed) {
            throw ValidationException::withMessages([
                'recipient_user_id' => 'You can only start a conversation with a teacher assigned to one of your children.',
            ]);
        }

        // The recipient was resolved from a teaching assignment in a linked child's section.
        $schoolId = DB::table('staff_profiles')
            ->where('user_id', $allowed->user_id)
            ->value('school_id');

        $conversationId = DB::transaction(function () use ($data, $schoolId, $allowed) {
            $id = DB::table('conversations')->insertGetId([
                'school_id' => $schoolId,
                'subject' => $data['subject'],
                'created_by' => auth()->id(),
                'conversation_type' => 'direct',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ([auth()->id(), $allowed->user_id] as $userId) {
                DB::table('conversation_participants')->insert([
                    'conversation_id' => $id,
                    'user_id' => $userId,
                    'last_read_at' => (int) $userId === (int) auth()->id() ? now() : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('conversation_messages')->insert([
                'conversation_id' => $id,
                'sender_id' => auth()->id(),
                'body' => $data['message'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return $id;
        });

        return redirect()->route('parent.messages.show', $conversationId)->with('success', 'Message sent.');
    }

    public function show(int $id)
    {
        $this->parentContext();
        $conversation = $this->participantConversation($id);
        $messages = DB::table('conversation_messages as cm')
            ->join('users as u', 'u.id', '=', 'cm.sender_id')
            ->where('cm.conversation_id', $id)
            ->select('cm.*', 'u.name as sender_name')
            ->orderBy('cm.created_at')->get();

        DB::table('conversation_participants')
            ->where('conversation_id', $id)->where('user_id', auth()->id())
            ->update(['last_read_at' => now(), 'updated_at' => now()]);

        return view('parent.messages.show', compact('conversation', 'messages'));
    }

    public function reply(Request $request, int $id)
    {
        $this->parentContext();
        $this->participantConversation($id);
        $data = $request->validate(['message' => 'required|string|max:10000']);

        DB::table('conversation_messages')->insert([
            'conversation_id' => $id,
            'sender_id' => auth()->id(),
            'body' => $data['message'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('conversations')->where('id', $id)->update(['updated_at' => now()]);
        return back()->with('success', 'Reply sent.');
    }
}
