<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class CaspaAIService
{
    public function respond(
        string $prompt,
        string $role = 'general',
        array $context = []
    ): array {
        $key = config('services.gemini.key');
        $model = config('services.gemini.model', 'gemini-3.5-flash-lite');
        if (!$key) {
            throw new RuntimeException(
                'Gemini is not configured. Add GEMINI_API_KEY to your .env file.'
            );
        }

        $instructions = match ($role) {
            'student' => 'You are CASPA Student Learning, a supportive school tutor. Explain concepts clearly, encourage learning, and guide students step by step. Do not claim access to school records unless supplied in the context.',

            'teacher' => 'You are CASPA Teacher Assistant. Help teachers prepare lesson plans, worksheets, quizzes and teaching material. Mark generated academic content as a draft requiring teacher review before publishing.',

            'principal' => 'You are CASPA Principal Intelligence. Separate verified facts from assumptions and recommendations. Never invent school metrics. If data is absent, clearly say so.',

            default => 'You are CASPA, the SchoolNexa school management assistant. Be accurate, professional and concise. Do not claim access to school records unless supplied in the context.',
        };

        $contextText = json_encode(
            $context,
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        );

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-goog-api-key' => $key,
        ])
            ->acceptJson()
            ->connectTimeout(15)
            ->timeout(45)
            ->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent",
                [
                    'system_instruction' => [
                        'parts' => [
                            ['text' => $instructions],
                        ],
                    ],

                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                [
                                    'text' =>
                                        "Role context: {$role}\n\n" .
                                        "Authorized application context:\n{$contextText}\n\n" .
                                        "User request:\n{$prompt}",
                                ],
                            ],
                        ],
                    ],

                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 2048,
                    ],
                ]
            );

        if (!$response->successful()) {
            Log::warning('CASPA Gemini request failed', [
                'status' => $response->status(),
                'error' => $response->json('error.message'),
            ]);

            if ($response->status() === 429) {
                throw new RuntimeException(
                    'Gemini API rate limit or quota exceeded. Check your Google AI Studio usage and billing limits.'
                );
            }

            throw new RuntimeException(
                'Gemini API request failed (HTTP ' .
                $response->status() .
                '). Check your API key, model access and Laravel logs.'
            );
        }

        $data = $response->json();

        $text = collect(
            data_get($data, 'candidates.0.content.parts', [])
        )
            ->where('text', '!=', null)
            ->pluck('text')
            ->implode("\n");

        if (trim($text) === '') {
            $reason = data_get(
                $data,
                'candidates.0.finishReason'
            );

            Log::warning('CASPA Gemini returned no text', [
                'finish_reason' => $reason,
            ]);

            throw new RuntimeException(
                'Gemini returned no response text. Please try again.'
            );
        }

        return [
            'text' => $text,
            'response_id' => null,
            'model' => $model,
        ];
    }
}
