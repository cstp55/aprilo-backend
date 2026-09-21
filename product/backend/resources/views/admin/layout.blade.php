<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Aprilo AI Admin')</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --paper: #f8fafc;
            --surface: #ffffff;
            --ink: #0f172a;
            --muted: #64748b;
            --line: #e2e8f0;
            --green: #4f46e5; /* indigo-650 / primary */
            --green-dark: #3730a3;
            --amber: #d97706;
            --danger: #dc2626;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: var(--paper);
            color: var(--ink);
            font-family: 'Inter', sans-serif;
        }
        a { color: inherit; text-decoration: none; }
        button, input, select, textarea {
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
            padding: 24px;
            position: sticky;
            top: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        .brand-section {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .brand-mark {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--green), #6366f1);
            color: #fff;
            font-weight: 800;
            font-size: 20px;
            font-family: 'Outfit', sans-serif;
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.2);
        }
        .brand-title {
            font-family: 'Outfit', sans-serif;
            font-size: 18px;
            font-weight: 800;
            margin: 0;
            color: var(--ink);
        }
        .brand-subtitle {
            color: var(--muted);
            font-size: 10px;
            letter-spacing: .16em;
            text-transform: uppercase;
            font-weight: 700;
            margin-top: 2px;
        }
        .nav {
            margin-top: 36px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            flex-grow: 1;
        }
        .nav a {
            border-radius: 8px;
            padding: 10px 14px;
            color: #475569;
            font-size: 13.5px;
            font-weight: 600;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .nav a.active {
            background: var(--green);
            color: #fff;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);
        }
        .nav a:hover:not(.active) {
            background: #f1f5f9;
            color: var(--ink);
            transform: translateX(4px);
        }
        .logout {
            margin-top: auto;
            border-top: 1px solid var(--line);
            padding-top: 16px;
        }
        .logout button,
        .button {
            width: 100%;
            border: 0;
            border-radius: 8px;
            background: var(--green);
            color: #fff;
            padding: 11px 16px;
            font-weight: 700;
            font-size: 13.5px;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.15);
        }
        .logout button:hover,
        .button:hover {
            background: var(--green-dark);
            transform: translateY(-1px);
        }
        .logout button {
            background: transparent;
            border: 1px solid var(--line);
            color: #475569;
            box-shadow: none;
        }
        .logout button:hover {
            background: #f8fafc;
            color: var(--ink);
            border-color: #cbd5e1;
        }
        .main {
            padding: 32px;
            overflow-y: auto;
        }
        .header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 28px;
        }
        .eyebrow {
            color: var(--amber);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .16em;
            text-transform: uppercase;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Outfit', sans-serif;
            margin: 0;
        }
        h1 {
            margin-top: 8px;
            font-size: 32px;
            line-height: 1.15;
            font-weight: 800;
        }
        .help {
            margin-top: 8px;
            max-width: 680px;
            color: var(--muted);
            font-size: 14.5px;
            line-height: 1.7;
        }
        .grid {
            display: grid;
            gap: 20px;
        }
        .grid-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 22px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .metric {
            font-size: 32px;
            font-weight: 800;
            font-family: 'Outfit', sans-serif;
            color: var(--green);
        }
        .label {
            margin-top: 6px;
            color: var(--muted);
            font-size: 13px;
            font-weight: 600;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 10px;
            overflow: hidden;
        }
        th, td {
            border-bottom: 1px solid #f1f5f9;
            padding: 14px 16px;
            text-align: left;
            vertical-align: middle;
            font-size: 14px;
        }
        th {
            background: #f8fafc;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--muted);
        }
        tr:last-child td { border-bottom: 0; }
        .field {
            display: grid;
            gap: 7px;
            font-size: 13px;
            font-weight: 700;
        }
        .field input,
        .field select,
        .field textarea {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #fff;
            padding: 10px 12px;
            color: var(--ink);
            outline: none;
            transition: all 0.2s ease;
        }
        .field input:focus,
        .field select:focus,
        .field textarea:focus {
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        .notice {
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 14px 16px;
            font-size: 14px;
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
            font-weight: 600;
        }
        .error {
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 14px 16px;
            font-size: 14px;
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            font-weight: 600;
        }
        .pill {
            display: inline-flex;
            border-radius: 999px;
            background: #f1f5f9;
            padding: 4px 10px;
            color: #475569;
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
            .grid-4, .grid-3, .grid-2 { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="shell">
        <aside class="sidebar">
            <div>
                <div class="brand-section">
                    <div class="brand-mark">A</div>
                    <div>
                        <div class="brand-title">Aprilo AI</div>
                        <div class="brand-subtitle">Admin console</div>
                    </div>
                </div>
                
                <nav class="nav">
                    @if (auth()->check())
                        <div style="padding: 10px 12px; margin-bottom: 12px; background: rgba(79, 70, 229, 0.06); border-radius: 8px; border: 1px solid rgba(79, 70, 229, 0.15);">
                            <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--green); letter-spacing: 0.08em;">Active Role</div>
                            <div style="font-size: 13px; font-weight: 700; color: var(--ink); margin-top: 2px;">
                                {{ auth()->user()->roleModel->name ?? ucfirst(str_replace('_', ' ', auth()->user()->role_slug)) }}
                            </div>
                            <div style="font-size: 11px; color: var(--muted); margin-top: 1px;">
                                {{ auth()->user()->organization->name ?? 'Platform Global' }}
                            </div>
                        </div>
                    @endif

                    @forelse ($sidebarMenu ?? [] as $group)
                        <div style="margin: 18px 0 6px 12px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.12em; color: var(--muted);">
                            {{ $group['header'] }}
                        </div>
                        @foreach ($group['items'] as $item)
                            @php
                                $url = isset($item['route']) ? (Route::has($item['route']) ? route($item['route'], $item['params'] ?? []) : '#') : '#';
                            @endphp
                            <a class="{{ $item['active'] ? 'active' : '' }}" href="{{ $url }}">
                                {{ $item['title'] }}
                            </a>
                        @endforeach
                    @empty
                        <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
                    @endforelse
                </nav>
            </div>
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
