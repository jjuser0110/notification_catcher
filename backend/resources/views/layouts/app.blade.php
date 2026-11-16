<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Hpay')</title>
    <script>
        // Match the theme the user last chose, before first paint.
        try { var t = localStorage.getItem('hpay_theme'); if (t === 'light' || t === 'dark') document.documentElement.setAttribute('data-theme', t); } catch (e) {}
    </script>
    <style>
        :root {
            --bg: #0f172a; --panel: #1e293b; --panel2: #273449;
            --line: #334155; --text: #e2e8f0; --muted: #94a3b8;
            --accent: #38bdf8; --green: #22c55e; --amber: #f59e0b;
            --sms: #a855f7; --notif: #38bdf8;
        }
        :root[data-theme="light"] {
            --bg: #eef2f7; --panel: #ffffff; --panel2: #f3f6fb;
            --line: #e2e8f0; --text: #0f1b2d; --muted: #5b7290;
            --accent: #0ea5e9; --green: #16a34a; --amber: #d97706;
            --sms: #9333ea; --notif: #0ea5e9;
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            background: var(--bg); color: var(--text); font-size: 14px; }
        a { color: var(--accent); text-decoration: none; }
        header { background: var(--panel); border-bottom: 1px solid var(--line);
            padding: 14px 20px; display: flex; align-items: center; gap: 24px; }
        header .brand { font-weight: 700; font-size: 16px; }
        header nav { display: flex; gap: 18px; flex: 1; }
        header nav a { color: var(--muted); font-weight: 500; }
        header nav a.active { color: var(--text); }
        header form { margin: 0; }
        .btn { background: var(--accent); color: #082f49; border: none; padding: 8px 14px;
            border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 13px; }
        .btn.ghost { background: transparent; border: 1px solid var(--line); color: var(--muted); }
        main { padding: 20px; max-width: 1200px; margin: 0 auto; }
        .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 14px; margin-bottom: 24px; }
        .card { background: var(--panel); border: 1px solid var(--line); border-radius: 10px; padding: 16px; }
        .card .label { color: var(--muted); font-size: 12px; text-transform: uppercase; letter-spacing: .04em; }
        .card .value { font-size: 28px; font-weight: 700; margin-top: 6px; }
        table { width: 100%; border-collapse: collapse; background: var(--panel);
            border: 1px solid var(--line); border-radius: 10px; overflow: hidden; }
        th, td { text-align: left; padding: 10px 12px; border-bottom: 1px solid var(--line); vertical-align: top; }
        th { background: var(--panel2); color: var(--muted); font-size: 12px; text-transform: uppercase; letter-spacing: .03em; }
        tr:last-child td { border-bottom: none; }
        .tag { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 600; }
        .tag.notification { background: rgba(56,189,248,.15); color: var(--notif); }
        .tag.sms { background: rgba(168,85,247,.15); color: var(--sms); }
        .dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: 6px; }
        .dot.on { background: var(--green); } .dot.off { background: var(--muted); }
        .muted { color: var(--muted); }
        .filters { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 16px; }
        .filters input, .filters select { background: var(--panel); border: 1px solid var(--line);
            color: var(--text); padding: 8px 10px; border-radius: 6px; font-size: 13px; }
        .pagination { margin-top: 16px; display: flex; gap: 6px; flex-wrap: wrap; }
        .pagination a, .pagination span { padding: 6px 10px; border: 1px solid var(--line);
            border-radius: 6px; color: var(--text); }
        .pagination .active span { background: var(--accent); color: #082f49; border-color: var(--accent); }
        .section-title { font-size: 15px; font-weight: 600; margin: 24px 0 12px; }
        .empty { text-align: center; color: var(--muted); padding: 40px; }
    </style>
</head>
<body>
    @auth
    <header>
        <span class="brand">Hpay</span>
        <nav>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Devices</a>
            <a href="{{ route('messages') }}" class="{{ request()->routeIs('messages') ? 'active' : '' }}">Messages</a>
        </nav>
        <span class="muted">{{ auth()->user()->name }}</span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn ghost" type="submit">Log out</button>
        </form>
    </header>
    @endauth
    <main>
        @yield('content')
    </main>
</body>
</html>
