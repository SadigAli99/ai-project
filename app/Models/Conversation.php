<?php

namespace App\Models;

use App\Enums\ConversationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type', // sadə chat və ya live chat (ai ilə səsli danışıq olacaq)
        'title', // title (hər bir chat ilk mesaj yarandıqdan sonra, oradan 50 simvolluq bir şey seçib, başlığa qoyacaq)
        'last_message_at', // mesajın son göndərilmə tarixi
        'user_id', // kim tərəfindən yaradılıb
    ];

    protected $casts = [
        'type' => ConversationType::class,
        'last_message_at' => 'datetime',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'conversation_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function lastMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }
}
