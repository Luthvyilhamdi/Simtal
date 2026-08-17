@extends('layouts.app')
@section('title', 'Detail Versi Struktur Organisasi')
@section('breadcrumb-parent', 'Riwayat Struktur Organisasi')
@section('breadcrumb', $versi->nomor_sk)

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color .12s; }
    .back-link:hover { color:#15803d; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }

    .page-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;gap:12px;flex-wrap:wrap; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:4px; }
    .badge-aktif { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;background:#dcfce7;color:#15803d;margin-left:8px; }
    .badge-lampau { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;background:#f3f4f6;color:#6b7280;margin-left:8px; }
    .badge-draft { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;background:#fffbeb;color:#92400e;margin-left:8px; }
    .badge-final { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;background:#eff6ff;color:#1d4ed8;margin-left:8px; }

    .header-actions { display:flex;align-items:center;gap:10px;flex-wrap:wrap; }
    .btn-edit { display:inline-flex;align-items:center;gap:6px;background:white;color:#374151;padding:9px 16px;border-radius:9px;font-size:13px;font-weight:600;border:1.5px solid #e5e7eb;text-decoration:none; }
    .btn-edit:hover { background:#f9fafb; }
    .btn-edit svg { width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2; }
    .btn-tree { display:inline-flex;align-items:center;gap:6px;background:#15803d;color:white;padding:9px 16px;border-radius:9px;font-size:13px;font-weight:600;border:none;text-decoration:none; }
    .btn-tree:hover { background:#166534; }
    .btn-tree svg { width:13px;height:13px;stroke:white;fill:none;stroke-width:1.8; }
    .btn-finalize { display:inline-flex;align-items:center;gap:6px;background:#111827;color:white;padding:9px 16px;border-radius:9px;font-size:13px;font-weight:600;border:none;cursor:pointer;font-family:inherit;white-space:nowrap; }
    .btn-finalize:hover { background:#1f2937; }
    .btn-finalize svg { width:13px;height:13px;stroke:white;fill:none;stroke-width:2; }

    .modal-backdrop { position:fixed;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(3px);z-index:1000;display:flex;align-items:center;justify-content:center; }
    .modal-box { background:white;border-radius:16px;padding:26px;width:100%;max-width:460px;margin:16px;box-shadow:0 20px 60px rgba(0,0,0,0.2);text-align:center; }
    .modal-icon-wrap { width:56px;height:56px;border-radius:50%;background:#fef3c7;display:flex;align-items:center;justify-content:center;margin:0 auto 16px; }
    .modal-icon-wrap svg { width:26px;height:26px;stroke:#d97706;fill:none;stroke-width:2; }
    .modal-title { font-size:16px;font-weight:700;color:#111827;margin-bottom:6px; }
    .modal-sub { font-size:13px;color:#6b7280;margin-bottom:16px; }
    .modal-actions { display:flex;gap:10px;margin-top:20px; }
    .modal-btn { flex:1;padding:11px;border-radius:10px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all 0.15s; }
    .modal-btn.cancel { background:#f9fafb;color:#374151;border:1px solid #e5e7eb; }
    .modal-btn.save { background:#111827;color:white; }
    .modal-btn.save:hover { background:#1f2937; }
    [x-cloak] { display:none !important; }

    .info-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:22px 26px;margin-bottom:16px; }
    .info-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:16px; }
    .info-item { display:flex;flex-direction:column;gap:3px; }
    .info-label { font-size:10.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.4px; }
    .info-val { font-size:14px;font-weight:700;color:#111827; }
    .info-val.muted { color:#9ca3af;font-weight:600; }

    .ringkasan-grid { display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:16px; }
    .ringkasan-item { background:white;border-radius:12px;border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:14px 16px;text-align:center; }
    .ringkasan-num { font-size:20px;font-weight:800;color:#111827; }
    .ringkasan-label { font-size:11px;color:#6b7280;margin-top:2px;text-transform:capitalize; }

    .table-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);overflow:hidden; }
    .table-wrap { overflow-x:auto; }
    table { width:100%;border-collapse:collapse;font-size:13px;min-width:640px; }
    thead th { padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid #f3f4f6;background:#f9fafb;white-space:nowrap; }
    tbody td { padding:11px 16px;border-bottom:1px solid #f3f4f6;color:#374151;vertical-align:middle; }
    tbody tr:last-child td { border-bottom:none; }
    tbody tr:hover td { background:#fafaf8; }
    .level-badge { display:inline-block;font-size:11px;font-weight:700;padding:3px 10px;border-radius:6px;background:#f3f4f6;color:#374151;text-transform:capitalize; }
    .muted { color:#9ca3af; }

    .org-history-btn { width:18px;height:18px;border-radius:5px;border:1px solid #e5e7eb;background:#f9fafb;color:#6b7280;display:flex;align-items:center;justify-content:center;flex-shrink:0;text-decoration:none; }
    .org-history-btn:hover { background:#f0fdf4;border-color:#bbf7d0;color:#15803d; }
    .org-history-btn svg { width:11px;height:11px; }

    .toast-wrap { position:fixed;top:20px;right:20px;z-index:9999;pointer-events:none; }
    .toast { display:flex;align-items:center;gap:10px;background:white;border:1px solid #bbf7d0;border-left:4px solid #16a34a;border-radius:12px;padding:14px 16px;box-shadow:0 8px 32px rgba(0,0,0,0.12);font-size:13px;color:#15803d;font-weight:500;min-width:280px;position:relative;overflow:hidden;pointer-events:all;animation:toastIn 0.35s cubic-bezier(0.4,0,0.2,1) forwards; }
    .toast.hiding { animation:toastOut 0.3s forwards; }
    .toast-icon { width:22px;height:22px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .toast-icon svg { width:12px;height:12px;stroke:#16a34a;fill:none;stroke-width:2.5; }
    .toast-close { border:none;background:transparent;color:#9ca3af;cursor:pointer;font-size:18px;padding:0;margin-left:auto; }
    .toast-progress { position:absolute;bottom:0;left:0;height:3px;background:#16a34a;animation:toastProgress 4s linear forwards; }
    @keyframes toastIn { from{opacity:0;transform:translateX(110%);}to{opacity:1;transform:translateX(0);} }
    @keyframes toastOut { from{opacity:1;}to{opacity:0;transform:translateX(110%);} }
    @keyframes toastProgress { from{width:100%;}to{width:0%;} }

    @media (max-width:900px) {
        .info-grid { grid-template-columns:1fr 1fr; }
        .ringkasan-grid { grid-template-columns:repeat(2,1fr); }
    }
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

<a href="{{ route('organisasi.struktur.index') }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Riwayat Struktur Organisasi
</a>

<div x-data="{ showFinalizeModal: false }">
<div class="page-header">
    <div>
        <div class="page-title">
            SK {{ $versi->nomor_sk }}
            @if(!$versi->tanggal_berakhir)
                <span class="badge-aktif">Aktif</span>
            @else
                <span class="badge-lampau">Lampau</span>
            @endif
            @if($versi->isDraft())
                <span class="badge-draft">📝 Draft</span>
            @else
                <span class="badge-final">🔒 Final</span>
            @endif
        </div>
        <div class="page-sub">{{ $units->count() }} unit organisasi tercatat pada versi ini</div>
    </div>
    <div class="header-actions">
        <a href="{{ route('organisasi.struktur.tree', $versi) }}" class="btn-tree">
            <svg viewBox="0 0 24 24"><rect x="8" y="2" width="8" height="4" rx="1"/><rect x="1" y="14" width="6" height="4" rx="1"/><rect x="9" y="14" width="6" height="4" rx="1"/><rect x="17" y="14" width="6" height="4" rx="1"/><path d="M4 14v-3h16v3"/><path d="M12 6v5"/></svg>
            Lihat Pohon Organisasi
        </a>
        <a href="{{ route('organisasi.struktur.export-excel', $versi) }}" class="btn-edit">
            <svg viewBox="0 0 24 24" style="stroke:#15803d;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            Export Excel
        </a>
        <a href="{{ route('organisasi.struktur.export-pdf', $versi) }}" class="btn-edit">
            <svg viewBox="0 0 24 24" style="stroke:#dc2626;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            Export PDF
        </a>
        <a href="{{ route('organisasi.struktur.edit', $versi) }}" class="btn-edit">
            <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            {{ $versi->isDraft() ? 'Edit Roster & SK' : 'Edit Data SK' }}
        </a>
        @if($versi->isFinal())
        <a href="{{ route('organisasi.struktur.index', ['bandingkan' => $versi->id]) }}" class="btn-edit">
            <svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" fill="none" stroke-width="2"><path d="M8 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h3M16 3h3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-3M12 8v8M9 11l3-3 3 3"/></svg>
            Bandingkan dengan Versi Lain
        </a>
        @endif
        @if($versi->isDraft())
        <button type="button" class="btn-finalize" @click="showFinalizeModal = true">
            <svg viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg>
            Finalisasi Versi
        </button>
        @endif
    </div>
</div>

@if($versi->isDraft())
<div class="modal-backdrop" x-show="showFinalizeModal" x-cloak @keydown.escape.window="showFinalizeModal = false">
    <div class="modal-box" @click.outside="showFinalizeModal = false">
        <div class="modal-icon-wrap">
            <svg viewBox="0 0 24 24"><path d="M12 9v4M12 17h.01"/><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L14.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
        </div>
        <div class="modal-title">Finalisasi Versi {{ $versi->nomor_sk }}?</div>
        <div class="modal-sub">Setelah difinalisasi, roster unit &amp; tanggal mulai berlaku versi ini akan <strong>terkunci total</strong> dan tidak bisa diubah lagi lewat halaman ini. Tindakan ini tidak bisa dibatalkan lewat UI.</div>
        <form method="POST" action="{{ route('organisasi.struktur.finalize', $versi) }}">
            @csrf
            @method('PATCH')
            <div class="modal-actions">
                <button type="button" class="modal-btn cancel" @click="showFinalizeModal = false">Batal</button>
                <button type="submit" class="modal-btn save">Ya, Finalisasi</button>
            </div>
        </form>
    </div>
</div>
@endif
</div>

<div class="info-card">
    <div class="info-grid">
        <div class="info-item">
            <div class="info-label">Nomor SK</div>
            <div class="info-val">{{ $versi->nomor_sk }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Tanggal SK</div>
            <div class="info-val">{{ $versi->tanggal_sk->translatedFormat('d F Y') }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Mulai Berlaku</div>
            <div class="info-val">{{ $versi->tanggal_mulai_berlaku->translatedFormat('d F Y') }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Berakhir</div>
            @if($versi->tanggal_berakhir)
                <div class="info-val">{{ $versi->tanggal_berakhir->translatedFormat('d F Y') }}</div>
            @else
                <div class="info-val muted">Masih berlaku</div>
            @endif
        </div>
    </div>
    @if($versi->keterangan)
    <div style="margin-top:16px;padding-top:16px;border-top:1px solid #f3f4f6;">
        <div class="info-label" style="margin-bottom:4px;">Keterangan</div>
        <div style="font-size:13px;color:#374151;">{{ $versi->keterangan }}</div>
    </div>
    @endif
</div>

@if(!$isBaseline)
<div class="ringkasan-grid">
    @foreach(['baru' => 'Unit Baru', 'rename' => 'Rename', 'pindah_induk' => 'Pindah Induk', 'ganti_level' => 'Ganti Level', 'pecah' => 'Pecah', 'gabung' => 'Gabung', 'bubar' => 'Bubar'] as $jenis => $label)
    <div class="ringkasan-item">
        <div class="ringkasan-num">{{ $ringkasanTransisi[$jenis] ?? 0 }}</div>
        <div class="ringkasan-label">{{ $label }}</div>
    </div>
    @endforeach
</div>
@endif

<div class="table-card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nama Unit</th>
                    <th>Level</th>
                    <th>Parent</th>
                    <th>Formasi Unit</th>
                    <th>Total Bawahan</th>
                    <th>Grand Total</th>
                    <th>Keterangan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                {{-- Urut DFS top-down (1 direktorat & seluruh cabangnya penuh dulu, baru
                     direktorat berikutnya) — lihat StrukturOrganisasiVersiController::
                     dfsOrderSnapshots(), dipakai juga oleh export PDF. BUKAN $units biasa. --}}
                @forelse($unitsOrdered as $unit)
                @php $totalBawahan = $totals[$unit->unit_organisasi_id] ?? null; @endphp
                <tr>
                    <td style="font-weight:600;color:#111827;">{{ formatUnitLabel($unit->nama_unit, $unit->level) }}</td>
                    <td><span class="level-badge">{{ $unit->level }}</span></td>
                    <td>{{ $unit->parent_unit_organisasi_id ? ($namaByUnitId[$unit->parent_unit_organisasi_id] ?? '-') : '-' }}</td>
                    <td>{{ $unit->mc_formasi }}</td>
                    <td>{{ is_null($totalBawahan) ? '–' : $totalBawahan }}</td>
                    <td style="font-weight:700;color:#111827;">{{ $unit->mc_formasi + ($totalBawahan ?? 0) }}</td>
                    <td>{{ $unit->keterangan ?: '-' }}</td>
                    <td>
                        <a href="javascript:void(0)" class="org-history-btn" title="Lihat Riwayat Unit" x-data
                           @click="$store.riwayatOverlay.openPanel({{ $unit->unit_organisasi_id }}, {{ \Illuminate\Support\Js::from(formatUnitLabel($unit->nama_unit, $unit->level)) }})">
                            <svg viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="muted" style="text-align:center;padding:30px;">Belum ada unit di versi ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('organisasi.struktur.partials.riwayat-overlay-shell')

@endsection

@push('scripts')
{{-- Sebelumnya cuma di-load pas draft (utk modal finalisasi) — sekarang WAJIB selalu,
     krn overlay riwayat (dipicu dari kolom Aksi tabel di bawah) butuh Alpine apapun
     status versi-nya. --}}
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
    function closeToast() {
        const t = document.getElementById('toast');
        if (!t) return;
        t.classList.add('hiding');
        setTimeout(() => document.getElementById('toastWrap')?.remove(), 300);
    }
    window.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('toast')) setTimeout(() => closeToast(), 4000);
    });
</script>
@endpush
