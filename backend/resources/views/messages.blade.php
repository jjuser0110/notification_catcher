@extends('layouts.sidebar')
@section('title', 'Messages')

@section('content')
<div class="page-head"><h1>Notification Catcher — Messages</h1></div>

<form method="GET" action="{{ route('messages') }}" class="panel" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
    <select name="device" style="width:auto;">
        <option value="">All phones</option>
        @foreach($devices as $device)
            <option value="{{ $device->id }}" @selected(($filters['device'] ?? null) == $device->id)>{{ $device->name }}</option>
        @endforeach
    </select>
    <select name="type" style="width:auto;">
        <option value="">All types</option>
        <option value="notification" @selected(($filters['type'] ?? null) === 'notification')>Notifications</option>
        <option value="sms" @selected(($filters['type'] ?? null) === 'sms')>SMS</option>
    </select>
    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search text, sender, app…" style="width:240px;">
    <button class="btn" type="submit">Filter</button>
    <a href="{{ route('messages') }}" class="btn ghost">Reset</a>
</form>

@if($messages->isEmpty())
    <div class="panel muted" style="text-align:center;">No messages match.</div>
@else
<table>
    <thead><tr><th>Type</th><th>Phone</th><th>App / Sender</th><th>Content</th><th>Received</th></tr></thead>
    <tbody>
        @foreach($messages as $message)
        <tr>
            <td><span class="tag {{ $message->type === 'sms' ? 'approved' : 'on' }}">{{ $message->type === 'sms' ? 'SMS' : 'Notif' }}</span></td>
            <td>{{ $message->device->name }}</td>
            <td>
                @if($message->type === 'sms')
                    <strong>{{ $message->sender ?: 'Unknown' }}</strong>
                @else
                    <strong>{{ $message->app_name ?: $message->app_package }}</strong>
                @endif
            </td>
            <td>
                @if($message->title)<strong>{{ $message->title }}</strong><br>@endif
                <span class="muted">{{ \Illuminate\Support\Str::limit($message->body, 200) }}</span>
            </td>
            <td class="muted" title="{{ $message->received_at }}">{{ $message->received_at->diffForHumans() }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
<div>{{ $messages->links() }}</div>
@endif
@endsection
