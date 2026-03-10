<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use OpenAI\Laravel\Facades\OpenAI;

class OpenAIService
{
    public function transcribeSTT(string $fullPath)
    {
        $transcriptText = null;

        try {
            $response = OpenAI::audio()->transcribe([
                'model' => 'gpt-4o-transcribe',
                'file' => fopen($fullPath, 'r'),
                'language' => 'az',
                'temperature' => 0.0,
                'response_format' => 'json',

                // ✅ prompt-u daha konkret et (xüsusi adları qorumağa kömək edir)
                'prompt' =>
                "Bu Azərbaycan dilində danışıqdır. " .
                    "Mətnə heç nə əlavə etmə, yalnız eşitdiyini yaz. " .
                    "Xüsusi adları olduğu kimi saxla (məs: Sadiq). " .
                    "\"adım\" sözünü \"adam\" kimi dəyişmə.",

                'include' => ['logprobs'],
            ]);

            $transcriptText = $response->text ?? null;
            if ($transcriptText) {
                $transcriptText = preg_replace(
                    '/\badam\s+([A-ZƏÖÜĞÇŞİ][\p{L}-]{2,})dir\b/ui',
                    'adım $1dir',
                    $transcriptText
                );
            }

            return $transcriptText;
        } catch (\Throwable $e) {
            Log::error('Open AI STT error : ' . $e->getMessage());
            return null;
        }
    }

    public function generateResponse(string $transcriptText)
    {
        $aiText = null;

        try {

            $response = Http::withToken(config('openai.api_key'))
                ->timeout(60)
                ->post('https://api.openai.com/v1/responses', [
                    'model' => 'gpt-5.2',
                    'instructions' => 'Transcribe exactly what you hear. Keep proper names unchanged.',
                    'input' => $transcriptText,
                    // 'temperature' => 0.3,
                    'max_output_tokens' => 200,
                    'store' => false,
                    'reasoning' => ['effort' => 'medium'],
                ]);

            if ($response->successful()) {
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

                $aiText = trim(implode("\n", array_filter($texts)));
            } else {
                Log::error('OpenAI /responses HTTP error : ' . $response->status() . ' ' . $response->body());
            }

            return $aiText;
        } catch (\Throwable $e) {
            Log::error('OpenAI /responses Exception : ' . $e->getMessage());
        }
    }

    public function transcribeTTS(string $aiResponse, int $conversation_id): string
    {

        try {
            $aiTextForTTS = trim($aiResponse ?? '');

            if ($aiTextForTTS !== '') {
                $aiTextForTTS = mb_substr($aiTextForTTS, 0, 4000);

                $ttsResponse = Http::withToken(config('openai.api_key'))
                    ->asJson()
                    ->post('https://api.openai.com/v1/audio/speech', [
                        'model' => 'gpt-4o-mini-tts',
                        'voice' => 'coral',
                        'input' => $aiTextForTTS,
                        'response_format' => 'mp3',
                        'instructions' => 'Azərbaycan dilində səlis və təbii fonda danış',
                        'speed' => 1.0,
                    ]);

                if ($ttsResponse->successful()) {
                    $bytes = $ttsResponse->body();
                    $ttsPath = 'files-tts-' . $conversation_id . '/' . Str::uuid() . '.mp3';
                    Storage::disk('public')->put($ttsPath, $bytes);

                    return $ttsPath;
                } else {
                    Log::error('OPENAI TTS Error : ' . $ttsResponse->status() . ' ' . $ttsResponse->body());
                    return '';
                }
            }
            return '';
        } catch (\Throwable $ex) {
            Log::error('OpenAI TTS exception : ' . $ex->getMessage());
            return '';
        }
    }
}
