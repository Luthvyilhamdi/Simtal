<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMTAL - Lupa Password</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f0fdf4;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            background: white;
            border-radius: 16px;
            padding: 40px 44px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        }
        .card-header { text-align: center; margin-bottom: 28px; }
        .card-icon {
            width: 52px; height: 52px;
            background: #f0fdf4;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 14px;
            border: 1px solid #bbf7d0;
        }
        .card-icon svg { width: 24px; height: 24px; stroke: #16a34a; fill: none; stroke-width: 2; }
        .card-title { font-size: 20px; font-weight: 700; color: #111827; margin-bottom: 6px; }
        .card-sub { font-size: 13px; color: #6b7280; line-height: 1.6; }

        .status-msg {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 10px 14px;
            color: #15803d;
            font-size: 13px;
            margin-bottom: 16px;
        }
        .form-group { margin-bottom: 16px; }
        .form-group label {
            display: block;
            font-size: 11px; font-weight: 600;
            color: #6b7280;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .form-group input {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            background: #f9fafb;
            color: #111827;
            outline: none;
            transition: border-color 0.15s, background 0.15s;
        }
        .form-group input:focus {
            border-color: #16a34a;
            background: white;
            box-shadow: 0 0 0 3px rgba(22,163,74,0.1);
        }
        .error-msg { font-size: 12px; color: #dc2626; margin-top: 5px; }
        .btn-submit {
            width: 100%; padding: 13px;
            background: #15803d;
            color: white; border: none;
            border-radius: 8px;
            font-size: 15px; font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: background 0.15s;
            margin-top: 8px;
        }
        .btn-submit:hover { background: #166534; }
        .back-link {
            text-align: center;
            margin-top: 18px;
            font-size: 13px;
            color: #6b7280;
        }
        .back-link a { color: #16a34a; font-weight: 600; text-decoration: none; }
        .footer-note {
            text-align: center;
            margin-top: 28px;
            font-size: 12px;
            color: #9ca3af;
            border-top: 1px solid #f0f0eb;
            padding-top: 16px;
        }
    </style>
</head>
<body>
<div class="card">
    <div class="card-header">
        <div class="card-icon">
            <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </div>
        <div class="card-title">Lupa Password?</div>
        <div class="card-sub">Masukkan email kamu dan kami akan mengirimkan link untuk reset password.</div>
    </div>

    @if (session('status'))
        <div class="status-msg">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}"
                   placeholder="email@example.com" required autofocus />
            @error('email')
                <div class="error-msg">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn-submit">Kirim Link Reset Password</button>
    </form>

    <div class="back-link">
        Ingat password? <a href="{{ route('login') }}">Masuk di sini</a>
    </div>

    <div class="footer-note">
        &copy; {{ date('Y') }} SIMTAL &mdash; Talent Management System
    </div>
</div>
</body>
</html>