@extends('layouts.app')
@section('title', 'Kelola Job Profile — ' . $versi->nomor_sk)
@section('breadcrumb-parent', 'Job Profile')
@section('breadcrumb', 'Kelola ' . $versi->nomor_sk)

{{--
    TIDAK ADA collapse/expand di halaman ini SAMA SEKALI — semua unit & form
    tambah/ganti-nya langsung tampil terbuka dari render awal, tanpa klik apa pun.
    Struktur/CSS/markup halaman ini disamakan persis dgn show.blade.php versi
    SEBELUM restrukturisasi View/Edit (card per unit, list JD + 1 form tambah/ganti
    per unit LANGSUNG TERLIHAT) — SATU-SATUNYA beda dari versi lama itu: tombol
    hijau "Simpan / Ganti" yg dulu ada di SETIAP card sekarang dihapus, diganti 1
    tombol "Simpan Semua Perubahan" di atas halaman (sebelum list unit). Field
    tambah/ganti per unit tetap terhubung ke #jp-batch-form (storeBatch(), TIDAK
    diubah) — 1 x-data kecil per unit ({ hasFile: false }) TETAP ada, tapi itu
    murni utk enable/disable hidden field unit_organisasi_id pas file dipilih
    (bagian dari mekanisme batch), BUKAN utk buka/tutup tampilan.
--}}

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color .12s; }
    .back-link:hover { color:#15803d; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }

    .page-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;gap:12px;flex-wrap:wrap; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:4px; }

    .header-actions { display:flex;align-items:center;gap:10px;flex-wrap:wrap; }
    .btn-lihat { display:inline-flex;align-items:center;gap:6px;background:white;color:#374151;padding:9px 16px;border-radius:9px;font-size:13px;font-weight:600;border:1.5px solid #e5e7eb;text-decoration:none;white-space:nowrap; }
    .btn-lihat:hover { background:#f9fafb; }
    .btn-lihat svg { width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2; }

    .progress-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:18px 22px;margin-bottom:16px; }
    .progress-text { font-size:13px;font-weight:600;color:#374151;margin-bottom:8px; }
    .progress-text .num { color:#15803d;font-weight:800;font-size:15px; }
    .progress-bar-track { height:8px;border-radius:5px;background:#f3f4f6;overflow:hidden; }
    .progress-bar-fill { height:100%;border-radius:5px;background:#16a34a;transition:width .2s; }
    .progress-bar-fill.empty { background:#e5e7eb; }

    .save-bar { display:flex;justify-content:flex-end;margin-bottom:16px; }

    .unit-list { display:flex;flex-direction:column;gap:8px; }
    .unit-card { background:white;border-radius:12px;border:1px solid var(--card-border);box-shadow:var(--card-shadow);overflow:hidden; }
    .unit-row { display:flex;align-items:center;gap:12px;padding:13px 18px; }
    .unit-name { font-weight:700;color:#111827;font-size:13.5px; }
    .unit-depth-indent { display:inline-block; }
    .level-badge { display:inline-block;font-size:10.5px;font-weight:700;padding:2px 9px;border-radius:6px;background:#f3f4f6;color:#374151;text-transform:capitalize;margin-left:8px; }
    .profile-count-badge { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;margin-left:auto;white-space:nowrap; }
    .profile-count-badge.has { background:#dcfce7;color:#15803d; }
    .profile-count-badge.none { background:#f3f4f6;color:#9ca3af; }

    .unit-card-highlight { animation: unitHighlightFade 2.2s ease-out forwards; }
    @keyframes unitHighlightFade {
        0%   { box-shadow: 0 0 0 3px rgba(245,158,11,.35); border-color: #f59e0b; }
        100% { box-shadow: var(--card-shadow); border-color: var(--card-border); }
    }

    .unit-panel { border-top:1px solid #f3f4f6;padding:16px 18px;background:#fbfbfa; }
    .profile-item { display:flex;align-items:center;gap:10px;padding:9px 12px;background:white;border:1px solid #f3f4f6;border-radius:8px;margin-bottom:8px;font-size:12.5px; }
    .profile-item .jabatan { font-weight:700;color:#111827;min-width:140px; }
    .profile-item .keterangan-text { color:#6b7280;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
    .profile-item a.file-link { color:#1d4ed8;text-decoration:none;font-weight:600;display:inline-flex;align-items:center;gap:4px; }
    .profile-item a.file-link:hover { text-decoration:underline; }
    .profile-item a.file-link svg { width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2; }
    .btn-del { border:none;background:transparent;color:#ef4444;cursor:pointer;padding:4px;display:flex;align-items:center;border-radius:6px; }
    .btn-del:hover { background:#fef2f2; }
    .btn-del svg { width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2; }

    .add-form { display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end;margin-top:6px;padding-top:12px;border-top:1px dashed #e5e7eb; }
    .add-form.first { border-top:none;padding-top:0; }
    .form-group { display:flex;flex-direction:column;gap:4px; }
    .form-label { font-size:10.5px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.3px; }
    .form-input { padding:8px 10px;border:1.5px solid #e5e7eb;border-radius:7px;font-size:12.5px;font-family:inherit;color:#111827;background:white; }
    .form-input:focus { border-color:#16a34a;outline:none; }
    .form-jabatan { width:180px; }
    .form-file { width:220px; }
    .form-keterangan { flex:1;min-width:180px; }
    .btn-submit { display:inline-flex;align-items:center;gap:6px;background:#15803d;color:white;padding:8px 16px;border-radius:7px;font-size:12.5px;font-weight:600;border:none;cursor:pointer;font-family:inherit;white-space:nowrap; }
    .btn-submit:hover { background:#166534; }

    .empty-profiles { font-size:12px;color:#9ca3af;margin-bottom:10px; }

    [x-cloak] { display:none !important; }

    .toast-wrap { position:fixed;top:20px;right:20px;z-index:9999;pointer-events:none; }
    .toast { display:flex;align-items:center;gap:10px;background:white;border:1px solid #bbf7d0;border-left:4px solid #16a34a;border-radius:12px;padding:14px 16px;box-shadow:0 8px 32px rgba(0,0,0,0.12);font-size:13px;color:#15803d;font-weight:500;min-width:280px;position:relative;overflow:hidden;pointer-events:all;animation:toastIn 0.35s cubic-bezier(0.4,0,0.2,1) forwards; }
    .toast.error { border-color:#fecaca;border-left-color:#ef4444;color:#dc2626; }
    .toast.hiding { animation:toastOut 0.3s forwards; }
    .toast-icon { width:22px;height:22px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .toast.error .toast-icon { background:#fef2f2; }
    .toast-icon svg { width:12px;height:12px;stroke:#16a34a;fill:none;stroke-width:2.5; }
    .toast.error .toast-icon svg { stroke:#ef4444; }
    .toast-close { border:none;background:transparent;color:#9ca3af;cursor:pointer;font-size:18px;padding:0;margin-left:auto; }
    .toast-progress { position:absolute;bottom:0;left:0;height:3px;background:#16a34a;animation:toastProgress 4s linear forwards; }
    .toast.error .toast-progress { background:#ef4444; }
    @keyframes toastIn { from{opacity:0;transform:translateX(110%);}to{opacity:1;transform:translateX(0);} }
    @keyframes toastOut { from{opacity:1;}to{opacity:0;transform:translateX(110%);} }
    @keyframes toastProgress { from{width:100%;}to{width:0%;} }
</style>
@endpush

@section('content')

@if(session('success'))
<div class="toast-wrap" id="toastWrap">
    <div class="toast" id="toast">
        <div class="toast-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
        <div>{{ session('success') }}</div>
        <button class="toast-close" onclick="closeToast()">&times;</button>
        <div class="toast-progress"></div>
    </div>
</div>
@endif

@if(session('error') || $errors->any())
<div class="toast-wrap" id="toastWrap">
    <div class="toast error" id="toast">
        <div class="toast-icon"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></div>
        <div>{{ session('error') ?? $errors->first() }}</div>
        <button class="toast-close" onclick="closeToast()">&times;</button>
        <div class="toast-progress"></div>
    </div>
</div>
@endif

<a href="{{ route('organisasi.job-profile.show', $versi) }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Job Profile
</a>

{{-- Form batch "kosong" (cuma bawa token + tujuan) — field sesungguhnya tersebar di tiap
     unit, terhubung lewat atribut form="jp-batch-form" (bukan di-nest, krn tiap JD juga
     punya <form> hapus sendiri2 — HTML tidak boleh form di dalam form). --}}
<form id="jp-batch-form" method="POST" action="{{ route('organisasi.job-profile.storeBatch', $versi) }}" enctype="multipart/form-data">
    @csrf
</form>

<div class="page-header">
    <div>
        <div class="page-title">Kelola Job Profile — SK {{ $versi->nomor_sk }}</div>
        <div class="page-sub">{{ $totalUnit }} unit organisasi pada versi ini · berlaku {{ $versi->tanggal_mulai_berlaku->translatedFormat('d F Y') }}</div>
    </div>
    <div class="header-actions">
        <a href="{{ route('organisasi.job-profile.show', $versi) }}" class="btn-lihat">
            <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            Lihat
        </a>
    </div>
</div>

@php $pct = $totalUnit > 0 ? round(($unitWithProfileCount / $totalUnit) * 100) : 0; @endphp
<div class="progress-card">
    <div class="progress-text"><span class="num">{{ $unitWithProfileCount }}</span> dari {{ $totalUnit }} unit sudah punya minimal 1 Job Profile</div>
    <div class="progress-bar-track">
        <div class="progress-bar-fill {{ $unitWithProfileCount === 0 ? 'empty' : '' }}" style="width:{{ $pct }}%;"></div>
    </div>
</div>

{{-- SATU-SATUNYA tombol simpan di halaman ini (menggantikan tombol "Simpan / Ganti"
     yg dulu ada di tiap card) — di atas, sebelum list unit, TIDAK sticky/floating. --}}
<div class="save-bar">
    <button type="submit" form="jp-batch-form" class="btn-submit">
        <svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" fill="none" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        Simpan Semua Perubahan
    </button>
</div>

@php
    $highlightUnitId = request()->filled('highlight') ? (int) request()->query('highlight') : null;
@endphp

<div class="unit-list">
    @forelse($unitsOrdered as $item)
        @php
            $unit = $item['node'];
            $depth = $item['depth'];
            $unitProfiles = $profiles->get($unit->unit_organisasi_id, collect());
            $isHighlighted = !is_null($highlightUnitId) && $unit->unit_organisasi_id === $highlightUnitId;
        @endphp
        <div class="unit-card {{ $isHighlighted ? 'unit-card-highlight' : '' }}" id="unit-card-{{ $unit->unit_organisasi_id }}">
            <div class="unit-row">
                <span class="unit-depth-indent" style="width:{{ $depth * 18 }}px;"></span>
                <span class="unit-name">{{ $unit->nama_unit }}</span>
                <span class="level-badge">{{ $unit->level }}</span>
                @if($unitProfiles->count() > 0)
                    <span class="profile-count-badge has">{{ $unitProfiles->count() }} Job Profile</span>
                @else
                    <span class="profile-count-badge none">Belum ada</span>
                @endif
            </div>
            <div class="unit-panel">
                @if($unitProfiles->isEmpty())
                    <div class="empty-profiles">Belum ada Job Profile untuk unit ini.</div>
                @else
                    @foreach($unitProfiles as $profile)
                    <div class="profile-item">
                        <span class="jabatan">{{ $profile->nama_jabatan }}</span>
                        <a href="{{ asset('storage/' . $profile->file_path) }}" target="_blank" rel="noopener" class="file-link">
                            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            {{ $profile->file_original_name }}
                        </a>
                        <span class="keterangan-text">{{ $profile->keterangan }}</span>
                        <form method="POST" action="{{ route('organisasi.job-profile.destroy', $profile) }}" onsubmit="return confirm('Hapus Job Profile \'{{ $profile->nama_jabatan }}\' untuk unit \'{{ $unit->nama_unit }}\'? File akan dihapus permanen.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-del" title="Hapus">
                                <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </form>
                    </div>
                    @endforeach
                @endif

                {{-- Sama persis dgn mekanisme lama: ketik nama_jabatan yg SUDAH ADA + pilih
                     file baru = replace; ketik nama baru = tambah. Bedanya cuma tidak submit
                     sendiri lagi (field nyambung ke #jp-batch-form via atribut form=), dan
                     hidden unit_organisasi_id baru ikut ke-submit setelah file dipilih (spy
                     unit yg tidak disentuh tidak membengkakkan 1 request jadi ribuan field
                     kosong) — Nama Jabatan/Keterangan/File TETAP selalu terlihat & bisa diisi
                     kapan saja spt sebelumnya, TIDAK ada yg disembunyikan di balik klik. --}}
                <div class="add-form {{ $unitProfiles->isEmpty() ? 'first' : '' }}" x-data="{ hasFile: false }">
                    <input type="hidden" name="entries[u{{ $unit->unit_organisasi_id }}][unit_organisasi_id]" value="{{ $unit->unit_organisasi_id }}" form="jp-batch-form" :disabled="!hasFile">
                    <div class="form-group">
                        <label class="form-label">Nama Jabatan</label>
                        <input type="text" name="entries[u{{ $unit->unit_organisasi_id }}][nama_jabatan]" form="jp-batch-form" class="form-input form-jabatan" placeholder="mis. AVP" value="{{ old('entries.u' . $unit->unit_organisasi_id . '.nama_jabatan') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">File (PDF/Word)</label>
                        <input type="file" name="entries[u{{ $unit->unit_organisasi_id }}][file]" form="jp-batch-form" class="form-input form-file" accept=".pdf,.doc,.docx"
                               @change="hasFile = $event.target.files.length > 0">
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Keterangan (opsional)</label>
                        <input type="text" name="entries[u{{ $unit->unit_organisasi_id }}][keterangan]" form="jp-batch-form" class="form-input form-keterangan" placeholder="Catatan opsional">
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="unit-card"><div class="unit-row">Tidak ada unit pada versi ini.</div></div>
    @endforelse
</div>

@endsection

@push('scripts')
<script>
    function closeToast() {
        const t = document.getElementById('toast');
        if (!t) return;
        t.classList.add('hiding');
        setTimeout(() => document.getElementById('toastWrap')?.remove(), 300);
    }
    window.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('toast')) setTimeout(() => closeToast(), 4000);

        const highlightId = {{ \Illuminate\Support\Js::from($highlightUnitId) }};
        if (highlightId) {
            document.getElementById('unit-card-' + highlightId)
                ?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
</script>
@endpush
