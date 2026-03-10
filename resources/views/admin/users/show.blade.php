@extends('admin.layout')
@section('title', $user->name)

@section('content')
    <div style="margin-bottom:16px"><a href="{{ route('admin.users.index') }}" class="btn" style="font-size:12px">← Geri</a></div>

    <div class="info-grid">
        <div class="info-item">
            <div class="info-label">Ad</div>
            <div class="info-value">{{ $user->name }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Email</div>
            <div class="info-value">{{ $user->email }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Qeydiyyat</div>
            <div class="info-value">{{ $user->created_at->format('d.m.Y H:i') }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Status</div>
            <div class="info-value">
                @if($user->is_admin) <span class="badge badge-danger">Admin</span>
                @else <span class="badge badge-success">İstifadəçi</span>
                @endif
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Söhbətləri ({{ $user->conversations->count() }})</div>
        <div class="card-body" style="padding:0">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Başlıq</th>
                        <th>Tip</th>
                        <th>Mesaj</th>
                        <th>Son mesaj</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($user->conversations as $conv)
                        <tr>
                            <td style="color:var(--muted-2)">#{{ $conv->id }}</td>
                            <td style="font-weight:600">{{ $conv->title ?: 'Adsız' }}</td>
                            <td><span class="badge {{ $conv->type->value === 'live' ? 'badge-danger' : 'badge-primary' }}">{{ $conv->type->value }}</span></td>
                            <td><span class="badge badge-success">{{ $conv->messages_count }}</span></td>
                            <td style="color:var(--muted-2);font-size:12px">{{ $conv->last_message_at?->diffForHumans() ?? '—' }}</td>
                            <td><a href="{{ route('admin.conversations.show', $conv) }}" class="btn" style="padding:4px 10px;font-size:11px">Bax</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:24px">Söhbət yoxdur</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
