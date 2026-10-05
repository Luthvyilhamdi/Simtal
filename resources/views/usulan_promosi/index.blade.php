@extends('layouts.app')
@section('title', 'Usulan Promosi')
@section('breadcrumb-parent', 'Manajemen Talenta')
@section('breadcrumb', 'Usulan Promosi')

@push('styles')
<style>
* { box-sizing: border-box; }

/* ===== HEADER ===== */
.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    gap: 12px;
    flex-wrap: wrap;
}
.page-title { font-size: 18px; font-weight: 700; color: #111827; }
.page-sub { font-size: 12px; color: #6b7280; margin-top: 2px; }
.header-right { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #15803d;
    color: white;
    padding: 9px 16px;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: background .15s;
    white-space: nowrap;
    flex-shrink: 0;
}
.btn-primary:hover { background: #166534; }
.btn-primary svg { width: 13px; height: 13px; stroke: white; fill: none; stroke-width: 2.5; }

/* ===== SEARCH ===== */
.search-box {
    display: flex;
    align-items: center;
    gap: 8px;
    background: white;
    border: 1.5px solid #e5e7eb;
    border-radius: 9px;
    padding: 8px 12px;
    width: 212px;
    flex-shrink: 0;
    transition: border-color .15s;
}
.search-box:focus-within { border-color: #15803d; box-shadow: 0 0 0 2px rgba(21,128,61,.08); }
.search-box svg { width: 14px; height: 14px; stroke: #9ca3af; fill: none; flex-shrink: 0; }
.search-box input { border: none; outline: none; font-size: 13px; font-family: inherit; color: #111827; background: transparent; width: 100%; min-width: 0; }
.search-box input::placeholder { color: #9ca3af; }
.clear-btn { background: none; border: none; cursor: pointer; color: #d1d5db; font-size: 16px; padding: 0; display: none; flex-shrink: 0; }
.clear-btn.visible { display: block; }
.spin { display: none; width: 12px; height: 12px; border: 2px solid #e5e7eb; border-top-color: #15803d; border-radius: 50%; animation: rot .6s linear infinite; flex-shrink: 0; }
.spin.show { display: block; }
@keyframes rot { to { transform: rotate(360deg); } }

/* ===== CONTENT WRAPPER (AJAX search) ===== */
#upContent { transition: opacity .15s ease; }

/* Saringan rentang waktu: satu deret tombol, bukan dropdown — pilihannya
   cuma lima dan sering dipakai, jadi lebih cepat kalau langsung terlihat. */
/* Alat di kanan judul: pencarian, lalu saringan rentang waktu di kanannya. */
/* Alat duduk SEBARIS dengan ubin dan sejajar bagian bawahnya. Kalau layar
   kurang lebar, ia turun sendiri ke baris berikutnya — tetap rapat kanan. */
.flow-alat { display: flex; align-items: center; gap: 10px; margin-left: auto; align-self: flex-end; flex: 0 0 auto; flex-wrap: nowrap; }
.periode-sel { width: 146px; padding: 8px 12px; border: 1.5px solid #e5e7eb; border-radius: 9px; font-size: 13px; font-family: inherit; color: #111827; background: white; outline: none; cursor: pointer; }
.periode-sel:focus { border-color: #15803d; }

/* ===== ALUR USULAN (penyaring sekaligus peta tahapan) =====
   Dulu berupa stepper bercentang. Centangnya menyesatkan: yang ditandai
   "selesai" sebenarnya cuma tahap yang urutannya SEBELUM tab yang sedang
   dibuka — tidak ada hubungannya dengan kemajuan usulan mana pun.
   Sekarang tiap tahap jadi ubin: jumlahnya yang ditonjolkan, warnanya
   dipakai sebagai penanda tahap, dan yang sedang dipilih diberi warna. */
.flow-card { background: white; border: 1px solid var(--card-border); border-radius: var(--radius); box-shadow: var(--card-shadow); padding: 15px 18px 16px; margin-bottom: 20px; }
.flow-head { display: flex; align-items: flex-start; gap: 14px; flex-wrap: wrap; margin-bottom: 14px; }
.flow-judul { font-size: 13px; font-weight: 700; color: var(--text-strong); }
.flow-sub { font-size: 11.5px; color: #9ca3af; margin-top: 2px; }

.flow-pipa { display: flex; align-items: stretch; gap: 16px; flex-wrap: wrap; }
.pipa-grup { display: flex; flex-direction: column; gap: 7px; min-width: 0; flex-shrink: 1; }
.pipa-tag { font-size: 9.5px; font-weight: 800; color: #b0b7c3; text-transform: uppercase; letter-spacing: .7px; }
/* Tanpa geseran: begitu deret ubin meluap, batang gesernya menambah tinggi
   dan membuat alat di kanan tidak lagi sejajar dengan ubin. Kalau ruangnya
   kurang, biar .flow-alat saja yang turun ke barisnya sendiri. */
.pipa-isi { display: flex; align-items: center; gap: 7px; flex-wrap: nowrap; }
.pipa-pisah { width: 1px; align-self: stretch; background: #eef1f4; margin: 0 2px; }

/* Satu ubin tahap. --w = warna tahap, --wbg = latar saat dipilih. */
.tahap {
    --w: #6b7280; --wbg: #f3f4f6;
    display: flex; flex-direction: column; align-items: flex-start; gap: 1px;
    min-width: 84px; padding: 8px 11px; border: 1.5px solid #e9ecf0; border-radius: 11px;
    background: white; cursor: pointer; font-family: inherit; text-align: left;
    transition: border-color .13s, background .13s, box-shadow .13s;
}
.tahap:hover { border-color: #d7dce2; background: #fcfdfe; }
.tahap-num { font-size: 20px; font-weight: 800; line-height: 1.1; color: #475467; }
.tahap-lbl { font-size: 11px; font-weight: 600; color: #98a2b3; white-space: nowrap; }

.tahap.aktif { border-color: var(--w); background: var(--wbg); box-shadow: 0 0 0 3px rgba(16,24,40,.05); }
.tahap.aktif { box-shadow: 0 0 0 3px color-mix(in srgb, var(--w) 14%, transparent); }
.tahap.aktif .tahap-num { color: var(--w); }
.tahap.aktif .tahap-lbl { color: var(--w); font-weight: 700; }

/* Tahap tanpa isi tidak perlu menarik perhatian. */
.tahap.kosong .tahap-num { color: #d7dce2; }
.tahap.kosong .tahap-lbl { color: #c3c9d2; }
.tahap.kosong.aktif .tahap-num { color: var(--w); }
.tahap.kosong.aktif .tahap-lbl { color: var(--w); }

.tahap-panah { display: flex; align-items: center; color: #d7dce2; flex-shrink: 0; }
.tahap-panah svg { width: 12px; height: 12px; stroke: currentColor; fill: none; stroke-width: 2.2; }

@media (max-width: 760px) {
    .flow-pipa { flex-direction: column; gap: 13px; }
    .pipa-pisah { width: 100%; height: 1px; align-self: auto; margin: 0; }
    .pipa-isi { overflow-x: auto; flex-wrap: nowrap; padding-bottom: 2px; }
    .tahap { min-width: 88px; }
    .pipa-isi { overflow-x: auto; padding-bottom: 2px; }
    .flow-alat { flex: 0 0 100%; justify-content: flex-end; margin-top: 4px; }
    .flow-alat .search-box { flex: 1; min-width: 0; width: auto; }
    .periode-sel { width: 130px; }
}

/* ===== KARTU USULAN =====
   Dulu tabel selebar 1080px dengan dua kolom "Posisi Awal" & "Posisi Baru"
   berisi 6 baris label-nilai masing-masing. Yang dicari orang sebenarnya
   cuma APA YANG BERUBAH, jadi sekarang disusun sebagai perpindahan
   lama -> baru, dan unit yang tidak berubah diringkas satu baris. */
.table-card { background: white; border-radius: var(--radius); border: 1px solid var(--card-border); box-shadow: var(--card-shadow); overflow: hidden; }

.ucard { padding: 15px 18px; border-bottom: 1px solid #f1f3f5; transition: background .12s; }
.ucard:last-of-type { border-bottom: none; }
.ucard:hover { background: #fcfdfc; }

.uc-head { display: flex; align-items: center; gap: 11px; margin-bottom: 13px; }
.uc-id { flex: 1; min-width: 0; }
.uc-head .badge { flex-shrink: 0; }

/* Perpindahan jabatan: lama -> baru */
.uc-move { display: grid; grid-template-columns: 1fr 28px 1fr; gap: 10px; align-items: stretch; }
.uc-side { border-left: 3px solid #e5e7eb; padding-left: 11px; min-width: 0; }
.uc-side.baru { border-left-color: #15803d; }
.uc-cap { display: block; font-size: 9.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .6px; color: #9ca3af; margin-bottom: 4px; }
.uc-side.baru .uc-cap { color: #15803d; }
.uc-jab { display: block; font-size: 12.5px; color: #374151; line-height: 1.45; }
.uc-side.baru .uc-jab { color: #111827; font-weight: 600; }
.uc-master { display: block; font-size: 10px; color: #9ca3af; margin-top: 3px; }
.uc-arrow { display: flex; align-items: center; justify-content: center; }
.uc-arrow svg { width: 15px; height: 15px; stroke: #cbd5e1; fill: none; stroke-width: 2.2; }

/* Grade & unit */
.uc-fakta { display: flex; flex-wrap: wrap; gap: 7px; margin-top: 12px; }
.uc-chip { display: inline-flex; align-items: center; gap: 6px; background: #f8f9fa; border: 1px solid #eef0f2; border-radius: 9px; padding: 5px 11px; font-size: 11.5px; color: #9ca3af; font-weight: 600; }
.uc-chip b { font-weight: 700; color: #6b7280; }
.uc-chip b.naik { color: #15803d; }
.uc-ke { color: #cbd5e1; font-weight: 700; font-style: normal; }
.uc-chip.tetap b { font-weight: 600; color: #9ca3af; }

.uc-foot { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-top: 13px; padding-top: 11px; border-top: 1px dashed #eef0f2; }
.uc-meta { flex: 1; min-width: 0; font-size: 11.5px; color: #9ca3af; }
.uc-meta strong { color: #6b7280; font-weight: 600; }
.uc-act { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.uc-act .btn-soft { margin-top: 0; }

@media (max-width: 720px) {
    .uc-move { grid-template-columns: 1fr; gap: 8px; }
    .uc-arrow { justify-content: flex-start; padding-left: 4px; }
    .uc-arrow svg { transform: rotate(90deg); }
}

/* Avatar */
.av { width: 36px; height: 36px; border-radius: 50%; background: #f0fdf4; color: #15803d; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; flex-shrink: 0; border: 1.5px solid #bbf7d0; }
.td-nama { font-weight: 700; color: #111827; font-size: 13px; }
.td-nik { font-size: 11px; color: #9ca3af; margin-top: 2px; }

/* Badge */
.badge { display: inline-flex; align-items: center; padding: 4px 11px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap; }

/* Inline actions */
.btn-v { padding: 5px 10px; background: #f59e0b; color: white; border: none; border-radius: 7px; font-size: 11px; font-weight: 600; cursor: pointer; font-family: inherit; white-space: nowrap; }
.btn-v:hover { background: #d97706; }
.btn-soft {
    margin-top: 6px; padding: 5px 10px; background: white; color: #15803d; border: 1.5px solid #bbf7d0;
    border-radius: 7px; font-size: 11px; font-weight: 700; cursor: pointer; font-family: inherit;
    white-space: nowrap; display: inline-flex; align-items: center; gap: 5px;
}
.btn-soft:hover { background: #f0fdf4; }
.btn-soft svg { width: 11px; height: 11px; stroke: #15803d; fill: none; stroke-width: 2; flex-shrink: 0; }
.icon-row { display: flex; align-items: center; gap: 5px; }
.btn-ic { width: 28px; height: 28px; border-radius: 7px; border: 1px solid #e5e7eb; background: white; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all .12s; text-decoration: none; flex-shrink: 0; }
.btn-ic.v:hover { background: #eff6ff; border-color: #bfdbfe; }
.btn-ic.v svg { stroke: #3b82f6; }
.btn-ic.d:hover { background: #fef2f2; border-color: #fecaca; }
.btn-ic.d svg { stroke: #ef4444; }
.btn-ic svg { width: 13px; height: 13px; fill: none; stroke-width: 2; }

/* Empty */
.empty-wrap { text-align: center; padding: 56px 20px; }
.empty-ico { width: 52px; height: 52px; background: #f9fafb; border-radius: 14px; display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; border: 1px solid #e5e7eb; }
.empty-ico svg { width: 22px; height: 22px; stroke: #d1d5db; fill: none; stroke-width: 1.5; }
.empty-wrap h3 { font-size: 14px; font-weight: 700; color: #374151; margin-bottom: 5px; }
.empty-wrap p { font-size: 13px; color: #9ca3af; }

/* Pagination */
.pag-wrap { display: flex; align-items: center; justify-content: space-between; padding: 13px 18px; border-top: 1px solid #f3f4f6; background: #fafafa; font-size: 12px; color: #6b7280; flex-wrap: wrap; gap: 8px; }
.pag-btn { width: 30px; height: 30px; border-radius: 7px; border: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: center; text-decoration: none; color: #374151; background: white; font-size: 12px; transition: background .12s; cursor: pointer; }
.pag-btn.active { background: #15803d; border-color: #15803d; color: white; font-weight: 700; }
.pag-btn.disabled { opacity: .35; pointer-events: none; }
.pag-row { display: flex; align-items: center; gap: 3px; }

/* Highlight search */
mark { background: transparent; padding: 0; color: inherit; font-weight: 700; } /* hasil pencarian ditebalkan, tanpa blok warna */

/* Toast */
.toast-wrap { position: fixed; top: 20px; right: 20px; z-index: 9999; pointer-events: none; }
.toast { display: flex; align-items: center; gap: 10px; background: white; border: 1px solid #bbf7d0; border-left: 4px solid #16a34a; border-radius: 12px; padding: 14px 18px; box-shadow: 0 8px 32px rgba(0,0,0,.12); font-size: 13px; color: #15803d; font-weight: 500; min-width: 280px; position: relative; overflow: hidden; pointer-events: all; animation: tIn .3s forwards; }
.toast.hiding { animation: tOut .3s forwards; }
.toast-ic { width: 22px; height: 22px; background: #dcfce7; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.toast-ic svg { width: 11px; height: 11px; stroke: #16a34a; fill: none; stroke-width: 2.5; }
.toast-x { border: none; background: transparent; color: #9ca3af; cursor: pointer; font-size: 18px; padding: 0; margin-left: auto; }
.toast-bar { position: absolute; bottom: 0; left: 0; height: 3px; background: #16a34a; animation: tProg 3s linear forwards; }
@keyframes tIn { from{opacity:0;transform:translateX(110%)}to{opacity:1;transform:translateX(0)} }
@keyframes tOut { from{opacity:1}to{opacity:0;transform:translateX(110%)} }
@keyframes tProg { from{width:100%}to{width:0%} }

/* Modal */
.modal-bg { position: fixed; inset: 0; background: rgba(0,0,0,.5); backdrop-filter: blur(4px); z-index: 1000; display: none; align-items: center; justify-content: center; }
.modal-bg.show { display: flex; }
.modal-box { background: white; border-radius: 18px; padding: 30px; width: 100%; max-width: 380px; margin: 16px; box-shadow: 0 24px 64px rgba(0,0,0,.18); text-align: center; animation: mIn .25s cubic-bezier(.4,0,.2,1); }
.modal-ico { width: 56px; height: 56px; border-radius: 50%; background: #fef2f2; display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; }
.modal-ico svg { width: 26px; height: 26px; stroke: #ef4444; fill: none; stroke-width: 1.8; }
.modal-title { font-size: 17px; font-weight: 700; color: #111827; margin-bottom: 8px; }
.modal-desc { font-size: 13px; color: #6b7280; line-height: 1.6; margin-bottom: 22px; }
.modal-acts { display: flex; gap: 10px; }
.mbtn { flex: 1; padding: 11px; border-radius: 10px; font-size: 13px; font-weight: 600; font-family: inherit; cursor: pointer; border: none; }
.mbtn.c { background: #f9fafb; color: #374151; border: 1px solid #e5e7eb; }
.mbtn.c:hover { background: #f3f4f6; }
.mbtn.r { background: #ef4444; color: white; }
.mbtn.r:hover { background: #dc2626; }
@keyframes mIn { from{opacity:0;transform:scale(.92)}to{opacity:1;transform:scale(1)} }

/* ===== FORM TERBIT SK ===== */
.sk-lbl { display:block; font-size:11px; font-weight:700; color:#6b7280; margin-bottom:4px; }
.sk-req { color:#9ca3af; font-weight:700; }
.sk-note { font-size:11px; color:#9ca3af; margin:-2px 0 4px; }
.sk-inp { width:100%; border:1.5px solid #e5e7eb; border-radius:8px; padding:8px 10px; font-size:13px; font-family:inherit; color:#111827; outline:none; background:white; }
.sk-inp:focus { border-color:#16a34a; box-shadow:0 0 0 2px rgba(22,163,74,.08); }
.btn-sk { padding:6px 12px; background:#15803d; color:white; border:none; border-radius:7px; font-size:11px; font-weight:700; cursor:pointer; font-family:inherit; white-space:nowrap; display:inline-flex; align-items:center; gap:5px; }
.btn-sk:hover { background:#166534; }
.btn-sk svg { width:11px; height:11px; stroke:white; fill:none; stroke-width:2; }
.sk-done { display:inline-flex; align-items:center; gap:6px; background:#dcfce7; color:#15803d; border-radius:8px; padding:6px 9px; font-size:10px; font-weight:700; line-height:1.3; }
.sk-done svg { width:12px; height:12px; stroke:#15803d; fill:none; stroke-width:2.5; flex-shrink:0; }

@media (max-width: 480px) {
    .search-box { width: 100%; }
    .header-right { width: 100%; }
    .btn-primary { flex: 1; justify-content: center; }
    .page-header { flex-direction: column; align-items: flex-start; }
}
@media (max-width: 640px) {
    .flow-row { flex-wrap: nowrap; }
    .flow-tag { width: 70px; }
}
</style>
@endpush

@section('content')

@if(session('success'))
<div class="toast-wrap" id="twrap">
    <div class="toast" id="toast">
        <div class="toast-ic"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
        <span>{{ session('success') }}</span>
        <button class="toast-x" onclick="closeToast()">×</button>
        <div class="toast-bar"></div>
    </div>
</div>
@endif

<div class="modal-bg" id="mHapus">
    <div class="modal-box">
        <div class="modal-ico"><svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg></div>
        <div class="modal-title">Hapus Usulan?</div>
        <div class="modal-desc" id="mDesc">Data tidak dapat dikembalikan.</div>
        <div class="modal-acts">
            <button class="mbtn c" onclick="closeModal()">Batal</button>
            <button class="mbtn r" onclick="submitHapus()">Hapus</button>
        </div>
    </div>
</div>
<form id="fHapus" method="POST" style="display:none">@csrf @method('DELETE')</form>

{{-- MODAL TERBIT SK --}}
<div class="modal-bg" id="skModal">
    <div class="modal-box" style="max-width:480px;text-align:left">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px">
            <div style="width:42px;height:42px;border-radius:11px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;border:1px solid #bbf7d0">
                <svg viewBox="0 0 24 24" width="19" height="19" stroke="#15803d" fill="none" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="15" y2="17"/></svg>
            </div>
            <div>
                <div class="modal-title" style="margin:0">Terbitkan SK Promosi</div>
                <div style="font-size:12px;color:#9ca3af" id="skNama">—</div>
            </div>
        </div>
        <div class="sk-note">Kolom bertanda <span class="sk-req">*</span> wajib diisi.</div>

        <form id="skForm" method="POST">
            @csrf
            @method('PATCH')
            <div style="display:grid;gap:12px;margin-top:10px">
                <div>
                    <label class="sk-lbl">Nomor SK <span class="sk-req">*</span></label>
                    <input type="text" name="no_sk" id="skNoSk" class="sk-inp" placeholder="cth: 123/SK/DIR/2026" required>
                </div>
                <div>
                    <label class="sk-lbl">TMT — Tanggal Mulai Berlaku <span class="sk-req">*</span></label>
                    <input type="date" name="tmt" id="skTmt" class="sk-inp" required>
                </div>
                <div>
                    <label class="sk-lbl">Jabatan Tujuan</label>
                    <input type="text" id="skJabatanInfo" class="sk-inp" style="background:#f9fafb" readonly>
                    <div style="font-size:11px;color:#9ca3af;margin-top:4px">Diambil dari usulan — dipakai otomatis saat SK terbit.</div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                    <div>
                        <label class="sk-lbl">Job Grade <span class="sk-req">*</span></label>
                        <select name="job_grade_id" id="skJg" class="sk-inp select-search" required>
                            <option value="">— JG —</option>
                            @foreach($jobGrades as $jg)
                            <option value="{{ $jg->id }}" data-val="{{ $jg->job_grade }}">JG {{ $jg->job_grade }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="sk-lbl">Person Grade <span class="sk-req">*</span></label>
                        <select name="person_grade_id" id="skPg" class="sk-inp select-search" required>
                            <option value="">— PG —</option>
                            @foreach($personGrades as $pg)
                            <option value="{{ $pg->id }}" data-val="{{ $pg->person_grade }}">PG {{ $pg->person_grade }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="sk-lbl">Kode Struktur <span class="sk-req">*</span></label>
                    <select name="kode_struktur_id" id="skKode" class="sk-inp select-search" required>
                        <option value="">— Pilih Kode Struktur —</option>
                        @foreach($kodeStrukturs as $ks)
                        <option value="{{ $ks->id }}">{{ $ks->nama ?? $ks->kode_struktur ?? $ks->kode ?? ('#'.$ks->id) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="sk-lbl">Direktorat <span class="sk-req">*</span></label>
                    <select name="direktorat_id" id="skDir" class="sk-inp select-search" required>
                        <option value="">— Pilih Direktorat —</option>
                        @foreach($direktorats as $dr)
                        <option value="{{ $dr->id }}">{{ $dr->nama_direktorat ?? $dr->nama ?? ('#'.$dr->id) }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                    <div>
                        <label class="sk-lbl">Kompartemen <span class="sk-req">*</span></label>
                        <select name="kompartemen_id" id="skKomp" class="sk-inp select-search" required>
                            <option value="">— Pilih —</option>
                            @foreach($kompartemens as $kp)
                            <option value="{{ $kp->id }}">{{ $kp->nama_kompartemen ?? ('#'.$kp->id) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="sk-lbl">Departemen <span class="sk-req">*</span></label>
                        <select name="departemen_id" id="skDept" class="sk-inp select-search" required>
                            <option value="">— Pilih —</option>
                            @foreach($departemens as $dp)
                            <option value="{{ $dp->id }}">{{ $dp->nama_departemen ?? ('#'.$dp->id) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="sk-lbl">Keterangan (opsional)</label>
                    <input type="text" name="keterangan" class="sk-inp" placeholder="Catatan tambahan...">
                </div>
                <div style="display:flex;gap:8px;font-size:11px;color:#6b7280;background:#fafafa;border:1px solid #f3f4f6;border-radius:8px;padding:9px 11px;line-height:1.5">
                    <svg viewBox="0 0 24 24" width="14" height="14" stroke="#9ca3af" fill="none" stroke-width="2" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    <div>Jabatan tujuan diambil dari usulan. Menyimpan akan otomatis membuat <strong>history jabatan baru</strong> (tipe: promosi) &amp; memperbarui <strong>posisi terkini karyawan</strong>. Jika jabatan tujuan termasuk tingkat pejabat (SVP/VP/SPM/PM), otomatis tercatat di Pejabat Definitif.</div>
                </div>
            </div>
            <div class="modal-acts" style="margin-top:18px">
                <button type="button" class="mbtn c" onclick="closeSk()">Batal</button>
                <button type="submit" class="mbtn" style="background:#15803d;color:white">Terbitkan SK</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL TINDAK LANJUT (verif berkas) --}}
<div class="modal-bg" id="tlModal">
    <div class="modal-box" style="max-width:380px;text-align:left">
        <div class="modal-title" style="margin-bottom:2px">Tindak Lanjut Verifikasi</div>
        <div style="font-size:12px;color:#9ca3af;margin-bottom:16px" id="tlNama">—</div>
        <form id="tlForm" method="POST">
            @csrf @method('PATCH')
            <input type="hidden" name="status" id="tlStatus" value="verif_berkas">
            <div style="display:grid;gap:12px">
                <div>
                    <label class="sk-lbl">Tindak Lanjut</label>
                    <select id="tlSelect" name="tindak_lanjut" class="sk-inp select-search" onchange="onTlChange(this.value)">
                        <option value="">— Pilih —</option>
                        <option value="sidang">Lanjut Sidang</option>
                        <option value="ditolak">Ditolak</option>
                    </select>
                </div>
                <div id="tlDateWrap" style="display:none">
                    <label class="sk-lbl">Tanggal Sidang</label>
                    <input type="date" name="tanggal_sidang" id="tlTanggal" class="sk-inp">
                </div>
            </div>
            <div class="modal-acts" style="margin-top:18px">
                <button type="button" class="mbtn c" onclick="closeTl()">Batal</button>
                <button type="submit" class="mbtn" style="background:#15803d;color:white">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL HASIL SIDANG --}}
<div class="modal-bg" id="hsModal">
    <div class="modal-box" style="max-width:380px;text-align:left">
        <div class="modal-title" style="margin-bottom:2px">Hasil Sidang</div>
        <div style="font-size:12px;color:#9ca3af;margin-bottom:16px" id="hsNama">—</div>
        <form id="hsForm" method="POST">
            @csrf @method('PATCH')
            <input type="hidden" name="tindak_lanjut" id="hsTindakLanjut">
            <input type="hidden" name="tanggal_sidang" id="hsTanggalSidang">
            <input type="hidden" name="status" id="hsStatus">
            <div>
                <label class="sk-lbl">Hasil Sidang</label>
                <select id="hsSelect" name="hasil_sidang" class="sk-inp select-search" onchange="onHsChange(this.value)">
                    <option value="">— Pilih —</option>
                    <option value="lulus">Lulus</option>
                    <option value="tidak_lulus">Tidak Lulus</option>
                    <option value="tanpa_sidang">Tanpa Sidang</option>
                </select>
            </div>
            <div class="modal-acts" style="margin-top:18px">
                <button type="button" class="mbtn c" onclick="closeHs()">Batal</button>
                <button type="submit" class="mbtn" style="background:#15803d;color:white">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- DEFINISI ARRAY (dipakai workflow bar & panel) --}}
@php
$tabs = [
    'draft'        => 'Draft',
    'verif_berkas' => 'Verifikasi Berkas',
    'sidang'       => 'Sidang',
    'lulus'        => 'Lulus',
    'tidak_lulus'  => 'Tidak Lulus',
    'tanpa_sidang' => 'Tanpa Sidang',
    'ditolak'      => 'Ditolak',
];
$bc = [
    'draft'       =>['#f3f4f6','#374151'],'verif_berkas'=>['#fef3c7','#d97706'],
    'sidang'      =>['#dbeafe','#1d4ed8'],'lulus'       =>['#dcfce7','#15803d'],
    'tidak_lulus' =>['#fee2e2','#dc2626'],'tanpa_sidang'=>['#f5f3ff','#7c3aed'],
    'ditolak'     =>['#fce7f3','#be185d'],
];

// Tahapan proses utama (linear): Draft -> Verifikasi Berkas -> Sidang
$steps = ['draft' => 'Draft', 'verif_berkas' => 'Verifikasi Berkas', 'sidang' => 'Sidang'];
// Hasil akhir (cabang setelah sidang) — warnanya sama dengan $bc supaya konsisten
$outcomes = [
    'lulus'        => ['Lulus',        '#15803d', '#dcfce7'],
    'tanpa_sidang' => ['Tanpa Sidang',  '#7c3aed', '#f5f3ff'],
    'tidak_lulus'  => ['Tidak Lulus',   '#dc2626', '#fee2e2'],
    'ditolak'      => ['Ditolak',       '#be185d', '#fce7f3'],
];
@endphp

{{-- HEADER --}}
<div class="page-header">
    <div>
        <div class="page-title">Usulan Promosi</div>
        <div class="page-sub">Kelola proses promosi karyawan dari pengajuan hingga penerbitan SK</div>
    </div>
    <div class="header-right">
        <a href="{{ route('usulan_promosi.create') }}" class="btn-primary">
            <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Buat Usulan
        </a>
    </div>
</div>

{{-- ALUR USULAN: peta tahapan sekaligus penyaring daftar di bawah.
     Tujuh kartu statistik yang dulu ada di sini dihapus — angkanya persis
     sama dengan angka di ubin ini, dan yang di sana tidak bisa diklik. --}}
@php
// Warna per tahap: [warna teks/garis, latar saat dipilih]
$warnaTahap = [
    'draft'        => ['#6b7280', '#f3f4f6'],
    'verif_berkas' => ['#d97706', '#fef3c7'],
    'sidang'       => ['#1d4ed8', '#dbeafe'],
];
@endphp
<div class="flow-card">

    <div class="flow-head">
        <div>
            <div class="flow-judul">Alur Usulan</div>
            <div class="flow-sub">Klik salah satu tahap untuk melihat daftarnya</div>
        </div>
    </div>

    <div class="flow-pipa">

        <div class="pipa-grup">
            <span class="pipa-tag">Sedang berjalan</span>
            <div class="pipa-isi">
                @foreach($steps as $k => $label)
                @php $w = $warnaTahap[$k] ?? ['#6b7280', '#f3f4f6']; @endphp
                <button type="button"
                        class="tahap {{ $activeTab === $k ? 'aktif' : '' }} {{ $counts[$k] ? '' : 'kosong' }}"
                        style="--w:{{ $w[0] }};--wbg:{{ $w[1] }}"
                        onclick="switchTab('{{ $k }}',this)" data-tabkey="{{ $k }}"
                        aria-pressed="{{ $activeTab === $k ? 'true' : 'false' }}">
                    <span class="tahap-num step-count">{{ $counts[$k] }}</span>
                    <span class="tahap-lbl">{{ $label }}</span>
                </button>
                @if(!$loop->last)
                <span class="tahap-panah" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><line x1="4" y1="12" x2="18" y2="12"/><polyline points="12 6 18 12 12 18"/></svg>
                </span>
                @endif
                @endforeach
            </div>
        </div>

        <span class="pipa-pisah" aria-hidden="true"></span>

        <div class="pipa-grup">
            <span class="pipa-tag">Hasil akhir</span>
            <div class="pipa-isi">
                @foreach($outcomes as $k => $o)
                <button type="button"
                        class="tahap {{ $activeTab === $k ? 'aktif' : '' }} {{ $counts[$k] ? '' : 'kosong' }}"
                        style="--w:{{ $o[1] }};--wbg:{{ $o[2] }}"
                        onclick="switchTab('{{ $k }}',this)" data-tabkey="{{ $k }}"
                        aria-pressed="{{ $activeTab === $k ? 'true' : 'false' }}">
                    <span class="tahap-num outcome-count">{{ $counts[$k] }}</span>
                    <span class="tahap-lbl">{{ $o[0] }}</span>
                </button>
                @endforeach
            </div>
        </div>

        <div class="flow-alat">
            <div class="search-box">
                <svg viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="sInput" value="{{ request('search') }}" placeholder="Cari nama / NIK..." autocomplete="off">
                <div class="spin" id="spin"></div>
                <button class="clear-btn {{ request('search') ? 'visible':'' }}" id="clrBtn" onclick="clearSearch()">×</button>
            </div>
            <select class="periode-sel select-search" aria-label="Rentang waktu usulan"
                    onchange="window.location.href = urlPeriode(this.value)">
                @foreach(\App\Support\PeriodeUsulan::OPSI as $nilai => $label)
                <option value="{{ $nilai }}" {{ $periode === (string) $nilai ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

    </div>
</div>

{{-- ===== KONTEN YANG DI-UPDATE SAAT SEARCH (panel saja) ===== --}}
<div id="upContent">
<div id="countData" data-json='@json($counts)' hidden></div>

{{-- PANELS --}}
@foreach($tabs as $tabKey => $tabLabel)
<div id="p-{{ $tabKey }}" style="{{ $activeTab===$tabKey?'':'display:none' }}">
<div class="table-card">
    @php $d = $statusGroups[$tabKey]; @endphp
    @if($d->total() > 0)
    <div class="u-list">
        @foreach($d as $u)
        @php
            $cl = $bc[$u->status] ?? ['#f3f4f6','#374151'];

            // Unit tujuan boleh kosong — artinya unit itu tidak ikut berpindah,
            // jadi dibandingkan dengan unit sekarang untuk tahu mana yang berubah.
            $dirLama  = optional($u->karyawan->direktorat)->nama_direktorat ?? optional($u->karyawan->direktorat)->nama ?? '-';
            $dirBaru  = optional($u->direktoratTujuan)->nama_direktorat ?? optional($u->direktoratTujuan)->nama ?? $dirLama;
            $kompLama = $u->kompartemen_saat_ini ?? optional($u->karyawan->kompartemen)->nama_kompartemen ?? '-';
            $kompBaru = optional($u->kompartemenTujuan)->nama_kompartemen ?? $kompLama;
            $deptLama = $u->departemen_saat_ini ?? optional($u->karyawan->departemen)->nama_departemen ?? '-';
            $deptBaru = optional($u->departemenTujuan)->nama_departemen ?? $deptLama;
        @endphp
        <article class="ucard">

            <div class="uc-head">
                <div class="av">{{ initials($u->karyawan->nama) }}</div>
                <div class="uc-id">
                    <div class="td-nama">{{ $u->karyawan->nama ?? '-' }}</div>
                    <div class="td-nik">NIK {{ $u->karyawan->nik ?? '-' }}</div>
                </div>
                <span class="badge" style="background:{{ $cl[0] }};color:{{ $cl[1] }}">{{ $u->status_label }}</span>
                <div class="icon-row">
                    <a href="{{ route('usulan_promosi.show',$u) }}" class="btn-ic v" title="Detail">
                        <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </a>
                    <button type="button" class="btn-ic d" title="Hapus"
                        data-url="{{ route('usulan_promosi.destroy',$u) }}"
                        data-nama="{{ addslashes($u->karyawan->nama??'') }}">
                        <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                    </button>
                </div>
            </div>

            {{-- Perpindahan jabatan --}}
            <div class="uc-move">
                <div class="uc-side">
                    <span class="uc-cap">Jabatan sekarang</span>
                    <span class="uc-jab">{{ $u->jabatan_saat_ini ?? '-' }}</span>
                </div>
                <div class="uc-arrow">
                    <svg viewBox="0 0 24 24"><line x1="4" y1="12" x2="18" y2="12"/><polyline points="12 6 18 12 12 18"/></svg>
                </div>
                <div class="uc-side baru">
                    <span class="uc-cap">Diusulkan menjadi</span>
                    <span class="uc-jab">{{ $u->jabatan_tujuan }}</span>
                    @if(optional($u->jabatanTujuan)->nama_jabatan)
                        <span class="uc-master">Jabatan: {{ $u->jabatanTujuan->nama_jabatan }}</span>
                    @endif
                </div>
            </div>

            {{-- Grade & unit: hanya yang berubah yang ditulis lama -> baru --}}
            <div class="uc-fakta">
                <span class="uc-chip">Job Grade <b>JG {{ $u->job_grade_saat_ini ?? '-' }}</b><i class="uc-ke">→</i><b class="naik">JG {{ $u->job_grade_promosi ?? '-' }}</b></span>
                <span class="uc-chip">Person Grade <b>PG {{ $u->person_grade_saat_ini ?? '-' }}</b><i class="uc-ke">→</i><b class="naik">PG {{ $u->person_grade_promosi ?? '-' }}</b></span>

                {{-- Direktorat hanya ditulis bila berpindah. Kompartemen &
                     departemen selalu ditulis supaya penempatan unitnya
                     terbaca tanpa harus membuka detail. --}}
                @if($dirLama !== $dirBaru)
                    <span class="uc-chip">Direktorat <b>{{ $dirLama }}</b><i class="uc-ke">→</i><b class="naik">{{ $dirBaru }}</b></span>
                @endif
                @foreach([['Kompartemen', $kompLama, $kompBaru], ['Departemen', $deptLama, $deptBaru]] as [$labelUnit, $unitLama, $unitBaru])
                    @if($unitLama !== $unitBaru)
                        <span class="uc-chip">{{ $labelUnit }} <b>{{ $unitLama }}</b><i class="uc-ke">→</i><b class="naik">{{ $unitBaru }}</b></span>
                    @else
                        <span class="uc-chip tetap">{{ $labelUnit }} <b>{{ $unitLama }}</b></span>
                    @endif
                @endforeach
            </div>

            <div class="uc-foot">
                <div class="uc-meta">
                    Dibuat oleh <strong>{{ $u->createdBy->name ?? '-' }}</strong> · {{ $u->created_at->format('d M Y') }}
                </div>
                <div class="uc-act">

                    @if($tabKey==='draft')
                        <form method="POST" action="{{ route('usulan_promosi.update_status',$u) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="verif_berkas">
                            <button type="submit" class="btn-v">Verif →</button>
                        </form>
                    @endif

                    @if($tabKey==='verif_berkas')
                        @if($u->tindak_lanjut==='sidang')
                            <span class="badge" style="background:#dbeafe;color:#1d4ed8">Lanjut Sidang{{ $u->tanggal_sidang ? ' · '.$u->tanggal_sidang->format('d M Y') : '' }}</span>
                        @elseif($u->tindak_lanjut==='ditolak')
                            <span class="badge" style="background:#fee2e2;color:#dc2626">Ditolak</span>
                        @else
                            <span class="badge" style="background:#f3f4f6;color:#6b7280">Belum diproses</span>
                        @endif
                        <button type="button" class="btn-soft"
                            data-url="{{ route('usulan_promosi.update_status',$u) }}"
                            data-nama="{{ $u->karyawan->nama }}"
                            data-tl="{{ $u->tindak_lanjut }}"
                            data-tgl="{{ $u->tanggal_sidang?->format('Y-m-d') }}"
                            onclick="openTl(this)">
                            <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            Tindak Lanjut
                        </button>
                    @endif

                    @if($tabKey==='sidang')
                        @if($u->hasil_sidang==='lulus')
                            <span class="badge" style="background:#dcfce7;color:#15803d">Lulus</span>
                        @elseif($u->hasil_sidang==='tidak_lulus')
                            <span class="badge" style="background:#fee2e2;color:#dc2626">Tidak Lulus</span>
                        @elseif($u->hasil_sidang==='tanpa_sidang')
                            <span class="badge" style="background:#f5f3ff;color:#7c3aed">Tanpa Sidang</span>
                        @else
                            <span class="badge" style="background:#f3f4f6;color:#6b7280">Belum diproses</span>
                        @endif
                        <button type="button" class="btn-soft"
                            data-url="{{ route('usulan_promosi.update_status',$u) }}"
                            data-nama="{{ $u->karyawan->nama }}"
                            data-tl="{{ $u->tindak_lanjut }}"
                            data-tgl="{{ $u->tanggal_sidang?->format('Y-m-d') }}"
                            data-hs="{{ $u->hasil_sidang }}"
                            data-status="{{ $u->status }}"
                            onclick="openHs(this)">
                            <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            Hasil Sidang
                        </button>
                    @endif

                    @if($tabKey==='lulus' || $tabKey==='tanpa_sidang')
                        @if($u->sk_diproses)
                            <span class="sk-done" title="SK sudah diterbitkan">
                                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                SK {{ $u->no_sk }} · TMT {{ \Carbon\Carbon::parse($u->tmt)->format('d M Y') }}
                            </span>
                        @else
                            <button type="button" class="btn-sk"
                                data-url="{{ route('usulan_promosi.terbitkan_sk', $u) }}"
                                data-nama="{{ $u->karyawan->nama }}"
                                data-jab="{{ $u->jabatan_tujuan }}"
                                data-jg="{{ $u->job_grade_promosi }}"
                                data-pg="{{ $u->person_grade_promosi }}"
                                data-dir="{{ $u->direktorat_tujuan_id ?? $u->karyawan->direktorat_id }}"
                                data-komp="{{ $u->kompartemen_tujuan_id ?? $u->karyawan->kompartemen_id }}"
                                data-dept="{{ $u->departemen_tujuan_id ?? $u->karyawan->departemen_id }}"
                                onclick="openSk(this)">
                                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                Terbit SK
                            </button>
                        @endif
                    @endif

                </div>
            </div>
        </article>
        @endforeach
    </div>

    {{-- PAGINATION --}}
    <div class="pag-wrap">
        <span>Tampil <strong style="color:#374151">{{ $d->firstItem() }}–{{ $d->lastItem() }}</strong> dari <strong style="color:#374151">{{ $d->total() }}</strong></span>
        @if($d->hasPages())
        <div class="pag-row">
            <a href="{{ $d->onFirstPage() ? '#' : $d->previousPageUrl().'&tab='.$tabKey }}" class="pag-btn {{ $d->onFirstPage()?'disabled':'' }}">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            </a>
            @php $cur=$d->currentPage();$last=$d->lastPage();$s=max(1,$cur-2);$e=min($last,$cur+2); @endphp
            @if($s > 1)
                <a href="{{ $d->url(1) }}&tab={{ $tabKey }}" class="pag-btn">1</a>
                @if($s > 2)<span style="padding:0 2px;color:#9ca3af">…</span>@endif
            @endif
            @for($pg=$s;$pg<=$e;$pg++)
                <a href="{{ $d->url($pg) }}&tab={{ $tabKey }}" class="pag-btn {{ $pg===$cur?'active':'' }}">{{ $pg }}</a>
            @endfor
            @if($e < $last)
                @if($e < $last-1)<span style="padding:0 2px;color:#9ca3af">…</span>@endif
                <a href="{{ $d->url($last) }}&tab={{ $tabKey }}" class="pag-btn">{{ $last }}</a>
            @endif
            <a href="{{ $d->hasMorePages() ? $d->nextPageUrl().'&tab='.$tabKey : '#' }}" class="pag-btn {{ $d->hasMorePages()?'':'disabled' }}">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        </div>
        @endif
    </div>

    @else
    <div class="empty-wrap">
        <div class="empty-ico"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
        <h3>Tidak ada data {{ $tabLabel }}</h3>
        <p>Belum ada usulan promosi dengan status ini</p>
    </div>
    @endif
</div>
</div>
@endforeach

</div>{{-- /#upContent --}}

@endsection

@push('scripts')
<script>
// Toast
function closeToast() {
    const t = document.getElementById('toast');
    if(!t) return;
    t.classList.add('hiding');
    setTimeout(() => document.getElementById('twrap')?.remove(), 300);
}
window.addEventListener('DOMContentLoaded', () => {
    if(document.getElementById('toast')) setTimeout(closeToast, 3000);
});

// Ubin tahap & hasil akhir dipakai dengan cara yang sama: satu yang aktif.
// Warnanya datang dari CSS (--w / --wbg) yang dipasang di markup, jadi di sini
// cukup menukar satu kelas — tidak ada lagi gaya yang ditulis dari JS.
function switchTab(tab) {
    document.querySelectorAll('[id^="p-"]').forEach(p => p.style.display = 'none');
    const panel = document.getElementById('p-' + tab);
    if (panel) panel.style.display = '';

    document.querySelectorAll('.tahap[data-tabkey]').forEach(b => {
        const aktif = b.dataset.tabkey === tab;
        b.classList.toggle('aktif', aktif);
        b.setAttribute('aria-pressed', aktif ? 'true' : 'false');
    });

    const url = new URL(window.location.href);
    url.searchParams.set('tab', tab);
    window.history.pushState({}, '', url.toString());
}

// ===== MODAL TINDAK LANJUT (verif berkas) =====
function openTl(btn) {
    const d = btn.dataset;
    document.getElementById('tlForm').action = d.url;
    document.getElementById('tlNama').textContent = d.nama || '';
    document.getElementById('tlSelect').value = d.tl || '';
    document.getElementById('tlTanggal').value = d.tgl || '';
    onTlChange(d.tl || '');
    document.getElementById('tlModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}
function onTlChange(val) {
    document.getElementById('tlStatus').value = val === 'sidang' ? 'sidang' : (val === 'ditolak' ? 'ditolak' : 'verif_berkas');
    document.getElementById('tlDateWrap').style.display = val === 'sidang' ? 'block' : 'none';
}
function closeTl() {
    document.getElementById('tlModal').classList.remove('show');
    document.body.style.overflow = '';
}
document.getElementById('tlModal').addEventListener('click', e => { if(e.target===document.getElementById('tlModal')) closeTl(); });

// ===== MODAL HASIL SIDANG =====
function openHs(btn) {
    const d = btn.dataset;
    document.getElementById('hsForm').action = d.url;
    document.getElementById('hsNama').textContent = d.nama || '';
    document.getElementById('hsSelect').value = d.hs || '';
    document.getElementById('hsTindakLanjut').value = d.tl || '';
    document.getElementById('hsTanggalSidang').value = d.tgl || '';
    document.getElementById('hsStatus').value = d.status || 'sidang';
    onHsChange(d.hs || '');
    document.getElementById('hsModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}
function onHsChange(val) {
    const m = {lulus:'lulus',tidak_lulus:'tidak_lulus',tanpa_sidang:'lulus'};
    document.getElementById('hsStatus').value = m[val] ?? 'sidang';
}
function closeHs() {
    document.getElementById('hsModal').classList.remove('show');
    document.body.style.overflow = '';
}
document.getElementById('hsModal').addEventListener('click', e => { if(e.target===document.getElementById('hsModal')) closeHs(); });

// Modal
let dUrl = '';
function openModal(url, nama) {
    dUrl = url;
    document.getElementById('mDesc').innerHTML = 'Hapus usulan promosi <strong>'+nama+'</strong>?<br>Data tidak dapat dikembalikan.';
    document.getElementById('mHapus').classList.add('show');
    document.body.style.overflow = 'hidden';
}
function closeModal() {
    document.getElementById('mHapus').classList.remove('show');
    document.body.style.overflow = '';
}
function submitHapus() {
    document.getElementById('fHapus').action = dUrl;
    document.getElementById('fHapus').submit();
}
document.getElementById('mHapus').addEventListener('click', e => { if(e.target===document.getElementById('mHapus')) closeModal(); });
document.addEventListener('keydown', e => { if(e.key==='Escape') { closeModal(); closeSk(); closeTl(); closeHs(); } });

// Delegasi klik tombol hapus (data-* attributes, hindari onclick inline berisi route())
document.addEventListener('click', function(e) {
    const delBtn = e.target.closest('.btn-ic.d');
    if (delBtn) openModal(delBtn.dataset.url, delBtn.dataset.nama);
});

// ===== MODAL TERBIT SK =====
function openSk(btn) {
    const d = btn.dataset;
    document.getElementById('skForm').action = d.url;
    document.getElementById('skNama').textContent = d.nama || '';
    document.getElementById('skNoSk').value = '';
    document.getElementById('skTmt').value = '';
    document.getElementById('skKode').value = '';
    // jabatan tujuan tampil read-only; dipakai otomatis dari usulan saat terbit SK
    document.getElementById('skJabatanInfo').value = d.jab || '';
    preselectSk('skJg', 'data-val', d.jg);
    preselectSk('skPg', 'data-val', d.pg);
    // unit langsung pakai ID (default = unit tujuan usulan)
    document.getElementById('skDir').value  = d.dir  || '';
    document.getElementById('skKomp').value = d.komp || '';
    document.getElementById('skDept').value = d.dept || '';
    document.getElementById('skModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}
function preselectSk(selId, attr, val) {
    const sel = document.getElementById(selId);
    sel.value = '';
    if (!val) return;
    const target = ('' + val).trim().toLowerCase();
    for (const opt of sel.options) {
        const a = (opt.getAttribute(attr) || '').trim().toLowerCase();
        if (a && a === target) { sel.value = opt.value; break; }
    }
}
function closeSk() {
    document.getElementById('skModal').classList.remove('show');
    document.body.style.overflow = '';
}
document.getElementById('skModal').addEventListener('click', e => { if(e.target===document.getElementById('skModal')) closeSk(); });

// ===== REAL-TIME SEARCH (AJAX, tanpa reload halaman) =====
let sTimer = null;
const sInp = document.getElementById('sInput');
const clrB = document.getElementById('clrBtn');
const spin = document.getElementById('spin');

sInp.addEventListener('input', function() {
    clrB.classList.toggle('visible', this.value.trim().length > 0);
    clearTimeout(sTimer);
    sTimer = setTimeout(() => doSearch(this.value.trim()), 300);
});
sInp.addEventListener('keydown', e => {
    if (e.key === 'Enter') { clearTimeout(sTimer); doSearch(sInp.value.trim()); }
});

function clearSearch() {
    sInp.value = '';
    clrB.classList.remove('visible');
    doSearch('');
    sInp.focus();
}

// Tautan tombol periode dibuat di server, sedangkan pencarian AJAX mengubah
// URL tanpa memuat ulang halaman — tautannya jadi basi. Saat diklik, URL
// disusun ulang dari alamat yang sedang berlaku supaya kata pencarian dan
// tab yang sedang dibuka tidak hilang.
// `dasar` default ke alamat yang sedang berlaku; dipisah jadi parameter agar
// penyusunan URL-nya bisa diperiksa tanpa benar-benar berpindah halaman.
function urlPeriode(nilai, dasar) {
    const url = new URL(dasar || window.location.href);
    if (nilai) url.searchParams.set('periode', nilai);
    else url.searchParams.delete('periode');
    ['page_draft','page_verif','page_sidang','page_lulus','page_tidak_lulus','page_tanpa','page_ditolak'].forEach(p => url.searchParams.delete(p));
    return url.toString();
}

function doSearch(kw) {
    const url = new URL(window.location.href);
    if (kw) url.searchParams.set('search', kw);
    else url.searchParams.delete('search');
    // reset semua pagination ke halaman 1
    ['page_draft','page_verif','page_sidang','page_lulus','page_tidak_lulus','page_tanpa','page_ditolak']
        .forEach(p => url.searchParams.delete(p));

    window.history.pushState({}, '', url.toString());

    const content = document.getElementById('upContent');
    spin.classList.add('show');
    content.style.opacity = '0.5';

    fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.text())
        .then(html => {
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const fresh = doc.getElementById('upContent');
            if (fresh) content.innerHTML = fresh.innerHTML;
            updateCounts();        // sinkron angka stats & tab (di luar #upContent)
            content.style.opacity = '1';
            spin.classList.remove('show');
            if (kw) highlightText(content, kw);
        })
        .catch(() => {
            content.style.opacity = '1';
            spin.classList.remove('show');
        });
}

// Update angka stats & tab count dari #countData (hasil filter)
function updateCounts() {
    const cd = document.getElementById('countData');
    if (!cd) return;
    let counts;
    try { counts = JSON.parse(cd.dataset.json); } catch(e) { return; }
    Object.keys(counts).forEach(k => {
        document.querySelectorAll('.tahap[data-tabkey="' + k + '"]').forEach(ubin => {
            const angka = ubin.querySelector('.tahap-num');
            if (angka) angka.textContent = counts[k];
            // Tahap yang hasil pencariannya kosong ikut diredupkan.
            ubin.classList.toggle('kosong', !counts[k]);
        });
    });
}

function highlightText(root, keyword) {
    const kw = keyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const regex = new RegExp('(' + kw + ')', 'gi');
    root.querySelectorAll('.td-nama, .td-nik').forEach(node => {
        node.innerHTML = node.textContent.replace(regex, '<mark>$1</mark>');
    });
}

// Browser back/forward
window.addEventListener('popstate', () => {
    const url = new URL(window.location.href);
    const kw  = url.searchParams.get('search') || '';
    sInp.value = kw;
    clrB.classList.toggle('visible', kw.length > 0);
    doSearch(kw);
});
</script>
@endpush