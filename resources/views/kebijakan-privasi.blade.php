<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kebijakan Privasi — SIMTAL</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        :root { --brand:#14532d; --brand-2:#15803d; --ink:#111827; --muted:#6b7280; --line:#e5e7eb; }
        body { font-family:'Inter',sans-serif; background:#f0f2f5; color:var(--ink); line-height:1.65; padding:24px 16px; }

        .doc { max-width:820px; margin:0 auto; background:#fff; border-radius:16px; box-shadow:0 8px 40px rgba(0,0,0,0.07); overflow:hidden; }

        .doc-head { background:linear-gradient(160deg,#1a6b3c 0%,#145e34 45%,#0e4a28 100%); color:#fff; padding:32px 36px; }
        .doc-head .brand { font-size:13px; font-weight:600; color:rgba(255,255,255,0.7); letter-spacing:.5px; }
        .doc-head h1 { font-size:26px; font-weight:700; margin-top:4px; }
        .doc-head .updated { font-size:12.5px; color:rgba(255,255,255,0.75); margin-top:10px; }

        .doc-body { padding:32px 36px; }
        .doc-body p { font-size:14px; color:#374151; margin-bottom:14px; }
        .intro { font-size:14px; color:#4b5563; }

        .note { background:#fffbeb; border:1px solid #fde68a; color:#92400e; border-radius:10px; padding:12px 15px; font-size:12.5px; margin:18px 0 26px; }

        section { margin-bottom:24px; }
        section h2 { font-size:15px; font-weight:700; color:var(--brand); display:flex; align-items:baseline; gap:9px; margin-bottom:8px; padding-bottom:7px; border-bottom:1px solid var(--line); }
        section h2 .num { font-size:12px; font-weight:700; color:#fff; background:var(--brand-2); border-radius:6px; min-width:22px; height:22px; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0; }
        section p, section li { font-size:13.5px; color:#374151; }
        ul { margin:8px 0 4px 4px; padding-left:20px; }
        li { margin-bottom:5px; }
        strong { color:var(--ink); }

        .doc-foot { border-top:1px solid var(--line); padding:22px 36px; display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
        .doc-foot .copy { font-size:12px; color:var(--muted); }
        .back { display:inline-flex; align-items:center; gap:7px; background:var(--brand); color:#fff; text-decoration:none; font-size:13px; font-weight:600; padding:9px 16px; border-radius:9px; }
        .back:hover { background:var(--brand-2); }
        .back svg { width:15px; height:15px; stroke:currentColor; fill:none; stroke-width:2; }

        @media (max-width:560px){
            .doc-head, .doc-body, .doc-foot { padding-left:20px; padding-right:20px; }
        }
    </style>
</head>
<body>
    <div class="doc">
        <div class="doc-head">
            <div class="brand">SIMTAL — Sistem Manajemen Talenta</div>
            <h1>Kebijakan Privasi</h1>
            <div class="updated">Diperbarui: {{ \Carbon\Carbon::now()->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d F Y') }}</div>
        </div>

        <div class="doc-body">
            <p class="intro">
                Kebijakan ini menjelaskan bagaimana <strong>SIMTAL</strong> (Sistem Manajemen Talenta) mengumpulkan,
                menggunakan, menyimpan, dan melindungi data pribadi karyawan. SIMTAL adalah sistem <strong>internal
                perusahaan</strong> yang digunakan untuk pengelolaan talenta, bukan aplikasi publik.
            </p>

            <div class="note">
                ⚠️ <strong>Catatan:</strong> Dokumen ini adalah <strong>draf/template</strong>. Sebelum diberlakukan resmi,
                mohon ditinjau oleh tim Legal/HR agar sesuai dengan UU Pelindungan Data Pribadi dan kebijakan internal perusahaan.
            </div>

            <section>
                <h2><span class="num">1</span> Data yang Kami Kumpulkan</h2>
                <p>SIMTAL mengelola data pribadi karyawan yang meliputi, antara lain:</p>
                <ul>
                    <li><strong>Identitas:</strong> nama, NIK, tempat &amp; tanggal lahir, jenis kelamin, foto, kontak.</li>
                    <li><strong>Kepegawaian:</strong> jabatan, unit/direktorat, job &amp; person grade, status &amp; riwayat jabatan.</li>
                    <li><strong>Penilaian &amp; kompetensi:</strong> hasil assessment, kalibrasi, KPI, TOEFL, rekomendasi pengembangan.</li>
                    <li><strong>Pendidikan &amp; dokumen:</strong> riwayat pendidikan, surat, sertifikat, dan dokumen terkait.</li>
                </ul>
            </section>

            <section>
                <h2><span class="num">2</span> Tujuan Penggunaan Data</h2>
                <p>Data digunakan semata-mata untuk keperluan pengelolaan sumber daya manusia, antara lain:</p>
                <ul>
                    <li>Administrasi &amp; pengelolaan data karyawan;</li>
                    <li>Perencanaan promosi, mutasi, rotasi, dan suksesi;</li>
                    <li>Penilaian kinerja dan pengembangan kompetensi;</li>
                    <li>Pelaporan dan pengambilan keputusan manajemen.</li>
                </ul>
            </section>

            <section>
                <h2><span class="num">3</span> Dasar Hukum</h2>
                <p>
                    Pengolahan data pribadi mengacu pada <strong>Undang-Undang No. 27 Tahun 2022 tentang Pelindungan
                    Data Pribadi (UU PDP)</strong>, dengan dasar persetujuan karyawan dan/atau kepentingan sah perusahaan
                    dalam rangka hubungan kerja.
                </p>
            </section>

            <section>
                <h2><span class="num">4</span> Siapa yang Dapat Mengakses</h2>
                <p>
                    Akses data dibatasi hanya untuk pihak yang berwenang sesuai peran (role) masing-masing —
                    seperti pengelola SDM, atasan terkait, dan administrator sistem. Setiap peran hanya dapat
                    mengakses data sesuai kebutuhan tugasnya.
                </p>
            </section>

            <section>
                <h2><span class="num">5</span> Penyimpanan &amp; Keamanan</h2>
                <p>
                    Data disimpan pada sistem yang dilindungi dengan pembatasan akses berbasis login dan peran,
                    serta dicadangkan secara berkala. Perusahaan berupaya menjaga kerahasiaan, keutuhan, dan
                    ketersediaan data dari akses yang tidak sah.
                </p>
            </section>

            <section>
                <h2><span class="num">6</span> Masa Penyimpanan (Retensi)</h2>
                <p>
                    Data pribadi disimpan selama masih diperlukan untuk tujuan pengelolaan talenta, yaitu selama
                    masa kerja karyawan dan jangka waktu tertentu setelahnya sesuai ketentuan internal dan peraturan
                    yang berlaku.
                </p>
            </section>

            <section>
                <h2><span class="num">7</span> Pembagian Data</h2>
                <p>
                    Data pribadi <strong>tidak dibagikan kepada pihak ketiga di luar perusahaan</strong>, kecuali
                    diwajibkan oleh peraturan perundang-undangan atau atas persetujuan yang bersangkutan.
                </p>
            </section>

            <section>
                <h2><span class="num">8</span> Hak Karyawan atas Datanya</h2>
                <p>Sesuai UU PDP, karyawan berhak untuk:</p>
                <ul>
                    <li>Mengetahui dan mengakses data pribadinya yang tersimpan;</li>
                    <li>Meminta perbaikan atau pembaruan data yang tidak akurat;</li>
                    <li>Meminta penghapusan data sesuai ketentuan yang berlaku.</li>
                </ul>
                <p>Permintaan tersebut dapat diajukan melalui kontak pada bagian akhir kebijakan ini.</p>
            </section>

            <section>
                <h2><span class="num">9</span> Data Teknis &amp; Log Aktivitas</h2>
                <p>
                    Untuk keamanan dan audit, sistem mencatat data teknis seperti sesi login dan log aktivitas
                    (siapa mengakses/mengubah data dan kapan). Catatan ini digunakan untuk menjaga keamanan sistem.
                </p>
            </section>

            <section>
                <h2><span class="num">10</span> Perubahan Kebijakan</h2>
                <p>
                    Kebijakan ini dapat diperbarui sewaktu-waktu. Versi terbaru akan berlaku sejak tanggal
                    pembaruan yang tercantum di atas.
                </p>
            </section>

            <section>
                <h2><span class="num">11</span> Kontak</h2>
                <p>
                    Untuk pertanyaan atau permintaan terkait data pribadi, silakan hubungi
                    <strong>Administrator SIMTAL / Bagian SDM</strong> perusahaan.
                </p>
            </section>
        </div>

        <div class="doc-foot">
            <span class="copy">© {{ date('Y') }} SIMTAL — Talent Management System</span>
            <a href="{{ url('/') }}" class="back">
                <svg viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Kembali
            </a>
        </div>
    </div>
</body>
</html>
