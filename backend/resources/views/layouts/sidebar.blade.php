<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Hpay') — Hpay</title>
    <style>
        :root {
            --bg:#0b1220; --panel:#131c2e; --panel2:#1b2740; --line:#273449;
            --text:#e6edf6; --muted:#8aa0bd; --accent:#38bdf8; --accent2:#0ea5e9;
            --green:#22c55e; --amber:#f59e0b; --red:#ef4444; --purple:#a855f7;
        }
        * { box-sizing:border-box; }
        body { margin:0; font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;
            background:var(--bg); color:var(--text); font-size:14px; }
        a { color:var(--accent); text-decoration:none; }
        .layout { display:flex; min-height:100vh; }

        /* Sidebar */
        .sidebar { width:230px; background:var(--panel); border-right:1px solid var(--line);
            display:flex; flex-direction:column; position:sticky; top:0; height:100vh; overflow-y:auto; }
        .sidebar .brand { padding:18px 20px; font-weight:800; font-size:18px; letter-spacing:.5px;
            border-bottom:1px solid var(--line); }
        .sidebar .brand span { color:var(--accent); }
        .nav { padding:10px 0; flex:1; }
        .nav .group { padding:14px 20px 6px; font-size:11px; text-transform:uppercase;
            letter-spacing:.08em; color:var(--muted); }
        .nav a { display:flex; align-items:center; justify-content:space-between;
            padding:9px 20px; color:var(--muted); font-weight:500; border-left:3px solid transparent; }
        .nav a:hover { background:var(--panel2); color:var(--text); }
        .nav a.active { background:var(--panel2); color:var(--text); border-left-color:var(--accent); }
        .badge { background:var(--amber); color:#1a1200; font-size:11px; font-weight:700;
            border-radius:999px; padding:1px 8px; min-width:20px; text-align:center; }
        .badge.zero { background:var(--line); color:var(--muted); }

        /* Main */
        .main { flex:1; min-width:0; display:flex; flex-direction:column; }
        .topbar { display:flex; align-items:center; gap:16px; padding:14px 24px;
            border-bottom:1px solid var(--line); background:var(--panel); position:sticky; top:0; z-index:5; }
        .topbar .spacer { flex:1; }
        .topbar .who { color:var(--muted); font-size:13px; }
        .topbar .who b { color:var(--text); }
        .pill { font-size:11px; padding:2px 10px; border-radius:999px; background:var(--panel2);
            color:var(--accent); text-transform:uppercase; letter-spacing:.04em; font-weight:700; }
        main.content { padding:24px; max-width:1200px; width:100%; }

        /* Shared UI */
        .cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:14px; margin-bottom:22px; }
        .card { background:var(--panel); border:1px solid var(--line); border-radius:12px; padding:16px; }
        .card .label { color:var(--muted); font-size:12px; text-transform:uppercase; letter-spacing:.04em; }
        .card .value { font-size:26px; font-weight:800; margin-top:6px; }
        .panel { background:var(--panel); border:1px solid var(--line); border-radius:12px; padding:18px; margin-bottom:20px; }
        .panel h2 { margin:0 0 14px; font-size:15px; }
        table { width:100%; border-collapse:collapse; background:var(--panel);
            border:1px solid var(--line); border-radius:12px; overflow:hidden; }
        th,td { text-align:left; padding:10px 12px; border-bottom:1px solid var(--line); vertical-align:middle; }
        th { background:var(--panel2); color:var(--muted); font-size:12px; text-transform:uppercase; letter-spacing:.03em; }
        tr:last-child td { border-bottom:none; }
        .muted { color:var(--muted); }
        .right { text-align:right; }
        .tag { display:inline-block; padding:2px 9px; border-radius:999px; font-size:11px; font-weight:700; }
        .tag.pending { background:rgba(245,158,11,.15); color:var(--amber); }
        .tag.success,.tag.approved,.tag.on { background:rgba(34,197,94,.15); color:var(--green); }
        .tag.failed,.tag.rejected,.tag.off { background:rgba(239,68,68,.15); color:var(--red); }
        input,select,textarea { background:var(--panel2); border:1px solid var(--line); color:var(--text);
            padding:9px 10px; border-radius:8px; font-size:13px; width:100%; }
        label { display:block; color:var(--muted); font-size:12px; margin:0 0 5px; }
        .field { margin-bottom:14px; }
        .row { display:flex; gap:14px; flex-wrap:wrap; }
        .row > .field { flex:1; min-width:160px; }
        .btn { background:var(--accent); color:#06283b; border:none; padding:9px 16px; border-radius:8px;
            font-weight:700; cursor:pointer; font-size:13px; display:inline-block; }
        .btn:hover { background:var(--accent2); }
        .btn.ghost { background:transparent; border:1px solid var(--line); color:var(--muted); }
        .btn.sm { padding:5px 10px; font-size:12px; }
        .btn.danger { background:var(--red); color:#fff; }
        .btn.green { background:var(--green); color:#04260f; }
        .flash { padding:11px 14px; border-radius:9px; margin-bottom:16px; font-weight:500; }
        .flash.ok { background:rgba(34,197,94,.12); color:#86efac; border:1px solid rgba(34,197,94,.3); }
        .flash.err { background:rgba(239,68,68,.12); color:#fca5a5; border:1px solid rgba(239,68,68,.3); }
        .pagination { margin-top:16px; display:flex; gap:6px; flex-wrap:wrap; list-style:none; padding:0; }
        .pagination a,.pagination span { padding:6px 11px; border:1px solid var(--line); border-radius:7px; color:var(--text); }
        .pagination .active span { background:var(--accent); color:#06283b; border-color:var(--accent); }
        .page-head { display:flex; align-items:center; gap:14px; margin-bottom:18px; }
        .page-head h1 { font-size:20px; margin:0; flex:1; }
        .inline-form { display:inline; }
        code.k { background:var(--panel2); padding:2px 7px; border-radius:6px; font-size:12px; color:var(--accent); word-break:break-all; }
        /* Tablet / phone: stack the sidebar into a scrollable top nav, keep labels, let tables scroll */
        @media (max-width:820px){
            .layout{ flex-direction:column; }
            .sidebar{ width:100%; height:auto; position:sticky; top:0; z-index:10; flex-direction:column;
                border-right:none; border-bottom:1px solid var(--line); }
            .sidebar .brand{ padding:13px 16px; }
            .nav{ display:flex; flex-wrap:nowrap; overflow-x:auto; padding:4px 8px; gap:2px;
                -webkit-overflow-scrolling:touch; }
            .nav .group{ display:none; }
            .nav a{ border-left:none; border-bottom:3px solid transparent; white-space:nowrap;
                padding:10px 12px; border-radius:8px 8px 0 0; gap:6px; }
            .nav a.active{ border-left:none; border-bottom-color:var(--accent); }
            .topbar{ padding:11px 16px; flex-wrap:wrap; gap:8px 12px; position:static; }
            main.content{ padding:16px; }
            .page-head{ flex-wrap:wrap; }
            table{ display:block; max-width:100%; overflow-x:auto;
                -webkit-overflow-scrolling:touch; }
            .cards{ grid-template-columns:repeat(auto-fit,minmax(130px,1fr)); }
            .row > .field{ min-width:120px; }
        }
        @media (max-width:480px){
            .cards{ grid-template-columns:1fr 1fr; }
            main.content{ padding:12px; }
        }
    </style>
</head>
<body>
@php($u = auth()->user())
<div class="layout">
    <aside class="sidebar">
        <div class="brand"><span>H</span>pay</div>
        <nav class="nav">
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>

            <div class="group">Users</div>
            @if($u->isSuperadmin())
                <a href="{{ route('users.index','admin') }}" class="{{ request()->is('users/admin*') ? 'active' : '' }}">Admins</a>
            @endif
            @if($u->isSuperadmin() || $u->isAdmin())
                <a href="{{ route('users.index','staff') }}" class="{{ request()->is('users/staff*') ? 'active' : '' }}">Staff</a>
            @endif
            @if($u->isStaffSide() || $u->isCustomer())
                <a href="{{ route('users.index','customer') }}" class="{{ request()->is('users/customer*') ? 'active' : '' }}">Customers</a>
                <a href="{{ route('users.index','merchant') }}" class="{{ request()->is('users/merchant*') ? 'active' : '' }}">Merchants</a>
            @endif

            <div class="group">Transactions</div>
            <a href="{{ route('payins.index') }}" class="{{ request()->routeIs('payins.*') ? 'active' : '' }}">Payins</a>
            <a href="{{ route('settlements.index') }}" class="{{ request()->routeIs('settlements.*') ? 'active' : '' }}">
                <span>Settlements</span>
                <span class="badge {{ ($sidebarPending ?? 0) === 0 ? 'zero' : '' }}">{{ $sidebarPending ?? 0 }}</span>
            </a>
            @if($u->isCustomer())
                <a href="{{ route('settlement_banks.index') }}" class="{{ request()->routeIs('settlement_banks.*') ? 'active' : '' }}">Settlement Banks</a>
            @endif

            @if($u->isStaffSide())
                <div class="group">Configuration</div>
                <a href="{{ route('banks.index') }}" class="{{ request()->routeIs('banks.*') ? 'active' : '' }}">Banks</a>
                <a href="{{ route('bank_settings.index') }}" class="{{ request()->routeIs('bank_settings.*') ? 'active' : '' }}">Bank Accounts</a>
                @if($u->isSuperadmin())
                    <a href="{{ route('master_settings.edit') }}" class="{{ request()->routeIs('master_settings.*') ? 'active' : '' }}">Master Settings</a>
                @endif

                <div class="group">Notification Catcher</div>
                <a href="{{ route('devices') }}" class="{{ request()->routeIs('devices') ? 'active' : '' }}">Devices</a>
                <a href="{{ route('messages') }}" class="{{ request()->routeIs('messages') ? 'active' : '' }}">Messages</a>
            @endif
        </nav>
    </aside>

    <div class="main">
        <div class="topbar">
            <div class="who">Signed in as <b>{{ $u->name }}</b></div>
            <span class="pill">{{ $u->roleLabel() }}</span>
            <div class="spacer"></div>
            <form method="POST" action="{{ route('logout') }}" class="inline-form">@csrf
                <button class="btn ghost sm" type="submit">Log out</button>
            </form>
        </div>

        <main class="content">
            @if(session('ok'))<div class="flash ok">{{ session('ok') }}</div>@endif
            @if($errors->any())
                <div class="flash err">
                    @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
