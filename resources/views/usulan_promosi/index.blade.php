@extends('layouts.app')
@section('title', 'Usulan Promosi')
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
    width: 240px;
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

/* ===== STATS ===== */
.stats-outer { overflow-x: auto; -webkit-overflow-scrolling: touch; margin-bottom: 20px; padding-bottom: 4px; }
.stats-inner { display: flex; gap: 10px; width: max-content; }
.stat-card { background: white; border: 1px solid var(--card-border); border-radius: var(--radius); box-shadow: var(--card-shadow); padding: 14px 18px; min-width: 120px; }
.stat-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 5px; }
.stat-lbl { font-size: 10px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 6px; }
.stat-num { font-size: 26px; font-weight: 800; line-height: 1; }

/* ===== WORKFLOW BAR (stepper proses + pill hasil akhir) ===== */
.flow-card { background: white; border: 1px solid var(--card-border); border-radius: var(--radius); box-shadow: var(--card-shadow); padding: 16px 18px; margin-bottom: 20px; }
.flow-row { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
.flow-row + .flow-row { margin-top: 14px; padding-top: 14px; border-top: 1px solid #f3f4f6; }
.flow-tag { font-size: 10px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: .6px; width: 84px; flex-shrink: 0; }

/* Stepper (Draft -> Verifikasi -> Sidang) */
.stepper { display: flex; align-items: center; flex: 1; min-width: 0; overflow-x: auto; }
.step-item { display: inline-flex; align-items: center; gap: 8px; border: none; background: transparent; cursor: pointer; font-family: inherit; padding: 4px 6px; border-radius: 8px; transition: background .12s; white-space: nowrap; }
.step-item:hover { background: #f9fafb; }
.step-circle { width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; background: white; color: #9ca3af; border: 2px solid #e5e7eb; flex-shrink: 0; transition: all .15s; box-sizing: border-box; }
.step-check { width: 12px; height: 12px; stroke: white; fill: none; stroke-width: 3; display: none; }
.step-item.is-done .step-circle { background: #15803d; border-color: #15803d; color: white; }
.step-item.is-done .step-check { display: block; }
.step-item.is-done .step-num { display: none; }
.step-item.is-active .step-circle { background: #1d4ed8; border-color: #1d4ed8; color: white; box-shadow: 0 0 0 3px rgba(29,78,216,.15); }
.step-label { font-size: 13px; font-weight: 600; color: #9ca3af; }
.step-item.is-done .step-label { color: #6b7280; }
.step-item.is-active .step-label { color: #111827; font-weight: 700; }
.step-count { font-size: 10px; font-weight: 700; padding: 1px 6px; border-radius: 20px; background: #f3f4f6; color: #6b7280; }
.step-item.is-active .step-count { background: #dbeafe; color: #1d4ed8; }
.step-connector { width: 26px; height: 2px; background: #e5e7eb; flex-shrink: 0; margin: 0 2px; }
.step-connector.is-done { background: #15803d; }

/* Outcome pills (Lulus / Tidak Lulus / Tanpa Sidang / Ditolak) */
.outcomes { display: flex; align-items: center; gap: 8px; flex: 1; min-width: 0; overflow-x: auto; }
.outcome-tab {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 13px; border-radius: 20px; border: 1.5px solid #e5e7eb;
    background: white; cursor: pointer; font-family: inherit; font-size: 12px; font-weight: 600;
    color: #6b7280; white-space: nowrap; transition: all .15s;
}
.outcome-tab:hover { border-color: #d1d5db; }
.outcome-count { font-size: 10px; font-weight: 700; padding: 1px 6px; border-radius: 20px; background: #f3f4f6; color: #6b7280; }

/* ===== TABLE CARD ===== */
.table-card { background: white; border-radius: var(--radius); border: 1px solid var(--card-border); box-shadow: var(--card-shadow); overflow: hidden; }
.table-outer { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.table-outer::-webkit-scrollbar { height: 8px; }
.table-outer::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
.table-outer::-webkit-scrollbar-track { background: #f3f4f6; }
.table-outer table { border-collapse: collapse; width: 100%; min-width: 1080px; }
.table-outer thead th {
    background: #f9fafb;
    padding: 11px 16px;
    font-size: 11px;
    font-weight: 700;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: .5px;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
    white-space: nowrap;
}
.table-outer tbody tr { border-bottom: 1px solid #f3f4f6; }
.table-outer tbody tr:last-child { border-bottom: none; }
.table-outer tbody tr:hover { background: #fafafa; }
.table-outer tbody td { padding: 14px 16px; font-size: 13px; color: #374151; vertical-align: top; }

/* Avatar */
.av { width: 36px; height: 36px; border-radius: 50%; background: #f0fdf4; color: #15803d; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; flex-shrink: 0; border: 1.5px solid #bbf7d0; }
.td-nama { font-weight: 700; color: #111827; font-size: 13px; }
.td-nik { font-size: 11px; color: #9ca3af; margin-top: 2px; }

/* Posisi cols */
.pos-block { border-left: 3px solid #e5e7eb; padding-left: 10px; }
.pos-block.tujuan { border-left-color: #15803d; }
.pos-title { font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: .7px; margin-bottom: 8px; color: #9ca3af; }
.pos-block.tujuan .pos-title { color: #15803d; }
.pos-row { display: flex; align-items: baseline; gap: 6px; margin-bottom: 4px; }
.pos-lbl { font-size: 10px; color: #9ca3af; font-weight: 600; flex-shrink: 0; width: 80px; }
.pos-val { font-size: 12px; color: #111827; font-weight: 500; line-height: 1.4; }

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
mark { background: #fef08a; border-radius: 2px; padding: 0 1px; color: inherit; font-weight: 700; }

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
                        <select name="job_grade_id" id="skJg" class="sk-inp" required>
                            <option value="">— JG —</option>
                            @foreach($jobGrades as $jg)
                            <option value="{{ $jg->id }}" data-val="{{ $jg->job_grade }}">JG {{ $jg->job_grade }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="sk-lbl">Person Grade <span class="sk-req">*</span></label>
                        <select name="person_grade_id" id="skPg" class="sk-inp" required>
                            <option value="">— PG —</option>
                            @foreach($personGrades as $pg)
                            <option value="{{ $pg->id }}" data-val="{{ $pg->person_grade }}">PG {{ $pg->person_grade }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="sk-lbl">Kode Struktur <span class="sk-req">*</span></label>
                    <select name="kode_struktur_id" id="skKode" class="sk-inp" required>
                        <option value="">— Pilih Kode Struktur —</option>
                        @foreach($kodeStrukturs as $ks)
                        <option value="{{ $ks->id }}">{{ $ks->nama ?? $ks->kode_struktur ?? $ks->kode ?? ('#'.$ks->id) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="sk-lbl">Direktorat <span class="sk-req">*</span></label>
                    <select name="direktorat_id" id="skDir" class="sk-inp" required>
                        <option value="">— Pilih Direktorat —</option>
                        @foreach($direktorats as $dr)
                        <option value="{{ $dr->id }}">{{ $dr->nama_direktorat ?? $dr->nama ?? ('#'.$dr->id) }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                    <div>
                        <label class="sk-lbl">Kompartemen <span class="sk-req">*</span></label>
                        <select name="kompartemen_id" id="skKomp" class="sk-inp" required>
                            <option value="">— Pilih —</option>
                            @foreach($kompartemens as $kp)
                            <option value="{{ $kp->id }}">{{ $kp->nama_kompartemen ?? ('#'.$kp->id) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="sk-lbl">Departemen <span class="sk-req">*</span></label>
                        <select name="departemen_id" id="skDept" class="sk-inp" required>
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
                    <div>Jabatan tujuan diambil dari usulan. Menyimpan akan otomatis membuat <strong>riwayat jabatan baru</strong> (tipe: promosi) &amp; memperbarui <strong>posisi terkini karyawan</strong>. Jika jabatan tujuan termasuk tingkat pejabat (SVP/VP/SPM/PM), otomatis tercatat di Pejabat Definitif.</div>
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
                    <select id="tlSelect" name="tindak_lanjut" class="sk-inp" onchange="onTlChange(this.value)">
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
                <select id="hsSelect" name="hasil_sidang" class="sk-inp" onchange="onHsChange(this.value)">
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

{{-- DEFINISI ARRAY (dipakai stats, workflow bar, panels) --}}
@php
$sc = [
    'draft'        => ['Draft',       '#6b7280'],
    'verif_berkas' => ['Verif Berkas','#d97706'],
    'sidang'       => ['Sidang',      '#1d4ed8'],
    'lulus'        => ['Lulus',       '#15803d'],
    'tidak_lulus'  => ['Tidak Lulus', '#dc2626'],
    'tanpa_sidang' => ['Tanpa Sidang','#7c3aed'],
    'ditolak'      => ['Ditolak',     '#be185d'],
];
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
$stepOrder = ['draft' => 0, 'verif_berkas' => 1, 'sidang' => 2];
// Hasil akhir (cabang setelah sidang) — warnanya sama dengan $bc supaya konsisten
$outcomes = [
    'lulus'        => ['Lulus',        '#15803d', '#dcfce7'],
    'tanpa_sidang' => ['Tanpa Sidang',  '#7c3aed', '#f5f3ff'],
    'tidak_lulus'  => ['Tidak Lulus',   '#dc2626', '#fee2e2'],
    'ditolak'      => ['Ditolak',       '#be185d', '#fce7f3'],
];
$activeStepIdx = $stepOrder[$activeTab] ?? 3; // 3 = sudah lewat semua tahap (berada di tab hasil akhir)
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

{{-- STATS (di luar #upContent, angka di-update via JS) --}}
<div class="stats-outer">
    <div class="stats-inner">
        @foreach($sc as $k => $v)
        <div class="stat-card">
            <div class="stat-lbl"><span class="stat-dot" style="background:{{ $v[1] }}"></span>{{ $v[0] }}</div>
            <div class="stat-num" id="stat-{{ $k }}" style="color:{{ $v[1] }}">{{ $counts[$k] }}</div>
        </div>
        @endforeach
    </div>
</div>

{{-- WORKFLOW BAR: tahap proses (stepper) + hasil akhir (pill) + search --}}
<div class="flow-card">
    <div class="flow-row">
        <span class="flow-tag">Tahap Proses</span>
        <div class="stepper">
            @foreach($steps as $k => $label)
            @php $idx = $stepOrder[$k]; $state = $idx < $activeStepIdx ? 'is-done' : ($idx === $activeStepIdx ? 'is-active' : ''); @endphp
            <button class="step-item {{ $state }}" onclick="switchTab('{{ $k }}',this)" data-tabkey="{{ $k }}">
                <span class="step-circle">
                    <svg class="step-check" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    <span class="step-num">{{ $idx + 1 }}</span>
                </span>
                <span class="step-label">{{ $label }}</span>
                <span class="step-count">{{ $counts[$k] }}</span>
            </button>
            @if(!$loop->last)
            <span class="step-connector {{ $idx < $activeStepIdx ? 'is-done' : '' }}"></span>
            @endif
            @endforeach
        </div>
        <div class="search-box">
            <svg viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="sInput" value="{{ request('search') }}" placeholder="Cari nama / NIK..." autocomplete="off">
            <div class="spin" id="spin"></div>
            <button class="clear-btn {{ request('search') ? 'visible':'' }}" id="clrBtn" onclick="clearSearch()">×</button>
        </div>
    </div>
    <div class="flow-row">
        <span class="flow-tag">Hasil Akhir</span>
        <div class="outcomes">
            @foreach($outcomes as $k => $o)
            @php
                $oActive = $activeTab === $k;
                $ocText = $oActive ? $o[1] : '#6b7280';
                $ocBorder = $oActive ? $o[1] : '#e5e7eb';
                $ocBg = $oActive ? $o[2] : 'white';
                $ocCountBg = $oActive ? 'white' : '#f3f4f6';
            @endphp
            <button class="outcome-tab {{ $oActive?'active':'' }}"
                style="color:{{ $ocText }};border-color:{{ $ocBorder }};background:{{ $ocBg }}"
                onclick="switchTab('{{ $k }}',this)" data-tabkey="{{ $k }}">
                {{ $o[0] }} <span class="outcome-count" style="background:{{ $ocCountBg }};color:{{ $ocText }}">{{ $counts[$k] }}</span>
            </button>
            @endforeach
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
    <div class="table-outer">
        <table>
            <thead>
                <tr>
                    <th style="width:36px">#</th>
                    <th>Nama</th>
                    <th>Posisi Awal</th>
                    <th>Posisi Baru</th>
                    <th>Dibuat Oleh</th>
                    <th>Status</th>
                    @if($tabKey==='draft')<th>Verifikasi</th>@endif
                    @if($tabKey==='verif_berkas')<th>Tindak Lanjut</th>@endif
                    @if($tabKey==='sidang')<th>Hasil Sidang</th>@endif
                    @if($tabKey==='lulus' || $tabKey==='tanpa_sidang')<th>Terbitkan SK</th>@endif
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($d as $i => $u)
                @php $cl = $bc[$u->status] ?? ['#f3f4f6','#374151']; @endphp
                <tr>
                    <td style="color:#d1d5db;font-size:11px;font-weight:600;vertical-align:middle">
                        {{ ($d->currentPage()-1)*$d->perPage()+$i+1 }}
                    </td>

                    {{-- NAMA --}}
                    <td style="vertical-align:middle;min-width:160px">
                        <div style="display:flex;align-items:center;gap:10px">
                            <div class="av">{{ initials($u->karyawan->nama) }}</div>
                            <div>
                                <div class="td-nama">{{ $u->karyawan->nama??'-' }}</div>
                                <div class="td-nik">{{ $u->karyawan->nik??'-' }}</div>
                            </div>
                        </div>
                    </td>

                    {{-- POSISI AWAL --}}
                    <td style="min-width:200px">
                        <div class="pos-block">
                        <div class="pos-title">Posisi Awal</div>
                        <div class="pos-row"><span class="pos-lbl">Jabatan</span><span class="pos-val">{{ $u->jabatan_saat_ini??'-' }}</span></div>
                        <div class="pos-row"><span class="pos-lbl">Direktorat</span><span class="pos-val">{{ optional($u->karyawan->direktorat)->nama_direktorat ?? optional($u->karyawan->direktorat)->nama ?? '-' }}</span></div>
                        <div class="pos-row"><span class="pos-lbl">Kompartemen</span><span class="pos-val">{{ $u->kompartemen_saat_ini??$u->karyawan->kompartemen->nama_kompartemen??'-' }}</span></div>
                        <div class="pos-row"><span class="pos-lbl">Departemen</span><span class="pos-val">{{ $u->departemen_saat_ini??$u->karyawan->departemen->nama_departemen??'-' }}</span></div>
                        <div class="pos-row"><span class="pos-lbl">Job Grade</span><span class="pos-val" style="font-weight:700">JG {{ $u->job_grade_saat_ini??'-' }}</span></div>
                        <div class="pos-row"><span class="pos-lbl">Person Grade</span><span class="pos-val" style="font-weight:700">PG {{ $u->person_grade_saat_ini??'-' }}</span></div>
                        </div>
                    </td>

                    {{-- POSISI BARU --}}
                    <td style="min-width:200px">
                        <div class="pos-block tujuan">
                        <div class="pos-title">Posisi Baru</div>
                        <div class="pos-row"><span class="pos-lbl">Jabatan</span><span class="pos-val" style="font-weight:600;color:#111827">{{ $u->jabatan_tujuan }}@if(optional($u->jabatanTujuan)->nama_jabatan)<span style="display:block;font-size:10px;color:#9ca3af;font-weight:500">Master: {{ $u->jabatanTujuan->nama_jabatan }}</span>@endif</span></div>
                        <div class="pos-row"><span class="pos-lbl">Direktorat</span><span class="pos-val">{{ optional($u->direktoratTujuan)->nama_direktorat ?? optional($u->direktoratTujuan)->nama ?? optional($u->karyawan->direktorat)->nama_direktorat ?? optional($u->karyawan->direktorat)->nama ?? '-' }}</span></div>
                        <div class="pos-row"><span class="pos-lbl">Kompartemen</span><span class="pos-val">{{ optional($u->kompartemenTujuan)->nama_kompartemen ?? $u->karyawan->kompartemen->nama_kompartemen ?? '-' }}</span></div>
                        <div class="pos-row"><span class="pos-lbl">Departemen</span><span class="pos-val">{{ optional($u->departemenTujuan)->nama_departemen ?? $u->karyawan->departemen->nama_departemen ?? '-' }}</span></div>
                        <div class="pos-row"><span class="pos-lbl">Job Grade</span><span class="pos-val" style="font-weight:700;color:#15803d">JG {{ $u->job_grade_promosi??'-' }}</span></div>
                        <div class="pos-row"><span class="pos-lbl">Person Grade</span><span class="pos-val" style="font-weight:700;color:#15803d">PG {{ $u->person_grade_promosi??'-' }}</span></div>
                        </div>
                    </td>

                    {{-- DIBUAT OLEH --}}
                    <td style="vertical-align:middle;min-width:120px">
                        <div style="font-size:12px;font-weight:600;color:#374151">{{ $u->createdBy->name??'-' }}</div>
                        <div style="font-size:11px;color:#9ca3af;margin-top:2px">{{ $u->created_at->format('d M Y') }}</div>
                    </td>

                    {{-- STATUS --}}
                    <td style="vertical-align:middle;white-space:nowrap">
                        <span class="badge" style="background:{{ $cl[0] }};color:{{ $cl[1] }}">{{ $u->status_label }}</span>
                    </td>

                    {{-- TINDAK LANJUT (buka modal, bukan form inline) --}}
                    @if($tabKey==='verif_berkas')
                    <td style="vertical-align:middle;min-width:160px">
                        @if($u->tindak_lanjut==='sidang')
                            <span class="badge" style="background:#dbeafe;color:#1d4ed8">Lanjut Sidang{{ $u->tanggal_sidang ? ' · '.$u->tanggal_sidang->format('d M Y') : '' }}</span>
                        @elseif($u->tindak_lanjut==='ditolak')
                            <span class="badge" style="background:#fee2e2;color:#dc2626">Ditolak</span>
                        @else
                            <span class="badge" style="background:#f3f4f6;color:#6b7280">Belum diproses</span>
                        @endif
                        <div>
                            <button type="button" class="btn-soft"
                                data-url="{{ route('usulan_promosi.update_status',$u) }}"
                                data-nama="{{ $u->karyawan->nama }}"
                                data-tl="{{ $u->tindak_lanjut }}"
                                data-tgl="{{ $u->tanggal_sidang?->format('Y-m-d') }}"
                                onclick="openTl(this)">
                                <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Tindak Lanjut
                            </button>
                        </div>
                    </td>
                    @endif

                    {{-- HASIL SIDANG (buka modal, bukan form inline) --}}
                    @if($tabKey==='sidang')
                    <td style="vertical-align:middle;min-width:160px">
                        @if($u->hasil_sidang==='lulus')
                            <span class="badge" style="background:#dcfce7;color:#15803d">Lulus</span>
                        @elseif($u->hasil_sidang==='tidak_lulus')
                            <span class="badge" style="background:#fee2e2;color:#dc2626">Tidak Lulus</span>
                        @elseif($u->hasil_sidang==='tanpa_sidang')
                            <span class="badge" style="background:#f5f3ff;color:#7c3aed">Tanpa Sidang</span>
                        @else
                            <span class="badge" style="background:#f3f4f6;color:#6b7280">Belum diproses</span>
                        @endif
                        <div>
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
                        </div>
                    </td>
                    @endif

                    {{-- VERIFIKASI (kolom sendiri, hanya tab draft) --}}
                    @if($tabKey==='draft')
                    <td style="vertical-align:middle;min-width:100px">
                        <form method="POST" action="{{ route('usulan_promosi.update_status',$u) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="verif_berkas">
                            <button type="submit" class="btn-v">Verif →</button>
                        </form>
                    </td>
                    @endif

                    {{-- TERBITKAN SK (kolom sendiri, hanya tab lulus/tanpa sidang) --}}
                    @if($tabKey==='lulus' || $tabKey==='tanpa_sidang')
                    <td style="vertical-align:middle;min-width:160px">
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
                    </td>
                    @endif

                    {{-- AKSI (selalu kolom terakhir, isinya hanya Detail & Hapus) --}}
                    <td style="vertical-align:middle;min-width:80px">
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
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
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

// Tabs (stepper + outcome pills berbagi logika yang sama)
const STEP_ORDER = { draft: 0, verif_berkas: 1, sidang: 2 };
const OUTCOME_COLORS = {
    lulus:        ['#15803d', '#dcfce7'],
    tanpa_sidang: ['#7c3aed', '#f5f3ff'],
    tidak_lulus:  ['#dc2626', '#fee2e2'],
    ditolak:      ['#be185d', '#fce7f3'],
};

function switchTab(tab) {
    document.querySelectorAll('[id^="p-"]').forEach(p => p.style.display = 'none');
    const panel = document.getElementById('p-' + tab);
    if (panel) panel.style.display = '';

    // Stepper: step sebelum tab aktif ditandai selesai (is-done), tab aktif ditandai is-active
    const activeIdx = STEP_ORDER[tab] ?? 3; // 3 = sudah di tab hasil akhir, semua tahap proses selesai
    document.querySelectorAll('.step-item').forEach(b => {
        const idx = STEP_ORDER[b.dataset.tabkey];
        b.classList.remove('is-active', 'is-done');
        if (idx < activeIdx) b.classList.add('is-done');
        else if (idx === activeIdx) b.classList.add('is-active');
    });
    document.querySelectorAll('.step-connector').forEach((c, i) => {
        c.classList.toggle('is-done', i < activeIdx);
    });

    // Outcome pills: warna ikut status yang sedang dipilih
    document.querySelectorAll('.outcome-tab').forEach(b => {
        const isActive = b.dataset.tabkey === tab;
        b.classList.toggle('active', isActive);
        const countEl = b.querySelector('.outcome-count');
        const colors = OUTCOME_COLORS[b.dataset.tabkey];
        const [text, bg] = isActive && colors ? colors : ['#6b7280', 'white'];
        b.style.color = text;
        b.style.borderColor = isActive ? text : '#e5e7eb';
        b.style.background = bg;
        if (countEl) { countEl.style.background = isActive ? 'white' : '#f3f4f6'; countEl.style.color = text; }
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
        const st = document.getElementById('stat-' + k);
        if (st) st.textContent = counts[k];
        document.querySelectorAll('[data-tabkey="' + k + '"] .step-count, [data-tabkey="' + k + '"] .outcome-count')
            .forEach(el => el.textContent = counts[k]);
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