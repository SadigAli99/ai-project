<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MessageService
{
    protected $conversationService;

    public function __construct(ConversationService $conversationService)
    {
        $this->conversationService = $conversationService;
    }

    public function sendText(User $user, Conversation $conversation, string $content): Message
    {
        $this->conversationService->assertOwner($conversation, $user);

        return DB::transaction(function () use ($user, $conversation, $content) {

            $meta = [
                'type' => 'text',
                'processing' => true,
                'ai_done' => '',
            ];
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'role' => UserRole::USER,
                'content' => $content,
                'meta' => $meta
            ]);

            $conversation->update(['last_message_at' => now()]);

            return $message;
        });
    }

    public function sendAudio(User $user, Conversation $conversation, UploadedFile $audio): Message
    {
        $this->conversationService->assertOwner($conversation, $user);

        return DB::transaction(function () use ($user, $conversation, $audio) {
            $path = $audio->store('file-audio-' . $conversation->id, 'public');

            $meta = [
                'type' => 'audio',
                'path' => $path,
                'url' => Storage::disk('public')->url($path),
                'mime' => $audio->getMimeType(),
                'size' => $audio->getSize(),
                'original_name' => $audio->getClientOriginalName(),
                'processing' => true,
            ];



            $message = Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'role' => UserRole::USER,
                'content' => '',
                'audio_path' => $path,
                'meta' => $meta,
            ]);

            $conversation->update(['last_message_at' => now()]);

            return $message;
        });
    }
}
