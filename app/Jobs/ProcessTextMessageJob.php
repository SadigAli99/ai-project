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

class ProcessTextMessageJob implements ShouldQueue
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

        $content = $message->content;

        if (!$content) return;

        $meta = $message->meta ?? [];
        if (!empty($meta['ai_done'])) return;

        $meta['processing'] = false;

        $message->update([
            'meta' => $meta,
        ]);

        // Notify frontend that AI is typing
        broadcast(new AiTyping($conversation->id, 'text'));

        $aiMessages = ChatPrompt::buildMessages($conversation->id);

        $aiResponse = $openAIService->generateResponse($aiMessages);

        $aiMessage = Message::create([
            'user_id' => null,
            'conversation_id' => $conversation->id,
            'role' => UserRole::AI,
            'content' => $aiResponse,
            'audio_path' => '',
            'meta' => [
                'type' => 'text',
                'reply_message_id' => $message->id,
            ]
        ]);



        $meta = $message->meta ?? [];
        $meta['ai_done'] = true;
        $message->update(['meta' => $meta]);
        $conversation->update(['last_message_at' => now()]);
        broadcast(new MessageSent($aiMessage));
    }
}
