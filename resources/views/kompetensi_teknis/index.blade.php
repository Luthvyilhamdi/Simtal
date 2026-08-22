@extends('layouts.app')
@section('title', 'Kompetensi Teknis')
@section('breadcrumb-parent', 'Organisasi')
@section('breadcrumb', 'Kompetensi Teknis')

{{--
    Search + filter SENGAJA VANILLA JS MURNI (BUKAN Alpine) — konsisten dgn keputusan yg
    sama di organisasi/job-profile/show.blade.php (Alpine sudah beberapa kali gagal utk
    kebutuhan search/filter serupa di project ini). Pola persis ditiru dari file itu:
    querySelectorAll + data-* attribute + inline style.display, tanpa x-data/x-show/x-model
    sama sekali. Halaman READ-ONLY (belum ada create/update/delete dari UI) — LogsActivity
    belum dipasang di controller, lihat TODO di KompetensiTeknisController.

    MASTER-DETAIL: 1 baris tabel = 1 POSISI (kombinasi unit + jenjang_jabatan + versi),
    bukan 1 baris = 1 kompetensi lagi (lihat KompetensiTeknisController::index()). Tombol
    "Detail" tiap baris buka overlay per-posisi via openPosisiOverlay() (endpoint BARU,
    posisiOverlay() — TERPISAH dari unitOverlay() yg dipakai icon Tree View, lihat catatan
    di overlay-shell.blade.php & controller). Search & filter Tipe tetap bisa narrow
    berdasar kompetensi DI DALAM posisi (data-search-text & data-komb-list menyimpan
    gabungan semua kompetensi di posisi itu), meski nama kompetensi tidak tampil di baris
    master.
--}}

