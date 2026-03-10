@extends('admin.layout')
@section('title', $conversation->title ?: 'Söhbət #' . $conversation->id)

@section('content')
    <div style="margin-bottom:16px"><a href="{{ route('admin.conversations.index') }}" class="btn" style="font-size:12px">← Geri</a></div>

    <div class="info-grid">
        <div class="info-item">
            <div class="info-label">İstifadəçi</div>
            <div class="info-value">
                @if($conversation->user)
                    <a href="{{ route('admin.users.show', $conversation->user) }}">{{ $conversation->user->name }}</a>
                @else —
                @endif
            </div>
        </div>
        <div class="info-item">
            <div class="info-label">Tip</div>
            <div class="info-value"><span class="badge {{ $conversation->type->value === 'live' ? 'badge-danger' : 'badge-primary' }}">{{ $conversation->type->value }}</span></div>
        </div>
        <div class="info-item">
            <div class="info-label">Yaradılıb</div>
            <div class="info-value">{{ $conversation->created_at->format('d.m.Y H:i') }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Mesaj sayı</div>
            <div class="info-value">{{ $conversation->messages->count() }}</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Mesajlar</div>
        <div class="card-body">
            @forelse($conversation->messages as $msg)
                @php
                    $role = $msg->role->value ?? 'user';
                    $meta = $msg->meta ?? [];
                @endphp
                <div class="msg-row {{ $role }}">
                    <div class="msg-bubble">
                        <div class="msg-role">{{ $role === 'ai' ? 'AI' : ($role === 'system' ? 'System' : ($msg->user?->name ?? 'İstifadəçi')) }}</div>
                        @if(($meta['type'] ?? '') === 'audio' && ($meta['url'] ?? $msg->audio_path))
                            <div style="font-size:12px;color:var(--muted)">🎤 Səsli mesaj</div>
                            <audio controls preload="metadata" style="width:100%;height:32px;margin-top:6px">
                                <source src="{{ $meta['url'] ?? $msg->audio_path }}">
                            </audio>
                        @endif
                        @if($msg->content)
                            <div>{{ $msg->content }}</div>
                        @endif
                        <div class="msg-time">{{ $msg->created_at->format('H:i') }}</div>
                    </div>
                </div>
            @empty
                <div style="text-align:center;color:var(--muted);padding:24px">Mesaj yoxdur</div>
            @endforelse
        </div>
    </div>
@endsection
