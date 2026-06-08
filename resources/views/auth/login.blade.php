<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIMTAL</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
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
        .login-wrap {
            display: flex;
            width: 900px;
            min-height: 540px;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.12);
        }
        /* === LEFT PANEL === */
        .login-left {
            width: 42%;
            background: #14532d;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 40px 36px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        .login-left::before {
            content: '';
            position: absolute;
            top: -80px; right: -80px;
            width: 220px; height: 220px;
            border-radius: 50%;
            background: rgba(255,255,255,0.04);
        }
        .login-left::after {
            content: '';
            position: absolute;
            bottom: -60px; left: -60px;
            width: 200px; height: 200px;
            border-radius: 50%;
            background: rgba(255,255,255,0.04);
        }
        .brand-logo { display: flex; align-items: center; gap: 12px; }
        .brand-icon {
            width: 42px; height: 42px;
            background: #16a34a;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; font-weight: 700; color: white; 

        }
        .brand-text h1 { font-size: 20px; font-weight: 600; }
        .brand-text p { font-size: 11px; color: #86efac; margin-top: 2px; }
        .left-desc { position: relative; z-index: 1; }
        .left-desc h2 { font-size: 22px; font-weight: 600; line-height: 1.4; margin-bottom: 12px; }
        .left-desc p { font-size: 13px; color: #bbf7d0; line-height: 1.7; }
        .left-badges { display: flex; flex-direction: column; gap: 10px; position: relative; z-index: 1; }
        .badge-item {
            display: flex; align-items: center; gap: 10px;
            background: rgba(255,255,255,0.07);
            border-radius: 8px; padding: 12px 16px;
            border: 0.5px solid rgba(255,255,255,0.1);
        }
        .badge-dot { width: 8px; height: 8px; border-radius: 50%; background: #4ade80; flex-shrink: 0; }
        .badge-item span { font-size: 13px; color: #dcfce7; }

        /* === RIGHT PANEL === */
        .login-right {
            flex: 1;
            background: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 50px 44px;
        }
        .login-right h2 { font-size: 24px; font-weight: 600; color: #111827; margin-bottom: 6px; }
        .login-right .sub { font-size: 14px; color: #6b7280; margin-bottom: 32px; }
        .form-group { margin-bottom: 18px; }
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
        .form-group input:focus { border-color: #16a34a; background: white; box-shadow: 0 0 0 3px rgba(22,163,74,0.1); }
        .remember-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
        .remember-row label { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #6b7280; cursor: pointer; }
        .remember-row a { font-size: 13px; color: #16a34a; text-decoration: none; font-weight: 500; }
        .btn-login {
            width: 100%; padding: 13px;
            background: #15803d;
            color: white; border: none;
            border-radius: 8px;
            font-size: 15px; font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: background 0.15s;
        }
        .btn-login:hover { background: #166534; }
        .error-msg {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 10px 14px;
            color: #dc2626;
            font-size: 13px;
            margin-bottom: 16px;
        }
        .footer-note { text-align: center; margin-top: 24px; font-size: 12px; color: #9ca3af; }
        input[type=checkbox] { accent-color: #16a34a; }
    </style>
</head>
<body>
<div class="login-wrap">
    {{-- LEFT PANEL --}}
    <div class="login-left">
        <div class="brand-logo">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" style="width:42px; height:42px; object-fit:contain;">
            <div class="brand-text">
                <h1>SIMTAL</h1>
                <p>Sistem Manajemen Talenta</p>
            </div>
        </div>
        <div class="left-desc">
            <h2>Sistem Manajemen Talenta Karyawan</h2>
            <p>Kelola data karyawan, riwayat jabatan, dan assessment secara terpusat dan efisien.</p>
        </div>
        <div class="left-badges">
            <div class="badge-item">
                <div class="badge-dot"></div>
                <span>History Jabatan & Assessment</span>
            </div>
            <div class="badge-item">
                <div class="badge-dot"></div>
                <span>Pemantauan Karyawan Real-time</span>
            </div>
            <div class="badge-item">
                <div class="badge-dot"></div>
                <span>Laporan & Repository Dokumen</span>
            </div>
        </div>
    </div>

    {{-- RIGHT PANEL --}}
    <div class="login-right">
        <h2>Selamat Datang 👋</h2>
        <p class="sub">Masuk ke akun SIMTAL kamu</p>

        @if ($errors->any())
            <div class="error-msg">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       placeholder="email@example.com" required autofocus />
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required />
            </div>
            <div class="remember-row">
                <label>
                    <input type="checkbox" name="remember" />
                    Ingat saya
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}">Lupa password?</a>
                @endif
            </div>
            <button type="submit" class="btn-login">Masuk ke SIMTAL</button>
        </form>

        <footer style="text-align:center; padding: 16px; font-size: 12px; color: #9ca3af; border-top: 1px solid #f0f0eb; background: white;">
            &copy; {{ date('Y') }} SIMTAL &mdash; Talent Management System. All rights reserved.
        </footer>
    </div>
</div>
</body>
</html>