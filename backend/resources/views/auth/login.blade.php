@extends('layouts.app')
@section('title', 'Log in')

@section('content')
<div style="max-width: 360px; margin: 60px auto;">
    <h1 style="text-align:center; font-size:20px;">📡 Notification Catcher</h1>
    <p class="muted" style="text-align:center; margin-bottom:24px;">Sign in to view your devices</p>

    <form method="POST" action="{{ url('/login') }}"
          style="background:var(--panel); border:1px solid var(--line); border-radius:10px; padding:24px;">
        @csrf

        @error('username')
            <div style="background:rgba(239,68,68,.12); color:#fca5a5; padding:10px; border-radius:6px; margin-bottom:16px;">
                {{ $message }}
            </div>
        @enderror

        <label style="display:block; margin-bottom:6px; color:var(--muted);">Username</label>
        <input type="text" name="username" value="{{ old('username') }}" autofocus required
               style="width:100%; padding:10px; margin-bottom:16px; background:var(--panel2); border:1px solid var(--line); border-radius:6px; color:var(--text);">

        <label style="display:block; margin-bottom:6px; color:var(--muted);">Password</label>
        <input type="password" name="password" required
               style="width:100%; padding:10px; margin-bottom:16px; background:var(--panel2); border:1px solid var(--line); border-radius:6px; color:var(--text);">

        <label style="display:flex; align-items:center; gap:8px; margin-bottom:16px; color:var(--muted);">
            <input type="checkbox" name="remember" value="1"> Remember me
        </label>

        <button class="btn" type="submit" style="width:100%;">Log in</button>
    </form>
</div>
@endsection
