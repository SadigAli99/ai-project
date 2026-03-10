@extends('admin.layout')
@section('title','Söhbətlər')

@section('content')
    <form class="search-form" method="GET">
        <input class="search-input" name="search" value="{{ request('search') }}" placeholder="Başlığa görə axtar...">
        <select name="type" class="search-input" style="flex:0 0 140px">
            <option value="">Hamısı</option>
            <option value="chat" {{ request('type') === 'chat' ? 'selected' : '' }}>Chat</option>
            <option value="live" {{ request('type') === 'live' ? 'selected' : '' }}>Live</option>
        </select>
        <button class="btn btn-primary" type="submit">Axtar</button>
    </form>

    <div class="card">
        <div class="card-header">
            <span>Söhbətlər ({{ $conversations->total() }})</span>
        </div>
        <div class="card-body" style="padding:0">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Başlıq</th>
                        <th>İstifadəçi</th>
                        <th>Tip</th>
                        <th>Mesaj</th>
                        <th>Son mesaj</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($conversations as $conv)
                        <tr>
                            <td style="color:var(--muted-2)">#{{ $conv->id }}</td>
                            <td style="font-weight:600">{{ $conv->title ?: 'Adsız' }}</td>
                            <td>
                                @if($conv->user)
                                    <a href="{{ route('admin.users.show', $conv->user) }}">{{ $conv->user->name }}</a>
                                @else
                                    <span style="color:var(--muted-2)">—</span>
                                @endif
                            </td>
                            <td><span class="badge {{ $conv->type->value === 'live' ? 'badge-danger' : 'badge-primary' }}">{{ $conv->type->value }}</span></td>
                            <td><span class="badge badge-success">{{ $conv->messages_count }}</span></td>
                            <td style="color:var(--muted-2);font-size:12px">{{ $conv->last_message_at?->diffForHumans() ?? '—' }}</td>
                            <td><a href="{{ route('admin.conversations.show', $conv) }}" class="btn" style="padding:4px 10px;font-size:11px">Bax</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:24px">Söhbət tapılmadı</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="pagination">
        {{ $conversations->withQueryString()->links('admin.partials.pagination') }}
    </div>
@endsection
