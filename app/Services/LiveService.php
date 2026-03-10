<?php

namespace App\Services;

use App\Support\ChatPrompt;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LiveService
{
    protected OpenAIService $openAI;

    public function __construct(OpenAIService $openAI)
    {
        $this->openAI = $openAI;
    }

    /**
     * Canlı səs sorğusunu emal et: STT → AI → TTS
     *
     * @param UploadedFile $audio
     * @param array $history  [{role, content}, ...]
     * @return array{transcript: string, reply: string, audio: string|null}
     */
    public function respond(UploadedFile $audio, array $history = []): array
    {
        // 1. Temp faylı .webm uzantısı ilə saxla (OpenAI uzantıdan formatı tanıyır)
        $tmpPath = sys_get_temp_dir() . '/' . uniqid('live_') . '.webm';
        copy($audio->getRealPath(), $tmpPath);

        // 2. STT
        $transcript = $this->openAI->transcribeSTT($tmpPath);
        @unlink($tmpPath);

        if (!$transcript || trim($transcript) === '') {
            return ['transcript' => '', 'reply' => '', 'audio' => null];
        }

        // 3. History-yə yeni user mesajını əlavə et
        $history[] = ['role' => 'user', 'content' => $transcript];

        // 4. AI cavab
        $aiText = $this->generateAiResponse($history);

        if (!$aiText) {
            return ['transcript' => $transcript, 'reply' => '', 'audio' => null];
        }

        // 5. TTS
        $audioBytes = $this->openAI->ttsRaw($aiText);

        return [
            'transcript' => $transcript,
            'reply' => $aiText,
            'audio' => $audioBytes ? base64_encode($audioBytes) : null,
        ];
    }

    protected function generateAiResponse(array $messages): ?string
    {
        try {
            $response = Http::withToken(config('openai.api_key'))
                ->timeout(60)
                ->post('https://api.openai.com/v1/responses', [
                    'model' => 'gpt-5.2',
                    'instructions' => ChatPrompt::systemPrompt(),
                    'input' => $messages,
                    'max_output_tokens' => 500,
                    'store' => false,
                    'reasoning' => ['effort' => 'medium'],
                ]);

            if (!$response->successful()) {
                Log::error('Live AI HTTP error: ' . $response->status() . ' ' . $response->body());
                return null;
            }

            $data = $response->json();
            $texts = [];

            foreach (($data['output'] ?? []) as $item) {
                if (($item['type'] ?? null) === 'message' && ($item['role'] ?? null) === 'assistant') {
                    foreach (($item['content'] ?? []) as $content) {
                        if (($content['type'] ?? null) === 'output_text') {
                            $texts[] = $content['text'] ?? '';
                        }
                    }
                }
            }

            return trim(implode("\n", array_filter($texts))) ?: null;
        } catch (\Throwable $e) {
            Log::error('Live AI exception: ' . $e->getMessage());
            return null;
        }
    }
}
