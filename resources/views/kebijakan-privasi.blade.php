<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kebijakan Privasi — SiMental</title>
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/favicon.png') }}?v={{ filemtime(public_path('images/favicon.png')) }}">
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
            <div class="brand">SiMental — Sistem Manajemen Talenta</div>
            <h1>Kebijakan Privasi</h1>
            <div class="updated">Diperbarui: {{ \Carbon\Carbon::now()->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d F Y') }}</div>
        </div>

        <div class="doc-body">
            <p class="intro">
                Kebijakan ini menjelaskan bagaimana <strong>SiMental</strong> (Sistem Manajemen Talenta) mengumpulkan,
                menggunakan, menyimpan, dan melindungi data pribadi karyawan. SiMental adalah sistem <strong>internal
                perusahaan</strong> yang digunakan untuk pengelolaan talenta, bukan aplikasi publik.
            </p>

            <div class="note">
                ⚠️ <strong>Catatan:</strong> Dokumen ini adalah <strong>draf/template</strong>. Sebelum diberlakukan resmi,
                mohon ditinjau oleh tim Legal/HR agar sesuai dengan UU Pelindungan Data Pribadi dan kebijakan internal perusahaan.
            </div>

            <section>
                <h2><span class="num">1</span> Data yang Kami Kumpulkan</h2>
                <p>SiMental mengelola data pribadi karyawan yang meliputi, antara lain:</p>
                <ul>
                    <li><strong>Identitas:</strong> nama, NIK, tempat &amp; tanggal lahir, jenis kelamin, foto, nomor telepon, dan alamat surel.</li>
                    <li><strong>Kepegawaian:</strong> jabatan, unit/direktorat, job &amp; person grade, band, status, serta riwayat jabatan.</li>
                    <li><strong>Penilaian &amp; kompetensi:</strong> hasil assessment, kalibrasi, KPI, nilai TOEFL, dan rekomendasi pengembangan.</li>
                    <li><strong>Pendidikan &amp; dokumen:</strong> riwayat pendidikan, surat keputusan, sertifikat, dan dokumen terkait.</li>
                </ul>
            </section>

            <section>
                <h2><span class="num">2</span> Data yang Tidak Kami Kumpulkan</h2>
                <p>
                    Sebagai batasan yang tegas, SiMental <strong>tidak menyimpan</strong> data gaji dan tunjangan,
                    nomor KTP maupun NPWP, data keagamaan, data kesehatan, data biometrik, alamat rumah,
                    maupun data lokasi. Sistem juga tidak melakukan pelacakan posisi karyawan dalam bentuk apa pun.
                </p>
            </section>

            <section>
                <h2><span class="num">3</span> Tujuan Penggunaan Data</h2>
                <p>Data digunakan semata-mata untuk keperluan pengelolaan sumber daya manusia, antara lain:</p>
                <ul>
                    <li>Administrasi &amp; pengelolaan data karyawan;</li>
                    <li>Perencanaan promosi, mutasi, rotasi, dan suksesi;</li>
                    <li>Penilaian kinerja dan pengembangan kompetensi;</li>
                    <li>Pelaporan dan pengambilan keputusan manajemen.</li>
                </ul>
            </section>

            <section>
                <h2><span class="num">4</span> Dasar Hukum</h2>
                <p>
                    Pengolahan data pribadi mengacu pada <strong>Undang-Undang No. 27 Tahun 2022 tentang Pelindungan
                    Data Pribadi (UU PDP)</strong>, dengan dasar persetujuan karyawan dan/atau kepentingan sah perusahaan
                    dalam rangka hubungan kerja.
                </p>
            </section>

            <section>
                <h2><span class="num">5</span> Siapa yang Dapat Mengakses</h2>
                <p>
                    Akses dibatasi berdasarkan peran, dan pembatasannya dijalankan oleh sistem &mdash; bukan sekadar
                    disembunyikan dari tampilan. Terdapat tiga peran:
                </p>
                <ul>
                    <li><strong>Karyawan (user):</strong> hanya dapat melihat struktur organisasi dan data penempatan dirinya sendiri.</li>
                    <li><strong>Admin:</strong> hanya dapat membuka menu yang secara khusus diberikan kepadanya, satu per satu.</li>
                    <li><strong>Super Admin:</strong> memiliki akses penuh, termasuk pengelolaan akun dan cadangan data.</li>
                </ul>
            </section>

            <section>
                <h2><span class="num">6</span> Akses sebagai Akun Lain</h2>
                <p>
                    Untuk keperluan pemeriksaan hak akses, Super Admin dapat masuk sementara sebagai akun lain.
                    Selama berlangsung, sebuah <strong>peringatan tetap tampil di layar</strong>, dan tindakan yang
                    dilakukan tercatat atas nama akun tersebut. <strong>Awal dan akhir</strong> penggunaan fasilitas
                    ini dicatat dalam log aktivitas, lengkap dengan nama pihak yang melakukannya. Fasilitas ini
                    <strong>tidak dapat digunakan</strong> terhadap sesama Super Admin.
                </p>
            </section>

            <section>
                <h2><span class="num">7</span> Penyimpanan &amp; Keamanan</h2>
                <p>
                    Data disimpan pada peladen perusahaan dengan pembatasan akses berbasis login dan peran.
                    Kata sandi tidak pernah disimpan dalam bentuk aslinya, melainkan diacak satu arah sehingga
                    tidak dapat dibaca kembali oleh siapa pun, termasuk administrator.
                </p>
            </section>

            <section>
                <h2><span class="num">8</span> Dokumen &amp; Berkas Unggahan</h2>
                <p>
                    Surat keputusan, sertifikat, dan dokumen lain disimpan pada <strong>penyimpanan tertutup</strong>,
                    bukan pada folder publik. Berkas hanya dapat dibuka melalui aplikasi oleh pengguna yang sudah
                    masuk dan berhak &mdash; tidak dapat diakses melalui tautan langsung, sekalipun tautannya diketahui.
                </p>
            </section>

            <section>
                <h2><span class="num">9</span> Ekspor &amp; Cadangan Data</h2>
                <p>
                    Peran tertentu dapat mengekspor data ke berkas Excel atau PDF, dan Super Admin dapat membuat
                    serta mengunduh cadangan basis data yang berisi <strong>salinan lengkap seluruh data</strong>.
                    Pembuatan, pengunduhan, dan penghapusan berkas cadangan dicatat dalam log aktivitas.
                </p>
                <p>
                    Perlu dipahami: setelah sebuah berkas diunduh, <strong>isinya berada di luar kendali sistem</strong>.
                    Perlindungan selanjutnya menjadi tanggung jawab pihak yang mengunduh, sesuai ketentuan
                    kerahasiaan yang berlaku di perusahaan.
                </p>
            </section>

            <section>
                <h2><span class="num">10</span> Data Teknis, Log Aktivitas &amp; Status Daring</h2>
                <p>Untuk keamanan dan keperluan audit, sistem mencatat data teknis berikut:</p>
                <ul>
                    <li><strong>Sesi login:</strong> alamat IP, jenis peramban dan perangkat, serta waktu aktivitas terakhir.</li>
                    <li><strong>Log aktivitas:</strong> siapa melakukan apa, pada modul mana, kapan, dan dari alamat IP mana.</li>
                    <li><strong>Status daring:</strong> pada halaman Manajemen Akun, Super Admin dapat melihat akun mana yang sedang aktif, dihitung dari waktu permintaan terakhir ke sistem.</li>
                </ul>
                <p>
                    Catatan ini digunakan untuk menjaga keamanan dan menelusuri perubahan data, bukan untuk menilai
                    kinerja atau mengawasi kehadiran karyawan.
                </p>
            </section>

            <section>
                <h2><span class="num">11</span> Masa Penyimpanan (Retensi)</h2>
                <p>
                    Data pribadi disimpan selama masih diperlukan untuk tujuan pengelolaan talenta, yaitu selama
                    masa kerja karyawan dan jangka waktu tertentu setelahnya sesuai ketentuan internal dan peraturan
                    yang berlaku. Data riwayat jabatan dan penilaian sengaja dipertahankan karena menjadi dasar
                    penelusuran karier dan keperluan audit.
                </p>
            </section>

            <section>
                <h2><span class="num">12</span> Pembagian Data</h2>
                <p>
                    Data pribadi <strong>tidak dibagikan kepada pihak ketiga di luar perusahaan</strong>, kecuali
                    diwajibkan oleh peraturan perundang-undangan atau atas persetujuan yang bersangkutan.
                    SiMental tidak menggunakan layanan pelacak, iklan, maupun analitik pihak ketiga.
                </p>
            </section>

            <section>
                <h2><span class="num">13</span> Hak Karyawan atas Datanya</h2>
                <p>Sesuai UU PDP, karyawan berhak untuk:</p>
                <ul>
                    <li>Mengetahui dan mengakses data pribadinya yang tersimpan;</li>
                    <li>Meminta perbaikan atau pembaruan data yang tidak akurat;</li>
                    <li>Meminta penghapusan data sesuai ketentuan yang berlaku.</li>
                </ul>
                <p>
                    Hak pertama sudah dapat digunakan langsung: setiap karyawan yang memiliki akun dapat melihat
                    data penempatan dirinya melalui menu <strong>Profil Karyawan</strong>. Untuk perbaikan dan
                    penghapusan, permintaan diajukan melalui kontak pada bagian akhir kebijakan ini.
                </p>
            </section>

            <section>
                <h2><span class="num">14</span> Perubahan Kebijakan</h2>
                <p>
                    Kebijakan ini dapat diperbarui sewaktu-waktu. Versi terbaru berlaku sejak tanggal
                    pembaruan yang tercantum di atas.
                </p>
            </section>

            <section>
                <h2><span class="num">15</span> Kontak</h2>
                <p>
                    Untuk pertanyaan atau permintaan terkait data pribadi, silakan hubungi
                    <strong>Bagian SDM</strong> atau <strong>Administrator SiMental</strong> melalui kanal bantuan
                    yang tercantum pada halaman <strong>FAQ &amp; Panduan</strong> di dalam aplikasi.
                </p>
            </section>
        </div>

        <div class="doc-foot">
            <span class="copy">© {{ date('Y') }} SiMental — Sistem Manajemen Talenta</span>
            <a href="{{ url('/') }}" class="back">
                <svg viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Kembali
            </a>
        </div>
    </div>
</body>
</html>
