@extends('layouts.app')
@section('title', 'Export Builder')
@section('breadcrumb', 'Export Builder')

@push('styles')
<style>
    .page-header { margin-bottom:20px; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:3px; }

    .eb-grid { display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start; }
    @media (max-width:900px){ .eb-grid{ grid-template-columns:1fr; } }

    .card { background:#fff;border:1px solid var(--card-border);border-radius:var(--radius);box-shadow:var(--card-shadow);padding:18px 20px; }
    .card + .card { margin-top:16px; }
    .card-title { font-size:13px;font-weight:700;color:#374151;margin-bottom:12px;display:flex;align-items:center;gap:8px; }

    .col-group { margin-bottom:14px; }
    .col-group-label { font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#15803d;margin-bottom:8px; }
    .col-items { display:grid;grid-template-columns:1fr 1fr;gap:6px 14px; }
    @media (max-width:560px){ .col-items{ grid-template-columns:1fr; } }
    .col-check { display:flex;align-items:center;gap:8px;font-size:13px;color:#374151;cursor:pointer;padding:4px 6px;border-radius:6px; }
    .col-check:hover { background:#f0fdf4; }
    .col-check input { width:15px;height:15px;accent-color:#15803d;cursor:pointer; }

    .field { margin-bottom:14px; }
    .field label { display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:5px; }
    .field select, .field textarea { width:100%;border:1px solid #d1d5db;border-radius:8px;padding:8px 10px;font-size:13px;background:#fff;font-family:inherit; }
    .field textarea { resize:vertical;line-height:1.5; }
    .field select:focus, .field textarea:focus { outline:none;border-color:#15803d;box-shadow:0 0 0 2px rgba(21,128,61,.1); }

    .hint { font-size:11px;color:#9ca3af;margin-top:4px; }
    .err { font-size:12px;color:#dc2626;margin-top:6px; }

    /* Pemilih karyawan (cari → tambah → chip) */
    .picker { display:flex;gap:8px;align-items:stretch; }
    .picker .pk-wrap { position:relative;flex:1; }
    .picker input { width:100%;border:1px solid #d1d5db;border-radius:8px;padding:8px 10px;font-size:13px;font-family:inherit; }
    .picker input:focus { outline:none;border-color:#15803d;box-shadow:0 0 0 2px rgba(21,128,61,.1); }
    .pk-add { border:none;background:#15803d;color:#fff;border-radius:8px;padding:0 14px;font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap; }
    .pk-add:hover { background:#166534; }
    .suggest { position:absolute;z-index:20;left:0;right:0;top:calc(100% + 4px);background:#fff;border:1px solid #e5e7eb;border-radius:8px;box-shadow:0 8px 24px rgba(16,24,40,.12);max-height:230px;overflow-y:auto; }
    .suggest-item { padding:8px 10px;font-size:12.5px;cursor:pointer;display:flex;justify-content:space-between;gap:8px;border-bottom:1px solid #f3f4f6; }
    .suggest-item:last-child { border-bottom:none; }
    .suggest-item:hover, .suggest-item.active { background:#f0fdf4; }
    .chips { display:flex;flex-wrap:wrap;gap:6px;margin-top:8px; }
    .chip { display:inline-flex;align-items:center;gap:6px;background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;border-radius:999px;padding:3px 6px 3px 10px;font-size:12px;font-weight:500; }
    .chip .cx { border:none;background:#d1fae5;color:#15803d;border-radius:50%;width:16px;height:16px;line-height:14px;font-size:13px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center; }
    .chip .cx:hover { background:#15803d;color:#fff; }
    .chips-meta { font-size:11px;color:#6b7280;margin-top:6px;display:none; }

    .btn-row { display:flex;gap:10px;margin-top:6px; }
    .btn { flex:1;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:11px 16px;border-radius:9px;font-size:13px;font-weight:600;border:none;cursor:pointer;transition:.15s; }
    .btn svg { width:16px;height:16px;fill:none;stroke:currentColor;stroke-width:2; }
    .btn-excel { background:#15803d;color:#fff; } .btn-excel:hover { background:#166534; }
    .btn-pdf { background:#b91c1c;color:#fff; } .btn-pdf:hover { background:#991b1b; }
    .btn-preview { background:#fff;color:#15803d;border:1.5px solid #15803d; } .btn-preview:hover { background:#f0fdf4; }
    .btn:disabled { opacity:.6;cursor:not-allowed; }

    .prev-table { width:100%;border-collapse:collapse;font-size:12px; }
    .prev-table th, .prev-table td { border:1px solid #e5e7eb;padding:6px 9px;text-align:left;white-space:nowrap; }
    .prev-table th { background:#15803d;color:#fff;position:sticky;top:0;font-weight:600; }
    .prev-table tr:nth-child(even) td { background:#f9fafb; }

    .toolbar { display:flex;gap:12px;margin-bottom:14px; }
    .link-btn { font-size:12px;color:#15803d;background:none;border:none;cursor:pointer;font-weight:600;text-decoration:underline; }

    /* Header kolom: pencarian + penghitung */
    .col-topbar { display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:10px;flex-wrap:wrap; }
    .col-search { position:relative;flex:1;min-width:180px; }
    .col-search input { width:100%;border:1px solid #d1d5db;border-radius:8px;padding:8px 10px 8px 32px;font-size:13px;font-family:inherit; }
    .col-search input:focus { outline:none;border-color:#15803d;box-shadow:0 0 0 2px rgba(21,128,61,.1); }
    .col-search svg { position:absolute;left:10px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#9ca3af;stroke-width:2;fill:none;stroke:currentColor; }
    .col-count-total { font-size:12px;color:#15803d;font-weight:700;white-space:nowrap; }

    /* Grup kolom: accordion + pilih semua per grup */
    .col-group-head { display:flex;align-items:center;gap:8px;margin-bottom:8px; }
    .col-group-toggle { display:flex;align-items:center;gap:7px;background:none;border:none;cursor:pointer;padding:2px 0;flex:1;text-align:left; }
    .cg-caret { width:11px;height:11px;color:#9ca3af;transition:transform .15s;flex-shrink:0;stroke-width:2.5;fill:none;stroke:currentColor; }
    .col-group.collapsed .cg-caret { transform:rotate(-90deg); }
    .col-group.collapsed .col-items { display:none; }
    .col-group-label { margin-bottom:0; }
    .col-group-count { font-size:11px;color:#9ca3af;font-weight:600; }
    .col-group-all { display:flex;align-items:center;gap:5px;font-size:11px;color:#6b7280;cursor:pointer;white-space:nowrap;user-select:none; }
    .col-group-all input { width:13px;height:13px;accent-color:#15803d;cursor:pointer; }
    .col-group.hidden-search, .col-check.hidden-search { display:none; }
    .no-col-result { font-size:12px;color:#9ca3af;text-align:center;padding:16px 0;display:none; }

    /* Sidebar kanan sticky agar tombol unduh selalu terlihat */
    .eb-side { position:sticky;top:16px;align-self:start; }
    @media (max-width:900px){ .eb-side { position:static; } }

    /* Kolom terpilih — atur urutan (drag / naik-turun) */
    .order-list { display:flex;flex-direction:column;gap:6px;max-height:300px;overflow-y:auto;margin-top:6px; }
    .order-item { display:flex;align-items:center;gap:7px;background:#f9fafb;border:1px solid #eef0f2;border-radius:8px;padding:6px 8px;font-size:12.5px;color:#374151; }
    .order-item.dragging { opacity:.4; }
    .order-item.drag-over { border-color:#15803d;box-shadow:0 0 0 2px rgba(21,128,61,.12); }
    .order-item .oi-handle { color:#cbd5e1;flex-shrink:0;cursor:grab;display:inline-flex;width:14px;height:14px; }
    .order-item .oi-idx { color:#9ca3af;font-size:11px;font-weight:700;min-width:16px;text-align:right; }
    .order-item .oi-label { flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
    .order-item .oi-btn { border:none;background:none;color:#9ca3af;cursor:pointer;width:20px;height:20px;display:inline-flex;align-items:center;justify-content:center;border-radius:5px;font-size:14px;line-height:1;flex-shrink:0; }
    .order-item .oi-btn:hover { background:#eef0f2;color:#374151; }
    .order-item .oi-btn:disabled { opacity:.25;cursor:default; }
    .order-item .oi-btn svg { width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2.2; }
    .order-empty { font-size:12px;color:#9ca3af;text-align:center;padding:16px 0; }
    .order-badge { margin-left:auto;background:#dcfce7;color:#15803d;border-radius:100px;padding:1px 9px;font-size:11px;font-weight:700; }

    /* Modal atur urutan kolom */
    .eb-modal { position:fixed;inset:0;z-index:200;display:none;align-items:center;justify-content:center;padding:20px; }
    .eb-modal.open { display:flex; }
    .eb-modal-backdrop { position:absolute;inset:0;background:rgba(17,24,39,.5); }
    .eb-modal-card { position:relative;background:#fff;border-radius:14px;box-shadow:0 24px 60px rgba(0,0,0,.28);width:100%;max-width:520px;max-height:84vh;display:flex;flex-direction:column;overflow:hidden; }
    .eb-modal-head { padding:16px 20px;border-bottom:1px solid #eef0f2;display:flex;align-items:flex-start;justify-content:space-between;gap:12px; }
    .eb-modal-title { font-size:15px;font-weight:700;color:#111827; }
    .eb-modal-sub { font-size:12px;color:#6b7280;margin-top:2px;line-height:1.4; }
    .eb-modal-close { border:none;background:#f3f4f6;border-radius:8px;width:30px;height:30px;font-size:18px;line-height:1;cursor:pointer;color:#6b7280;flex-shrink:0; }
    .eb-modal-close:hover { background:#e5e7eb;color:#111827; }
    .eb-modal-body { overflow-y:auto;padding:14px 20px; }
    .eb-modal-body .order-list { max-height:none;margin-top:0; }
    .eb-modal-foot { padding:12px 20px;border-top:1px solid #eef0f2;display:flex;align-items:center;justify-content:space-between;gap:12px; }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-title">Export Builder</div>
    <div class="page-sub">Pilih kolom yang ingin diexport, atur filter, lalu unduh sebagai Excel atau PDF.</div>
</div>

@if($errors->any())
    <div class="card" style="border-color:#fecaca;background:#fef2f2;margin-bottom:16px;">
        @foreach($errors->all() as $e)
            <div class="err" style="margin:0;">• {{ $e }}</div>
        @endforeach
    </div>
@endif

<form method="POST" action="{{ route('export_builder.export') }}" id="exportForm" data-download>
    @csrf
    <div class="eb-grid">
        {{-- Kolom --}}
        <div class="card">
            <div class="card-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2" style="width:16px;height:16px;"><path d="M9 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h4M9 3v18M9 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H9"/></svg>
                Pilih Kolom
            </div>
            <div class="col-topbar">
                <div class="col-search">
                    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" id="colSearch" placeholder="Cari kolom…" oninput="filterColumns(this.value)" autocomplete="off">
                </div>
                <span id="colCountTotal" class="col-count-total">0 kolom dipilih</span>
            </div>
            <div class="toolbar">
                <button type="button" class="link-btn" onclick="toggleAll(true)">Pilih semua</button>
                <button type="button" class="link-btn" onclick="toggleAll(false)">Kosongkan</button>
            </div>

            @foreach($grouped as $grup => $kolom)
                <div class="col-group">
                    <div class="col-group-head">
                        <button type="button" class="col-group-toggle" onclick="toggleGroup(this)">
                            <svg class="cg-caret" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                            <span class="col-group-label">{{ $grup }}</span>
                            <span class="col-group-count" data-group-count>0/{{ count($kolom) }}</span>
                        </button>
                        <label class="col-group-all" title="Pilih semua kolom di grup ini">
                            <input type="checkbox" data-group-all onchange="toggleGroupAll(this)"> semua
                        </label>
                    </div>
                    <div class="col-items">
                        @foreach($kolom as $key => $label)
                            <label class="col-check">
                                <input type="checkbox" name="columns[]" value="{{ $key }}"
                                    {{ in_array($key, (array) old('columns', ['nik','nama'])) ? 'checked' : '' }}>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
            <div class="no-col-result" id="noColResult">Tidak ada kolom yang cocok.</div>
        </div>

        {{-- Filter & Aksi --}}
        <div class="eb-side">
            <div class="card">
                <div class="card-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2" style="width:16px;height:16px;"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    Filter
                </div>

                <div class="field">
                    <label>Tahun data</label>
                    <select name="tahun">
                        <option value="">Semua Tahun (kolom per tahun)</option>
                        @foreach($tahunList as $th)
                            <option value="{{ $th }}" {{ old('tahun') == $th ? 'selected' : '' }}>{{ $th }}</option>
                        @endforeach
                    </select>
                    <div class="hint">Berlaku untuk Kalibrasi, KPI, Assessment, Kompetensi & Talent Pool. "Semua Tahun" = tetap 1 baris per karyawan, tapi tiap data tahunan dipecah jadi kolom per tahun (mis. Kalibrasi 2025, Kalibrasi 2024, …).</div>
                </div>

                <div class="field">
                    <label>Bulan data</label>
                    <select name="bulan">
                        <option value="">Semua Bulan</option>
                        @foreach($bulanList as $no => $nama)
                            <option value="{{ $no }}" {{ old('bulan') == $no ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                    <div class="hint">Hanya berlaku untuk Assessment & Assessment Kompetensi (data berbasis tanggal).</div>
                </div>

                <div class="field">
                    <label>Pendidikan</label>
                    <select name="pendidikan">
                        <option value="">Tidak disertakan</option>
                        <option value="terakhir" {{ old('pendidikan')=='terakhir'?'selected':'' }}>Pendidikan Terakhir</option>
                    </select>
                    <div class="hint">Bila dipilih, menambah kolom <b>Pendidikan Terakhir · Jurusan · Institusi</b> (jenjang tertinggi). Untuk daftar lengkap semua jenjang, gunakan menu <b>History Pendidikan</b> → Export.</div>
                </div>

                <div class="field">
                    <label>Status</label>
                    <select name="status">
                        <option value="">Semua</option>
                        <option value="aktif" {{ old('status')=='aktif'?'selected':'' }}>Aktif</option>
                        <option value="tidak aktif" {{ old('status')=='tidak aktif'?'selected':'' }}>Tidak Aktif</option>
                    </select>
                </div>

                <div class="field">
                    <label>Direktorat</label>
                    <select name="direktorat_id">
                        <option value="">Semua</option>
                        @foreach($direktorats as $d)
                            <option value="{{ $d->id }}" {{ old('direktorat_id')==$d->id?'selected':'' }}>{{ $d->nama_direktorat }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label>Kompartemen</label>
                    <select name="kompartemen_id">
                        <option value="">Semua</option>
                        @foreach($kompartemens as $d)
                            <option value="{{ $d->id }}" {{ old('kompartemen_id')==$d->id?'selected':'' }}>{{ $d->nama_kompartemen }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label>Departemen</label>
                    <select name="departemen_id">
                        <option value="">Semua</option>
                        @foreach($departemens as $d)
                            <option value="{{ $d->id }}" {{ old('departemen_id')==$d->id?'selected':'' }}>{{ $d->nama_departemen }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label>Tier Pejabat</label>
                    <select name="tier">
                        <option value="">Semua</option>
                        @foreach($tierList as $t)
                            <option value="{{ $t }}" {{ old('tier')==$t?'selected':'' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                    <div class="hint">Menyaring karyawan berdasarkan tier pejabat aktif (SVP/VP/SPM/PM).</div>
                </div>

                <div class="field">
                    <label>Pilih Karyawan (opsional)</label>
                    <div class="picker">
                        <div class="pk-wrap">
                            <input type="text" id="empSearch" autocomplete="off" placeholder="Cari NIK atau nama…">
                            <div id="empSuggest" class="suggest" style="display:none;"></div>
                        </div>
                        <button type="button" class="pk-add" id="empAddBtn">+ Tambah</button>
                    </div>
                    <div id="empChips" class="chips"></div>
                    <div id="empChipsMeta" class="chips-meta"></div>
                    <input type="hidden" name="nik_nama" id="empNik" value="{{ old('nik_nama') }}">
                    <div class="hint">Kosongkan untuk semua karyawan. Bisa juga tempel banyak NIK sekaligus (dipisah koma/baris) lalu klik Tambah.</div>
                </div>
            </div>

            <div class="card">
                <button type="button" class="btn btn-preview" style="width:100%;" onclick="openOrderModal()">
                    <svg viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                    <span>Atur Urutan Kolom</span>
                    <span id="orderBtnCount" class="order-badge">0</span>
                </button>
                <input type="hidden" name="col_order" id="colOrder">
            </div>

            <div class="card">
                <div class="card-title">Preview & Unduh</div>
                <button type="button" id="previewBtn" class="btn btn-preview" onclick="doPreview()" style="width:100%;margin-bottom:10px;">
                    <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <span>Preview Data</span>
                </button>
                <div class="btn-row">
                    <button type="submit" name="format" value="excel" class="btn btn-excel">
                        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        Excel
                    </button>
                    <button type="submit" name="format" value="pdf" class="btn btn-pdf">
                        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        PDF
                    </button>
                </div>
                <div id="downloadHint" class="hint" style="display:none;margin-top:8px;text-align:center;">Menyiapkan file unduhan…</div>
            </div>
        </div>
    </div>
</form>

{{-- Modal: atur urutan kolom --}}
<div class="eb-modal" id="orderModal">
    <div class="eb-modal-backdrop" onclick="closeOrderModal()"></div>
    <div class="eb-modal-card">
        <div class="eb-modal-head">
            <div>
                <div class="eb-modal-title">Atur Urutan Kolom</div>
                <div class="eb-modal-sub">Seret <strong>⋮⋮</strong> atau pakai ▲▼ untuk mengatur urutan kolom di Preview, Excel &amp; PDF.</div>
            </div>
            <button type="button" class="eb-modal-close" onclick="closeOrderModal()">&times;</button>
        </div>
        <div class="eb-modal-body">
            <div class="order-list" id="orderList"></div>
            <div class="order-empty" id="orderEmpty">Belum ada kolom dipilih. Centang kolom dulu di panel kiri.</div>
        </div>
        <div class="eb-modal-foot">
            <span id="orderCount" style="font-size:12px;color:#9ca3af;"></span>
            <button type="button" class="btn btn-excel" style="flex:none;padding:9px 22px;" onclick="closeOrderModal()">Selesai</button>
        </div>
    </div>
</div>

{{-- Panel preview --}}
<div class="card" id="previewPanel" style="display:none;margin-top:20px;">
    <div class="card-title" style="justify-content:space-between;">
        <span style="display:flex;align-items:center;gap:8px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2" style="width:16px;height:16px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            Preview Data
        </span>
        <span id="previewMeta" style="font-size:12px;font-weight:500;color:#6b7280;"></span>
    </div>
    <div id="previewBody" style="overflow-x:auto;"></div>
</div>

<script>
    const exportForm = document.getElementById('exportForm');
    const previewRoute = "{{ route('export_builder.preview') }}";
    const previewBtnDefault = document.getElementById('previewBtn').innerHTML;

    // ===== Pemilih kolom (cari, accordion, pilih-semua, penghitung) =====
    function colBoxes(scope) {
        return Array.from((scope || document).querySelectorAll('input[name="columns[]"]'));
    }

    function refreshColCounts() {
        let total = 0;
        document.querySelectorAll('.col-group').forEach(g => {
            const boxes   = colBoxes(g);
            const checked = boxes.filter(b => b.checked).length;
            total += checked;
            const cnt = g.querySelector('[data-group-count]');
            if (cnt) cnt.textContent = checked + '/' + boxes.length;
            const all = g.querySelector('[data-group-all]');
            if (all) {
                all.checked = boxes.length > 0 && checked === boxes.length;
                all.indeterminate = checked > 0 && checked < boxes.length;
            }
        });
        const t = document.getElementById('colCountTotal');
        if (t) t.textContent = total + ' kolom dipilih';
        syncOrderList();
    }

    // ===== Urutan kolom (drag / naik-turun) =====
    let colOrderKeys = [];

    function colLabel(key) {
        const cb = exportForm.querySelector('input[name="columns[]"][value="' + key + '"]');
        if (!cb) return key;
        const span = cb.closest('.col-check').querySelector('span');
        return span ? span.textContent.trim() : key;
    }

    function syncOrderList() {
        const checked = colBoxes().filter(b => b.checked).map(b => b.value);
        colOrderKeys = colOrderKeys.filter(k => checked.indexOf(k) !== -1);   // buang yg di-uncheck
        checked.forEach(k => { if (colOrderKeys.indexOf(k) === -1) colOrderKeys.push(k); }); // tambah yg baru
        renderOrderList();
    }

    function renderOrderList() {
        const list  = document.getElementById('orderList');
        const empty = document.getElementById('orderEmpty');
        const cnt   = document.getElementById('orderCount');
        const hidden= document.getElementById('colOrder');
        const badge = document.getElementById('orderBtnCount');
        hidden.value = colOrderKeys.join(',');
        cnt.textContent = colOrderKeys.length ? colOrderKeys.length + ' kolom terpilih' : '';
        if (badge) badge.textContent = colOrderKeys.length;
        if (!colOrderKeys.length) { list.innerHTML = ''; empty.style.display = 'block'; return; }
        empty.style.display = 'none';
        list.innerHTML = colOrderKeys.map((k, i) =>
            '<div class="order-item" draggable="true" data-key="' + k + '">' +
                '<span class="oi-handle"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><circle cx="9" cy="6" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="9" cy="18" r="1"/><circle cx="15" cy="6" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="18" r="1"/></svg></span>' +
                '<span class="oi-idx">' + (i + 1) + '</span>' +
                '<span class="oi-label">' + escapeHtml(colLabel(k)) + '</span>' +
                '<button type="button" class="oi-btn" title="Naik" ' + (i === 0 ? 'disabled' : '') + ' onclick="moveOrder(\'' + k + '\',-1)"><svg viewBox="0 0 24 24"><polyline points="18 15 12 9 6 15"/></svg></button>' +
                '<button type="button" class="oi-btn" title="Turun" ' + (i === colOrderKeys.length - 1 ? 'disabled' : '') + ' onclick="moveOrder(\'' + k + '\',1)"><svg viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg></button>' +
                '<button type="button" class="oi-btn" title="Hapus" onclick="removeOrder(\'' + k + '\')">&times;</button>' +
            '</div>'
        ).join('');
        bindOrderDrag();
    }

    function moveOrder(key, dir) {
        const i = colOrderKeys.indexOf(key);
        const j = i + dir;
        if (i === -1 || j < 0 || j >= colOrderKeys.length) return;
        [colOrderKeys[i], colOrderKeys[j]] = [colOrderKeys[j], colOrderKeys[i]];
        renderOrderList();
    }

    function removeOrder(key) {
        const cb = exportForm.querySelector('input[name="columns[]"][value="' + key + '"]');
        if (cb) cb.checked = false;
        refreshColCounts();   // → syncOrderList() ikut membuangnya
    }

    function openOrderModal() {
        syncOrderList();
        document.getElementById('orderModal').classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeOrderModal() {
        document.getElementById('orderModal').classList.remove('open');
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeOrderModal();
    });

    let _dragKey = null;
    function bindOrderDrag() {
        document.querySelectorAll('#orderList .order-item').forEach(it => {
            it.addEventListener('dragstart', () => { _dragKey = it.dataset.key; it.classList.add('dragging'); });
            it.addEventListener('dragend',   () => { it.classList.remove('dragging'); document.querySelectorAll('.order-item').forEach(x => x.classList.remove('drag-over')); });
            it.addEventListener('dragover',  e => { e.preventDefault(); it.classList.add('drag-over'); });
            it.addEventListener('dragleave', () => it.classList.remove('drag-over'));
            it.addEventListener('drop', e => {
                e.preventDefault();
                const target = it.dataset.key;
                if (!_dragKey || _dragKey === target) return;
                const from = colOrderKeys.indexOf(_dragKey), to = colOrderKeys.indexOf(target);
                if (from === -1 || to === -1) return;
                colOrderKeys.splice(from, 1);
                colOrderKeys.splice(to, 0, _dragKey);
                renderOrderList();
            });
        });
    }

    function toggleAll(state) {
        colBoxes().forEach(cb => cb.checked = state);
        refreshColCounts();
    }

    function toggleGroup(btn) {
        btn.closest('.col-group').classList.toggle('collapsed');
    }

    function toggleGroupAll(cb) {
        colBoxes(cb.closest('.col-group')).forEach(x => x.checked = cb.checked);
        refreshColCounts();
    }

    function filterColumns(q) {
        q = q.trim().toLowerCase();
        let anyGroup = false;
        document.querySelectorAll('.col-group').forEach(g => {
            let visible = 0;
            g.querySelectorAll('.col-check').forEach(lbl => {
                const match = !q || lbl.textContent.toLowerCase().includes(q);
                lbl.classList.toggle('hidden-search', !match);
                if (match) visible++;
            });
            g.classList.toggle('hidden-search', visible === 0);
            if (q) g.classList.remove('collapsed'); // buka grup saat mencari
            if (visible > 0) anyGroup = true;
        });
        const none = document.getElementById('noColResult');
        if (none) none.style.display = anyGroup ? 'none' : 'block';
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
    }

    // ===== Pemilih karyawan (cari → tambah → chip) =====
    const EMP = {!! json_encode($karyawanPilih->map(fn($k) => ['nik' => (string) $k->nik, 'nama' => $k->nama])->values()) !!};
    const empByNik = new Map(EMP.map(e => [e.nik, e.nama]));
    const selectedEmp = new Map();            // nik -> nama
    const searchEl  = document.getElementById('empSearch');
    const suggestEl = document.getElementById('empSuggest');
    const chipsEl   = document.getElementById('empChips');
    const metaEl    = document.getElementById('empChipsMeta');
    const hiddenEl  = document.getElementById('empNik');

    function syncEmp() {
        hiddenEl.value = Array.from(selectedEmp.keys()).join(',');
        const n = selectedEmp.size;
        metaEl.style.display = n ? 'block' : 'none';
        metaEl.textContent = n ? `${n} karyawan dipilih` : '';
    }
    function renderChips() {
        chipsEl.innerHTML = '';
        selectedEmp.forEach((nama, nik) => {
            const chip = document.createElement('span');
            chip.className = 'chip';
            chip.innerHTML = `<span>${escapeHtml(nama)} · ${escapeHtml(nik)}</span>`;
            const x = document.createElement('button');
            x.type = 'button'; x.className = 'cx'; x.textContent = '×';
            x.addEventListener('click', () => { selectedEmp.delete(nik); renderChips(); syncEmp(); });
            chip.appendChild(x);
            chipsEl.appendChild(chip);
        });
    }
    function addEmp(nik, nama) {
        nik = String(nik).trim();
        if (!nik || selectedEmp.has(nik)) return;
        selectedEmp.set(nik, nama || empByNik.get(nik) || nik);
    }
    function renderSuggest() {
        const q = searchEl.value.trim().toLowerCase();
        if (!q) { suggestEl.style.display = 'none'; return; }
        const matches = EMP
            .filter(e => !selectedEmp.has(e.nik) && (e.nik + ' ' + e.nama).toLowerCase().includes(q))
            .slice(0, 8);
        if (!matches.length) { suggestEl.style.display = 'none'; return; }
        suggestEl.innerHTML = '';
        matches.forEach(e => {
            const it = document.createElement('div');
            it.className = 'suggest-item';
            it.innerHTML = `<strong>${escapeHtml(e.nama)}</strong><span style="color:#9ca3af">${escapeHtml(e.nik)}</span>`;
            it.addEventListener('click', () => {
                addEmp(e.nik, e.nama); renderChips(); syncEmp();
                searchEl.value = ''; suggestEl.style.display = 'none'; searchEl.focus();
            });
            suggestEl.appendChild(it);
        });
        suggestEl.style.display = 'block';
    }
    function commitEmp() {
        const raw = searchEl.value;
        const tokens = raw.split(/[\n,;]+/).map(t => t.trim()).filter(Boolean);
        if (tokens.length > 1) {
            // Tempel banyak: cocokkan tiap token ke NIK persis / nama persis.
            tokens.forEach(tok => {
                if (empByNik.has(tok)) { addEmp(tok, empByNik.get(tok)); return; }
                const byName = EMP.filter(e => e.nama.toLowerCase() === tok.toLowerCase());
                if (byName.length === 1) { addEmp(byName[0].nik, byName[0].nama); return; }
                if (/^\d+$/.test(tok)) addEmp(tok, null); // NIK mentah walau tak ada di daftar
            });
        } else if (tokens.length === 1) {
            const tok = tokens[0];
            if (empByNik.has(tok)) { addEmp(tok, empByNik.get(tok)); }
            else {
                const m = EMP.find(e => !selectedEmp.has(e.nik) && (e.nik + ' ' + e.nama).toLowerCase().includes(tok.toLowerCase()));
                if (m) addEmp(m.nik, m.nama);
                else if (/^\d+$/.test(tok)) addEmp(tok, null);
            }
        }
        renderChips(); syncEmp();
        searchEl.value = ''; suggestEl.style.display = 'none';
    }
    searchEl.addEventListener('input', renderSuggest);
    searchEl.addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); commitEmp(); }
    });
    document.getElementById('empAddBtn').addEventListener('click', commitEmp);
    document.addEventListener('click', e => { if (!e.target.closest('.picker')) suggestEl.style.display = 'none'; });

    // Pulihkan pilihan bila halaman reload (mis. gagal validasi).
    (function initEmp() {
        (hiddenEl.value || '').split(',').map(t => t.trim()).filter(Boolean)
            .forEach(nik => addEmp(nik, empByNik.get(nik)));
        renderChips(); syncEmp();
    })();

    // Update penghitung tiap kali kolom dicentang manual, dan saat halaman siap.
    exportForm.addEventListener('change', e => {
        if (e.target.matches('input[name="columns[]"]')) refreshColCounts();
    });
    refreshColCounts();

    async function doPreview() {
        const btn = document.getElementById('previewBtn');
        const panel = document.getElementById('previewPanel');
        const meta = document.getElementById('previewMeta');
        const body = document.getElementById('previewBody');

        if (!exportForm.querySelector('input[name="columns[]"]:checked')) {
            alert('Pilih minimal satu kolom terlebih dahulu.');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span>Memuat…</span>';

        try {
            const res = await fetch(previewRoute, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: new FormData(exportForm),
            });
            const data = await res.json();

            if (!res.ok) {
                meta.style.color = '#dc2626';
                meta.textContent = data.message || 'Gagal memuat preview.';
                body.innerHTML = '';
                panel.style.display = 'block';
                return;
            }

            meta.style.color = '#6b7280';
            meta.textContent = `Menampilkan ${data.shown} dari ${data.total} baris`;

            let html = '<table class="prev-table"><thead><tr>';
            data.headings.forEach(h => html += `<th>${escapeHtml(h)}</th>`);
            html += '</tr></thead><tbody>';
            if (!data.rows.length) {
                html += `<tr><td colspan="${data.headings.length}" style="text-align:center;color:#9ca3af;padding:14px;">Tidak ada data yang cocok dengan filter.</td></tr>`;
            } else {
                data.rows.forEach(r => {
                    html += '<tr>';
                    r.forEach(c => html += `<td>${escapeHtml(c)}</td>`);
                    html += '</tr>';
                });
                if (data.total > data.shown) {
                    html += `<tr><td colspan="${data.headings.length}" style="text-align:center;color:#9ca3af;padding:8px;">…dan ${data.total - data.shown} baris lainnya (tampil penuh di file export)</td></tr>`;
                }
            }
            html += '</tbody></table>';
            body.innerHTML = html;
            panel.style.display = 'block';
            panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } catch (err) {
            alert('Terjadi kesalahan saat memuat preview.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = previewBtnDefault;
        }
    }

    // Beri tanda singkat saat tombol unduh ditekan (download tidak reload halaman).
    exportForm.addEventListener('submit', function () {
        const hint = document.getElementById('downloadHint');
        hint.style.display = 'block';
        setTimeout(() => hint.style.display = 'none', 4000);
    });
</script>
@endsection
