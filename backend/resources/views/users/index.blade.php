@extends('layouts.sidebar')
@section('title', $title)

@section('content')
<div class="page-head">
    <h1>{{ $title }}</h1>
    <form method="GET" class="inline-form">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search username / name" style="width:220px;display:inline-block;">
    </form>
    @if($canCreate)
        <a href="{{ route('users.create', $role) }}" class="btn">+ New {{ ucfirst($role) }}</a>
    @endif
</div>

@if($users->isEmpty())
    <div class="panel muted" style="text-align:center;">No {{ $role }}s yet.</div>
@else
<table>
    <thead>
        <tr>
            <th>Username</th><th>Name</th><th>Parent</th>
            @if($hasRate)<th class="right">Payin %</th><th class="right">Payout %</th>@endif
            <th class="right">Payin wallet</th><th class="right">Payout wallet</th>
            <th>Status</th><th>Last login</th><th class="right">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $user)
        <tr>
            <td><strong>{{ $user->username }}</strong></td>
            <td>{{ $user->name }}</td>
            <td class="muted">{{ $user->parent?->username ?? '—' }}</td>
            @if($hasRate)
                <td class="right">{{ $user->payin_rate !== null ? number_format($user->payin_rate,2) : '—' }}</td>
                <td class="right">{{ $user->payout_rate !== null ? number_format($user->payout_rate,2) : '—' }}</td>
            @endif
            <td class="right">{{ number_format($user->payin_wallet,2) }}</td>
            <td class="right">{{ number_format($user->payout_wallet,2) }}</td>
            <td><span class="tag {{ $user->is_active ? 'on' : 'off' }}">{{ $user->is_active ? 'Active' : 'Disabled' }}</span></td>
            <td class="muted">{{ $user->last_login_at?->diffForHumans() ?? 'never' }}</td>
            <td class="right" style="white-space:nowrap;">
                <a href="{{ route('users.edit', $user) }}" class="btn ghost sm">Edit</a>
                <form method="POST" action="{{ route('users.toggle', $user) }}" class="inline-form">@csrf
                    <button class="btn sm {{ $user->is_active ? 'danger' : 'green' }}">{{ $user->is_active ? 'Disable' : 'Enable' }}</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
<div>{{ $users->links() }}</div>
@endif
@endsection
