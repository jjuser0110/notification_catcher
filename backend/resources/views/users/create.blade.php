@extends('layouts.sidebar')
@section('title', 'New '.$roleLabel)

@section('content')
<div class="page-head"><h1>New {{ $roleLabel }}</h1>
    <a href="{{ route('users.index', $role) }}" class="btn ghost">Back</a></div>

<form method="POST" action="{{ route('users.store', $role) }}" class="panel" style="max-width:640px;">
    @csrf
    <div class="row">
        <div class="field"><label>Username</label>
            <input type="text" name="username" value="{{ old('username') }}" required autofocus></div>
        <div class="field"><label>Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required></div>
    </div>
    <div class="row">
        <div class="field"><label>Password</label>
            <input type="text" name="password" value="{{ old('password') }}" required placeholder="min 6 characters"></div>
        <div class="field"><label>Quota (optional)</label>
            <input type="number" step="0.01" name="quota" value="{{ old('quota', 0) }}"></div>
    </div>

    @if($hasRate)
    <div class="row">
        <div class="field">
            <label>Payin rate % — allowed {{ $payinMin }}–{{ $payinMax }}</label>
            <input type="number" step="0.01" name="payin_rate" value="{{ old('payin_rate', $payinDefault) }}"
                   min="{{ $payinMin }}" max="{{ $payinMax }}" required>
        </div>
        <div class="field">
            <label>Payout rate % — allowed {{ $payoutMin }}–{{ $payoutMax }}</label>
            <input type="number" step="0.01" name="payout_rate" value="{{ old('payout_rate', $payoutDefault) }}"
                   min="{{ $payoutMin }}" max="{{ $payoutMax }}" required>
        </div>
    </div>
    @if($role === 'merchant')
        <p class="muted">API key &amp; token are generated automatically. Merchant rate follows your customer rate band.</p>
    @endif
    @endif

    <button class="btn" type="submit">Create {{ $roleLabel }}</button>
</form>
@endsection
