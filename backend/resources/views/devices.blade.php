@extends('layouts.sidebar')
@section('title', 'Devices')

@section('content')
<div class="page-head"><h1>Notification Catcher — Devices</h1></div>

<div class="cards">
    <div class="card"><div class="label">Phones</div><div class="value">{{ $deviceStats['devices'] }}</div></div>
    <div class="card"><div class="label">Online now</div><div class="value" style="color:var(--green)">{{ $deviceStats['online'] }}</div></div>
    <div class="card"><div class="label">Notifications</div><div class="value">{{ number_format($deviceStats['notifications']) }}</div></div>
    <div class="card"><div class="label">SMS</div><div class="value">{{ number_format($deviceStats['sms']) }}</div></div>
</div>

@if($devices->isEmpty())
    <div class="panel muted" style="text-align:center;">No phones yet.</div>
@else
<table>
    <thead><tr><th>Status</th><th>Name</th><th>Device</th><th>Android</th><th class="right">Messages</th><th>Last seen</th><th class="right"></th></tr></thead>
    <tbody>
        @foreach($devices as $d)
        <tr>
            <td data-label="Status"><span class="tag {{ $d->isOnline() ? 'on' : 'off' }}">{{ $d->isOnline() ? 'Online' : 'Offline' }}</span></td>
            <td data-label="Name"><strong>{{ $d->name }}</strong></td>
            <td data-label="Device" class="muted">{{ $d->manufacturer }} {{ $d->model }}</td>
            <td data-label="Android" class="muted">{{ $d->android_version ? 'Android '.$d->android_version : '—' }}</td>
            <td data-label="Messages" class="right">{{ number_format($d->messages_count) }}</td>
            <td data-label="Last seen" class="muted">{{ $d->last_seen_at?->diffForHumans() ?? 'never' }}</td>
            <td class="right actions"><a href="{{ route('messages', ['device' => $d->id]) }}" class="btn ghost sm">View</a></td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif
@endsection
