<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Aprilo AI Admin')</title>
    <style>
        :root {
            --paper: #f7f7f4;
            --surface: #ffffff;
            --ink: #181b1f;
            --muted: #5b6661;
            --line: #d8d1c4;
            --green: lab(55.4814% 75.0732 48.8528);
            --green-dark: lab(48.4493% 77.4328 61.5452);
            --amber: #9a5f30;
            --danger: #9d2b22;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: var(--paper);
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
        }
        a { color: inherit; text-decoration: none; }
        button, input, select {
            font: inherit;
        }
        .shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 260px 1fr;
        }
        .sidebar {
            background: var(--surface);
            border-right: 1px solid var(--line);
            padding: 22px;
            position: sticky;
            top: 0;
            height: 100vh;
        }
        .brand-mark {
            width: 38px;
            height: 38px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--green);
            color: #fff;
            font-weight: 700;
        }
        .brand-title {
            margin-top: 14px;
            font-size: 20px;
            font-weight: 700;
        }
        .brand-subtitle {
            margin-top: 4px;
            color: var(--muted);
            font-size: 12px;
            letter-spacing: .16em;
            text-transform: uppercase;
        }
        .nav {
            margin-top: 34px;
            display: grid;
            gap: 8px;
        }
        .nav a {
            border-radius: 6px;
            padding: 10px 12px;
            color: #34413c;
            font-size: 14px;
            font-weight: 600;
        }
        .nav a.active,
        .nav a:hover {
            background: var(--green);
            color: #fff;
        }
        .logout {
            position: absolute;
            left: 22px;
            right: 22px;
            bottom: 22px;
        }
        .logout button,
        .button {
            width: 100%;
            border: 0;
            border-radius: 6px;
            background: var(--green);
            color: #fff;
            padding: 10px 14px;
            font-weight: 700;
            cursor: pointer;
        }
        .logout button {
            background: transparent;
            border: 1px solid var(--line);
            color: #34413c;
        }
        .main {
            padding: 28px;
        }
        .header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 24px;
        }
        .eyebrow {
            color: var(--amber);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .16em;
            text-transform: uppercase;
        }
        h1 {
            margin: 8px 0 0;
            font-size: 30px;
            line-height: 1.15;
        }
        .help {
            margin-top: 8px;
            max-width: 680px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.7;
        }
        .grid {
            display: grid;
            gap: 16px;
        }
        .grid-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 18px;
        }
        .metric {
            font-size: 30px;
            font-weight: 700;
        }
        .label {
            margin-top: 6px;
            color: var(--muted);
            font-size: 13px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            border-bottom: 1px solid #e7e1d7;
            padding: 12px;
            text-align: left;
            vertical-align: top;
            font-size: 14px;
        }
        th {
            background: #ece8df;
            font-size: 12px;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        tr:last-child td { border-bottom: 0; }
        .field {
            display: grid;
            gap: 7px;
            font-size: 13px;
            font-weight: 700;
        }
        .field input,
        .field select {
            width: 100%;
            border: 1px solid #cfc7b8;
            border-radius: 6px;
            background: #fff;
            padding: 10px;
            color: var(--ink);
            outline: none;
        }
        .field input:focus,
        .field select:focus {
            border-color: var(--green);
        }
        .notice {
            border-radius: 6px;
            margin-bottom: 18px;
            padding: 12px 14px;
            font-size: 14px;
            background: #e8f5ee;
            color: var(--green);
        }
        .error {
            border-radius: 6px;
            margin-bottom: 18px;
            padding: 12px 14px;
            font-size: 14px;
            background: #fff1f0;
            color: var(--danger);
        }
        .pill {
            display: inline-flex;
            border-radius: 999px;
            background: #eef1ea;
            padding: 5px 10px;
            color: #34413c;
            font-size: 12px;
            font-weight: 700;
        }
        .row-form {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        @media (max-width: 900px) {
            .shell { grid-template-columns: 1fr; }
            .sidebar {
                position: static;
                height: auto;
            }
            .logout { position: static; margin-top: 24px; }
            .grid-4, .grid-2 { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="shell">
        <aside class="sidebar">
            <div class="brand-mark">A</div>
            <div class="brand-title">Aprilo AI</div>
            <div class="brand-subtitle">Admin console</div>
            <nav class="nav">
                <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
                <a class="{{ request()->routeIs('admin.sources') ? 'active' : '' }}" href="{{ route('admin.sources') }}">Sources</a>
                <a class="{{ request()->routeIs('admin.escalations') ? 'active' : '' }}" href="{{ route('admin.escalations') }}">Escalations</a>
                <a class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}" href="{{ route('admin.settings') }}">Settings</a>
                <a class="{{ request()->routeIs('admin.logs') ? 'active' : '' }}" href="{{ route('admin.logs') }}">Interaction Logs</a>
                
                <div style="margin: 16px 0 6px 12px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--muted);">HR Operations</div>
                <a class="{{ request()->routeIs('admin.leaves') ? 'active' : '' }}" href="{{ route('admin.leaves') }}">Leave Requests</a>
                <a class="{{ request()->routeIs('admin.wfh') ? 'active' : '' }}" href="{{ route('admin.wfh') }}">WFH Requests</a>
                <a class="{{ request()->routeIs('admin.employees') ? 'active' : '' }}" href="{{ route('admin.employees') }}">Validate Employee</a>
                <a class="{{ request()->routeIs('admin.idcards') ? 'active' : '' }}" href="{{ route('admin.idcards') }}">ID Cards</a>
            </nav>
            <form class="logout" method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit">Sign out</button>
            </form>
        </aside>
        <main class="main">
            @if (session('status'))
                <div class="notice">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="error">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</body>
</html>
