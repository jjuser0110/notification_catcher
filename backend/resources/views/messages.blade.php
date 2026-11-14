@extends('layouts.app')
@section('title', 'Messages')

@section('content')
<div class="section-title">Messages &amp; notifications</div>

<form method="GET" action="{{ route('messages') }}" class="filters">
    <select name="device">
        <option value="">All phones</option>
        @foreach($devices as $device)
            <option value="{{ $device->id }}" @selected(($filters['device'] ?? null) == $device->id)>{{ $device->name }}</option>
        @endforeach
    </select>
    <select name="type">
        <option value="">All types</option>
        <option value="notification" @selected(($filters['type'] ?? null) === 'notification')>Notifications</option>
        <option value="sms" @selected(($filters['type'] ?? null) === 'sms')>SMS</option>
    </select>
    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search text, sender, app…">
    <button class="btn" type="submit">Filter</button>
    <a href="{{ route('messages') }}" class="btn ghost" style="line-height:1.6;">Reset</a>
</form>

@if($messages->isEmpty())
    <div class="card empty">No messages match.</div>
@else
<table>
    <thead>
        <tr><th>Type</th><th>Phone</th><th>App / Sender</th><th>Content</th><th>Received</th></tr>
    </thead>
    <tbody>
        @foreach($messages as $message)
        <tr>
            <td><span class="tag {{ $message->type }}">{{ $message->type === 'sms' ? 'SMS' : 'Notif' }}</span></td>
            <td>{{ $message->device->name }}</td>
            <td>
                @if($message->type === 'sms')
                    <strong>{{ $message->sender ?: 'Unknown' }}</strong>
                @else
                    <strong>{{ $message->app_name ?: $message->app_package }}</strong>
                    @if($message->sender)<div class="muted">{{ $message->sender }}</div>@endif
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
<div class="pagination">{{ $messages->links() }}</div>
@endif
@endsection
