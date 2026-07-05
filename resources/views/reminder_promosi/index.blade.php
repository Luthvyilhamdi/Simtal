@extends('layouts.app')
@section('title', 'Reminder Promosi')
@section('breadcrumb-parent', 'Manajemen Talenta')
@section('breadcrumb', 'Reminder Promosi')

@php
    $jenisLabel = [
        'naik_pg'   => 'Naik Person Grade',
        'naik_jg'   => 'Naik Job Grade',
        'naik_band' => 'Naik Band',
    ];
    $jenisBadge = [
        'naik_pg'   => 'badge-blue',
        'naik_jg'   => 'badge-amber',
        'naik_band' => 'badge-purple',
    ];
@endphp

@push('styles')
<style>
    .page-header { margin-bottom:20px; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:4px; }

    .note-info { background:#eff6ff;border:1px solid #bfdbfe;border-radius:11px;padding:12px 16px;font-size:12.5px;color:#1e40af;margin-bottom:16px;display:flex;gap:9px;align-items:flex-start; }
    .note-info svg { width:16px;height:16px;stroke:#2563eb;fill:none;stroke-width:2;flex-shrink:0;margin-top:1px; }

    .summary-grid { display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:18px; }
    .sum-card { background:white;border-radius:14px;border:1px solid var(--card-border);padding:18px 20px;box-shadow:var(--card-shadow);display:flex;align-items:center;gap:14px; }
    .sum-ico { width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .sum-ico svg { width:22px;height:22px;fill:none;stroke-width:1.9; }
    .sum-ico.green { background:#dcfce7; } .sum-ico.green svg { stroke:#15803d; }
    .sum-ico.amber { background:#fef3c7; } .sum-ico.amber svg { stroke:#b45309; }
    .sum-ico.gray  { background:#f3f4f6; } .sum-ico.gray svg { stroke:#6b7280; }
    .sum-num { font-size:26px;font-weight:800;color:#111827;line-height:1; }
    .sum-label { font-size:12px;color:#6b7280;margin-top:3px;font-weight:500; }

    .toolbar { background:white;border-radius:12px;border:1px solid var(--card-border);padding:12px 16px;margin-bottom:16px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;box-shadow:var(--card-shadow); }
    .toolbar select { border:1px solid #e4e7ec;border-radius:9px;padding:8px 12px;font-size:13px;outline:none;background:#fcfcfd;color:#374151;cursor:pointer; }
    .toolbar select:focus { border-color:#16a34a; }
    /* Search kecil — samakan dengan karyawan/index */
    .search-mini { display:flex;align-items:center;gap:8px;background:white;border:1px solid #e5e7eb;border-radius:9px;padding:8px 12px;width:240px;transition:border-color .15s; }
    .search-mini:focus-within { border-color:#15803d;box-shadow:0 0 0 2px rgba(21,128,61,0.1); }
    .search-mini svg { width:14px;height:14px;stroke:#9ca3af;fill:none;flex-shrink:0; }
    .search-mini input { border:none;outline:none;font-size:13px;font-family:inherit;color:#111827;background:transparent;width:100%; }
    .search-mini input::placeholder { color:#9ca3af; }
    .clear-btn { background:none;border:none;cursor:pointer;color:#9ca3af;font-size:15px;line-height:1;padding:0;display:none;flex-shrink:0; }
    .clear-btn.visible { display:block; }
    .toolbar .tb-reset { font-size:12.5px;color:#6b7280;text-decoration:none;padding:8px 10px; }
    .toolbar .tb-reset:hover { color:#15803d; }

    .card-table { background:white;border-radius:14px;border:1px solid var(--card-border);overflow:hidden;box-shadow:var(--card-shadow); }
    table.rm { width:100%;border-collapse:collapse;font-size:13px;min-width:1040px; }
    /* Kolom pendek: jaga satu baris biar rapi (tabel scroll horizontal bila sempit) */
    table.rm td.col-nowrap { white-space:nowrap; }
    /* Kolom jabatan (panjang): boleh turun baris, diberi lebar wajar */
    table.rm td.col-jab { white-space:normal;min-width:260px;max-width:440px; }
    table.rm thead th { background:#f9fafb;text-align:left;font-size:11px;font-weight:700;color:#98a2b3;text-transform:uppercase;letter-spacing:.4px;padding:11px 16px;white-space:nowrap;border-bottom:1px solid #eef0f2; }
    table.rm tbody td { padding:12px 16px;border-bottom:1px solid #f5f6f7;vertical-align:middle; }
    table.rm tbody tr:last-child td { border-bottom:none; }
    table.rm tbody tr:hover { background:#fcfdfc; }
    .emp-name { font-weight:700;color:#111827;white-space:nowrap; }
    .emp-nik { font-size:11.5px;color:#9ca3af;margin-top:1px; }
    .sl-badge-wrap { margin-top:6px; }
    .emp-jab { color:#374151; }
    .emp-unit { font-size:11.5px;color:#9ca3af;margin-top:1px; }

    .badge { display:inline-flex;align-items:center;gap:4px;padding:3px 11px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap; }
    .badge-green  { background:#dcfce7;color:#15803d; }
    .badge-amber  { background:#fef3c7;color:#b45309; }
    .badge-blue   { background:#eff6ff;color:#1d4ed8; }
    .badge-purple { background:#f5f3ff;color:#7c3aed; }
    .badge-gray   { background:#f3f4f6;color:#374151; }

    .status-now  { color:#15803d;font-weight:700; }
    .status-soon { color:#b45309;font-weight:700; }
    .status-sub  { font-size:11px;color:#9ca3af;margin-top:2px;white-space:nowrap; }

    .btn-usul { display:inline-flex;align-items:center;gap:6px;background:#15803d;color:white;padding:7px 13px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;white-space:nowrap;transition:background .15s; }
    .btn-usul:hover { background:#166534; }
    .btn-usul svg { width:13px;height:13px;stroke:white;fill:none;stroke-width:2; }

    .empty-state { padding:56px 20px;text-align:center; }
    .empty-state svg { width:46px;height:46px;stroke:#d1d5db;fill:none;stroke-width:1.5;margin:0 auto 14px; }
    .empty-title { font-size:15px;font-weight:600;color:#374151;margin-bottom:6px; }
    .empty-sub { font-size:13px;color:#9ca3af;max-width:440px;margin:0 auto; }

    .table-scroll { overflow-x:auto;-webkit-overflow-scrolling:touch; }
    @media (max-width:768px) {
        .summary-grid { grid-template-columns:1fr; }
        table.rm { min-width:760px; }
    }
</style>
@endpush

@section('content')

<div class="page-header">
    <div class="page-title">Reminder Daftar Promosi Karyawan</div>
    <div class="page-sub">Karyawan yang sudah / akan memenuhi Masa Dinas Grade dalam {{ $windowBulan }} bulan ke depan.</div>
</div>

<div class="note-info">
    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
    <div>
        Daftar ini <strong>hanya pengingat</strong> berdasarkan MDG &mdash; tidak mengubah data. Ambang <strong>selaras dengan Data Talent</strong>:
        yang <strong>shortlist{{ $shortlistPeriode ? ' '.$shortlistPeriode : '' }}</strong> (ditandai ★) pakai ambang longgar (Band 2 thn, JG 1 thn), lainnya normal (Band 3 thn, JG 2 thn, PG 1 thn).
        @if($shortlistPeriode)<br>Shortlist diambil dari <strong>periode terbaru yang tersedia ({{ $shortlistPeriode }})</strong>.@endif
        Hanya karyawan yang <strong>TMT grade-nya terisi</strong> yang bisa dihitung. Keputusan promosi tetap melalui Usulan Promosi.
        <br>Karyawan yang <strong>usulan promosinya sedang berjalan atau sudah lulus otomatis disembunyikan</strong> (yang ditolak/tidak lulus tetap tampil).
    </div>
</div>

@if(($disembunyikan ?? 0) > 0)
<div class="note-info" style="background:#f0fdf4;border-color:#bbf7d0;color:#166534;">
    <svg viewBox="0 0 24 24" style="stroke:#16a34a"><path d="M20 6 9 17l-5-5"/></svg>
    <div><strong>{{ $disembunyikan }}</strong> karyawan disembunyikan dari daftar karena sudah dibuatkan usulan promosi (sedang diproses / lulus).</div>
</div>
@endif

{{-- SUMMARY --}}
<div class="summary-grid">
    <div class="sum-card">
        <div class="sum-ico green"><svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
        <div>
            <div class="sum-num">{{ $countNow }}</div>
            <div class="sum-label">Sudah eligible sekarang</div>
        </div>
    </div>
    <div class="sum-card">
        <div class="sum-ico amber"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
        <div>
            <div class="sum-num">{{ $countSoon }}</div>
            <div class="sum-label">Akan eligible ≤ {{ $windowBulan }} bulan</div>
        </div>
    </div>
    <div class="sum-card">
        <div class="sum-ico gray"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
        <div>
            <div class="sum-num">{{ $totalDinilai }}</div>
            <div class="sum-label">Karyawan dinilai (punya TMT)</div>
        </div>
    </div>
</div>

{{-- FILTER --}}
<div class="toolbar">
    <div class="search-mini" id="searchWrap">
        <svg viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" id="searchInput" placeholder="Cari nama / NIK..." autocomplete="off">
        <button type="button" class="clear-btn" id="clearBtn" onclick="clearMdgSearch()">×</button>
    </div>
    <select id="filterDir" onchange="applyMdgFilter('direktorat', this.value)">
        <option value="">Semua Direktorat</option>
        @foreach($direktorats as $d)
            <option value="{{ $d->nama_direktorat }}" {{ $direktoratFilter === $d->nama_direktorat ? 'selected' : '' }}>{{ $d->nama_direktorat }}</option>
        @endforeach
    </select>
    <select id="filterJenis" onchange="applyMdgFilter('jenis', this.value)">
        <option value="">Semua Jenis Kenaikan</option>
        <option value="naik_pg"   {{ $jenisFilter==='naik_pg'   ? 'selected' : '' }}>Naik Person Grade</option>
        <option value="naik_jg"   {{ $jenisFilter==='naik_jg'   ? 'selected' : '' }}>Naik Job Grade</option>
        <option value="naik_band" {{ $jenisFilter==='naik_band' ? 'selected' : '' }}>Naik Band</option>
    </select>
    @if($direktoratFilter || $jenisFilter)
        <a href="{{ route('reminder_promosi.index') }}" class="tb-reset">✕ Reset filter</a>
    @endif
    <span style="margin-left:auto;font-size:12px;color:#9ca3af" id="resultCount">{{ count($items) }} hasil</span>
</div>

{{-- TABEL --}}
<div class="card-table">
    @if(count($items) === 0)
        <div class="empty-state">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <div class="empty-title">Belum ada yang mendekati eligible</div>
            <div class="empty-sub">
                Tidak ada karyawan yang sudah/akan memenuhi MDG dalam {{ $windowBulan }} bulan ke depan pada filter ini.
                Pastikan <strong>TMT Job Grade / Person Grade</strong> karyawan sudah terisi agar bisa dihitung.
            </div>
        </div>
    @else
    <div class="table-scroll">
        <table class="rm">
            <thead>
                <tr>
                    <th>Karyawan</th>
                    <th>Jabatan &amp; Unit</th>
                    <th>Band / Grade</th>
                    <th>Rencana Kenaikan</th>
                    <th>Status</th>
                    <th style="text-align:right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $i)
                @php $k = $i['karyawan']; $sk = $i['sk']; @endphp
                <tr class="rm-row" data-search="{{ strtolower($k->nama.' '.$k->nik) }}">
                    <td>
                        <div class="emp-name">{{ $k->nama }}</div>
                        <div class="emp-nik">NIK {{ $k->nik }}</div>
                        @if($i['is_shortlist'])
                            <div class="sl-badge-wrap"><span class="badge badge-green" title="Shortlist Talent Pool {{ $shortlistPeriode }}">★ Shortlist{{ $shortlistPeriode ? ' '.$shortlistPeriode : '' }}</span></div>
                        @endif
                    </td>
                    <td class="col-jab">
                        <div class="emp-jab">{{ $k->jabatan_saat_ini ?: ($k->jabatan->nama_jabatan ?? '-') }}</div>
                        <div class="emp-unit">{{ $k->direktorat->nama_direktorat ?? '-' }}</div>
                    </td>
                    <td class="col-nowrap">
                        <span class="badge badge-green">{{ $k->band }}</span>
                        <span class="badge badge-gray" style="margin-left:3px">JG {{ $k->jobGrade->job_grade ?? '-' }}</span>
                    </td>
                    <td class="col-nowrap">
                        <span class="badge {{ $jenisBadge[$sk['status']] ?? 'badge-gray' }}">{{ $jenisLabel[$sk['status']] ?? $sk['status'] }}</span>
                        <div class="status-sub">{{ $sk['label'] }}</div>
                    </td>
                    <td class="col-nowrap">
                        @if($i['eligible_now'])
                            <div class="status-now">✅ Eligible sekarang</div>
                            <div class="status-sub">syarat MDG terpenuhi</div>
                        @else
                            <div class="status-soon">⏳ ± {{ $i['sisa'] }} bulan lagi</div>
                            <div class="status-sub">menuju syarat terlama</div>
                        @endif
                    </td>
                    <td style="text-align:right">
                        <a href="{{ route('usulan_promosi.create') }}" class="btn-usul" title="Buat usulan promosi untuk {{ $k->nama }}">
                            <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Buat Usulan
                        </a>
                    </td>
                </tr>
                @endforeach
                <tr id="noMatchRow" style="display:none">
                    <td colspan="6" style="text-align:center;padding:30px;color:#9ca3af;font-size:13px">Tidak ada karyawan yang cocok dengan pencarian.</td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
    // Navigasi filter direktorat/jenis (reload dengan query, pertahankan filter lain)
    function applyMdgFilter(key, val) {
        var u = new URL(window.location.href);
        if (val) u.searchParams.set(key, val); else u.searchParams.delete(key);
        window.location.href = u.toString();
    }

    (function () {
        var input   = document.getElementById('searchInput');
        var clear   = document.getElementById('clearBtn');
        var rows    = Array.prototype.slice.call(document.querySelectorAll('.rm-row'));
        var noMatch = document.getElementById('noMatchRow');
        var countEl = document.getElementById('resultCount');
        if (!input) return;

        function run() {
            var q = input.value.trim().toLowerCase();
            clear.classList.toggle('visible', q.length > 0);
            var shown = 0;
            rows.forEach(function (r) {
                var match = r.getAttribute('data-search').indexOf(q) !== -1;
                r.style.display = match ? '' : 'none';
                if (match) shown++;
            });
            if (noMatch) noMatch.style.display = (rows.length > 0 && shown === 0) ? '' : 'none';
            if (countEl) countEl.textContent = shown + ' hasil';
        }

        // Real-time: langsung saring saat mengetik (tanpa Enter)
        input.addEventListener('input', run);
        window.clearMdgSearch = function () { input.value = ''; run(); input.focus(); };
    })();
</script>
@endpush

@push('scripts')
<script>
    (function () {
        var input = document.getElementById('searchInput');
        var clear = document.getElementById('clearBtn');
        if (!input) return;
        input.addEventListener('input', function () {
            clear.classList.toggle('visible', this.value.trim().length > 0);
        });
    })();
    // Kosongkan pencarian lalu submit (kembali ke daftar penuh)
    function clearMdgSearch(btn) {
        var input = document.getElementById('searchInput');
        input.value = '';
        btn.closest('form').submit();
    }
</script>
@endpush
