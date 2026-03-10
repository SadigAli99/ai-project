<?php

namespace App\Support;

use App\Enums\UserRole;
use App\Models\Message;
use Illuminate\Support\Str;

class ChatPrompt
{
    public static function build(int $conversationId, int $limit = 25): string
    {
        $aiName = config('chat.ai_name');

        $items = Message::query()
            ->where('conversation_id', $conversationId)
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();

        $lines = [];

        foreach ($items as $m) {
            $text = trim((string) $m->content);

            if ($text === '') continue;

            $text = preg_replace('/\+s/', ' ', $text);
            $text = Str::limit($text, 450);

            $who = $m->role === UserRole::AI ? 'AI' : 'User';
            $lines[] = "{$who}: {$text}";
        }

        return "Sənin adın {$aiName}-dir. İstifadəçi səndən adını soruşsa, dəqiq belə cavab ver : 'Mənim adım {$aiName}-dir'.\n" .
            " Sən Azərbaycan dilində danışan faydalı bir köməkçisən.\n" .
            "Aşağıda söhbətin tarixi var. Tarixçəyə uyğun cavab ver, ziddiyyət olmasın.\n" .
            "Əgər user özü haqqında məlumat veribsə (ad, iş, və s.), onu yadda saxla və uyğun cavab ver.\n\n" .
            implode("\n", $lines) .
            "\n\nCavabı yalnız cavab kimi yaz (əlavə izah/format vermə).";
    }
}
