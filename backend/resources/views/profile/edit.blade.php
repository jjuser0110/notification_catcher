@extends('layouts.sidebar')
@section('title', 'Profile')

@section('content')
<div class="page-head"><h1>Profile</h1></div>

<div class="row" style="align-items:flex-start;">
    <div class="panel" style="flex:1; min-width:280px;">
        <h2>Account</h2>
        <div class="field"><label>Name</label><input type="text" value="{{ $me->name }}" disabled></div>
        <div class="field"><label>Username</label><input type="text" value="{{ $me->username }}" disabled></div>
        <div class="field"><label>Role</label><input type="text" value="{{ $me->roleLabel() }}" disabled></div>
        <div class="field"><label>Last login</label>
            <input type="text" value="{{ $me->last_login_at ? $me->last_login_at->format('Y-m-d H:i') : '—' }}" disabled></div>
    </div>

    <div class="panel" style="flex:1; min-width:280px;">
        <h2>Change password</h2>
        <p class="muted" style="margin-top:-6px;">Enter your current password, then your new password twice.</p>
        <form method="POST" action="{{ route('profile.password') }}">
            @csrf @method('PUT')
            <div class="field">
                <label>Current password</label>
                <input type="password" name="current_password" required autocomplete="current-password">
            </div>
            <div class="field">
                <label>New password</label>
                <input type="password" name="password" required autocomplete="new-password">
            </div>
            <div class="field">
                <label>Confirm new password</label>
                <input type="password" name="password_confirmation" required autocomplete="new-password">
            </div>
            <button class="btn" type="submit">Update password</button>
        </form>
    </div>
</div>
@endsection
