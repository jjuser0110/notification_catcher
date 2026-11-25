@php
    $u = auth()->user();
    $theme = ($u && $u->theme === 'light') ? 'light' : 'dark';
    $nameParts = preg_split('/\s+/', trim($u->name ?? 'User'));
    $initials = strtoupper(mb_substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? mb_substr($nameParts[1], 0, 1) : mb_substr($nameParts[0], 1, 1)));
@endphp
<!DOCTYPE html>
<html lang="en" data-theme="{{ $theme }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — HPay</title>
    <style>
        :root {
            --bg:#09090c; --panel:#141419; --panel2:#1c1c24; --line:#282831;
            --text:#eceef2; --muted:#8a8a99; --accent:#f5415f; --accent-ink:#ffffff;
            --accent-soft:rgba(245,65,95,.14); --green:#34d399; --amber:#fbbf24; --red:#f87171;
            --topbar:#0d0d12; --sidebar:#0d0d12; --shadow:0 12px 34px rgba(0,0,0,.5);
            --grad-active:linear-gradient(135deg,#ff3b57,#d20a2d);
            --grad-hero:linear-gradient(120deg,#2b0c14 0%,#18090f 55%,#141419 100%);
            --hero-border:#3d1621; --hero-text:#f6e9ec;
            --sidebar-w:252px; --rail-w:76px; --topbar-h:62px;
        }
        :root[data-theme="light"] {
            --bg:#f4f5f8; --panel:#ffffff; --panel2:#f3f4f7; --line:#e8eaee;
            --text:#14151c; --muted:#6b7280; --accent:#e4002b; --accent-ink:#ffffff;
            --accent-soft:rgba(228,0,43,.08); --green:#16a34a; --amber:#d97706; --red:#dc2626;
            --topbar:#ffffff; --sidebar:#ffffff; --shadow:0 10px 30px rgba(20,21,28,.08);
            --grad-active:linear-gradient(135deg,#ff2d4b,#d6002a);
            --grad-hero:linear-gradient(120deg,#fff0f2 0%,#ffe2e7 58%,#fff1f3 100%);
            --hero-border:#ffd4dc; --hero-text:#3a0a14;
        }
        * { box-sizing:border-box; }
        html,body { height:100%; }
        body { margin:0; font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;
            background:var(--bg); color:var(--text); font-size:14px; padding-top:var(--topbar-h);
            transition:background .2s,color .2s; }
        a { color:var(--accent); text-decoration:none; }

        /* ---- Topbar ---- */
        .topbar { position:fixed; top:0; left:0; right:0; height:var(--topbar-h); z-index:40;
            display:flex; align-items:center; gap:14px; padding:0 20px;
            background:var(--topbar); border-bottom:1px solid var(--line); }
        .crumb { display:flex; align-items:center; gap:9px; color:var(--muted); font-size:13.5px; white-space:nowrap; overflow:hidden; }
        .crumb .sep { opacity:.5; }
        .crumb .here { color:var(--text); font-weight:700; }
        .topbar .spacer { flex:1; }
        .demo-pill { display:inline-flex; align-items:center; gap:7px; font-size:12px; color:var(--muted);
            border:1px solid var(--line); border-radius:999px; padding:5px 12px; background:var(--panel2); white-space:nowrap; }
        .demo-pill::before { content:""; width:7px; height:7px; border-radius:50%; background:var(--accent); }
        .icon-btn { width:38px; height:38px; display:inline-flex; align-items:center; justify-content:center;
            border:1px solid var(--line); background:var(--panel); color:var(--text); border-radius:10px;
            cursor:pointer; flex:none; position:relative; transition:background .15s,border-color .15s; }
        .icon-btn:hover { background:var(--panel2); border-color:var(--accent); }
        .icon-btn svg { width:19px; height:19px; }
        .bell-dot { position:absolute; top:9px; right:10px; width:7px; height:7px; border-radius:50%;
            background:var(--accent); border:2px solid var(--topbar); }
        .avatar { width:38px; height:38px; border-radius:50%; flex:none; display:flex; align-items:center;
            justify-content:center; font-weight:800; font-size:13px; color:#fff; letter-spacing:.3px;
            background:var(--grad-active); box-shadow:0 4px 12px rgba(225,0,43,.35); }
        #themeToggle .sun { display:none; }
        :root[data-theme="light"] #themeToggle .sun { display:block; }
        :root[data-theme="light"] #themeToggle .moon { display:none; }

        /* ---- Sidebar ---- */
        .sidebar { position:fixed; top:var(--topbar-h); left:0; bottom:0; width:var(--sidebar-w);
            background:var(--sidebar); border-right:1px solid var(--line); overflow-y:auto; z-index:30;
            transition:width .2s ease, transform .2s ease; display:flex; flex-direction:column; }
        .brand { padding:20px 20px 10px; }
        .brand .logo { font-size:25px; font-weight:900; font-style:italic; letter-spacing:-.5px; color:var(--accent); }
        .brand .logo b { color:var(--text); font-style:normal; }
        .brand .tag { font-size:10px; letter-spacing:.22em; color:var(--muted); margin-top:3px; font-weight:700; }
        .ws-card { margin:6px 14px 10px; padding:12px; display:flex; align-items:center; gap:11px;
            background:var(--panel2); border:1px solid var(--line); border-radius:14px; }
        .ws-card .sq { width:40px; height:40px; border-radius:11px; flex:none; display:flex; align-items:center;
            justify-content:center; font-weight:800; color:#fff; background:var(--grad-active);
            box-shadow:0 4px 12px rgba(225,0,43,.3); }
        .ws-card .nm { font-weight:700; font-size:13.5px; line-height:1.2; }
        .ws-card .rl { color:var(--muted); font-size:11.5px; margin-top:2px; }
        .nav { padding:6px 0 18px; flex:1; }
        .nav-group { padding:16px 22px 7px; font-size:10.5px; text-transform:uppercase;
            letter-spacing:.12em; color:var(--muted); font-weight:700; white-space:nowrap; }
        .nav-link { position:relative; display:flex; align-items:center; gap:12px; margin:2px 12px;
            padding:10px 13px; border-radius:11px; color:var(--muted); font-weight:600; white-space:nowrap;
            transition:background .15s,color .15s; }
        .nav-link .ic { width:20px; height:20px; flex:none; }
        .nav-link .label { flex:1; }
        .nav-link:hover { background:var(--panel2); color:var(--text); }
        .nav-link.active { background:var(--grad-active); color:#fff; box-shadow:0 8px 20px rgba(225,0,43,.32); }
        .nav-link.active::after { content:"/"; position:absolute; right:14px; font-style:italic; font-weight:800; opacity:.45; }
        .nav-link.active.has-badge::after { content:""; }
        .badge { background:var(--accent-soft); color:var(--accent); font-size:11px; font-weight:800;
            border-radius:999px; padding:1px 8px; min-width:22px; text-align:center; }
        .badge.zero { background:var(--panel2); color:var(--muted); }
        .nav-link.active .badge { background:rgba(255,255,255,.25); color:#fff; }

        /* ---- Collapsed rail (desktop) ---- */
        body.collapsed .sidebar { width:var(--rail-w); }
        body.collapsed .main { margin-left:var(--rail-w); }
        body.collapsed .brand { text-align:center; padding:18px 0 8px; }
        body.collapsed .brand .logo { font-size:21px; }
        body.collapsed .brand .logo b, body.collapsed .brand .tag { display:none; }
        body.collapsed .ws-card { margin:6px auto 10px; padding:0; border:none; background:transparent; justify-content:center; }
        body.collapsed .ws-card .meta { display:none; }
        body.collapsed .nav-link { justify-content:center; padding:11px 0; gap:0; margin:2px 10px; }
        body.collapsed .nav-link .label { display:none; }
        body.collapsed .nav-link.active::after { content:""; }
        body.collapsed .nav-group { text-align:center; font-size:0; padding:10px 0 4px; }
        body.collapsed .nav-group::after { content:"•"; font-size:12px; color:var(--line); }
        body.collapsed .nav-link .badge { position:absolute; top:4px; right:14px; min-width:8px;
            height:8px; padding:0; border-radius:50%; font-size:0; }
        body.collapsed .nav-link .badge.zero { display:none; }

        /* ---- Main ---- */
        .main { margin-left:var(--sidebar-w); min-height:calc(100vh - var(--topbar-h));
            transition:margin-left .2s ease; }
        .content { padding:26px; max-width:1280px; width:100%; }
        .overlay { display:none; position:fixed; inset:var(--topbar-h) 0 0 0; background:rgba(4,4,8,.6); z-index:20; }

        /* ---- Mobile: off-canvas drawer ---- */
        @media (max-width:1023px) {
            .sidebar { transform:translateX(-100%); box-shadow:var(--shadow); width:var(--sidebar-w); }
            .main { margin-left:0; }
            body.nav-open .sidebar { transform:none; }
            body.nav-open .overlay { display:block; }
            body.collapsed .sidebar { width:var(--sidebar-w); }
            body.collapsed .brand { text-align:left; padding:20px 20px 10px; }
            body.collapsed .brand .logo { font-size:25px; }
            body.collapsed .brand .logo b, body.collapsed .brand .tag { display:inline; }
            body.collapsed .ws-card { margin:6px 14px 10px; padding:12px; border:1px solid var(--line); background:var(--panel2); justify-content:flex-start; }
            body.collapsed .ws-card .meta { display:block; }
            body.collapsed .nav-link { justify-content:flex-start; padding:10px 13px; gap:12px; margin:2px 12px; }
            body.collapsed .nav-link .label { display:block; }
            body.collapsed .nav-group { text-align:left; font-size:10.5px; padding:16px 22px 7px; }
            body.collapsed .nav-group::after { content:""; }
            body.collapsed .nav-link .badge { position:static; min-width:22px; height:auto; padding:1px 8px; border-radius:999px; font-size:11px; }
            .crumb .work { display:none; }
            .demo-pill { display:none; }
            .content { padding:16px; }
            table { display:block; max-width:100%; overflow-x:auto; -webkit-overflow-scrolling:touch; }
        }
        @media (max-width:560px) { .crumb .sep.first { display:none; } }

        /* ---- Hero + stat cards ---- */
        .hero { background:var(--grad-hero); border:1px solid var(--hero-border); border-radius:20px;
            padding:24px 26px; margin-bottom:18px; color:var(--hero-text); box-shadow:var(--shadow); position:relative; overflow:hidden; }
        .hero-eye { font-size:11px; font-weight:800; letter-spacing:.14em; color:var(--accent); margin-bottom:12px; }
        .hero-flex { display:flex; align-items:flex-end; justify-content:space-between; gap:18px; flex-wrap:wrap; }
        .hero-label { font-size:13px; opacity:.85; }
        .hero-value { font-size:40px; font-weight:900; line-height:1.05; margin-top:6px; letter-spacing:-.5px; }
        .hero-value .cur { font-size:18px; font-weight:700; opacity:.7; margin-right:8px; vertical-align:2px; }
        .hero-sub { font-size:12.5px; opacity:.8; margin-top:10px; }
        .hero-chip { background:rgba(255,255,255,.14); border:1px solid rgba(255,255,255,.16); border-radius:14px;
            padding:12px 16px; text-align:right; }
        :root[data-theme="light"] .hero-chip { background:rgba(228,0,43,.07); border-color:var(--hero-border); }
        .hero-chip .k { font-size:11px; opacity:.8; }
        .hero-chip .v { font-size:22px; font-weight:800; margin-top:3px; }

        .cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:15px; margin-bottom:18px; }
        .card { position:relative; background:var(--panel); border:1px solid var(--line); border-radius:18px; padding:18px 20px; box-shadow:var(--shadow); }
        .card .ico { position:absolute; top:16px; right:16px; width:34px; height:34px; border-radius:10px;
            display:flex; align-items:center; justify-content:center; background:var(--accent-soft); color:var(--accent); }
        .card .ico svg { width:18px; height:18px; }
        .card .label { color:var(--muted); font-size:12px; text-transform:uppercase; letter-spacing:.04em; }
        .card .value { font-size:27px; font-weight:800; margin-top:7px; letter-spacing:-.3px; }
        .card .sub { color:var(--muted); font-size:12px; margin-top:6px; }

        /* ---- Shared UI ---- */
        .panel { background:var(--panel); border:1px solid var(--line); border-radius:18px; padding:20px; margin-bottom:20px; box-shadow:var(--shadow); }
        .panel h2 { margin:0 0 14px; font-size:15px; }
        table { width:100%; border-collapse:collapse; background:var(--panel);
            border:1px solid var(--line); border-radius:16px; overflow:hidden; box-shadow:var(--shadow); }
        th,td { text-align:left; padding:12px 14px; border-bottom:1px solid var(--line); vertical-align:middle; }
        th { background:var(--panel2); color:var(--muted); font-size:12px; text-transform:uppercase; letter-spacing:.03em; }
        tr:last-child td { border-bottom:none; }
        tbody tr:hover td { background:var(--panel2); }
        .muted { color:var(--muted); }
        .right { text-align:right; }
        .tag { display:inline-block; padding:2px 9px; border-radius:999px; font-size:11px; font-weight:700; }
        .tag.pending { background:rgba(251,191,36,.16); color:var(--amber); }
        .tag.success,.tag.approved,.tag.on { background:rgba(52,211,153,.16); color:var(--green); }
        .tag.failed,.tag.rejected,.tag.off { background:rgba(248,113,113,.16); color:var(--red); }
        input,select,textarea { background:var(--panel2); border:1px solid var(--line); color:var(--text);
            padding:9px 11px; border-radius:10px; font-size:13px; width:100%; }
        input:focus,select:focus,textarea:focus { outline:none; border-color:var(--accent); }
        label { display:block; color:var(--muted); font-size:12px; margin:0 0 5px; }
        .field { margin-bottom:14px; }
        .row { display:flex; gap:14px; flex-wrap:wrap; }
        .row > .field { flex:1; min-width:150px; }
        .btn { background:var(--accent); color:var(--accent-ink); border:none; padding:9px 17px; border-radius:10px;
            font-weight:700; cursor:pointer; font-size:13px; display:inline-block; }
        .btn:hover { filter:brightness(1.07); }
        .btn.ghost { background:transparent; border:1px solid var(--line); color:var(--muted); }
        .btn.ghost:hover { color:var(--text); border-color:var(--accent); filter:none; }
        .btn.sm { padding:6px 12px; font-size:12px; }
        .btn.danger { background:var(--red); color:#fff; }
        .btn.green { background:var(--green); color:#04260f; }
        .flash { padding:12px 15px; border-radius:12px; margin-bottom:16px; font-weight:500; }
        .flash.ok { background:rgba(52,211,153,.12); color:var(--green); border:1px solid rgba(52,211,153,.3); }
        .flash.err { background:rgba(248,113,113,.12); color:var(--red); border:1px solid rgba(248,113,113,.3); }
        .pagination { margin-top:16px; display:flex; gap:6px; flex-wrap:wrap; list-style:none; padding:0; }
        .pagination a,.pagination span { padding:6px 12px; border:1px solid var(--line); border-radius:9px; color:var(--text); background:var(--panel); }
        .pagination .active span { background:var(--accent); color:var(--accent-ink); border-color:var(--accent); }
        .pagination .disabled span { color:var(--muted); }
        .page-head { display:flex; align-items:center; gap:14px; margin-bottom:18px; }
        .page-head h1 { font-size:22px; margin:0; flex:1; }
        .inline-form { display:inline; }
        code.k { background:var(--panel2); padding:2px 7px; border-radius:6px; font-size:12px; color:var(--accent); word-break:break-all; }
    </style>
</head>
<body>
<header class="topbar">
    <button class="icon-btn" id="menuToggle" aria-label="Toggle menu" title="Toggle sidebar">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
    <div class="crumb">
        <span class="work">Workspace</span><span class="sep first">/</span><span class="here">@yield('title', 'Dashboard')</span>
    </div>
    <div class="spacer"></div>
    <span class="demo-pill">{{ $u->roleLabel() }} workspace</span>
    <button class="icon-btn" id="themeToggle" aria-label="Toggle theme" title="Toggle light / dark">
        <svg class="moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.8A9 9 0 1111.2 3 7 7 0 0021 12.8z"/></svg>
        <svg class="sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M19.1 4.9l-1.4 1.4M6.3 17.7l-1.4 1.4"/></svg>
    </button>
    <button class="icon-btn" id="bellBtn" aria-label="Notifications" title="Notifications">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9a6 6 0 1112 0c0 5 2 6 2 6H4s2-1 2-6z"/><path d="M10 20a2 2 0 004 0"/></svg>
        @if(($sidebarPending ?? 0) > 0)<span class="bell-dot"></span>@endif
    </button>
    <div class="avatar" title="{{ $u->name }}">{{ $initials }}</div>
    <form method="POST" action="{{ route('logout') }}" class="inline-form">@csrf
        <button class="btn ghost sm" type="submit">Log out</button>
    </form>
</header>

<aside class="sidebar" id="sidebar">
    <div class="brand">
        <div class="logo">H<b>Pay</b><span class="pw"></span></div>
        <div class="tag">PAYMENT GATEWAY</div>
    </div>
    <div class="ws-card">
        <div class="sq">{{ $initials }}</div>
        <div class="meta">
            <div class="nm">{{ $u->name }}</div>
            <div class="rl">{{ $u->roleLabel() }} workspace</div>
        </div>
    </div>

    <nav class="nav">
        <div class="nav-group">Workspace</div>
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" title="Dashboard">
            {!! hpayIcon('dashboard') !!}<span class="label">Dashboard</span>
        </a>
        <a href="{{ route('payins.index') }}" class="nav-link {{ request()->routeIs('payins.*') ? 'active' : '' }}" title="Payins">
            {!! hpayIcon('payin') !!}<span class="label">Payins</span>
        </a>
        <a href="{{ route('settlements.index') }}" class="nav-link has-badge {{ request()->routeIs('settlements.*') ? 'active' : '' }}" title="Settlements">
            {!! hpayIcon('settle') !!}<span class="label">Settlements</span>
            <span class="badge {{ ($sidebarPending ?? 0) === 0 ? 'zero' : '' }}">{{ $sidebarPending ?? 0 }}</span>
        </a>
        @if($u->isCustomer())
            <a href="{{ route('settlement_banks.index') }}" class="nav-link {{ request()->routeIs('settlement_banks.*') ? 'active' : '' }}" title="Settlement Banks">
                {!! hpayIcon('bank') !!}<span class="label">Settlement Banks</span>
            </a>
        @endif

        @if($u->isSuperadmin() || $u->isAdmin() || $u->isStaffSide() || $u->isCustomer())
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
        @endif

        @if($u->isStaffSide())
            <div class="nav-group">Management</div>
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
