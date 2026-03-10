@extends('admin.layout')
@section('title','İstifadəçilər')

@section('content')
    <form class="search-form" method="GET">
        <input class="search-input" name="search" value="{{ request('search') }}" placeholder="Ad və ya email ilə axtar...">
        <button class="btn btn-primary" type="submit">Axtar</button>
    </form>

    <div class="card">
        <div class="card-header">
            <span>Bütün istifadəçilər ({{ $users->total() }})</span>
        </div>
        <div class="card-body" style="padding:0">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ad</th>
                        <th>Email</th>
                        <th>Söhbət</th>
                        <th>Mesaj</th>
                        <th>Admin</th>
                        <th>Qeydiyyat</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td style="color:var(--muted-2)">#{{ $user->id }}</td>
                            <td style="font-weight:700">{{ $user->name }}</td>
                            <td style="color:var(--muted)">{{ $user->email }}</td>
                            <td><span class="badge badge-primary">{{ $user->conversations_count }}</span></td>
                            <td><span class="badge badge-success">{{ $user->messages_count }}</span></td>
                            <td>
                                @if($user->is_admin)
                                    <span class="badge badge-danger">Admin</span>
                                @endif
                            </td>
                            <td style="color:var(--muted-2);font-size:12px">{{ $user->created_at->format('d.m.Y') }}</td>
                            <td><a href="{{ route('admin.users.show', $user) }}" class="btn" style="padding:4px 10px;font-size:11px">Bax</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:24px">İstifadəçi tapılmadı</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="pagination">
        {{ $users->withQueryString()->links('admin.partials.pagination') }}
    </div>
@endsection
