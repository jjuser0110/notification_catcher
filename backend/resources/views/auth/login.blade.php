@extends('layouts.app')
@section('title', 'Log in')

@section('content')
<style>
    main { padding:0 !important; max-width:none !important; }
    .login-screen { min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px;
        position:relative; overflow:hidden; }
    .login-screen::before { content:""; position:absolute; width:620px; height:620px; border-radius:50%;
        top:-220px; right:-160px; background:radial-gradient(circle, rgba(228,0,43,.30), transparent 62%); filter:blur(8px); }
    .login-screen::after { content:""; position:absolute; width:520px; height:520px; border-radius:50%;
        bottom:-220px; left:-160px; background:radial-gradient(circle, rgba(228,0,43,.18), transparent 62%); }
    .login-card { position:relative; z-index:1; width:100%; max-width:390px; background:var(--panel);
        border:1px solid var(--line); border-radius:22px; box-shadow:0 30px 70px rgba(0,0,0,.45); overflow:hidden; }
    .login-top { padding:30px 32px 22px; text-align:center;
        background:linear-gradient(135deg, rgba(228,0,43,.14), rgba(228,0,43,.02)); border-bottom:1px solid var(--line); }
    .login-top .logo { font-size:40px; font-weight:900; font-style:italic; letter-spacing:-1.5px; line-height:1;
        color:var(--accent); }
    .login-top .logo span { color:var(--text); font-style:normal; }
    .login-top .tag { font-size:10px; letter-spacing:.26em; font-weight:800; color:var(--muted); margin-top:7px; }
    .login-body { padding:26px 32px 30px; }
    .login-body h2 { margin:0 0 4px; font-size:18px; }
    .login-body .hint { color:var(--muted); font-size:13px; margin:0 0 22px; }
    .login-field { margin-bottom:16px; }
    .login-field label { display:block; margin-bottom:7px; color:var(--muted); font-size:12.5px; font-weight:600; }
    .login-field input[type=text], .login-field input[type=password] { width:100%; padding:12px 13px;
        background:var(--panel2); border:1px solid var(--line); border-radius:11px; color:var(--text); font-size:14px;
        transition:border-color .15s, box-shadow .15s; }
    .login-field input:focus { outline:none; border-color:var(--accent); box-shadow:0 0 0 3px rgba(228,0,43,.15); }
    .login-remember { display:flex; align-items:center; gap:9px; margin:4px 0 22px; color:var(--muted); font-size:13px; }
    .login-remember input { width:16px; height:16px; accent-color:var(--accent); }
    .login-btn { width:100%; padding:13px; border:none; border-radius:12px; cursor:pointer; font-size:14.5px;
        font-weight:800; color:#fff; letter-spacing:.2px; background:linear-gradient(135deg,#ff3b57,#d20a2d);
        box-shadow:0 12px 26px rgba(228,0,43,.35); transition:filter .15s, transform .05s; }
    .login-btn:hover { filter:brightness(1.06); }
    .login-btn:active { transform:translateY(1px); }
    .login-err { background:rgba(248,113,113,.12); color:var(--red, #f87171); border:1px solid rgba(248,113,113,.3);
        padding:11px 13px; border-radius:11px; margin-bottom:18px; font-size:13px; }
    .login-foot { text-align:center; color:var(--muted); font-size:11.5px; margin-top:18px; letter-spacing:.02em; }
</style>

<div class="login-screen">
    <div class="login-card">
        <div class="login-top">
            <div class="logo">H<span>Pay</span></div>
            <div class="tag">PAYMENT GATEWAY</div>
        </div>
        <div class="login-body">
            <h2>Welcome back</h2>
            <p class="hint">Sign in to your workspace to continue.</p>

            <form method="POST" action="{{ url('/login') }}">
                @csrf
                @error('username')
                    <div class="login-err">{{ $message }}</div>
                @enderror

                <div class="login-field">
                    <label>Username</label>
                    <input type="text" name="username" value="{{ old('username') }}" autofocus required autocomplete="username">
                </div>
                <div class="login-field">
                    <label>Password</label>
                    <input type="password" name="password" required autocomplete="current-password">
                </div>

                <label class="login-remember">
                    <input type="checkbox" name="remember" value="1"> Remember me
                </label>

                <button class="login-btn" type="submit">Sign in</button>
            </form>

            <div class="login-foot">HPay admin workspace · secure sign-in</div>
        </div>
    </div>
</div>
@endsection
