<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>SIMTAL - Login</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    {{-- PWA / mobile app feel --}}
    <meta name="theme-color" content="#14532d">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SIMTAL">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @php
        // Selalu pakai WIB (app timezone = UTC), agar sapaan sesuai waktu Indonesia.
        $wib = now()->timezone('Asia/Jakarta');
        $jam = (int) $wib->format('H');
        $salam = $jam < 11 ? 'Selamat Pagi' : ($jam < 15 ? 'Selamat Siang' : ($jam < 18 ? 'Selamat Sore' : 'Selamat Malam'));
        $emoji = $jam < 11 ? '🌅' : ($jam < 15 ? '☀️' : ($jam < 18 ? '🌇' : '🌙'));
        $tanggal = $wib->locale('id')->translatedFormat('l, d F Y');
        $jamWib  = $wib->format('H:i');
    @endphp
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root { --brand: #14532d; --brand-2: #166534; }

        html, body { height: 100%; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100dvh;
            background: #f0f2f5;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            padding: clamp(12px, 4vw, 32px);
            /* aman untuk notch/home-indicator HP */
            padding-top: max(clamp(12px, 4vw, 32px), env(safe-area-inset-top));
            padding-bottom: max(clamp(12px, 4vw, 32px), env(safe-area-inset-bottom));
        }

        .card {
            background: white;
            border-radius: clamp(16px, 3vw, 20px);
            box-shadow: 0 8px 40px rgba(0,0,0,0.08);
            display: flex;
            width: 100%;
            max-width: 900px;
            min-width: 0;
            min-height: 540px;
            overflow: hidden;
            animation: fadeUp .55s cubic-bezier(.2,.7,.2,1) both;
        }

        /* LEFT - Illustration Panel */
        .panel-left {
            width: 480px;
            flex-shrink: 0;
            min-width: 0;
            background: linear-gradient(160deg, #1a6b3c 0%, #145e34 40%, #0e4a28 100%);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: clamp(28px, 5vw, 40px) clamp(20px, 4vw, 40px);
            color: #fff;
        }

        /* Background circles */
        .panel-left::before,
        .panel-left::after {
            content: '';
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            border-radius: 50%;
        }
        .panel-left::before { width: 380px; height: 380px; background: rgba(255,255,255,0.05); }
        .panel-left::after  { width: 260px; height: 260px; background: rgba(255,255,255,0.06); }

        /* Top logos */
        .top-logos {
            position: absolute;
            top: 18px; left: 50%;
            transform: translateX(-50%);
            background: white;
            border-radius: 10px;
            padding: 7px 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 2;
            max-width: calc(100% - 32px);
        }
        .top-logos .company-name { font-size: 13px; font-weight: 700; color: #14532d; letter-spacing: .5px; }
        .top-logos .divider-v { width: 1px; height: 18px; background: #e5e7eb; flex-shrink: 0; }
        .top-logos img { height: 26px; width: auto; object-fit: contain; flex-shrink: 0; }
        .top-logos .sub-name { font-size: 11px; font-weight: 500; color: #6b7280; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        /* Center illustration */
        .illus-wrap { position: relative; z-index: 2; text-align: center; margin-top: 24px; }

        .center-icon {
            width: clamp(88px, 20vw, 110px); height: clamp(88px, 20vw, 110px);
            background: rgba(255,255,255,0.12);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 22px;
            border: 2px solid rgba(255,255,255,0.2);
            animation: floaty 4.5s ease-in-out infinite;
        }
        .center-icon svg { width: 48%; height: 48%; stroke: white; fill: none; stroke-width: 1.5; }

        .app-name { font-size: clamp(26px, 7vw, 32px); font-weight: 700; letter-spacing: 1px; }
        .app-tagline { font-size: clamp(12px, 3.4vw, 13px); color: rgba(255,255,255,0.6); margin-top: 6px; letter-spacing: .5px; }

        /* Rotator */
        .rotator {
            margin-top: 10px;
            font-size: clamp(12px, 3.4vw, 13px);
            font-weight: 600;
            color: #bbf7d0;
            min-height: 1.2em;
            transition: opacity .35s ease;
        }

        /* Floating info chips */
        .chips { display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; margin-top: 28px; position: relative; z-index: 2; }
        .chip {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 100px;
            padding: 6px 13px;
            font-size: clamp(10px, 3vw, 11px);
            color: rgba(255,255,255,0.78);
            -webkit-backdrop-filter: blur(4px);
            backdrop-filter: blur(4px);
            animation: fadeUp .5s ease both;
        }
        .chip:nth-child(1){ animation-delay:.15s } .chip:nth-child(2){ animation-delay:.25s }
        .chip:nth-child(3){ animation-delay:.35s } .chip:nth-child(4){ animation-delay:.45s }

        /* RIGHT - Login */
        .panel-right {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: clamp(32px, 6vw, 48px) clamp(24px, 5vw, 52px);
            position: relative;
        }

        .login-inner { width: 100%; max-width: 300px; text-align: center; margin: auto; }

        .greeting {
            display: inline-flex; align-items: center; gap: 6px;
            background: #f0fdf4; color: #15803d;
            font-size: 12px; font-weight: 600;
            padding: 5px 12px; border-radius: 100px;
            margin-bottom: 16px;
            animation: fadeUp .5s ease .1s both;
        }
        .login-title { font-size: clamp(24px, 6.5vw, 28px); font-weight: 700; color: #111827; margin-bottom: 8px; animation: fadeUp .5s ease .18s both; }
        .login-sub { font-size: clamp(13px, 3.6vw, 14px); color: #6b7280; margin-bottom: 28px; animation: fadeUp .5s ease .26s both; }

        .btn-identik {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            width: 100%;
            background: var(--brand);
            color: white;
            padding: 14px 24px;
            border-radius: 10px;
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: background .15s, box-shadow .15s, transform .1s;
            box-shadow: 0 2px 12px rgba(20,83,45,0.25);
            animation: fadeUp .5s ease .34s both;
            -webkit-tap-highlight-color: transparent;
        }
        .btn-identik svg { width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 2; }
        .btn-identik:hover { background: var(--brand-2); box-shadow: 0 4px 20px rgba(20,83,45,0.32); transform: translateY(-1px); }
        .btn-identik:active { transform: translateY(0); }

        .login-date { font-size: 12px; color: #9ca3af; margin-top: 18px; animation: fadeUp .5s ease .42s both; }

        /* Footer */
        .panel-footer {
            width: 100%;
            display: flex; align-items: center; justify-content: space-between;
            gap: 10px; flex-wrap: wrap;
            margin-top: 24px;
            padding-top: 14px;
            border-top: 1px solid #f3f4f6;
        }
        .panel-footer .copy { font-size: 11px; color: #9ca3af; }
        .privacy-link { font-size: 12px; color: #9ca3af; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
        .privacy-link:hover { color: #6b7280; }
        .privacy-link svg { width: 12px; height: 12px; stroke: currentColor; fill: none; stroke-width: 2; }

        /* Overlay transition */
        .overlay { position: fixed; inset: 0; background: var(--brand); z-index: 1000; opacity: 0; pointer-events: none; transition: opacity .4s ease; }
        .overlay.active { opacity: 1; pointer-events: all; }

        @keyframes fadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: none; } }
        @keyframes floaty { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Loading state tombol */
        .spinner { width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.4); border-top-color: #fff; border-radius: 50%; display: inline-block; animation: spin .7s linear infinite; }
        .btn-identik.loading { opacity: .9; cursor: default; pointer-events: none; }

        /* Fokus keyboard yang jelas (aksesibilitas) */
        .btn-identik:focus-visible { outline: 3px solid #86efac; outline-offset: 2px; }
        .privacy-link:focus-visible { outline: 2px solid #86efac; outline-offset: 2px; border-radius: 4px; }

        /* ===== RESPONSIVE ===== */
        /* Tablet & bawah: susun vertikal */
        @media (max-width: 760px) {
            body { align-items: flex-start; }
            .card { flex-direction: column; min-height: auto; max-width: 460px; }
            .panel-left { width: 100%; padding-top: 62px; padding-bottom: 30px; }
            .panel-left::before { width: 300px; height: 300px; }
            .panel-left::after  { width: 210px; height: 210px; }
            .illus-wrap { margin-top: 8px; }
            .chips { margin-top: 20px; }
            .panel-right { padding: 32px 28px; }
            .login-inner { max-width: 340px; }
        }
        /* HP kecil */
        @media (max-width: 400px) {
            .top-logos { gap: 8px; padding: 6px 10px; }
            .top-logos .sub-name { display: none; }
            .chips { gap: 6px; }
            .panel-right { padding: 28px 20px; }
            .panel-footer { justify-content: center; }
        }
        /* HP sangat kecil */
        @media (max-width: 340px) {
            .app-name { letter-spacing: .5px; }
            .chip { padding: 5px 10px; }
        }
        /* Layar rendah (landscape HP) */
        @media (max-height: 560px) and (min-width: 761px) {
            .card { min-height: auto; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, .card, .center-icon, .chip, .greeting, .login-title, .login-sub, .btn-identik, .login-date {
                animation: none !important;
            }
        }
    </style>
</head>
<body>

<div class="overlay" id="overlay"></div>

<div class="card">

    <!-- LEFT -->
    <div class="panel-left">
        <div class="top-logos">
            <span class="company-name">SIMTAL</span>
            <div class="divider-v"></div>
            <img src="{{ asset('images/PIM.png') }}" alt="Pupuk Iskandar Muda">
            <span class="sub-name">Pupuk Iskandar Muda</span>
        </div>

        <div class="illus-wrap">
            <div class="center-icon">
                <svg viewBox="0 0 64 64">
                    <circle cx="22" cy="18" r="8" stroke-width="2"/>
                    <path d="M6 50c0-9 7-16 16-16h0c9 0 16 7 16 16" stroke-width="2" stroke-linecap="round"/>
                    <path d="M40 28l5 5 10-14" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="47" cy="26" r="10" stroke-width="1.5" stroke-dasharray="3 2"/>
                </svg>
            </div>

            <div class="app-name">SIMTAL</div>
            <div class="app-tagline">Sistem Manajemen Talenta</div>
            <div class="rotator" id="rotator">Kelola Talenta</div>
        </div>

        <div class="chips">
            <span class="chip">👥 Manajemen Karyawan</span>
            <span class="chip">📊 Assessment</span>
            <span class="chip">🏅 PGS &amp; PJS</span>
            <span class="chip">🎓 Kompetensi</span>
        </div>
    </div>

    <!-- RIGHT -->
    <div class="panel-right">
        <div class="login-inner">
            <div class="greeting">{{ $emoji }} {{ $salam }}</div>
            <div class="login-title">Selamat Datang</div>
            <div class="login-sub">Masuk ke SIMTAL untuk melanjutkan</div>

            <a href="#" class="btn-identik" id="btnMasuk" role="button" aria-label="Login dengan Identik">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                Login with Identik
            </a>

            <div class="login-date">{{ $tanggal }} · {{ $jamWib }} WIB</div>
        </div>

        <div class="panel-footer">
            <span class="copy">© {{ date('Y') }} Pupuk Iskandar Muda · v1.0</span>
            <a href="#" class="privacy-link">
                Kebijakan Privasi
                <svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            </a>
        </div>
    </div>

</div>

<script>
    // Rotator tagline
    (function () {
        var items = ['Kelola Talenta', 'Pantau MDG & Promosi', 'Assessment & Kompetensi', 'Perencanaan Suksesi'];
        var el = document.getElementById('rotator');
        var i = 0;
        if (el && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            setInterval(function () {
                el.style.opacity = 0;
                setTimeout(function () {
                    i = (i + 1) % items.length;
                    el.textContent = items[i];
                    el.style.opacity = 1;
                }, 350);
            }, 2600);
        }
    })();

    // Transisi masuk + loading state
    var LOGIN_URL = '{{ route("login") }}';
    var btn = document.getElementById('btnMasuk');
    var overlay = document.getElementById('overlay');
    var btnDefault = btn.innerHTML;

    btn.addEventListener('click', function (e) {
        e.preventDefault();
        if (btn.dataset.loading) return;
        btn.dataset.loading = '1';
        btn.classList.add('loading');
        btn.innerHTML = '<span class="spinner"></span> Menghubungkan…';
        overlay.classList.add('active');
        setTimeout(function () { window.location.href = LOGIN_URL; }, 500);
    });

    // Reset saat halaman ditampilkan kembali (termasuk restore dari bfcache saat
    // tombol Back ditekan) — mencegah layar "hijau saja" & tombol stuck loading.
    window.addEventListener('pageshow', function () {
        overlay.classList.remove('active');
        btn.classList.remove('loading');
        delete btn.dataset.loading;
        btn.innerHTML = btnDefault;
    });
</script>
</body>
</html>
