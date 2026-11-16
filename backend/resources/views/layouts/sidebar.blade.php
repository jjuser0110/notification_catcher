@php
    $u = auth()->user();
    $theme = ($u && $u->theme === 'light') ? 'light' : 'dark';
    if (!function_exists('hpayIcon')) {
        function hpayIcon($n) {
            $p = [
                'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/>',
                'shield'    => '<path d="M12 3l7 3v5c0 4.6-3 7.8-7 9-4-1.2-7-4.4-7-9V6z"/>',
                'users'     => '<circle cx="9" cy="8" r="3"/><path d="M3.5 20c0-3 2.7-5 5.5-5s5.5 2 5.5 5"/><path d="M16 5.2a3 3 0 010 5.6"/><path d="M18 15.3c1.9.6 3.5 2.1 3.5 4.7"/>',
                'user'      => '<circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-3.5 3.1-6 7-6s7 2.5 7 6"/>',
                'store'     => '<path d="M4 9l1.2-4.5h13.6L20 9"/><path d="M5 9v10h14V9"/><path d="M4 9a2 2 0 004 0 2 2 0 004 0 2 2 0 004 0 2 2 0 004 0"/><path d="M10 19v-5h4v5"/>',
                'payin'     => '<path d="M12 3v11"/><path d="M8 10l4 4 4-4"/><rect x="4" y="18" width="16" height="3" rx="1"/>',
                'settle'    => '<path d="M4 8h12l-3-3"/><path d="M20 16H8l3 3"/>',
                'bank'      => '<path d="M3 9l9-5 9 5"/><path d="M5 9.5v8M9.5 9.5v8M14.5 9.5v8M19 9.5v8"/><path d="M3.5 21h17"/>',
                'card'      => '<rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="M3 10h18"/>',
                'sliders'   => '<path d="M4 7h8M16 7h4M4 12h4M12 12h8M4 17h12M19 17h1"/><circle cx="14" cy="7" r="2"/><circle cx="10" cy="12" r="2"/><circle cx="17.5" cy="17" r="2"/>',
                'phone'     => '<rect x="7" y="3" width="10" height="18" rx="2.5"/><path d="M10.5 18h3"/>',
                'message'   => '<path d="M4 5h16v11H9l-4 4z"/>',
            ];
            return '<svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($p[$n] ?? '').'</svg>';
        }
    }
