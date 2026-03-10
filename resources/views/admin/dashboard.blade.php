@extends('admin.layout')
@section('title','Dashboard')

@section('content')
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Ümumi İstifadəçilər</div>
            <div class="stat-value">{{ number_format($totalUsers) }}</div>
            <div class="stat-sub">Bu gün: +{{ $todayUsers }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Ümumi Söhbətlər</div>
            <div class="stat-value">{{ number_format($totalConversations) }}</div>
            <div class="stat-sub">Bu gün: +{{ $todayConversations }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Ümumi Mesajlar</div>
            <div class="stat-value">{{ number_format($totalMessages) }}</div>
            <div class="stat-sub">Bu gün: +{{ $todayMessages }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Canlı Yayınlar</div>
            <div class="stat-value">{{ number_format($totalLiveSessions) }}</div>
            <div class="stat-sub">Bu gün: +{{ $todayLiveSessions }}</div>
        </div>
    </div>

    {{-- 7-günlük qrafik --}}
    <div class="card">
        <div class="card-header">Son 7 gün statistikası</div>
        <div class="card-body">
            @php $maxVal = max($dailyStats->max('messages_count'), 1); @endphp
            <div class="chart-bars">
                @foreach($dailyStats as $day)
                    <div class="chart-col">
                        <div class="chart-val">{{ $day->messages_count }}</div>
                        <div class="chart-bar" style="height:{{ ($day->messages_count / $maxVal) * 100 }}%"></div>
                        <div class="chart-label">{{ \Carbon\Carbon::parse($day->date)->format('d/m') }}</div>
                    </div>
                @endforeach
            </div>
            <div style="display:flex;gap:20px;margin-top:12px;font-size:11px;color:var(--muted)">
                <span>Mesajlar: {{ $dailyStats->sum('messages_count') }}</span>
                <span>Söhbətlər: {{ $dailyStats->sum('conversations_count') }}</span>
                <span>Canlı: {{ $dailyStats->sum('live_sessions_count') }}</span>
            </div>
        </div>
    </div>

    {{-- Son istifadəçilər --}}
    <div class="card">
        <div class="card-header">Son qeydiyyat olan istifadəçilər</div>
        <div class="card-body" style="padding:0">
            <table>
                <thead>
                    <tr>
                        <th>Ad</th>
                        <th>Email</th>
                        <th>Söhbət</th>
                        <th>Qeydiyyat</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentUsers as $user)
                        <tr>
                            <td style="font-weight:700">{{ $user->name }}</td>
                            <td style="color:var(--muted)">{{ $user->email }}</td>
                            <td><span class="badge badge-primary">{{ $user->conversations_count }}</span></td>
                            <td style="color:var(--muted-2);font-size:12px">{{ $user->created_at->diffForHumans() }}</td>
                            <td><a href="{{ route('admin.users.show', $user) }}" class="btn" style="padding:4px 10px;font-size:11px">Bax</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
