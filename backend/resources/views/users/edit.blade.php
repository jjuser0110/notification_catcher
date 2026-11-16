@extends('layouts.sidebar')
@section('title', 'Edit '.$user->username)

@section('content')
<div class="page-head"><h1>Edit {{ $user->username }}</h1>
    <a href="{{ route('users.index', $user->role) }}" class="btn ghost">Back</a></div>

<form method="POST" action="{{ route('users.update', $user) }}" class="panel" style="max-width:640px;">
    @csrf @method('PUT')
    <div class="row">
        <div class="field"><label>Username (read-only)</label>
            <input type="text" value="{{ $user->username }}" disabled></div>
        <div class="field"><label>Name</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required></div>
    </div>
    <div class="field" style="max-width:300px;"><label>Quota</label>
        <input type="number" step="0.01" name="quota" value="{{ old('quota', $user->quota) }}"></div>

    @if($hasRate)
    <div class="row">
        <div class="field"><label>Payin rate % — allowed {{ $payinMin }}–{{ $payinMax }}</label>
            <input type="number" step="0.01" name="payin_rate" value="{{ old('payin_rate', $user->payin_rate) }}"
                   min="{{ $payinMin }}" max="{{ $payinMax }}" required></div>
        <div class="field"><label>Payout rate % — allowed {{ $payoutMin }}–{{ $payoutMax }}</label>
            <input type="number" step="0.01" name="payout_rate" value="{{ old('payout_rate', $user->payout_rate) }}"
                   min="{{ $payoutMin }}" max="{{ $payoutMax }}" required></div>
    </div>
    @endif

    <button class="btn" type="submit">Save changes</button>
</form>

<form method="POST" action="{{ route('users.reset', $user) }}" class="panel" style="max-width:640px;">
    @csrf
    <h2>Reset password</h2>
    <div class="row" style="align-items:flex-end;">
        <div class="field"><label>New password</label>
            <input type="text" name="password" placeholder="min 6 characters" required></div>
        <div class="field" style="flex:0;"><button class="btn" type="submit">Reset</button></div>
    </div>
</form>

@if($user->role === 'merchant')
<div class="panel" style="max-width:640px;">
    <h2>API credentials</h2>
    <div class="field"><label>X-API-KEY</label><code class="k">{{ $user->api_key }}</code></div>
    <div class="field"><label>X-API-TOKEN</label><code class="k">{{ $user->api_token }}</code></div>
</div>
@endif
@endsection
