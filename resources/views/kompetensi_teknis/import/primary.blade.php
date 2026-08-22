@extends('layouts.app')
@section('title', 'Pilih Primary — Import Kompetensi Teknis')
@section('breadcrumb-parent', 'Kompetensi Teknis')
@section('breadcrumb', 'Pilih Primary')

{{--
    STEP 3 dari 4 alur import self-service — GANTI TOTAL dari deteksi border cell Excel
    (tidak reliable, beda file beda orang bikin border-nya) jadi pemilihan manual. Centang
    = prioritas='primary', tidak dicentang = 'secondary' (default). asal (Native/Generic)
    HANYA badge konteks kecil di sini, TIDAK menentukan bisa/tidaknya dicentang — kombinasi
    Generic+Primary sekarang valid krn keputusan manusia (dulu mustahil dari border).

    0 dicentang tetap valid (tidak wajib ada primary) — makanya TIDAK ada validasi minimal
    di form ini, beda dgn mapping unit yg wajib semua terisi.

    Search/filter SENGAJA vanilla JS murni (bukan Alpine), konsisten dgn keputusan final
    project ini di seluruh alur import — style.display per baris, tanpa Alpine sama sekali.

    Bulk-select per kompetensi per unit: PURE UI HELPER sisi klien, TIDAK ada perubahan
    controller/backend sama sekali — checkbox bulk cuma nge-toggle .checked checkbox
    individual yg match data-unit+data-kompetensi (dicocokkan via JS dataset comparison,
    BUKAN CSS selector string dgn value ditempel langsung, supaya aman dari nama kompetensi
    yg mengandung karakter spesial spt kutip/kurung). Checkbox individual TETAP bisa
    di-override manual sesudahnya (checkbox bulk otomatis balik ke state indeterminate
    kalau ada yg di-uncheck manual sebagian) — submit form tetap kirim SEMUA checkbox
    primary_row_ids[] yg tercentang apa adanya, terlepas dari cara centangnya.
--}}

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color .12s; }
    .back-link:hover { color:#15803d; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }
    .page-header { margin-bottom:16px; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:4px; }

    .progress-note { display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:10px;font-size:13px;font-weight:600;margin-bottom:16px;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe; }

    .filter-box { display:flex;align-items:center;gap:8px;background:white;border:1.5px solid #e5e7eb;border-radius:10px;padding:10px 14px;margin-bottom:14px; }
    .filter-box svg { width:14px;height:14px;stroke:#9ca3af;fill:none;flex-shrink:0; }
    .filter-box input { border:none;outline:none;font-size:13px;font-family:inherit;color:#111827;background:transparent;width:100%; }
    .filter-box input::placeholder { color:#9ca3af; }
    .result-count { font-size:11.5px;color:#6b7280;margin-bottom:14px; }

    .unit-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:18px 22px;margin-bottom:14px; }
    .unit-card-title { font-size:14.5px;font-weight:800;color:#111827;margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid #f3f4f6; }

    .bulk-select-section { background:#fafaf8;border:1px solid #f3f4f6;border-radius:10px;padding:12px 14px;margin-bottom:16px; }
    .bulk-select-title { font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.4px;margin-bottom:9px; }
    .bulk-select-list { display:flex;flex-wrap:wrap;gap:8px; }
    .bulk-select-item { display:inline-flex;align-items:center;gap:6px;background:white;border:1px solid #e5e7eb;border-radius:20px;padding:5px 12px 5px 10px;font-size:12px;font-weight:600;color:#374151;cursor:pointer;user-select:none; }
    .bulk-select-item:hover { border-color:#bbf7d0;background:#f0fdf4; }
    .bulk-select-item input[type=checkbox] { width:15px;height:15px;cursor:pointer;accent-color:#15803d;flex-shrink:0; }

    .jenjang-block { margin-bottom:16px; }
    .jenjang-block:last-child { margin-bottom:0; }
    .jenjang-block-title { font-size:12.5px;font-weight:700;color:#7c3aed;background:#f5f3ff;display:inline-block;padding:3px 11px;border-radius:20px;margin-bottom:8px; }

    .primary-item-row { display:flex;align-items:center;gap:10px;padding:8px 12px;border:1px solid #f3f4f6;border-radius:8px;margin-bottom:6px;font-size:12.5px; }
    .primary-item-row:last-child { margin-bottom:0; }
    .primary-item-row input[type=checkbox] { width:16px;height:16px;flex-shrink:0;accent-color:#15803d;cursor:pointer; }
    .primary-item-row .nama { font-weight:600;color:#111827;flex:1; }
    .primary-item-row .level-text { color:#6b7280;white-space:nowrap; }
    .asal-badge { display:inline-block;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;white-space:nowrap;text-transform:capitalize; }
    .asal-badge.native { background:#f3f4f6;color:#374151; }
    .asal-badge.generic { background:#fef3c7;color:#b45309; }

    .form-actions-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:20px 28px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;position:sticky;bottom:16px; }
    .btn-cancel { display:inline-flex;align-items:center;gap:8px;background:white;color:#374151;padding:10px 20px;border-radius:9px;font-size:13px;font-weight:600;border:1.5px solid #e5e7eb;text-decoration:none;transition:all .15s; }
    .btn-cancel:hover { background:#f9fafb; }
    .btn-save { display:inline-flex;align-items:center;gap:8px;background:#15803d;color:white;padding:10px 24px;border-radius:9px;font-size:13px;font-weight:600;border:none;cursor:pointer;font-family:inherit;transition:all .15s; }
    .btn-save:hover { background:#166534; }
    .btn-save svg { width:14px;height:14px;stroke:white;fill:none;stroke-width:2; }
</style>
@endpush

@section('content')
<a href="{{ route('organisasi.kompetensi-teknis.import.mapping', ['token' => $token]) }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Mapping Unit
</a>

<div class="page-header">
    <div class="page-title">Pilih Kompetensi Primary</div>
    <div class="page-sub">
        Step 3 dari 4 &middot; Job Family: <strong>{{ $payload['job_family_nama'] }}</strong>
        &middot; Versi acuan: <strong>SK {{ $versi->nomor_sk }}</strong>
    </div>
</div>

<div class="progress-note">
    ☑️ Centang kompetensi yang jadi <strong>Primary</strong> (selection criteria) untuk tiap jenjang. Tidak dicentang = tetap Secondary. Boleh 0 dicentang. Native maupun Generic sama-sama boleh dipilih Primary.
</div>

<div class="filter-box">
    <svg viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input type="text" id="primary-quick-filter" oninput="filterPrimaryRows()" placeholder="Filter nama unit atau nama kompetensi...">
</div>
<div class="result-count" id="primary-result-count"></div>

<form method="POST" action="{{ route('organisasi.kompetensi-teknis.import.primary.store', ['token' => $token]) }}">
    @csrf

    @foreach($unitGroups as $ug)
        @php
            // Gabungan kompetensi unik lintas SEMUA jenjang dalam unit ini — dasar tombol
            // bulk-select "centang semua jenjang utk kompetensi ini di unit ini".
            $kompetensiUnikDiUnit = collect($ug['jenjang_groups'])
                ->flatMap(fn ($jg) => collect($jg['items'])->pluck('nama_kompetensi'))
                ->unique()
                ->sort()
                ->values();
        @endphp
        <div class="unit-card" data-unit-card>
            <div class="unit-card-title">{{ $ug['nama_unit_label'] }}</div>

            @if($kompetensiUnikDiUnit->isNotEmpty())
                <div class="bulk-select-section">
                    <div class="bulk-select-title">Pilih Cepat — centang semua jenjang di unit ini sekaligus</div>
                    <div class="bulk-select-list">
                        @foreach($kompetensiUnikDiUnit as $namaKomp)
                            <label class="bulk-select-item">
                                <input type="checkbox" class="bulk-select-checkbox"
                                       data-bulk-unit="{{ $ug['unit_organisasi_id'] }}"
                                       data-bulk-kompetensi="{{ $namaKomp }}"
                                       onclick="toggleBulkKompetensi(this)">
                                {{ $namaKomp }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            @foreach($ug['jenjang_groups'] as $jg)
                <div class="jenjang-block">
                    <div class="jenjang-block-title">{{ $jg['jenjang_jabatan'] }}</div>

                    @foreach($jg['items'] as $item)
                        <div class="primary-item-row" data-primary-row
                             data-filter-text="{{ mb_strtolower($ug['nama_unit_label'] . ' ' . $item['nama_kompetensi']) }}">
                            <input type="checkbox" name="primary_row_ids[]" value="{{ $item['row_id'] }}" {{ $item['checked'] ? 'checked' : '' }}
                                   class="primary-item-checkbox"
                                   data-unit="{{ $ug['unit_organisasi_id'] }}"
                                   data-kompetensi="{{ $item['nama_kompetensi'] }}">
                            <span class="nama">{{ $item['nama_kompetensi'] }}</span>
                            <span class="asal-badge {{ $item['asal'] }}">{{ ucfirst($item['asal']) }}</span>
                            <span class="level-text">Level {{ $item['level'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    @endforeach

    <div class="form-actions-card">
        <a href="{{ route('organisasi.kompetensi-teknis.import.mapping', ['token' => $token]) }}" class="btn-cancel">Batal</a>
        <button type="submit" class="btn-save">
            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            Simpan Pilihan Primary
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    // VANILLA JS MURNI, tanpa Alpine — filter per baris (style.display), tidak menyentuh
    // isi checkbox sama sekali.
    function filterPrimaryRows() {
        const q = document.getElementById('primary-quick-filter').value.toLowerCase().trim();
        let visibleCount = 0;

        document.querySelectorAll('[data-primary-row]').forEach(row => {
            const visible = q === '' || row.dataset.filterText.includes(q);
            row.style.display = visible ? '' : 'none';
            if (visible) visibleCount++;
        });

        // Unit card ikut disembunyikan kalau SEMUA baris di dalamnya tidak match (murni
        // rapi tampilan, tidak mempengaruhi data yg tersimpan sama sekali).
        document.querySelectorAll('[data-unit-card]').forEach(card => {
            const anyVisible = [...card.querySelectorAll('[data-primary-row]')].some(r => r.style.display !== 'none');
            card.style.display = anyVisible ? '' : 'none';
        });

        const countEl = document.getElementById('primary-result-count');
        if (countEl) countEl.textContent = visibleCount + ' baris kompetensi ditemukan';
    }

    window.addEventListener('DOMContentLoaded', () => filterPrimaryRows());

    // ===== Bulk-select per kompetensi per unit — VANILLA JS MURNI, tanpa Alpine =====
    // Pure UI helper: cuma nge-toggle .checked checkbox individual yg match unit+kompetensi
    // (dicocokkan via JS dataset, BUKAN CSS selector string dgn value ditempel langsung,
    // supaya aman dari nama kompetensi yg mengandung karakter spesial). Submit form tetap
    // kirim checkbox primary_row_ids[] yg tercentang apa adanya — tidak ada logic backend
    // yg berubah krn fitur ini.
    function toggleBulkKompetensi(bulkCheckbox) {
        const unitId = bulkCheckbox.dataset.bulkUnit;
        const komp   = bulkCheckbox.dataset.bulkKompetensi;
        // Klik native sudah nge-toggle .checked bulkCheckbox itu sendiri (termasuk resolve
        // dari state indeterminate -> checked) — tinggal disamakan ke semua target.
        const shouldCheck = bulkCheckbox.checked;

        document.querySelectorAll('.primary-item-checkbox').forEach(cb => {
            if (cb.dataset.unit === unitId && cb.dataset.kompetensi === komp) {
                cb.checked = shouldCheck;
            }
        });
    }

    // Sinkronkan tampilan checkbox bulk (checked/unchecked/indeterminate) berdasar state
    // checkbox individual SEKARANG — dipanggil saat load & tiap kali checkbox individual
    // manapun berubah (termasuk uncheck manual satu2 stlh pakai bulk-select).
    function refreshBulkCheckboxState(bulkCheckbox) {
        const unitId = bulkCheckbox.dataset.bulkUnit;
        const komp   = bulkCheckbox.dataset.bulkKompetensi;
        const targets = [...document.querySelectorAll('.primary-item-checkbox')]
            .filter(cb => cb.dataset.unit === unitId && cb.dataset.kompetensi === komp);

        if (targets.length === 0) return;

        const checkedCount = targets.filter(cb => cb.checked).length;
        bulkCheckbox.checked = checkedCount === targets.length;
        bulkCheckbox.indeterminate = checkedCount > 0 && checkedCount < targets.length;
    }

    function refreshAllBulkCheckboxes() {
        document.querySelectorAll('.bulk-select-checkbox').forEach(refreshBulkCheckboxState);
    }

    window.addEventListener('DOMContentLoaded', () => {
        refreshAllBulkCheckboxes();

        // Checkbox individual di-uncheck/dicentang manual -> update tampilan bulk terkait.
        document.querySelectorAll('.primary-item-checkbox').forEach(cb => {
            cb.addEventListener('change', refreshAllBulkCheckboxes);
        });
    });
</script>
@endpush
