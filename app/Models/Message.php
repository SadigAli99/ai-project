<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'role', // User, AI, System seçimləri olacaq, yaradılan mesajın hansı növ istifadəçi tərəfindən yaradıldığı göstəriləcək.
        'content', // Mesajın mətni (null ola bilər).
        'audio_path', // Səsli mesaj göndərilsə, saxlanılan path.
        'meta', // Mesaj haqqında məlumat (tutalım, səsli mesaj göndərdik, ölçüsü, fayl növü və s. məlumatları burada saxlayacağıq)
        'conversation_id', // Hansı söhbətin mesajıdır, o qeyd olunacaq.
        'user_id', // hansı user tərəfindən yazılır(null ola bilər).
    ];

    protected $casts = [
        'role' => UserRole::class,
        'meta' => 'array',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'conversation_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
