<?php

namespace App\Support;

use App\Enums\UserRole;
use App\Models\Message;
use Illuminate\Support\Str;

class ChatPrompt
{
    public static function systemPrompt(): string
    {
        $aiName = config('app.name', 'Callini');

        return "Sənin adın {$aiName}-dir. İstifadəçi səndən adını soruşsa, dəqiq belə cavab ver: 'Mənim adım {$aiName}-dir'.\n" .
            "Sən Azərbaycan dilində danışan faydalı bir köməkçisən.\n" .
            "Söhbətin tarixçəsinə uyğun cavab ver, ziddiyyət olmasın.\n" .
            "Əgər istifadəçi özü haqqında məlumat veribsə (ad, iş, yaş və s.), onu yadda saxla və uyğun cavab ver.\n" .
            "Cavabı yalnız cavab kimi yaz (əlavə izah/format vermə).";
    }

    public static function buildMessages(int $conversationId, int $limit = 25): array
    {
        $items = Message::query()
            ->where('conversation_id', $conversationId)
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();

        $messages = [];

        foreach ($items as $m) {
            $text = trim((string) $m->content);

            if ($text === '') continue;

            $text = preg_replace('/\s+/', ' ', $text);
            $text = Str::limit($text, 1000);

            $role = $m->role === UserRole::AI ? 'assistant' : 'user';

            $messages[] = [
                'role' => $role,
                'content' => $text,
            ];
        }

        return $messages;
    }
}
