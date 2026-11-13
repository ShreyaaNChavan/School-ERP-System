<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CaspaConversationService
{
    public function history(string $screen, int $limit = 10)
    {
        return DB::table('caspa_ai_conversations')
            ->where('user_id', auth()->id())
            ->where('role_context', $screen)
            ->where('status', 'active')
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get();
    }

    public function messages(int $conversationId)
    {
        return DB::table('caspa_ai_messages')
            ->where('conversation_id', $conversationId)
            ->orderBy('id')
            ->get();
    }

    public function create(
        string $screen,
        string $title = 'New conversation'
    ): int {
        return DB::table('caspa_ai_conversations')->insertGetId([
            'user_id' => auth()->id(),
            'school_id' => (int) session('school_id', 1),
            'role_context' => $screen,
            'title' => mb_substr($title, 0, 180),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function addMessage(
        int $conversationId,
        string $role,
        string $content,
        array $meta = []
    ): void {
        DB::table('caspa_ai_messages')->insert([
            'conversation_id' => $conversationId,

            'sender' => $role === 'assistant'
                ? 'assistant'
                : 'user',

            'content' => $content,

            'provider_response_id' =>
                $meta['provider_response_id'] ?? null,

            'model' => $meta['model'] ?? null,

            // Your database requires non-null token values.
            'input_tokens' => (int) ($meta['input_tokens'] ?? 0),

            'output_tokens' => (int) ($meta['output_tokens'] ?? 0),

            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('caspa_ai_conversations')
            ->where('id', $conversationId)
            ->where('user_id', auth()->id())
            ->update([
                'updated_at' => now(),
            ]);
    }

    public function ownedConversation(int $id, string $screen)
    {
        return DB::table('caspa_ai_conversations')
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->where('role_context', $screen)
            ->first();
    }
}
