<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Teacher\Concerns\ResolvesTeacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TeacherCommunicationController extends Controller
{
    use ResolvesTeacher;

    private function allowedRecipients($teacher)
    {
        $parents = DB::table('guardians as g')
            ->join('student_guardians as sg', 'sg.guardian_id', '=', 'g.id')
            ->join('student_enrollments as se', 'se.student_id', '=', 'sg.student_id')
            ->join('teaching_assignments as ta', function ($join) {
                $join->on('ta.section_id', '=', 'se.section_id')
                    ->on('ta.academic_year_id', '=', 'se.academic_year_id');
            })
            ->join('class_subjects as cs', 'cs.id', '=', 'ta.class_subject_id')
            ->join('school_classes as sc', 'sc.id', '=', 'cs.school_class_id')
            ->where('ta.staff_profile_id', $teacher->id)
            ->where('sc.school_id', $teacher->school_id)
            ->where('se.status', 'active')
            ->where('sg.receives_communications', true)
            ->whereNotNull('g.user_id')
            ->select(
                'g.user_id as id',
                'g.full_name as name',
                'g.email',
                DB::raw("'Parent' as recipient_type")
            )
            ->distinct()
            ->get();

        $principals = DB::table('user_role_assignments as ura')
            ->join('roles as r', 'r.id', '=', 'ura.role_id')
            ->join('users as u', 'u.id', '=', 'ura.user_id')
            ->where('ura.school_id', $teacher->school_id)
            ->where('ura.is_active', true)
            ->where('r.code', 'PRINCIPAL')
            ->where('r.is_active', true)
            ->where(function ($q) {
                $q->whereNull('ura.starts_at')->orWhere('ura.starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ura.expires_at')->orWhere('ura.expires_at', '>=', now());
            })
            ->select(
                'u.id',
                'u.name',
                'u.email',
                DB::raw("'Principal' as recipient_type")
            )
            ->distinct()
            ->get();

        return $parents->concat($principals)
            ->unique('id')
            ->reject(fn($recipient) => (int) $recipient->id === (int) auth()->id())
            ->values();
    }


    public function index()
    {
        $teacher = $this->currentTeacher();

        $lastMessages = DB::table('messages')
            ->select('conversation_id', DB::raw('MAX(id) as last_message_id'))
            ->whereNull('deleted_at')
            ->groupBy('conversation_id');

        // Resolve the other participants' actual names.
        $otherParticipants = DB::table('conversation_participants as ocp')
            ->join('users as ou', 'ou.id', '=', 'ocp.user_id')
            ->leftJoin('staff_profiles as osp', 'osp.user_id', '=', 'ou.id')
            ->leftJoin('guardians as og', 'og.user_id', '=', 'ou.id')
            ->whereColumn('ocp.conversation_id', 'c.id')
            ->where('ocp.user_id', '<>', auth()->id())
            ->selectRaw("
            GROUP_CONCAT(
                DISTINCT COALESCE(
                    NULLIF(CONCAT_WS(' ',
                        NULLIF(osp.first_name, ''),
                        NULLIF(osp.last_name, '')
                    ), ''),
                    NULLIF(og.full_name, ''),
                    ou.name
                )
                ORDER BY ou.name
                SEPARATOR ', '
            )
        ");

        // Resolve participant types.
        $otherParticipantTypes = DB::table('conversation_participants as ocp')
            ->join('users as ou', 'ou.id', '=', 'ocp.user_id')
            ->leftJoin('staff_profiles as osp', 'osp.user_id', '=', 'ou.id')
            ->leftJoin('guardians as og', 'og.user_id', '=', 'ou.id')
            ->whereColumn('ocp.conversation_id', 'c.id')
            ->where('ocp.user_id', '<>', auth()->id())
            ->selectRaw("
            GROUP_CONCAT(
                DISTINCT CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM user_role_assignments ura
                        JOIN roles r ON r.id = ura.role_id
                        WHERE ura.user_id = ou.id
                          AND ura.school_id = c.school_id
                          AND ura.is_active = 1
                          AND r.code = 'PRINCIPAL'
                          AND r.is_active = 1
                    ) THEN 'Principal'
                    WHEN osp.id IS NOT NULL THEN 'Teacher'
                    WHEN og.id IS NOT NULL THEN 'Parent'
                    ELSE 'Participant'
                END
                SEPARATOR ', '
            )
        ");

        $items = DB::table('conversations as c')
            ->join('conversation_participants as cp', 'cp.conversation_id', '=', 'c.id')
            ->leftJoinSub(
                $lastMessages,
                'lm',
                fn($join) => $join->on('lm.conversation_id', '=', 'c.id')
            )
            ->leftJoin('messages as m', 'm.id', '=', 'lm.last_message_id')
            ->where('c.school_id', $teacher->school_id)
            ->where('cp.user_id', auth()->id())
            ->select(
                'c.id',
                'c.subject',
                'c.conversation_type',
                'c.updated_at',
                'm.body as last_message',
                'm.created_at as last_message_at',
                'm.sender_id as last_sender_id'
            )
            ->selectSub($otherParticipants, 'other_participant_name')
            ->selectSub($otherParticipantTypes, 'other_participant_type')
            ->selectRaw("
            COALESCE(
                (
                    SELECT NULLIF(CONCAT_WS(' ',
                        NULLIF(sp.first_name, ''),
                        NULLIF(sp.last_name, '')
                    ), '')
                    FROM staff_profiles sp
                    WHERE sp.user_id = m.sender_id
                    LIMIT 1
                ),
                (
                    SELECT g.full_name
                    FROM guardians g
                    WHERE g.user_id = m.sender_id
                    LIMIT 1
                ),
                (
                    SELECT u.name
                    FROM users u
                    WHERE u.id = m.sender_id
                    LIMIT 1
                )
            ) as last_sender_name
        ")
            ->orderByRaw('COALESCE(m.created_at, c.updated_at) DESC')
            ->paginate(15);

        return view('teacher.communication.index', compact('teacher', 'items'));
    }


    public function compose()
    {
        $teacher = $this->currentTeacher();
        $recipients = $this->allowedRecipients($teacher);

        return view('teacher.communication.compose', compact('teacher', 'recipients'));
    }

    public function send(Request $request)
    {
        $teacher = $this->currentTeacher();

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:10000'],
            'recipient_ids' => ['required', 'array', 'min:1'],
            'recipient_ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $allowedIds = $this->allowedRecipients($teacher)->pluck('id')->map(fn($id) => (int) $id)->all();
        $requestedIds = collect($data['recipient_ids'])->map(fn($id) => (int) $id)->unique()->values();

        if ($requestedIds->isEmpty() || $requestedIds->diff($allowedIds)->isNotEmpty()) {
            throw ValidationException::withMessages([
                'recipient_ids' => 'One or more selected recipients are not linked to your assigned classes or school.',
            ]);
        }

        $conversationId = DB::transaction(function () use ($data, $requestedIds, $teacher) {
            $id = DB::table('conversations')->insertGetId([
                'school_id' => $teacher->school_id,
                'subject' => $data['subject'],
                'created_by' => auth()->id(),
                'conversation_type' => $requestedIds->count() > 1 ? 'group' : 'direct',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($requestedIds->push((int) auth()->id())->unique() as $userId) {
                DB::table('conversation_participants')->insert([
                    'conversation_id' => $id,
                    'user_id' => $userId,
                    'last_read_at' => (int) $userId === (int) auth()->id() ? now() : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('messages')->insert([
                'conversation_id' => $id,
                'sender_id' => auth()->id(),
                'body' => $data['message'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $id;
        });

        return redirect()->route('teacher.messages.show', $conversationId)->with('success', 'Message sent.');
    }


    public function show(int $id)
    {
        $teacher = $this->currentTeacher();

        $conversation = DB::table('conversations as c')
            ->join('conversation_participants as cp', 'cp.conversation_id', '=', 'c.id')
            ->where('c.id', $id)
            ->where('c.school_id', $teacher->school_id)
            ->where('cp.user_id', auth()->id())
            ->select('c.*')
            ->first();

        abort_unless($conversation, 404);

        // Get the other participants in this conversation.
        $otherParticipants = DB::table('conversation_participants as cp')
            ->join('users as u', 'u.id', '=', 'cp.user_id')
            ->leftJoin('staff_profiles as sp', 'sp.user_id', '=', 'u.id')
            ->leftJoin('guardians as g', 'g.user_id', '=', 'u.id')
            ->where('cp.conversation_id', $id)
            ->where('cp.user_id', '<>', auth()->id())
            ->select(
                'u.id',
                'u.name',
                'u.email',
                'sp.first_name',
                'sp.last_name',
                'g.full_name'
            )
            ->get()
            ->map(function ($person) use ($teacher) {

                $person->display_name = trim(
                    ($person->first_name ?? '') . ' ' .
                    ($person->last_name ?? '')
                );

                if (!$person->display_name) {
                    $person->display_name = $person->full_name ?: $person->name;
                }

                if ($person->full_name) {
                    $person->role = 'Parent';
                } elseif ($person->first_name || $person->last_name) {
                    $person->role = 'Teacher';
                } else {
                    $isPrincipal = DB::table('user_role_assignments as ura')
                        ->join('roles as r', 'r.id', '=', 'ura.role_id')
                        ->where('ura.user_id', $person->id)
                        ->where('ura.school_id', $teacher->school_id)
                        ->where('ura.is_active', true)
                        ->where('r.code', 'PRINCIPAL')
                        ->where('r.is_active', true)
                        ->exists();

                    $person->role = $isPrincipal ? 'Principal' : 'Participant';
                }

                return $person;
            });

        $messages = DB::table('messages as m')
            ->join('users as u', 'u.id', '=', 'm.sender_id')
            ->leftJoin('staff_profiles as sp', 'sp.user_id', '=', 'u.id')
            ->leftJoin('guardians as g', 'g.user_id', '=', 'u.id')
            ->where('m.conversation_id', $id)
            ->whereNull('m.deleted_at')
            ->orderBy('m.created_at')
            ->orderBy('m.id')
            ->get([
                'm.id',
                'm.body',
                'm.created_at',
                'm.sender_id',
                'u.name as sender_name',
                'sp.first_name',
                'sp.last_name',
                'g.full_name',
            ])
            ->map(function ($message) {
                $staffName = trim(
                    ($message->first_name ?? '') . ' ' .
                    ($message->last_name ?? '')
                );

                if ($staffName) {
                    $message->sender_name = $staffName;
                } elseif ($message->full_name) {
                    $message->sender_name = $message->full_name;
                }

                return $message;
            });

        DB::table('conversation_participants')
            ->where('conversation_id', $id)
            ->where('user_id', auth()->id())
            ->update([
                'last_read_at' => now(),
                'updated_at' => now(),
            ]);

        return view(
            'teacher.communication.show',
            compact('teacher', 'conversation', 'messages', 'otherParticipants')
        );
    }


    public function reply(Request $request, int $id)
    {
        $teacher = $this->currentTeacher();

        $isParticipant = DB::table('conversations as c')
            ->join('conversation_participants as cp', 'cp.conversation_id', '=', 'c.id')
            ->where('c.id', $id)
            ->where('c.school_id', $teacher->school_id)
            ->where('cp.user_id', auth()->id())
            ->exists();

        abort_unless($isParticipant, 404);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:10000'],
        ]);

        DB::transaction(function () use ($id, $data) {
            DB::table('messages')->insert([
                'conversation_id' => $id,
                'sender_id' => auth()->id(),
                'body' => $data['message'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('conversations')->where('id', $id)->update(['updated_at' => now()]);
        });

        return back()->with('success', 'Reply sent.');
    }
}
