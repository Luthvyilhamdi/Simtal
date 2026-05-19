<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMTAL - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            height: 100vh;
            background: #f0f2f5;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.08);
            display: flex;
            width: 900px;
            min-height: 540px;
            overflow: hidden;
        }

        /* LEFT - Illustration Panel */
        .panel-left {
            width: 480px;
            flex-shrink: 0;
            background: linear-gradient(160deg, #1a6b3c 0%, #145e34 40%, #0e4a28 100%);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }

        /* Background circles */
        .panel-left::before {
            content: '';
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 380px; height: 380px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
        }
        .panel-left::after {
            content: '';
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 260px; height: 260px;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
        }

        /* Top logos */
        .top-logos {
            position: absolute;
            top: 24px; left: 50%;
            transform: translateX(-50%);
            background: white;
            border-radius: 10px;
            padding: 8px 18px;
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 2;
            white-space: nowrap;
        }
        .top-logos .company-name {
            font-size: 13px;
            font-weight: 700;
            color: #14532d;
            letter-spacing: 0.5px;
        }
        .top-logos .divider-v {
            width: 1px;
            height: 18px;
            background: #e5e7eb;
        }
        .top-logos .sub-name {
            font-size: 11px;
            font-weight: 500;
            color: #6b7280;
        }

        /* Center illustration - SVG scene */
        .illus-wrap {
            position: relative;
            z-index: 2;
            text-align: center;
            margin-top: 20px;
        }

        /* Big icon in center */
        .center-icon {
            width: 110px; height: 110px;
            background: rgba(255,255,255,0.12);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 24px;
            border: 2px solid rgba(255,255,255,0.2);
        }
        .center-icon svg {
            width: 54px; height: 54px;
            stroke: white;
            fill: none;
            stroke-width: 1.5;
        }

        .app-name {
            font-size: 32px;
            font-weight: 700;
            color: white;
            letter-spacing: 1px;
        }
        .app-tagline {
            font-size: 13px;
            color: rgba(255,255,255,0.6);
            margin-top: 6px;
            letter-spacing: 0.5px;
        }

        /* Floating info chips */
        .chips {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px;
            margin-top: 32px;
            position: relative;
            z-index: 2;
        }
        .chip {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 100px;
            padding: 6px 14px;
            font-size: 11px;
            color: rgba(255,255,255,0.75);
            backdrop-filter: blur(4px);
        }

        /* RIGHT - Login */
        .panel-right {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 52px;
            position: relative;
        }

        .login-inner {
            width: 100%;
            max-width: 280px;
            text-align: center;
        }

        .login-title {
            font-size: 28px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
        }
        .login-sub {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 36px;
        }

        .btn-identik {
            display: block;
            width: 100%;
            background: #14532d;
            color: white;
            padding: 14px 24px;
            border-radius: 10px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: background 0.15s, box-shadow 0.15s, transform 0.1s;
            box-shadow: 0 2px 12px rgba(20,83,45,0.25);
        }
        .btn-identik:hover {
            background: #166534;
            box-shadow: 0 4px 20px rgba(20,83,45,0.32);
            transform: translateY(-1px);
        }
        .btn-identik:active { transform: translateY(0); }

        .privacy-link {
            position: absolute;
            bottom: 24px;
            font-size: 12px;
            color: #9ca3af;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .privacy-link:hover { color: #6b7280; }
        .privacy-link svg { width: 12px; height: 12px; stroke: currentColor; fill: none; stroke-width: 2; }

        /* Transition */
        .overlay {
            position: fixed; inset: 0;
            background: #14532d;
            z-index: 1000;
            opacity: 0; pointer-events: none;
            transition: opacity 0.4s ease;
        }
        .overlay.active { opacity: 1; pointer-events: all; }

        @media (max-width: 760px) {
            .card { flex-direction: column; width: 94%; min-height: auto; }
            .panel-left { width: 100%; min-height: 260px; }
            .panel-right { padding: 40px 32px; }
        }
    </style>
</head>
<body>

<div class="overlay" id="overlay"></div>

<div class="card">

    <!-- LEFT -->
    <div class="panel-left">
        <!-- Company logos -->
        <div class="top-logos">
            <span class="company-name">SIMTAL</span>
            <div class="divider-v"></div>
            <span class="sub-name">Pupuk Iskandar Muda</span>
        </div>

        <!-- Center icon -->
        <div class="illus-wrap">
            <div class="center-icon">
                <!-- Person + chart icon -->
                <svg viewBox="0 0 64 64">
                    <circle cx="22" cy="18" r="8" stroke-width="2"/>
                    <path d="M6 50c0-9 7-16 16-16h0c9 0 16 7 16 16" stroke-width="2" stroke-linecap="round"/>
                    <path d="M40 28l5 5 10-14" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="47" cy="26" r="10" stroke-width="1.5" stroke-dasharray="3 2"/>
                </svg>
            </div>

            <div class="app-name">SIMTAL</div>
            <div class="app-tagline">Sistem Manajemen Talenta</div>
        </div>

        <!-- Chips -->
        <div class="chips">
            <span class="chip">👥 Manajemen Karyawan</span>
            <span class="chip">📊 Assessment</span>
            <span class="chip">🏅 PGS & PJS</span>
            <span class="chip">🎓 Kompetensi</span>
        </div>
    </div>

    <!-- RIGHT -->
    <div class="panel-right">
        <div class="login-inner">
            <div class="login-title">Login</div>
            <div class="login-sub">Selamat Datang di SIMTAL</div>

            <a href="#" class="btn-identik" id="btnMasuk">Login with Identik</a>
        </div>

        <a href="#" class="privacy-link">
            Kebijakan Privasi
            <svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        </a>
    </div>

</div>

<script>
    document.getElementById('btnMasuk').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('overlay').classList.add('active');
        setTimeout(() => {
            window.location.href = '{{ route("login") }}';
        }, 420);
    });
</script>
</body>
</html>