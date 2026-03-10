<?php

namespace App\Services;

use App\Enums\ConversationType;
use App\Enums\UserRole;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ConversationService
{
    public function list(Request $request): LengthAwarePaginator
    {
        $userId = $request->user()->id;
        $search = trim((string)$request->get('search'));
        $query = Conversation::query()->where('user_id', $userId);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('lastMessage', fn($m) => $m->where('content', 'like', "%{$search}%"));
            });
        }

        $conversations = $query
            ->orderBy('last_message_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20);

        return $conversations;
    }

    public function createWithFirstMessage(User $user, string $message_type, ?string $content = null, ?UploadedFile $audioFile = null): array
    {
        return DB::transaction(function () use ($user, $message_type, $content, $audioFile) {
            $title = $this->makeTitleFromFirstMessage($content);
            $conversation = Conversation::create([
                'type' => ConversationType::CHAT,
                'title' => $title,
                'user_id' => $user->id,
                'last_message_at' => now(),
            ]);

            $messageData = [
                'content' => $content,
                'role' => UserRole::USER,
                'user_id' => $user->id,
                'conversation_id' => $conversation->id,
                'meta' => [],
            ];

            if ($message_type == 'text') {
                $messageData['meta'] = [
                    'type' => 'text',
                    'processing' => true,
                    'ai_done' => '',
                ];
            } else if ($message_type == 'audio') {

                $path = $audioFile->store('file-audio-' . $conversation->id, 'public');
                $messageData['meta'] = [
                    'type' => 'audio',
                    'path' => $path,
                    'url' => Storage::disk('public')->url($path),
                    'mime' => $audioFile->getMimeType(),
                    'size' => $audioFile->getSize(),
                    'original_name' => $audioFile->getClientOriginalName(),
                    'processing' => true,
                ];
            }

            $message = Message::create($messageData);

            return [$conversation, $message];
        });
    }

    public function messages(Conversation $conversation)
    {
        $this->assertOwner($conversation, request()->user());
        $conversation->load([
            'messages' => fn($q) => $q->with('user:id,name')->orderBy('id', 'desc')->paginate(20)
        ]);

        $conversation->setRelation('messages', $conversation->messages->reverse()->values());

        return $conversation;
    }

    public function rename(User $user, Conversation $conversation, string $title): Conversation
    {
        $this->assertOwner($conversation, $user);
        $conversation->update([
            'title' => trim($title),
        ]);

        return $conversation->refresh();
    }

    public function delete(User $user, Conversation $conversation)
    {
        $this->assertOwner($conversation, $user);

        $conversation->loadMissing('messages'); // ehtiyat üçün

        foreach ($conversation->messages as $message) {
            $audio = $message->audio_path;

            // text mesaj ola bilər
            if (empty($audio)) {
                continue;
            }

            // 1) URL-dirsə -> yalnız path hissəsini götür: /storage/files-tts-5/xxx.mp3
            if (filter_var($audio, FILTER_VALIDATE_URL)) {
                $audio = parse_url($audio, PHP_URL_PATH) ?? '';
            }

            // 2) /storage/... formasındadırsa -> public disk üçün relativ yola çevir
            // /storage/files-tts-5/xxx.mp3  => files-tts-5/xxx.mp3
            $audio = preg_replace('#^/storage/#', '', $audio);
            $audio = ltrim($audio, '/');

            // 3) boş qalıbsa keç
            if ($audio === '') {
                continue;
            }

            Storage::disk('public')->delete($audio);
        }

        $conversation->delete();
    }

    public function assertOwner(Conversation $conversation, User $user): void
    {
        abort_unless((int)$conversation->user_id === (int) $user->id, 403);
    }

    private function makeTitleFromFirstMessage(?string $content = null): string
    {
        $clean = trim(preg_replace('/\s+/', ' ', $content));
        $clean = Str::limit($clean, 60, '');
        return $clean !== '' ? $clean : 'Yeni Chat';
    }
}