@push('styles')
<style>
    .page-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;gap:12px;flex-wrap:wrap; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:4px; }

    .search-box { display:flex;align-items:center;gap:8px;background:white;border:1.5px solid #e5e7eb;border-radius:10px;padding:11px 16px;margin-bottom:12px; }
    .search-box:focus-within { border-color:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,.08); }
    .search-box svg { width:15px;height:15px;stroke:#9ca3af;fill:none;flex-shrink:0; }
    .search-box input { border:none;outline:none;font-size:13px;font-family:inherit;color:#111827;background:transparent;width:100%; }
    .search-box input::placeholder { color:#9ca3af; }

    .filter-row { display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px; }
    .filter-select { flex:1;min-width:170px;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:9px;font-size:12.5px;font-family:inherit;color:#111827;background:white; }
    .filter-select:focus { border-color:#16a34a;outline:none; }
    .result-count { font-size:11.5px;color:#6b7280;margin-bottom:10px; }

    .komtek-table-wrap { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);overflow-x:auto; }
    .komtek-table { width:100%;border-collapse:collapse;font-size:12.5px;min-width:920px; }
    .komtek-table thead th { text-align:left;padding:11px 14px;background:#fafaf8;border-bottom:1px solid #f3f4f6;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.4px;white-space:nowrap; }
    .komtek-table tbody td { padding:10px 14px;border-bottom:1px solid #f3f4f6;color:#374151;vertical-align:middle; }
    .komtek-table tbody tr:last-child td { border-bottom:none; }
    .komtek-table tbody tr:hover { background:#fafaf8; }
    .komtek-table .unit-name { font-weight:700;color:#111827; }

    .managerial-badge { display:inline-block;font-size:10px;font-weight:700;padding:2px 8px;border-radius:6px;white-space:nowrap; }
    .managerial-badge.ya { background:#dcfce7;color:#15803d; }
    .managerial-badge.tidak { background:#f3f4f6;color:#6b7280; }

    .jumlah-komp-badge { display:inline-block;font-size:11px;font-weight:700;padding:2px 9px;border-radius:20px;background:#f5f3ff;color:#7c3aed; }

    .btn-detail-posisi { display:inline-flex;align-items:center;gap:5px;border:1px solid #ddd6fe;background:#f5f3ff;color:#7c3aed;font-size:11.5px;font-weight:600;padding:6px 11px;border-radius:8px;cursor:pointer;font-family:inherit;white-space:nowrap; }
    .btn-detail-posisi:hover { background:#ede9fe; }
    .btn-detail-posisi svg { width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2; }

    .empty-row td { text-align:center;color:#9ca3af;padding:40px 0; }

    .btn-import { display:inline-flex;align-items:center;gap:6px;background:#15803d;color:white;padding:9px 16px;border-radius:9px;font-size:13px;font-weight:600;text-decoration:none;white-space:nowrap; }
    .btn-import:hover { background:#166534; }
    .btn-import svg { width:14px;height:14px;stroke:white;fill:none;stroke-width:2; }

    .success-banner { background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;border-radius:10px;padding:12px 16px;font-size:13px;margin-bottom:16px; }
</style>
@endpush

@section('content')

@if(session('success'))
<div class="success-banner">{{ session('success') }}</div>
@endif

<div class="page-header">
    <div>
        <div class="page-title">Kompetensi Teknis</div>
        <div class="page-sub">{{ $positions->count() }} posisi (kombinasi unit &times; jenjang &times; versi)</div>
    </div>
    <a href="{{ route('organisasi.kompetensi-teknis.import.create') }}" class="btn-import">
        <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        Import dari Excel
    </a>
</div>

<div class="search-box">
    <svg viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input type="text" id="kt-search" oninput="filterKompetensiTeknis()" placeholder="Cari nama unit atau nama kompetensi...">
</div>

<div class="filter-row">
    <select class="filter-select" id="kt-filter-unit" onchange="filterKompetensiTeknis()">
        <option value="">Semua Unit</option>
        @foreach($unitOptions as $opt)
            <option value="{{ $opt }}">{{ $opt }}</option>
        @endforeach
    </select>
    <select class="filter-select" id="kt-filter-jenjang" onchange="filterKompetensiTeknis()">
        <option value="">Semua Jenjang</option>
        @foreach($jenjangOptions as $opt)
            <option value="{{ $opt }}">{{ $opt }}</option>
        @endforeach
    </select>
    <select class="filter-select" id="kt-filter-komb" onchange="filterKompetensiTeknis()">
        <option value="">Semua Asal &amp; Prioritas</option>
        {{-- value tetap "asal-prioritas" (skema DB tidak berubah) — TEKS opsi disamakan dgn
             label badge tunggal yg dipakai di overlay (lihat UnitKompetensiTeknis::
             getPrioritasLabelAttribute()), supaya filter & badge konsisten istilah. --}}
        <option value="native-primary">Primary</option>
        <option value="native-secondary">Secondary</option>
        <option value="generic-secondary">Generic</option>
        <option value="generic-primary">Primary (Generic)</option>
    </select>
    <select class="filter-select" id="kt-filter-versi" onchange="filterKompetensiTeknis()">
        <option value="">Semua Versi</option>
        @foreach($versiList as $v)
            <option value="{{ $v->id }}">SK {{ $v->nomor_sk }}</option>
        @endforeach
    </select>
</div>

<div class="result-count" id="kt-result-count"></div>

<div class="komtek-table-wrap">
    <table class="komtek-table">
        <thead>
            <tr>
                <th>Nama Posisi</th>
                <th>Grade</th>
                <th>Nama Jobs</th>
                <th>Managerial</th>
                <th>Unit</th>
                <th>Versi</th>
                <th>Jumlah Kompetensi</th>
                <th>Detail</th>
            </tr>
        </thead>
        <tbody id="kt-table-body">
            @forelse($positions as $pos)
                @php
                    // Concat sederhana jenjang + nama unit — REPRODUKSI PERSIS penulisan
                    // "List Jabatan" asli di file sumber Excel (mis. "Officer Organisasi",
                    // "VP Organisasi & Manajemen Talenta"), jadi natural dibaca tanpa perlu
                    // kolom data terpisah.
                    $namaPosisi = $pos->jenjang_jabatan . ' ' . $pos->nama_unit;
                    $searchText = mb_strtolower($namaPosisi . ' ' . $pos->nama_unit . ' ' . $pos->kompetensi_names . ' ' . $pos->rumpun_asal_list);
                    $kombList   = implode(',', $pos->komb_list);
                @endphp
                <tr class="kt-row"
                    data-search-text="{{ $searchText }}"
                    data-unit="{{ $pos->nama_unit }}"
                    data-jenjang="{{ $pos->jenjang_jabatan }}"
                    data-komb-list="{{ $kombList }}"
                    data-versi="{{ $pos->struktur_organisasi_versi_id }}">
                    <td><span class="unit-name">{{ $namaPosisi }}</span></td>
                    <td>{{ $pos->grade ?? '–' }}</td>
                    <td>{{ $pos->nama_jobs }}</td>
                    <td><span class="managerial-badge {{ $pos->managerial ? 'ya' : 'tidak' }}">{{ $pos->managerial ? 'Managerial' : 'Non-Managerial' }}</span></td>
                    <td>
                        {{ formatUnitLabel($pos->nama_unit, $pos->unit_level) }}
                    </td>
                    <td>SK {{ $pos->nomor_sk }}</td>
                    <td><span class="jumlah-komp-badge">{{ $pos->jumlah_kompetensi }} kompetensi</span></td>
                    <td>
                        <button type="button" class="btn-detail-posisi"
                            onclick="openPosisiOverlay({{ $pos->unit_organisasi_id }}, {{ \Illuminate\Support\Js::from($pos->jenjang_jabatan) }}, {{ \Illuminate\Support\Js::from($namaPosisi) }}, {{ $pos->struktur_organisasi_versi_id }})">
                            <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            Detail
                        </button>
                    </td>
                </tr>
            @empty
                <tr class="empty-row"><td colspan="8">Belum ada data Kompetensi Teknis.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@include('kompetensi_teknis.partials.overlay-shell')

@endsection

@push('scripts')
<script>
    // VANILLA JS MURNI, tanpa Alpine — pola SAMA PERSIS dgn filterJobProfiles() di
    // organisasi/job-profile/show.blade.php.
    function filterKompetensiTeknis() {
        const search   = document.getElementById('kt-search').value.toLowerCase().trim();
        const unit     = document.getElementById('kt-filter-unit').value;
        const jenjang  = document.getElementById('kt-filter-jenjang').value;
        const komb     = document.getElementById('kt-filter-komb').value;
        const versi    = document.getElementById('kt-filter-versi').value;

        let visibleCount = 0;
        document.querySelectorAll('.kt-row').forEach(row => {
            const matchSearch  = search === '' || row.dataset.searchText.includes(search);
            const matchUnit    = unit === '' || row.dataset.unit === unit;
            const matchJenjang = jenjang === '' || row.dataset.jenjang === jenjang;
            // komb_list = SET kombinasi "asal-prioritas" yg ADA di posisi ini (bisa lebih
            // dari 1, mis. "native-primary,generic-secondary") — posisi ikut match kalau
            // kombinasi yg dipilih ADA di dalamnya, walau tidak ditampilkan sbg kolom
            // sendiri di baris master.
            const matchKomb    = komb === '' || row.dataset.kombList.split(',').includes(komb);
            const matchVersi   = versi === '' || row.dataset.versi === versi;
            const visible = matchSearch && matchUnit && matchJenjang && matchKomb && matchVersi;
            row.style.display = visible ? '' : 'none';
            if (visible) visibleCount++;
        });

        const countEl = document.getElementById('kt-result-count');
        if (countEl) countEl.textContent = visibleCount + ' posisi ditemukan';
    }

    // Auto-init filter dari query string ?versi=&rumpun= — dipakai link redirect stlh
    // commit Step 3 alur import (organisasi.kompetensi-teknis.import.review.commit)
    // supaya user langsung lihat hasil importnya. "rumpun" mengisi search box (bukan
    // filter <select> tersendiri, krn rumpun_asal itu atribut per-kompetensi, bukan
    // per-posisi — 1 posisi bisa punya kompetensi dari rumpun campuran) — search text
    // per baris SUDAH termasuk rumpun_asal (lihat $searchText di atas).
    window.addEventListener('DOMContentLoaded', () => {
        const params = new URLSearchParams(window.location.search);
        const versi  = params.get('versi');
        const rumpun = params.get('rumpun');

        if (versi) {
            const versiSelect = document.getElementById('kt-filter-versi');
            if (versiSelect && [...versiSelect.options].some(o => o.value === versi)) {
                versiSelect.value = versi;
            }
        }
        if (rumpun) {
            document.getElementById('kt-search').value = rumpun;
        }

        filterKompetensiTeknis();
    });
</script>
@endpush
