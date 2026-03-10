<?php

namespace App\Jobs;

use App\Enums\UserRole;
use App\Events\AiTyping;
use App\Events\MessageSent;
use App\Models\Message;
use App\Services\OpenAIService;
use App\Support\ChatPrompt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessAudioMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $messageId;
    public function __construct(int $messageId)
    {
        $this->messageId = $messageId;
    }

    /**
     * Execute the job.
     */
    public function handle(OpenAIService $openAIService): void
    {
        $message = Message::find($this->messageId);
        if (!$message) return;

        $conversation = $message->conversation;

        $path = $message->meta['path'] ?? '';

        if (!$path) return;

        $fullPath = storage_path('app/public/' . $path);

        if (!is_file($fullPath)) return;

        // 1. STT

        $transcriptText = $openAIService->transcribeSTT($fullPath);

        if (!empty($transcriptText)) {
            $meta = $message->meta ?? [];

            if (!empty($meta['ai_done'])) return;

            $meta['processing'] = false;
            $meta['transcript'] = $transcriptText;

            $message->update([
                'content' => $transcriptText,
                'meta' => $meta,
            ]);
        }

        // Notify frontend that AI is preparing audio response
        broadcast(new AiTyping($conversation->id, 'audio'));

        // 2. Respond to transcript text
        $aiMessages = ChatPrompt::buildMessages($conversation->id);
        $aiResponse = $openAIService->generateResponse($aiMessages);

        if (!$aiResponse) return;

        // 3. TTS AI Response

        $ttsPath = $openAIService->transcribeTTS($aiResponse, $conversation->id);

        if (!$ttsPath) return;

        $fullTTSPath = Storage::disk('public')->url($ttsPath);

        $aiMessage = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => null,
            'role' => UserRole::AI,
            'content' => $aiResponse,
            'audio_path' => $fullTTSPath,
            'meta' => [
                'type' => 'audio',
                'path' => $ttsPath,
                'url' => $fullTTSPath,
                'mime' => 'audio/mpeg',
                'tts' => [
                    'model' => 'gpt-4o-mini-tts',
                    'voice' => 'cedar',
                    'reply_message_id' => $message->id,
                ]
            ]
        ]);

        $meta = $message->meta ?? [];
        $meta['ai_done'] = true;
        $message->update(['meta' => $meta]);
        $conversation->update(['last_message_at' => now()]);
        broadcast(new MessageSent($aiMessage));
    }
}