@endphp
<!DOCTYPE html>
<html lang="en" data-theme="{{ $theme }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Hpay') — Hpay</title>
    <style>
        :root {
            --bg:#0b1220; --panel:#131c2e; --panel2:#1b2740; --line:#273449;
            --text:#e6edf6; --muted:#8aa0bd; --accent:#38bdf8; --accent-ink:#06283b;
            --accent2:#0ea5e9; --green:#22c55e; --amber:#f59e0b; --red:#ef4444; --purple:#a855f7;
            --topbar:#0f1830; --shadow:0 10px 30px rgba(0,0,0,.45);
            --sidebar-w:244px; --rail-w:74px; --topbar-h:60px;
        }
        :root[data-theme="light"] {
            --bg:#eef2f7; --panel:#ffffff; --panel2:#f3f6fb; --line:#e2e8f0;
            --text:#0f1b2d; --muted:#5b7290; --accent:#0ea5e9; --accent-ink:#ffffff;
            --accent2:#0284c7; --green:#16a34a; --amber:#d97706; --red:#dc2626; --purple:#9333ea;
            --topbar:#ffffff; --shadow:0 10px 30px rgba(15,23,42,.14);
        }
        * { box-sizing:border-box; }
        html,body { height:100%; }
        body { margin:0; font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;
            background:var(--bg); color:var(--text); font-size:14px; padding-top:var(--topbar-h);
            transition:background .2s,color .2s; }
        a { color:var(--accent); text-decoration:none; }

        /* ---- Topbar ---- */
        .topbar { position:fixed; top:0; left:0; right:0; height:var(--topbar-h); z-index:40;
            display:flex; align-items:center; gap:14px; padding:0 18px;
            background:var(--topbar); border-bottom:1px solid var(--line); }
        .topbar .brand { font-weight:800; font-size:19px; letter-spacing:.3px; }
        .topbar .brand span { color:var(--accent); }
        .icon-btn { width:38px; height:38px; display:inline-flex; align-items:center; justify-content:center;
            border:1px solid var(--line); background:var(--panel); color:var(--text); border-radius:9px;
            cursor:pointer; flex:none; transition:background .15s,border-color .15s; }
        .icon-btn:hover { background:var(--panel2); border-color:var(--accent); }
        .icon-btn svg { width:20px; height:20px; }
        .topbar .spacer { flex:1; }
        .topbar .who { color:var(--muted); font-size:13px; }
        .topbar .who b { color:var(--text); }
        .pill { font-size:11px; padding:3px 10px; border-radius:999px; background:var(--panel2);
            color:var(--accent); text-transform:uppercase; letter-spacing:.04em; font-weight:700; border:1px solid var(--line); }
        #themeToggle .sun { display:none; }
        :root[data-theme="light"] #themeToggle .sun { display:block; }
        :root[data-theme="light"] #themeToggle .moon { display:none; }

        /* ---- Sidebar ---- */
        .sidebar { position:fixed; top:var(--topbar-h); left:0; bottom:0; width:var(--sidebar-w);
            background:var(--panel); border-right:1px solid var(--line); overflow-y:auto; z-index:30;
            transition:width .2s ease, transform .2s ease; }
        .nav { padding:12px 0; }
        .nav-group { padding:16px 20px 6px; font-size:10.5px; text-transform:uppercase;
            letter-spacing:.09em; color:var(--muted); white-space:nowrap; }
        .nav-link { position:relative; display:flex; align-items:center; gap:13px; padding:10px 20px;
            color:var(--muted); font-weight:500; border-left:3px solid transparent; white-space:nowrap; }
        .nav-link .ic { width:21px; height:21px; flex:none; }
        .nav-link .label { flex:1; }
        .nav-link:hover { background:var(--panel2); color:var(--text); }
        .nav-link.active { background:var(--panel2); color:var(--text); border-left-color:var(--accent); }
        .badge { background:var(--amber); color:#1a1200; font-size:11px; font-weight:700;
            border-radius:999px; padding:1px 8px; min-width:20px; text-align:center; }
        .badge.zero { background:var(--line); color:var(--muted); }

        /* ---- Collapsed rail (desktop) ---- */
        body.collapsed .sidebar { width:var(--rail-w); }
        body.collapsed .main { margin-left:var(--rail-w); }
        body.collapsed .nav-link { justify-content:center; padding:12px 0; gap:0; }
        body.collapsed .nav-link .label { display:none; }
        body.collapsed .nav-group { text-align:center; font-size:0; padding:10px 0 4px; }
        body.collapsed .nav-group::after { content:"•"; font-size:12px; color:var(--line); }
        body.collapsed .nav-link .badge { position:absolute; top:5px; right:12px; min-width:8px;
            height:8px; padding:0; border-radius:50%; font-size:0; }
        body.collapsed .nav-link .badge.zero { display:none; }

        /* ---- Main ---- */
        .main { margin-left:var(--sidebar-w); min-height:calc(100vh - var(--topbar-h));
            transition:margin-left .2s ease; }
        .content { padding:24px; max-width:1240px; width:100%; }
        .overlay { display:none; position:fixed; inset:var(--topbar-h) 0 0 0; background:rgba(2,8,20,.55); z-index:20; }

        /* ---- Mobile: off-canvas drawer ---- */
        @media (max-width:1023px) {
            .sidebar { transform:translateX(-100%); box-shadow:var(--shadow); width:var(--sidebar-w); }
            .main { margin-left:0; }
            body.nav-open .sidebar { transform:none; }
            body.nav-open .overlay { display:block; }
            body.collapsed .sidebar { width:var(--sidebar-w); }
            body.collapsed .nav-link { justify-content:flex-start; padding:10px 20px; gap:13px; }
            body.collapsed .nav-link .label { display:block; }
            body.collapsed .nav-group { text-align:left; font-size:10.5px; padding:16px 20px 6px; }
            body.collapsed .nav-group::after { content:""; }
            body.collapsed .nav-link .badge { position:static; min-width:20px; height:auto; padding:1px 8px;
                border-radius:999px; font-size:11px; }
            .topbar .who { display:none; }
            .content { padding:16px; }
            table { display:block; max-width:100%; overflow-x:auto; -webkit-overflow-scrolling:touch; }
        }
        @media (max-width:480px) { .topbar .pill { display:none; } }

        /* ---- Shared UI ---- */
        .cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:14px; margin-bottom:22px; }
        .card { background:var(--panel); border:1px solid var(--line); border-radius:14px; padding:18px; box-shadow:var(--shadow); }
        .card .label { color:var(--muted); font-size:12px; text-transform:uppercase; letter-spacing:.04em; }
        .card .value { font-size:26px; font-weight:800; margin-top:6px; }
        .panel { background:var(--panel); border:1px solid var(--line); border-radius:14px; padding:18px; margin-bottom:20px; box-shadow:var(--shadow); }
        .panel h2 { margin:0 0 14px; font-size:15px; }
        table { width:100%; border-collapse:collapse; background:var(--panel);
            border:1px solid var(--line); border-radius:14px; overflow:hidden; box-shadow:var(--shadow); }
        th,td { text-align:left; padding:11px 13px; border-bottom:1px solid var(--line); vertical-align:middle; }
        th { background:var(--panel2); color:var(--muted); font-size:12px; text-transform:uppercase; letter-spacing:.03em; }
        tr:last-child td { border-bottom:none; }
        tbody tr:hover td { background:var(--panel2); }
        .muted { color:var(--muted); }
        .right { text-align:right; }
        .tag { display:inline-block; padding:2px 9px; border-radius:999px; font-size:11px; font-weight:700; }
        .tag.pending { background:rgba(245,158,11,.15); color:var(--amber); }
        .tag.success,.tag.approved,.tag.on { background:rgba(34,197,94,.15); color:var(--green); }
        .tag.failed,.tag.rejected,.tag.off { background:rgba(239,68,68,.15); color:var(--red); }
        input,select,textarea { background:var(--panel2); border:1px solid var(--line); color:var(--text);
            padding:9px 11px; border-radius:9px; font-size:13px; width:100%; }
        input:focus,select:focus,textarea:focus { outline:none; border-color:var(--accent); }
        label { display:block; color:var(--muted); font-size:12px; margin:0 0 5px; }
        .field { margin-bottom:14px; }
        .row { display:flex; gap:14px; flex-wrap:wrap; }
        .row > .field { flex:1; min-width:150px; }
        .btn { background:var(--accent); color:var(--accent-ink); border:none; padding:9px 16px; border-radius:9px;
            font-weight:700; cursor:pointer; font-size:13px; display:inline-block; }
        .btn:hover { background:var(--accent2); }
        .btn.ghost { background:transparent; border:1px solid var(--line); color:var(--muted); }
        .btn.ghost:hover { color:var(--text); border-color:var(--accent); }
        .btn.sm { padding:5px 11px; font-size:12px; }
        .btn.danger { background:var(--red); color:#fff; }
        .btn.green { background:var(--green); color:#04260f; }
        .flash { padding:11px 14px; border-radius:10px; margin-bottom:16px; font-weight:500; }
        .flash.ok { background:rgba(34,197,94,.12); color:var(--green); border:1px solid rgba(34,197,94,.3); }
        .flash.err { background:rgba(239,68,68,.12); color:var(--red); border:1px solid rgba(239,68,68,.3); }
        .pagination { margin-top:16px; display:flex; gap:6px; flex-wrap:wrap; list-style:none; padding:0; }
        .pagination a,.pagination span { padding:6px 11px; border:1px solid var(--line); border-radius:8px; color:var(--text); background:var(--panel); }
        .pagination .active span { background:var(--accent); color:var(--accent-ink); border-color:var(--accent); }
        .pagination .disabled span { color:var(--muted); }
        .page-head { display:flex; align-items:center; gap:14px; margin-bottom:18px; }
        .page-head h1 { font-size:21px; margin:0; flex:1; }
        .inline-form { display:inline; }
        code.k { background:var(--panel2); padding:2px 7px; border-radius:6px; font-size:12px; color:var(--accent); word-break:break-all; }
    </style>
</head>
<body>
<header class="topbar">
    <button class="icon-btn" id="menuToggle" aria-label="Toggle menu" title="Toggle sidebar">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
    <div class="brand"><span>H</span>pay</div>
    <div class="spacer"></div>
    <button class="icon-btn" id="themeToggle" aria-label="Toggle theme" title="Toggle light / dark">
        <svg class="moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.8A9 9 0 1111.2 3 7 7 0 0021 12.8z"/></svg>
        <svg class="sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M19.1 4.9l-1.4 1.4M6.3 17.7l-1.4 1.4"/></svg>
    </button>
    <div class="who">Signed in as <b>{{ $u->name }}</b></div>
    <span class="pill">{{ $u->roleLabel() }}</span>
    <form method="POST" action="{{ route('logout') }}" class="inline-form">@csrf
        <button class="btn ghost sm" type="submit">Log out</button>
    </form>
</header>

<aside class="sidebar" id="sidebar">
    <nav class="nav">
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" title="Dashboard">
            {!! hpayIcon('dashboard') !!}<span class="label">Dashboard</span>
        </a>

        <div class="nav-group">Users</div>
        @if($u->isSuperadmin())
            <a href="{{ route('users.index','admin') }}" class="nav-link {{ request()->is('users/admin*') ? 'active' : '' }}" title="Admins">
                {!! hpayIcon('shield') !!}<span class="label">Admins</span>
            </a>
        @endif
        @if($u->isSuperadmin() || $u->isAdmin())
            <a href="{{ route('users.index','staff') }}" class="nav-link {{ request()->is('users/staff*') ? 'active' : '' }}" title="Staff">
                {!! hpayIcon('user') !!}<span class="label">Staff</span>
            </a>
        @endif
        @if($u->isStaffSide() || $u->isCustomer())
            <a href="{{ route('users.index','customer') }}" class="nav-link {{ request()->is('users/customer*') ? 'active' : '' }}" title="Customers">
                {!! hpayIcon('users') !!}<span class="label">Customers</span>
            </a>
            <a href="{{ route('users.index','merchant') }}" class="nav-link {{ request()->is('users/merchant*') ? 'active' : '' }}" title="Merchants">
                {!! hpayIcon('store') !!}<span class="label">Merchants</span>
            </a>
        @endif

        <div class="nav-group">Transactions</div>
        <a href="{{ route('payins.index') }}" class="nav-link {{ request()->routeIs('payins.*') ? 'active' : '' }}" title="Payins">
            {!! hpayIcon('payin') !!}<span class="label">Payins</span>
        </a>
        <a href="{{ route('settlements.index') }}" class="nav-link {{ request()->routeIs('settlements.*') ? 'active' : '' }}" title="Settlements">
            {!! hpayIcon('settle') !!}<span class="label">Settlements</span>
            <span class="badge {{ ($sidebarPending ?? 0) === 0 ? 'zero' : '' }}">{{ $sidebarPending ?? 0 }}</span>
        </a>
        @if($u->isCustomer())
            <a href="{{ route('settlement_banks.index') }}" class="nav-link {{ request()->routeIs('settlement_banks.*') ? 'active' : '' }}" title="Settlement Banks">
                {!! hpayIcon('bank') !!}<span class="label">Settlement Banks</span>
            </a>
        @endif

        @if($u->isStaffSide())
            <div class="nav-group">Configuration</div>
            <a href="{{ route('banks.index') }}" class="nav-link {{ request()->routeIs('banks.*') ? 'active' : '' }}" title="Banks">
                {!! hpayIcon('bank') !!}<span class="label">Banks</span>
            </a>
            <a href="{{ route('bank_settings.index') }}" class="nav-link {{ request()->routeIs('bank_settings.*') ? 'active' : '' }}" title="Bank Accounts">
                {!! hpayIcon('card') !!}<span class="label">Bank Accounts</span>
            </a>
            @if($u->isSuperadmin())
                <a href="{{ route('master_settings.edit') }}" class="nav-link {{ request()->routeIs('master_settings.*') ? 'active' : '' }}" title="Master Settings">
                    {!! hpayIcon('sliders') !!}<span class="label">Master Settings</span>
                </a>
            @endif

            <div class="nav-group">Notification Catcher</div>
            <a href="{{ route('devices') }}" class="nav-link {{ request()->routeIs('devices') ? 'active' : '' }}" title="Devices">
                {!! hpayIcon('phone') !!}<span class="label">Devices</span>
            </a>
            <a href="{{ route('messages') }}" class="nav-link {{ request()->routeIs('messages') ? 'active' : '' }}" title="Messages">
                {!! hpayIcon('message') !!}<span class="label">Messages</span>
            </a>
        @endif
    </nav>
</aside>

<div class="overlay" id="overlay"></div>

<main class="main">
    <div class="content">
        @if(session('ok'))<div class="flash ok">{{ session('ok') }}</div>@endif
        @if($errors->any())
            <div class="flash err">
                @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
        @endif
        @yield('content')
    </div>
</main>

<script>
(function () {
    var body = document.body;
    var html = document.documentElement;
    var mqMobile = window.matchMedia('(max-width:1023px)');

    try { if (localStorage.getItem('hpay_sidebar') === '1' && !mqMobile.matches) body.classList.add('collapsed'); } catch (e) {}

    document.getElementById('menuToggle').addEventListener('click', function () {
        if (mqMobile.matches) {
            body.classList.toggle('nav-open');
        } else {
            body.classList.toggle('collapsed');
            try { localStorage.setItem('hpay_sidebar', body.classList.contains('collapsed') ? '1' : '0'); } catch (e) {}
        }
    });

    var overlay = document.getElementById('overlay');
    if (overlay) overlay.addEventListener('click', function () { body.classList.remove('nav-open'); });

    var themeUrl = "{{ route('preferences.theme') }}";
    var csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');
    document.getElementById('themeToggle').addEventListener('click', function () {
        var next = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
        html.setAttribute('data-theme', next);
        try { localStorage.setItem('hpay_theme', next); } catch (e) {}
        fetch(themeUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ theme: next })
        }).catch(function () {});
    });

    try { localStorage.setItem('hpay_theme', html.getAttribute('data-theme')); } catch (e) {}
})();
</script>
</body>
</html>
