@extends('layouts.app')
@section('title', 'Devices')

@section('content')
<div class="cards">
    <div class="card"><div class="label">Phones</div><div class="value">{{ $stats['devices'] }}</div></div>
    <div class="card"><div class="label">Online now</div><div class="value" style="color:var(--green)">{{ $stats['online'] }}</div></div>
    <div class="card"><div class="label">Notifications</div><div class="value">{{ number_format($stats['notifications']) }}</div></div>
    <div class="card"><div class="label">SMS</div><div class="value">{{ number_format($stats['sms']) }}</div></div>
</div>

<div class="section-title">Installed phones</div>

@if($devices->isEmpty())
    <div class="card empty">No phones yet. Install the app and register with the enroll key.</div>
@else
<table>
    <thead>
        <tr>
            <th>Status</th><th>Name</th><th>Device</th><th>Android</th>
            <th>Messages</th><th>Last seen</th><th></th>
        </tr>
    </thead>
    <tbody>
        @foreach($devices as $device)
        <tr>
            <td><span class="dot {{ $device->isOnline() ? 'on' : 'off' }}"></span>{{ $device->isOnline() ? 'Online' : 'Offline' }}</td>
            <td><strong>{{ $device->name }}</strong></td>
            <td class="muted">{{ $device->manufacturer }} {{ $device->model }}</td>
            <td class="muted">{{ $device->android_version ? 'Android '.$device->android_version : '—' }}</td>
            <td>{{ number_format($device->messages_count) }}</td>
            <td class="muted">{{ $device->last_seen_at?->diffForHumans() ?? 'never' }}</td>
            <td><a href="{{ route('messages', ['device' => $device->id]) }}">View →</a></td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif
@endsection
