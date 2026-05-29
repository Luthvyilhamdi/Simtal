@extends('layouts.app')
@section('title', 'FAQ')
@section('breadcrumb', 'FAQ')

@push('styles')
<style>
    .page-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;gap:12px;flex-wrap:wrap; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:12px;color:#6b7280;margin-top:3px; }

    /* Search */
    .search-wrap { margin-bottom:20px; }
    .search-faq { display:flex;align-items:center;gap:10px;background:white;border:1.5px solid #e5e7eb;border-radius:12px;padding:12px 18px;max-width:560px;transition:border-color 0.15s; }
    .search-faq:focus-within { border-color:#15803d;box-shadow:0 0 0 3px rgba(21,128,61,0.08); }
    .search-faq svg { width:18px;height:18px;stroke:#9ca3af;fill:none;flex-shrink:0; }
    .search-faq input { border:none;outline:none;font-size:14px;font-family:inherit;color:#111827;background:transparent;width:100%; }
    .search-faq input::placeholder { color:#9ca3af; }

    /* Category tabs */
    .cat-tabs { display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px; }
    .cat-tab { padding:6px 14px;border-radius:20px;font-size:12px;font-weight:600;border:1.5px solid #e5e7eb;background:white;color:#6b7280;cursor:pointer;transition:all 0.12s; }
    .cat-tab:hover { border-color:#15803d;color:#15803d; }
    .cat-tab.active { background:#15803d;color:white;border-color:#15803d; }

    /* FAQ List */
    .faq-section { margin-bottom:24px; }
    .faq-section-title { font-size:13px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:12px;display:flex;align-items:center;gap:8px; }
    .faq-section-title::after { content:'';flex:1;height:1px;background:#f3f4f6; }

    .faq-grid { display:flex;flex-direction:column;gap:8px; }
    .faq-item { background:white;border-radius:12px;border:1px solid #e5e7eb;overflow:hidden;transition:box-shadow 0.15s; }
    .faq-item:hover { box-shadow:0 2px 10px rgba(0,0,0,0.06); }
    .faq-item.hidden { display:none; }

    .faq-question { display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 18px;cursor:pointer;user-select:none; }
    .faq-q-text { font-size:13px;font-weight:600;color:#111827;flex:1;line-height:1.4; }
    .faq-q-icon { width:24px;height:24px;border-radius:50%;background:#f3f4f6;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all 0.2s; }
    .faq-q-icon svg { width:12px;height:12px;stroke:#6b7280;fill:none;stroke-width:2.5;transition:transform 0.2s; }
    .faq-item.open .faq-q-icon { background:#dcfce7; }
    .faq-item.open .faq-q-icon svg { stroke:#15803d;transform:rotate(45deg); }

    .faq-answer { display:none;padding:0 18px 16px;font-size:13px;color:#6b7280;line-height:1.7;border-top:1px solid #f3f4f6;padding-top:12px; }
    .faq-item.open .faq-answer { display:block; }
    .faq-answer ul { margin:8px 0 0 16px; }
    .faq-answer ul li { margin-bottom:4px; }
    .faq-answer code { background:#f3f4f6;padding:1px 6px;border-radius:4px;font-size:12px;color:#15803d;font-family:monospace; }
    .faq-answer strong { color:#374151; }

    /* Contact */
    .contact-card { background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1px solid #bbf7d0;border-radius:14px;padding:24px;text-align:center;margin-top:24px; }
    .contact-card h3 { font-size:16px;font-weight:700;color:#15803d;margin-bottom:6px; }
    .contact-card p { font-size:13px;color:#6b7280;margin-bottom:14px; }
    .contact-btn { display:inline-flex;align-items:center;gap:6px;background:#15803d;color:white;padding:9px 20px;border-radius:9px;font-size:13px;font-weight:600;text-decoration:none; }
    .contact-btn:hover { background:#166534; }

    /* No result */
    .no-result { text-align:center;padding:48px 20px;display:none; }
    .no-result.show { display:block; }
    .no-result svg { width:48px;height:48px;stroke:#d1d5db;fill:none;margin:0 auto 12px;display:block; }
</style>
@endpush

@section('content')

<div class="page-header">
    <div>
        <div class="page-title">❓ FAQ — Pertanyaan Umum</div>
        <div class="page-sub">Panduan & pertanyaan yang sering ditanyakan seputar SIMTAL</div>
    </div>
</div>

{{-- Search --}}
<div class="search-wrap">
    <div class="search-faq">
        <svg viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" id="faqSearch" placeholder="Cari pertanyaan..." autocomplete="off">
    </div>
</div>

{{-- Category Tabs --}}
<div class="cat-tabs">
    <button class="cat-tab active" data-cat="all">Semua</button>
    <button class="cat-tab" data-cat="karyawan">👤 Karyawan</button>
    <button class="cat-tab" data-cat="assessment">📊 Assessment</button>
    <button class="cat-tab" data-cat="struktur">🏢 Struktur Org</button>
    <button class="cat-tab" data-cat="surat">📄 Surat</button>
    <button class="cat-tab" data-cat="akun">⚙️ Akun & Akses</button>
</div>

{{-- No Result --}}
<div class="no-result" id="noResult">
    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <p style="font-size:14px;font-weight:600;color:#6b7280;margin-bottom:4px;">Pertanyaan tidak ditemukan</p>
    <span style="font-size:12px;color:#9ca3af;">Coba kata kunci lain atau hubungi admin</span>
</div>

{{-- === KARYAWAN === --}}
<div class="faq-section" data-section="karyawan">
    <div class="faq-section-title">👤 Profil Karyawan</div>
    <div class="faq-grid">

        <div class="faq-item" data-cat="karyawan" data-q="bagaimana cara menambahkan karyawan baru">
            <div class="faq-question" onclick="toggleFaq(this)">
                <span class="faq-q-text">Bagaimana cara menambahkan data karyawan baru?</span>
                <div class="faq-q-icon"><svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>
            </div>
            <div class="faq-answer">
                Pergi ke menu <strong>Profil Karyawan</strong>, lalu klik tombol <strong>+ Tambah</strong> di pojok kanan atas. Isi semua data yang diperlukan seperti nama, NIK, jabatan, departemen, dan tanggal masuk, lalu klik <strong>Simpan</strong>.
            </div>
        </div>

        <div class="faq-item" data-cat="karyawan" data-q="import data karyawan excel massal">
            <div class="faq-question" onclick="toggleFaq(this)">
                <span class="faq-q-text">Apakah bisa import data karyawan secara massal via Excel?</span>
                <div class="faq-q-icon"><svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>
            </div>
            <div class="faq-answer">
                Ya, fitur <strong>Import Excel</strong> tersedia untuk Super Admin. Klik tombol <strong>Import</strong> di halaman Profil Karyawan, unduh template Excel yang disediakan, isi data, lalu upload kembali. Pastikan format kolom sesuai template agar tidak terjadi error.
            </div>
        </div>

        <div class="faq-item" data-cat="karyawan" data-q="cara mengedit mengubah data karyawan">
            <div class="faq-question" onclick="toggleFaq(this)">
                <span class="faq-q-text">Bagaimana cara mengubah data karyawan yang sudah ada?</span>
                <div class="faq-q-icon"><svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>
            </div>
            <div class="faq-answer">
                Di halaman Profil Karyawan, klik ikon ✏️ <strong>Edit</strong> pada baris karyawan yang ingin diubah. Ubah data yang diperlukan, lalu klik <strong>Simpan Perubahan</strong>.
            </div>
        </div>

        <div class="faq-item" data-cat="karyawan" data-q="hapus karyawan siapa yang bisa">
            <div class="faq-question" onclick="toggleFaq(this)">
                <span class="faq-q-text">Siapa yang bisa menghapus data karyawan?</span>
                <div class="faq-q-icon"><svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>
            </div>
            <div class="faq-answer">
                Hanya <strong>Super Admin</strong> yang dapat menghapus data karyawan. Administrator biasa hanya dapat melihat dan mengedit data. Tombol hapus tidak akan muncul jika Anda login sebagai Administrator.
            </div>
        </div>

        <div class="faq-item" data-cat="karyawan" data-q="cara mencari karyawan search">
            <div class="faq-question" onclick="toggleFaq(this)">
                <span class="faq-q-text">Bagaimana cara mencari karyawan dengan cepat?</span>
                <div class="faq-q-icon"><svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>
            </div>
            <div class="faq-answer">
                Gunakan kolom <strong>Search</strong> di pojok kanan atas halaman Profil Karyawan. Ketik nama atau NIK karyawan — hasil akan muncul otomatis tanpa perlu tekan Enter. Sistem mendukung pencarian real-time.
            </div>
        </div>

    </div>
</div>

{{-- === ASSESSMENT === --}}
<div class="faq-section" data-section="assessment">
    <div class="faq-section-title">📊 Assessment</div>
    <div class="faq-grid">

        <div class="faq-item" data-cat="assessment" data-q="bedanya assessment rekomendasi dan kompetensi">
            <div class="faq-question" onclick="toggleFaq(this)">
                <span class="faq-q-text">Apa bedanya Assessment Rekomendasi dan Assessment Kompetensi?</span>
                <div class="faq-q-icon"><svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>
            </div>
            <div class="faq-answer">
                <ul>
                    <li><strong>Assessment Rekomendasi</strong>: Menilai kesiapan karyawan untuk jabatan tertentu. Hasilnya berupa <em>Ready</em>, <em>Ready with Development</em>, atau <em>Not Ready</em>. Dilengkapi dengan tanggal IDP.</li>
                    <li><strong>Assessment Kompetensi</strong>: Menilai gap kompetensi karyawan. Hasilnya berupa <em>Qualified</em> atau <em>Not Qualified</em> berdasarkan jumlah under-competency dan under-qualification.</li>
                </ul>
            </div>
        </div>

        <div class="faq-item" data-cat="assessment" data-q="status idp expired kadaluarsa apa artinya">
            <div class="faq-question" onclick="toggleFaq(this)">
                <span class="faq-q-text">Apa arti status IDP "Expired"?</span>
                <div class="faq-q-icon"><svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>
            </div>
            <div class="faq-answer">
                Status <strong>Expired</strong> berarti tanggal berlaku IDP (Individual Development Plan) karyawan tersebut sudah melewati tanggal hari ini. Karyawan perlu melakukan assessment ulang atau perpanjangan IDP. Dashboard akan menampilkan peringatan untuk assessment yang akan expire dalam 30 hari ke depan.
            </div>
        </div>

        <div class="faq-item" data-cat="assessment" data-q="filter tahun rekomendasi assessment">
            <div class="faq-question" onclick="toggleFaq(this)">
                <span class="faq-q-text">Bagaimana cara memfilter data assessment berdasarkan tahun atau hasil?</span>
                <div class="faq-q-icon"><svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>
            </div>
            <div class="faq-answer">
                Di halaman <strong>History Assessment</strong> tab Rekomendasi, tersedia dua filter:
                <ul>
                    <li>Filter <strong>Rekomendasi</strong>: pilih Ready, Ready with Development, atau Not Ready</li>
                    <li>Filter <strong>Tahun</strong>: pilih tahun pelaksanaan assessment</li>
                </ul>
                Bisa juga menggunakan search real-time untuk mencari nama karyawan.
            </div>
        </div>

    </div>
</div>

{{-- === STRUKTUR ORGANISASI === --}}
<div class="faq-section" data-section="struktur">
    <div class="faq-section-title">🏢 Struktur Organisasi</div>
    <div class="faq-grid">

        <div class="faq-item" data-cat="struktur" data-q="cara assign karyawan ke posisi struktur organisasi">
            <div class="faq-question" onclick="toggleFaq(this)">
                <span class="faq-q-text">Bagaimana cara assign karyawan ke posisi di Struktur Organisasi?</span>
                <div class="faq-q-icon"><svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>
            </div>
            <div class="faq-answer">
                Di halaman <strong>Struktur Organisasi</strong>, klik ikon 👤+ pada posisi yang ingin diisi. Akan muncul modal pencarian karyawan — ketik nama atau NIK, pilih karyawan yang sesuai, lalu klik <strong>Simpan</strong>.
            </div>
        </div>

        <div class="faq-item" data-cat="struktur" data-q="menambah departemen bagian posisi baru">
            <div class="faq-question" onclick="toggleFaq(this)">
                <span class="faq-q-text">Bagaimana cara menambahkan Departemen, Bagian, atau Posisi baru?</span>
                <div class="faq-q-icon"><svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>
            </div>
            <div class="faq-answer">
                Pada baris Kompartemen atau Departemen di halaman Struktur Organisasi, tersedia tombol <strong>+ Departemen</strong>, <strong>+ Bagian</strong>, dan <strong>+ Staff</strong>. Klik tombol yang sesuai dan isi form yang muncul. Semua perubahan langsung tersimpan ke database.
            </div>
        </div>

        <div class="faq-item" data-cat="struktur" data-q="hapus posisi struktur siapa bisa">
            <div class="faq-question" onclick="toggleFaq(this)">
                <span class="faq-q-text">Siapa yang bisa menghapus posisi di Struktur Organisasi?</span>
                <div class="faq-q-icon"><svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>
            </div>
            <div class="faq-answer">
                Hanya <strong>Super Admin</strong> yang dapat menghapus posisi. Administrator biasa tidak akan melihat tombol hapus 🗑️ di halaman Struktur Organisasi.
            </div>
        </div>

        <div class="faq-item" data-cat="struktur" data-q="export struktur organisasi excel">
            <div class="faq-question" onclick="toggleFaq(this)">
                <span class="faq-q-text">Bagaimana cara export Struktur Organisasi ke Excel?</span>
                <div class="faq-q-icon"><svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>
            </div>
            <div class="faq-answer">
                Di halaman Struktur Organisasi, klik tombol <strong>Export Excel</strong> di pojok kanan atas. File Excel akan otomatis terunduh dengan format hierarki lengkap: Direktorat → Kompartemen → Departemen → Bagian → Fungsional → Posisi, beserta data karyawan yang mengisi setiap posisi.
            </div>
        </div>

        <div class="faq-item" data-cat="struktur" data-q="total mc tko tidak sama excel pedoman">
            <div class="faq-question" onclick="toggleFaq(this)">
                <span class="faq-q-text">Mengapa total MC/TKO di web berbeda dengan Excel Pedoman?</span>
                <div class="faq-q-icon"><svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>
            </div>
            <div class="faq-answer">
                Total MC/TKO dihitung dari baris posisi asli (bukan baris header placeholder). Pastikan data sudah diimport ulang menggunakan <strong>StrukturOrganisasiMei2026Seeder</strong> terbaru. Beberapa posisi khusus seperti Project Manager mungkin memiliki <code>mc_tko = 0</code> sesuai pedoman Excel.
            </div>
        </div>

    </div>
</div>

{{-- === SURAT PENTING === --}}
<div class="faq-section" data-section="surat">
    <div class="faq-section-title">📄 Surat Penting</div>
    <div class="faq-grid">

        <div class="faq-item" data-cat="surat" data-q="cara upload surat dokumen karyawan">
            <div class="faq-question" onclick="toggleFaq(this)">
                <span class="faq-q-text">Bagaimana cara mengupload surat atau dokumen karyawan?</span>
                <div class="faq-q-icon"><svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>
            </div>
            <div class="faq-answer">
                Di halaman <strong>Surat Penting</strong>, klik tombol <strong>Upload Surat</strong>. Isi detail surat seperti judul, kategori, nomor surat, tanggal, dan pilih karyawan yang bersangkutan. Upload file (PDF, JPG, PNG, DOCX) lalu klik Simpan.
            </div>
        </div>

        <div class="faq-item" data-cat="surat" data-q="format file yang diizinkan upload surat">
            <div class="faq-question" onclick="toggleFaq(this)">
                <span class="faq-q-text">Format file apa saja yang bisa diupload di Surat Penting?</span>
                <div class="faq-q-icon"><svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>
            </div>
            <div class="faq-answer">
                Sistem mendukung format: <code>PDF</code>, <code>JPG/JPEG</code>, <code>PNG</code>, dan <code>DOCX</code>. Ukuran file maksimal bergantung pada konfigurasi server. File PDF dan gambar bisa langsung di-preview dari halaman Surat Penting.
            </div>
        </div>

        <div class="faq-item" data-cat="surat" data-q="notifikasi expired surat dokumen">
            <div class="faq-question" onclick="toggleFaq(this)">
                <span class="faq-q-text">Bagaimana sistem memberi tahu jika surat akan expired?</span>
                <div class="faq-q-icon"><svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>
            </div>
            <div class="faq-answer">
                Surat yang akan expired dalam 30 hari ditandai dengan label <strong>⏰ SOON</strong> (kuning) di kartu surat. Surat yang sudah expired ditandai <strong>⚠ EXPIRED</strong> (merah). Statistik expired juga ditampilkan di bagian atas halaman Surat Penting.
            </div>
        </div>

    </div>
</div>

{{-- === AKUN === --}}
<div class="faq-section" data-section="akun">
    <div class="faq-section-title">⚙️ Akun & Akses</div>
    <div class="faq-grid">

        <div class="faq-item" data-cat="akun" data-q="perbedaan super admin dan administrator">
            <div class="faq-question" onclick="toggleFaq(this)">
                <span class="faq-q-text">Apa perbedaan Super Admin dan Administrator?</span>
                <div class="faq-q-icon"><svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>
            </div>
            <div class="faq-answer">
                <ul>
                    <li><strong>Super Admin ⭐</strong>: Akses penuh — dapat hapus data, import Excel, kelola master data (Jabatan, Direktorat, Job Grade, dll), kelola akun user, dan lihat Log Aktivitas.</li>
                    <li><strong>Administrator 🔵</strong>: Akses terbatas — dapat tambah, edit, dan lihat data, namun tidak bisa menghapus data atau mengakses Master Data.</li>
                </ul>
            </div>
        </div>

        <div class="faq-item" data-cat="akun" data-q="cara ganti password ubah profil">
            <div class="faq-question" onclick="toggleFaq(this)">
                <span class="faq-q-text">Bagaimana cara mengubah password atau profil akun?</span>
                <div class="faq-q-icon"><svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>
            </div>
            <div class="faq-answer">
                Klik avatar/nama Anda di pojok kanan atas → pilih <strong>Edit Profil</strong>. Di halaman profil, Anda dapat mengubah nama, email, dan password. Pastikan password baru minimal 8 karakter.
            </div>
        </div>

        <div class="faq-item" data-cat="akun" data-q="tambah user akun baru">
            <div class="faq-question" onclick="toggleFaq(this)">
                <span class="faq-q-text">Bagaimana cara menambahkan akun pengguna baru?</span>
                <div class="faq-q-icon"><svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>
            </div>
            <div class="faq-answer">
                Fitur ini hanya tersedia untuk <strong>Super Admin</strong>. Buka dropdown avatar → pilih <strong>Manajemen Akun</strong> → klik <strong>+ Tambah Akun</strong>. Isi nama, email, password, dan tentukan role (Super Admin atau Administrator).
            </div>
        </div>

        <div class="faq-item" data-cat="akun" data-q="lupa password reset">
            <div class="faq-question" onclick="toggleFaq(this)">
                <span class="faq-q-text">Bagaimana jika lupa password?</span>
                <div class="faq-q-icon"><svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>
            </div>
            <div class="faq-answer">
                Hubungi <strong>Super Admin</strong> sistem untuk mereset password Anda melalui halaman Manajemen Akun. Super Admin dapat mengubah password akun mana pun tanpa perlu mengetahui password lama.
            </div>
        </div>

    </div>
</div>

{{-- Contact --}}
<div class="contact-card">
    <h3>🙋 Masih ada pertanyaan?</h3>
    <p>Tidak menemukan jawaban yang Anda cari? Hubungi tim IT atau Super Admin SIMTAL.</p>
    <a href="mailto:admin@simtal.com" class="contact-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="width:14px;height:14px;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        Hubungi Admin
    </a>
</div>

@endsection

@push('scripts')
<script>
function toggleFaq(btn) {
    const item = btn.closest('.faq-item');
    const isOpen = item.classList.contains('open');
    // Tutup semua yang lain
    document.querySelectorAll('.faq-item.open').forEach(el => el.classList.remove('open'));
    if (!isOpen) item.classList.add('open');
}

// Category filter
document.querySelectorAll('.cat-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        const cat = this.dataset.cat;
        document.querySelectorAll('.faq-item').forEach(item => {
            if (cat === 'all' || item.dataset.cat === cat) item.classList.remove('hidden');
            else item.classList.add('hidden');
        });
        document.querySelectorAll('.faq-section').forEach(sec => {
            const visible = sec.querySelectorAll('.faq-item:not(.hidden)').length;
            sec.style.display = visible > 0 ? 'block' : 'none';
        });
        checkNoResult();
        // Reset search
        document.getElementById('faqSearch').value = '';
    });
});

// Search
document.getElementById('faqSearch').addEventListener('input', function() {
    const q = this.value.toLowerCase().trim();
    const activeCat = document.querySelector('.cat-tab.active').dataset.cat;

    document.querySelectorAll('.faq-item').forEach(item => {
        const text = (item.dataset.q || '') + ' ' + item.querySelector('.faq-q-text').textContent.toLowerCase() + ' ' + item.querySelector('.faq-answer').textContent.toLowerCase();
        const catMatch = activeCat === 'all' || item.dataset.cat === activeCat;
        const textMatch = !q || text.includes(q);
        item.classList.toggle('hidden', !(catMatch && textMatch));
    });

    document.querySelectorAll('.faq-section').forEach(sec => {
        const visible = sec.querySelectorAll('.faq-item:not(.hidden)').length;
        sec.style.display = visible > 0 ? 'block' : 'none';
    });
    checkNoResult();
});

function checkNoResult() {
    const total = document.querySelectorAll('.faq-item:not(.hidden)').length;
    document.getElementById('noResult').classList.toggle('show', total === 0);
}
</script>
@endpush