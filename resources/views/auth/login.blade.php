<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#14532d">
    <title>Login - SIMTAL</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --green-900: #14532d;
            --green-800: #166534;
            --green-700: #15803d;
            --green-600: #16a34a;
            --green-400: #4ade80;
        }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background:
                radial-gradient(1000px 600px at 100% 0%, #dcfce7 0%, transparent 60%),
                radial-gradient(900px 600px at 0% 100%, #d1fae5 0%, transparent 55%),
                #f0fdf4;
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            color: #111827;
        }
        .login-wrap {
            display: flex;
            width: 100%;
            max-width: 960px;
            min-height: 560px;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 24px 70px rgba(6, 78, 59, 0.18), 0 2px 8px rgba(0,0,0,0.04);
        }

        /* === LEFT PANEL === */
        .login-left {
            width: 44%;
            background:
                radial-gradient(600px 400px at 120% -10%, rgba(74,222,128,0.22), transparent 60%),
                linear-gradient(160deg, #14532d 0%, #166534 55%, #0f3d21 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 44px 40px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        .login-left::before {
            content: '';
            position: absolute;
            top: -90px; right: -90px;
            width: 240px; height: 240px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
        }
        .login-left::after {
            content: '';
            position: absolute;
            bottom: -70px; left: -70px;
            width: 220px; height: 220px;
            border-radius: 50%;
            background: rgba(255,255,255,0.04);
        }
        .brand-logo { display: flex; align-items: center; gap: 14px; position: relative; z-index: 1; }
        .brand-logo img {
            width: 48px; height: 48px; object-fit: contain;
            background: white; border-radius: 12px; padding: 6px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.18);
        }
        .brand-text h1 { font-size: 22px; font-weight: 800; letter-spacing: 0.5px; }
        .brand-text p { font-size: 11.5px; color: #86efac; margin-top: 2px; letter-spacing: 0.3px; }
        .left-desc { position: relative; z-index: 1; }
        .left-desc h2 { font-size: 24px; font-weight: 700; line-height: 1.35; margin-bottom: 14px; }
        .left-desc p { font-size: 13.5px; color: #bbf7d0; line-height: 1.7; max-width: 320px; }
        .left-badges { display: flex; flex-direction: column; gap: 10px; position: relative; z-index: 1; }
        .badge-item {
            display: flex; align-items: center; gap: 12px;
            background: rgba(255,255,255,0.08);
            border-radius: 10px; padding: 13px 16px;
            border: 1px solid rgba(255,255,255,0.12);
            backdrop-filter: blur(2px);
        }
        .badge-dot { width: 9px; height: 9px; border-radius: 50%; background: var(--green-400); flex-shrink: 0; box-shadow: 0 0 0 4px rgba(74,222,128,0.18); }
        .badge-item span { font-size: 13px; color: #dcfce7; font-weight: 500; }

        /* === RIGHT PANEL === */
        .login-right {
            flex: 1;
            background: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 52px 48px;
        }
        .welcome-head h2 { font-size: 26px; font-weight: 700; color: #111827; margin-bottom: 6px; }
        .welcome-head .sub { font-size: 14px; color: #6b7280; margin-bottom: 30px; }

        /* Header ringkas — hanya muncul di HP */
        .mobile-brand { display: none; }

        .form-group { margin-bottom: 18px; }
        .form-group label {
            display: block;
            font-size: 11px; font-weight: 700;
            color: #6b7280;
            margin-bottom: 7px;
            letter-spacing: 0.6px;
            text-transform: uppercase;
        }
        .input-wrap { position: relative; }
        .form-group input {
            width: 100%;
            padding: 13px 14px;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-size: 15px;
            font-family: inherit;
            background: #f9fafb;
            color: #111827;
            outline: none;
            transition: border-color 0.15s, background 0.15s, box-shadow 0.15s;
        }
        .form-group input::placeholder { color: #9ca3af; }
        .form-group input:focus {
            border-color: var(--green-600);
            background: white;
            box-shadow: 0 0 0 4px rgba(22,163,74,0.12);
        }
        .input-wrap input.has-toggle { padding-right: 48px; }
        .toggle-pass {
            position: absolute; top: 50%; right: 6px;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            padding: 8px; border-radius: 8px;
            color: #9ca3af; display: flex; align-items: center;
            transition: color 0.15s, background 0.15s;
        }
        .toggle-pass:hover { color: var(--green-700); background: #f0fdf4; }

        .remember-row { display: flex; align-items: center; justify-content: space-between; margin: 4px 0 24px; flex-wrap: wrap; gap: 10px; }
        .remember-row label { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #6b7280; cursor: pointer; }
        .remember-row a { font-size: 13px; color: var(--green-600); text-decoration: none; font-weight: 600; }
        .remember-row a:hover { text-decoration: underline; }

        .btn-login {
            width: 100%; padding: 14px;
            background: linear-gradient(135deg, var(--green-700), var(--green-800));
            color: white; border: none;
            border-radius: 10px;
            font-size: 15px; font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: transform 0.12s, box-shadow 0.15s, filter 0.15s;
            box-shadow: 0 8px 20px rgba(21,128,61,0.25);
        }
        .btn-login:hover { filter: brightness(1.06); box-shadow: 0 10px 26px rgba(21,128,61,0.32); }
        .btn-login:active { transform: translateY(1px); }

        .error-msg {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 12px 14px;
            color: #dc2626;
            font-size: 13px;
            margin-bottom: 18px;
            display: flex; align-items: center; gap: 8px;
        }
        .footer-note {
            text-align: center; margin-top: 28px; padding-top: 18px;
            font-size: 12px; color: #9ca3af; border-top: 1px solid #f1f5f0;
        }
        input[type=checkbox] { width: 16px; height: 16px; accent-color: var(--green-600); }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 860px) {
            .login-wrap { flex-direction: column; max-width: 460px; min-height: 0; }
            .login-left { display: none; }        /* panel dekoratif disembunyikan di HP */
            .login-right { padding: 34px 26px 28px; }
            .mobile-brand {
                display: flex; flex-direction: column; align-items: center;
                text-align: center; gap: 12px; margin-bottom: 26px;
            }
            .mobile-brand img {
                width: 60px; height: 60px; object-fit: contain;
                background: linear-gradient(160deg, #14532d, #166534);
                border-radius: 16px; padding: 10px;
                box-shadow: 0 10px 24px rgba(20,83,45,0.28);
            }
            .mobile-brand h1 { font-size: 22px; font-weight: 800; color: var(--green-900); letter-spacing: 0.5px; }
            .mobile-brand p { font-size: 12px; color: #6b7280; margin-top: 1px; }
            .welcome-head { text-align: center; }
            .welcome-head h2 { font-size: 22px; }
            .welcome-head .sub { margin-bottom: 26px; }
        }
        @media (max-width: 420px) {
            body { padding: 0; align-items: stretch; }
            .login-wrap { border-radius: 0; box-shadow: none; max-width: 100%; min-height: 100dvh; justify-content: center; }
            .login-right { padding: 30px 22px; }
            .form-group input { font-size: 16px; }  /* cegah zoom auto di iOS */
        }
    </style>
</head>
<body>
<div class="login-wrap">
    {{-- LEFT PANEL (desktop) --}}
    <div class="login-left">
        <div class="brand-logo">
            <img src="{{ asset('images/logo.png') }}" alt="Logo SIMTAL">
            <div class="brand-text">
                <h1>SIMTAL</h1>
                <p>Sistem Manajemen Talenta</p>
            </div>
        </div>
        <div class="left-desc">
            <h2>Sistem Manajemen Talenta Karyawan</h2>
            <p>Kelola data karyawan, riwayat jabatan, dan assessment secara terpusat, aman, dan efisien.</p>
        </div>
        <div class="left-badges">
            <div class="badge-item">
                <div class="badge-dot"></div>
                <span>History Jabatan &amp; Assessment</span>
            </div>
            <div class="badge-item">
                <div class="badge-dot"></div>
                <span>Pemantauan Karyawan Real-time</span>
            </div>
            <div class="badge-item">
                <div class="badge-dot"></div>
                <span>Laporan &amp; Repository Dokumen</span>
            </div>
        </div>
    </div>

    {{-- RIGHT PANEL --}}
    <div class="login-right">
        {{-- Brand ringkas untuk HP --}}
        <div class="mobile-brand">
            <img src="{{ asset('images/logo.png') }}" alt="Logo SIMTAL">
            <div>
                <h1>SIMTAL</h1>
                <p>Sistem Manajemen Talenta</p>
            </div>
        </div>

        <div class="welcome-head">
            <h2>Selamat Datang 👋</h2>
            <p class="sub">Masuk ke akun SIMTAL Anda</p>
        </div>

        @if ($errors->any())
            <div class="error-msg">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="flex-shrink:0;">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label for="nik">NIK</label>
                <div class="input-wrap">
                    <input type="text" id="nik" name="nik" value="{{ old('nik') }}"
                           placeholder="Nomor Induk Karyawan" inputmode="numeric" required autofocus autocomplete="username" />
                </div>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <input type="password" id="password" name="password" class="has-toggle"
                           placeholder="••••••••" required autocomplete="current-password" />
                    <button type="button" class="toggle-pass" id="togglePass" aria-label="Tampilkan password">
                        <svg id="eyeIcon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
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

        <div class="footer-note">
            &copy; {{ date('Y') }} SIMTAL &mdash; Talent Management System.
        </div>
    </div>
</div>

<script>
    (function () {
        var toggle = document.getElementById('togglePass');
        var input  = document.getElementById('password');
        var icon   = document.getElementById('eyeIcon');
        if (!toggle) return;
        var eyeOpen  = '<path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/>';
        var eyeClose = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
        toggle.addEventListener('click', function () {
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            icon.innerHTML = show ? eyeClose : eyeOpen;
            toggle.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
        });
    })();
</script>
</body>
</html>
