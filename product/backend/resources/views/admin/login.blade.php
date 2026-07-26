<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aprilo AI - Admin Login</title>
    <!-- Modern Premium Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-red-500: lab(55.4814% 75.0732 48.8528);
            --color-red-600: lab(48.4493% 77.4328 61.5452);
            --bg-gradient-start: #12141c;
            --bg-gradient-end: #1a1e2d;
            --card-bg: rgba(255, 255, 255, 0.03);
            --card-border: rgba(255, 255, 255, 0.08);
            --text-primary: #f3f4f6;
            --text-secondary: #9ca3af;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at top right, #2d1822, transparent 40%),
                        radial-gradient(circle at bottom left, #121d25, transparent 40%),
                        linear-gradient(135deg, var(--bg-gradient-start), var(--bg-gradient-end));
            color: var(--text-primary);
            font-family: 'Plus Jakarta Sans', sans-serif;
            padding: 24px;
            overflow-x: hidden;
        }

        .login-container {
            width: min(440px, 100%);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            background: rgba(26, 30, 45, 0.6);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5),
                        inset 0 1px 0 rgba(255, 255, 255, 0.1);
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header-wrapper {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--color-red-500), var(--color-red-600));
            color: #ffffff;
            font-family: 'Outfit', sans-serif;
            font-size: 22px;
            font-weight: 700;
            box-shadow: 0 8px 24px rgba(227, 38, 38, 0.25);
            margin-bottom: 16px;
        }

        .eyebrow {
            color: var(--color-red-500);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .2em;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 28px;
            font-weight: 600;
            letter-spacing: -0.02em;
            margin-bottom: 8px;
            background: linear-gradient(to right, #ffffff, #e5e7eb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .subtitle {
            color: var(--text-secondary);
            font-size: 14px;
            line-height: 1.6;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 20px;
        }

        label {
            font-size: 13px;
            font-weight: 600;
            color: #d1d5db;
        }

        input {
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 10px;
            background: rgba(0, 0, 0, 0.2);
            padding: 13px 16px;
            font-family: inherit;
            font-size: 14px;
            color: #ffffff;
            outline: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        input:focus {
            border-color: var(--color-red-500);
            box-shadow: 0 0 0 3px rgba(227, 38, 38, 0.15);
            background: rgba(0, 0, 0, 0.3);
        }

        button {
            width: 100%;
            margin-top: 12px;
            border: 0;
            border-radius: 10px;
            background: var(--color-red-500);
            color: #ffffff;
            padding: 14px;
            font-family: inherit;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(227, 38, 38, 0.2);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        button:hover {
            background: var(--color-red-600);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(227, 38, 38, 0.3);
        }

        button:active {
            transform: translateY(0);
        }

        .error {
            margin-bottom: 20px;
            border-radius: 10px;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            padding: 12px 16px;
            font-size: 13.5px;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf
            <div class="header-wrapper">
                <div class="logo-box">A</div>
                <div class="eyebrow">Aprilo AI</div>
                <h1>Admin console</h1>
                <p class="subtitle">Enter your credentials to manage resources, settings, and employee workflows.</p>
            </div>

            @if ($errors->any())
                <div class="error">{{ $errors->first() }}</div>
            @endif

            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', 'hr@example.com') }}" required autofocus placeholder="name@company.com">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" value="password" required placeholder="••••••••">
            </div>

            <button type="submit">Sign in</button>
        </form>
    </div>
</body>
</html>
