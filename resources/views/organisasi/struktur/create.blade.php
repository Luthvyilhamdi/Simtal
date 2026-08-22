@extends('layouts.app')
@section('title', 'Input Versi Struktur Organisasi')
@section('breadcrumb-parent', 'Riwayat Struktur Organisasi')
@section('breadcrumb', 'Input Versi Baru')

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color .12s; }
    .back-link:hover { color:#15803d; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }
    .page-header { margin-bottom:20px; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:4px; }

    .mode-banner { display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:10px;font-size:13px;font-weight:600;margin-bottom:16px; }
    .mode-banner.baseline { background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe; }
    .mode-banner.lanjutan { background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0; }

    .form-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:28px;margin-bottom:16px; }
    .section-header { display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid #f3f4f6;flex-wrap:wrap; }
    .section-title { font-size:14px;font-weight:700;color:#111827; }
    .section-sub { font-size:12px;color:#9ca3af;margin-top:1px; }

    .form-grid { display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px; }
    .form-group { display:flex;flex-direction:column;gap:6px; }
    .form-group.full { grid-column:1/-1; }
    .form-label { font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.5px; }
    .req { color:#ef4444; }
    .form-input, .form-select { padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:9px;font-size:13px;font-family:inherit;color:#111827;background:#fafafa;outline:none;transition:all .15s;width:100%; }
    .form-input:focus, .form-select:focus { border-color:#16a34a;background:white;box-shadow:0 0 0 3px rgba(22,163,74,.08); }
    .error-msg { font-size:11px;color:#ef4444;margin-top:2px; }

    .btn-add-row { display:inline-flex;align-items:center;gap:6px;background:#eff6ff;color:#1d4ed8;padding:8px 14px;border-radius:8px;font-size:12px;font-weight:600;border:1px solid #bfdbfe;cursor:pointer;font-family:inherit; }
    .btn-add-row:hover { background:#dbeafe; }
    .btn-add-row svg { width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2; }

    .toolbar { display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:14px;padding:10px 14px;background:#fafafa;border:1px solid #f3f4f6;border-radius:9px; }
    .toolbar-info { font-size:12px;color:#6b7280;font-weight:600; }
    .btn-toolbar { display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:600;border:1px solid #e5e7eb;background:white;color:#374151;cursor:pointer;font-family:inherit; }
    .btn-toolbar:hover { background:#f0fdf4;border-color:#bbf7d0;color:#15803d; }
    .btn-toolbar:disabled { opacity:.4;cursor:not-allowed; }

    .units-table-wrap { overflow-x:auto;overflow-y:visible;border:1px solid #f3f4f6;border-radius:10px; }
    table.units-table { width:100%;border-collapse:collapse;font-size:12.5px;min-width:920px; }
    table.units-table thead th { padding:9px 10px;text-align:left;font-size:10.5px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.4px;border-bottom:1px solid #f3f4f6;background:#f9fafb;white-space:nowrap; }
    table.units-table tbody td { padding:7px 10px;border-bottom:1px solid #f3f4f6;vertical-align:middle; }
    table.units-table tbody tr:last-child td { border-bottom:none; }
    table.units-table input, table.units-table select { padding:7px 9px;border:1.3px solid #e5e7eb;border-radius:7px;font-size:12.5px;font-family:inherit;width:100%;background:white; }
    table.units-table input:focus, table.units-table select:focus { border-color:#16a34a;outline:none; }
    .col-mc { width:90px; }
    .col-chk { width:34px;text-align:center; }
    .col-badge { width:78px; }
    .col-aksi { width:70px; }
    .col-parent { width:170px; }

    /* Combobox parent: sama pola dgn halaman edit — cuma render pilihan yg cocok pencarian
       & di-cap 30, bukan semua unit sekaligus (freeze risk utk versi lanjutan dari roster besar). */
    .parent-combo { position:relative; }
    .parent-chip { cursor:pointer;padding:7px 9px;border:1.3px solid #e5e7eb;border-radius:7px;background:white;font-size:12.5px;min-height:17px;color:#111827; }
    .parent-chip:hover { border-color:#16a34a; }
    .parent-chip.is-root { color:#9ca3af;font-style:italic; }
    .parent-dropdown { position:absolute;top:100%;left:0;right:0;background:white;border:1.5px solid #e5e7eb;border-radius:9px;box-shadow:0 8px 24px rgba(0,0,0,.12);z-index:50;max-height:220px;overflow-y:auto;margin-top:2px; }
    .parent-option { padding:8px 12px;cursor:pointer;font-size:12.5px;border-bottom:1px solid #f3f4f6; }
    .parent-option:last-child { border-bottom:none; }
    .parent-option:hover { background:#f0fdf4; }
    .parent-option.root-option { color:#6b7280;font-style:italic; }
    .parent-empty { padding:10px 12px;font-size:12px;color:#9ca3af;text-align:center; }
    .badge-new { display:inline-block;font-size:10.5px;font-weight:700;padding:3px 8px;border-radius:6px;background:#eff6ff;color:#1d4ed8; }
    .badge-bubar-row td { opacity:.5; }
    .btn-row-remove { background:none;border:none;color:#ef4444;cursor:pointer;font-size:12px;font-weight:600;padding:4px 6px; }
    .empty-units { text-align:center;padding:24px;color:#9ca3af;font-size:12.5px; }

    .group-chips { display:flex;flex-direction:column;gap:6px;margin-top:12px; }
    .group-chip { display:flex;align-items:center;justify-content:space-between;gap:10px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:8px 12px;font-size:12px;color:#92400e; }
    .group-chip.gabung { background:#f5f3ff;border-color:#ddd6fe;color:#5b21b6; }
    .group-chip button { background:none;border:none;color:inherit;cursor:pointer;font-weight:700;font-size:13px; }

    .form-actions-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:20px 28px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px; }
    .btn-cancel { display:inline-flex;align-items:center;gap:8px;background:white;color:#374151;padding:10px 20px;border-radius:9px;font-size:13px;font-weight:600;border:1.5px solid #e5e7eb;text-decoration:none;transition:all .15s; }
    .btn-cancel:hover { background:#f9fafb; }
    .btn-save { display:inline-flex;align-items:center;gap:8px;background:#15803d;color:white;padding:10px 24px;border-radius:9px;font-size:13px;font-weight:600;border:none;cursor:pointer;font-family:inherit;transition:all .15s; }
    .btn-save:hover { background:#166534; }
    .btn-save svg,.btn-cancel svg { width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2; }
    .btn-save svg { stroke:white; }

    .modal-backdrop { position:fixed;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(3px);z-index:1000;display:flex;align-items:center;justify-content:center; }
    .modal-box { background:white;border-radius:16px;padding:26px;width:100%;max-width:520px;margin:16px;box-shadow:0 20px 60px rgba(0,0,0,0.2);max-height:85vh;overflow-y:auto; }
    .modal-title { font-size:15px;font-weight:700;color:#111827;margin-bottom:4px; }
    .modal-sub { font-size:12px;color:#6b7280;margin-bottom:16px; }
    .modal-actions { display:flex;gap:10px;margin-top:20px; }
    .modal-btn { flex:1;padding:11px;border-radius:10px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all 0.15s; }
    .modal-btn.cancel { background:#f9fafb;color:#374151;border:1px solid #e5e7eb; }
    .modal-btn.save { background:#15803d;color:white; }
    .modal-btn.save:hover { background:#166534; }
    .target-block { border:1.3px solid #e5e7eb;border-radius:9px;padding:12px;margin-bottom:10px;position:relative; }
    .target-block .btn-row-remove { position:absolute;top:8px;right:8px; }

    @media (max-width:768px) {
        .form-grid { grid-template-columns:1fr; }
        .form-group.full { grid-column:1; }
    }

    [x-cloak] { display:none !important; }
</style>
@endpush

@section('content')
<a href="{{ route('organisasi.struktur.index') }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Riwayat Struktur Organisasi
</a>

<div class="page-header">
    <div class="page-title">Input Versi Struktur Organisasi</div>
    <div class="page-sub">Catat perubahan struktur organisasi berdasarkan SK terbaru</div>
</div>

@if($isBaseline)
<div class="mode-banner baseline">
    <div style="flex:1;">🆕 Ini adalah <strong>versi pertama (baseline)</strong> — belum ada data struktur organisasi sebelumnya. Tambahkan unit satu per satu di bawah, atau import dari Excel kalau sudah ditranskrip.</div>
    <a href="{{ route('organisasi.struktur.import') }}" style="display:inline-flex;align-items:center;gap:6px;background:white;color:#1d4ed8;padding:8px 14px;border-radius:8px;font-size:12.5px;font-weight:600;text-decoration:none;border:1px solid #bfdbfe;white-space:nowrap;">
        <svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" fill="none" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        Import dari Excel
    </a>
</div>
@else
<div class="mode-banner lanjutan">
    🔁 Versi lanjutan dari SK <strong>{{ $lastVersi->nomor_sk }}</strong> ({{ $lastVersi->tanggal_mulai_berlaku->translatedFormat('d F Y') }}). Daftar unit di bawah disalin dari versi terakhir — edit sesuai perubahan yang terjadi.
</div>
@endif

@if($lastVersiDraft)
<div class="mode-banner" style="background:#fffbeb;color:#92400e;border:1px solid #fde68a;">
    ⚠️ Versi terakhir (<strong>{{ $lastVersi->nomor_sk }}</strong>) masih berstatus <strong>draft</strong> dan belum difinalisasi. Sebaiknya selesaikan &amp; finalisasi versi tersebut dulu sebelum mulai versi baru — tapi kalau memang ada alasan untuk kerja paralel, silakan lanjutkan.
</div>
@endif

@if($errors->any())
<div class="mode-banner" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca;display:block;">
    <strong>Periksa kembali isian form:</strong>
    <ul style="margin:6px 0 0 18px;">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div x-data="strukturForm({{ $isBaseline ? 'true' : 'false' }}, {{ $existingUnits->toJson() }}, {{ Illuminate\Support\Js::from($levels) }})">
<form method="POST" action="{{ route('organisasi.struktur.store') }}" @submit="onSubmit">
    @csrf
    <input type="hidden" name="payload" id="payloadInput">

    {{-- ===== HEADER SK ===== --}}
    <div class="form-card">
        <div class="section-header">
            <div>
                <div class="section-title">Data SK</div>
                <div class="section-sub">Informasi surat keputusan yang menjadi dasar versi ini</div>
            </div>
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Nomor SK <span class="req">*</span></label>
                <input type="text" name="nomor_sk" value="{{ old('nomor_sk') }}" class="form-input" required maxlength="100">
            </div>
            <div class="form-group">
                <label class="form-label">Tanggal SK <span class="req">*</span></label>
                <input type="date" name="tanggal_sk" value="{{ old('tanggal_sk', now()->format('Y-m-d')) }}" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Tanggal Mulai Berlaku <span class="req">*</span></label>
                <input type="date" name="tanggal_mulai_berlaku" value="{{ old('tanggal_mulai_berlaku') }}" class="form-input" required>
                @if(!$isBaseline)
                <div class="section-sub">Harus setelah {{ $lastVersi->tanggal_mulai_berlaku->translatedFormat('d F Y') }}</div>
                @endif
            </div>
            <div class="form-group full">
                <label class="form-label">Keterangan</label>
                <textarea name="keterangan" class="form-input" rows="2">{{ old('keterangan') }}</textarea>
            </div>
        </div>
    </div>

    {{-- ===== DAFTAR UNIT ===== --}}
    <div class="form-card">
        <div class="section-header">
            <div>
                <div class="section-title">Daftar Unit Organisasi</div>
                <div class="section-sub">
                    @if($isBaseline)
                        Tambahkan unit satu per satu — unit induk (parent) harus sudah ada di daftar sebelum dipilih sebagai parent unit anak.
                    @else
                        Edit unit yang berubah, tandai jenis perubahannya, atau kelola pemecahan/penggabungan unit.
                    @endif
                </div>
            </div>
            <button type="button" class="btn-add-row" @click="addUnit()">
                <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                Tambah Unit
            </button>
        </div>

        @unless($isBaseline)
        <div class="toolbar">
            <span class="toolbar-info" x-text="selected.length + ' unit dipilih'"></span>
            <button type="button" class="btn-toolbar" :disabled="selected.length < 2" @click="openGabung()">🔗 Gabung jadi 1 Unit</button>
            <button type="button" class="btn-toolbar" :disabled="selected.length !== 1" @click="openPecah()">✂️ Pecah jadi Beberapa Unit</button>
        </div>
        @endunless

        <div class="units-table-wrap">
            <table class="units-table">
                <thead>
                    <tr>
                        @unless($isBaseline)
                        <th class="col-chk"></th>
                        @endunless
                        <th>Nama Unit</th>
                        <th>Level</th>
                        <th class="col-parent">Parent</th>
                        <th class="col-mc">MC Formasi</th>
                        <th>Keterangan</th>
                        @unless($isBaseline)
                        <th class="col-badge">Status</th>
                        @endunless
                        <th class="col-aksi"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="row in activeUnits()" :key="row.key">
                        <tr :class="row.jenis_transisi === 'bubar' ? 'badge-bubar-row' : ''">
                            <template x-if="!isBaseline">
                                <td class="col-chk">
                                    <input type="checkbox" x-show="!row.isNew" :checked="selected.includes(row.key)" @change="toggleSelect(row.key)">
                                </td>
                            </template>
                            <td><input type="text" x-model="row.nama_unit" required></td>
                            <td>
                                <select x-model="row.level">
                                    <template x-for="lvl in levels" :key="lvl">
                                        <option :value="lvl" x-text="lvl" :selected="row.level === lvl"></option>
                                    </template>
                                </select>
                            </td>
                            <td class="parent-combo">
                                <template x-if="row._comboOpen !== true">
                                    <div class="parent-chip" :class="{ 'is-root': !row.parent_key }"
                                         @click="openParentCombo(row)"
                                         x-text="parentLabel(row)"></div>
                                </template>
                                <template x-if="row._comboOpen === true">
                                    <div>
                                        <input type="text" x-model="row._parentSearch" placeholder="Cari unit..."
                                               @keydown.escape="row._comboOpen = false"
                                               @click.outside="row._comboOpen = false"
                                               x-init="$el.focus()">
                                        <div class="parent-dropdown">
                                            <div class="parent-option root-option" @click="selectParent(row, '')">(Tidak ada / Root)</div>
                                            <template x-for="opt in parentOptionsFor(row)" :key="opt.key">
                                                <div class="parent-option" x-text="opt.nama_unit" @click="selectParent(row, opt.key)"></div>
                                            </template>
                                            <div class="parent-empty" x-show="parentOptionsFor(row).length === 0" x-cloak>Tidak ada hasil</div>
                                        </div>
                                    </div>
                                </template>
                            </td>
                            <td class="col-mc"><input type="number" min="0" x-model.number="row.mc_formasi"></td>
                            <td><input type="text" x-model="row.keterangan"></td>
                            <template x-if="!isBaseline">
                                <td class="col-badge">
                                    <template x-if="row.isNew">
                                        <span class="badge-new">Baru</span>
                                    </template>
                                    <template x-if="!row.isNew">
                                        <select x-model="row.jenis_transisi">
                                            <option value="lanjut">Lanjut</option>
                                            <option value="rename">Rename</option>
                                            <option value="pindah_induk">Pindah Induk</option>
                                            <option value="ganti_level">Ganti Level</option>
                                            <option value="bubar">Bubar</option>
                                        </select>
                                    </template>
                                </td>
                            </template>
                            <td class="col-aksi">
                                <button type="button" class="btn-row-remove" x-show="row.isNew" @click="removeUnit(row.key)">Hapus</button>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="activeUnits().length === 0">
                        <td :colspan="isBaseline ? 6 : 8" class="empty-units">Belum ada unit. Klik "Tambah Unit" untuk mulai.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <template x-if="pecahGroups.length > 0 || gabungGroups.length > 0">
            <div class="group-chips">
                <template x-for="(g, i) in pecahGroups" :key="'p'+i">
                    <div class="group-chip">
                        <span x-text="'Pecah: ' + g.label"></span>
                        <button type="button" @click="undoPecah(i)">&times;</button>
                    </div>
                </template>
                <template x-for="(g, i) in gabungGroups" :key="'g'+i">
                    <div class="group-chip gabung">
                        <span x-text="'Gabung: ' + g.label"></span>
                        <button type="button" @click="undoGabung(i)">&times;</button>
                    </div>
                </template>
            </div>
        </template>
    </div>

    <div class="form-actions-card">
        <a href="{{ route('organisasi.struktur.index') }}" class="btn-cancel">Batal</a>
        <button type="submit" class="btn-save">
            <svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            Simpan Versi
        </button>
    </div>
</form>

{{-- ===== MODAL GABUNG ===== --}}
<div class="modal-backdrop" x-show="showGabungModal" x-cloak @keydown.escape.window="showGabungModal = false">
    <div class="modal-box" @click.outside="showGabungModal = false">
        <div class="modal-title">🔗 Gabung Unit</div>
        <div class="modal-sub" x-text="selected.map(k => unitLabel(k)).join(' + ') + ' akan digabung menjadi 1 unit baru'"></div>

        <div class="form-group" style="margin-bottom:10px;">
            <label class="form-label">Nama Unit Baru <span class="req">*</span></label>
            <input type="text" class="form-input" x-model="gabungForm.nama_unit">
        </div>
        <div class="form-group" style="margin-bottom:10px;">
            <label class="form-label">Level</label>
            <select class="form-select" x-model="gabungForm.level">
                <template x-for="lvl in levels" :key="lvl"><option :value="lvl" x-text="lvl"></option></template>
            </select>
        </div>
        <div class="form-group" style="margin-bottom:10px;">
            <label class="form-label">Parent</label>
            <select class="form-select" x-model="gabungForm.parent_key">
                <option value="">(Tidak ada / Root)</option>
                <template x-for="opt in activeUnits().filter(u => levels.indexOf(u.level) < levels.indexOf(gabungForm.level) || (levels.indexOf(u.level) === levels.indexOf(gabungForm.level) && !u.parent_key))" :key="opt.key"><option :value="opt.key" x-text="opt.nama_unit"></option></template>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">MC Formasi</label>
            <input type="number" min="0" class="form-input" x-model.number="gabungForm.mc_formasi">
        </div>

        <div class="modal-actions">
            <button type="button" class="modal-btn cancel" @click="showGabungModal = false">Batal</button>
            <button type="button" class="modal-btn save" @click="confirmGabung()" :disabled="!gabungForm.nama_unit">Simpan Penggabungan</button>
        </div>
    </div>
</div>

{{-- ===== MODAL PECAH ===== --}}
<div class="modal-backdrop" x-show="showPecahModal" x-cloak @keydown.escape.window="showPecahModal = false">
    <div class="modal-box" @click.outside="showPecahModal = false">
        <div class="modal-title">✂️ Pecah Unit</div>
        <div class="modal-sub" x-text="selected.length ? (unitLabel(selected[0]) + ' akan dipecah menjadi beberapa unit baru') : ''"></div>

        <template x-for="(t, i) in pecahForm.targets" :key="i">
            <div class="target-block">
                <button type="button" class="btn-row-remove" x-show="pecahForm.targets.length > 2" @click="pecahForm.targets.splice(i,1)">Hapus</button>
                <div class="form-group" style="margin-bottom:8px;">
                    <label class="form-label">Nama Unit Baru <span class="req">*</span></label>
                    <input type="text" class="form-input" x-model="t.nama_unit">
                </div>
                <div class="form-group" style="margin-bottom:8px;">
                    <label class="form-label">Level</label>
                    <select class="form-select" x-model="t.level">
                        <template x-for="lvl in levels" :key="lvl"><option :value="lvl" x-text="lvl"></option></template>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:8px;">
                    <label class="form-label">Parent</label>
                    <select class="form-select" x-model="t.parent_key">
                        <option value="">(Tidak ada / Root)</option>
                        <template x-for="opt in activeUnits().filter(u => levels.indexOf(u.level) < levels.indexOf(t.level) || (levels.indexOf(u.level) === levels.indexOf(t.level) && !u.parent_key))" :key="opt.key"><option :value="opt.key" x-text="opt.nama_unit"></option></template>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">MC Formasi</label>
                    <input type="number" min="0" class="form-input" x-model.number="t.mc_formasi">
                </div>
            </div>
        </template>

        <button type="button" class="btn-add-row" @click="pecahForm.targets.push({nama_unit:'',level:levels[0],parent_key:'',mc_formasi:0,keterangan:''})">
            + Tambah Target Pecahan
        </button>

        <div class="modal-actions">
            <button type="button" class="modal-btn cancel" @click="showPecahModal = false">Batal</button>
            <button type="button" class="modal-btn save" @click="confirmPecah()" :disabled="pecahForm.targets.some(t => !t.nama_unit)">Simpan Pemecahan</button>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function strukturForm(isBaseline, existingUnits, levels) {
    return {
        isBaseline: isBaseline,
        levels: levels,
        units: existingUnits.map(u => ({ ...u, jenis_transisi: 'lanjut', isNew: false, mergedInto: null, splitInto: null, _comboOpen: false, _parentSearch: '' })),
        counter: 0,
        selected: [],
        showGabungModal: false,
        showPecahModal: false,
        gabungForm: { nama_unit: '', level: '', parent_key: '', mc_formasi: 0, keterangan: '' },
        pecahForm: { targets: [] },
        gabungGroups: [],
        pecahGroups: [],

        activeUnits() {
            return this.units.filter(u => !u.mergedInto && !u.splitInto);
        },

        // Kandidat parent utk 1 baris: unit lain yg masih aktif, level-nya lebih tinggi
        // (rank lebih kecil di this.levels) drpd level baris ini, dicocokkan ke pencarian
        // teks, lalu di-cap 30 hasil — supaya rendernya tetap ringan brp pun total unit-nya
        // (lihat catatan performa di halaman edit utk bug O(n^2) yg pernah terjadi di sini).
        parentOptionsFor(row) {
            const selfRank = this.levels.indexOf(row.level);
            const query = (row._parentSearch || '').toLowerCase();

            let candidates = this.activeUnits().filter(u => u.key !== row.key);

            if (this.isBaseline) {
                const idx = this.units.findIndex(u => u.key === row.key);
                candidates = candidates.filter(u => this.units.findIndex(x => x.key === u.key) < idx);
            }

            // Level parent harus lebih tinggi drpd level baris ini — kecuali same-level ketika
            // kandidatnya sendiri root (tanpa parent), spt "Utama" membawahi beberapa direktorat
            // lain yg levelnya sama krn cuma 1 nilai enum utk 2 tingkat riil.
            candidates = candidates.filter(u => {
                const rank = this.levels.indexOf(u.level);
                if (selfRank === -1 || rank === -1) return true;
                if (rank < selfRank) return true;
                if (rank === selfRank) return !u.parent_key;
                return false;
            });

            if (query) {
                candidates = candidates.filter(u => u.nama_unit.toLowerCase().includes(query));
            }

            return candidates.slice(0, 30);
        },

        openParentCombo(row) {
            row._parentSearch = '';
            row._comboOpen = true;
        },

        selectParent(row, key) {
            row.parent_key = key;
            row._comboOpen = false;
        },

        parentLabel(row) {
            return row.parent_key ? (this.unitLabel(row.parent_key) || '(Tidak ada / Root)') : '(Tidak ada / Root)';
        },

        unitLabel(key) {
            const row = this.units.find(u => u.key === key);
            return row ? row.nama_unit : '';
        },

        addUnit() {
            this.units.push({
                key: 'n' + (++this.counter), unit_organisasi_id: null,
                nama_unit: '', level: this.levels[0], parent_key: '',
                mc_formasi: 0, keterangan: '', jenis_transisi: 'baru',
                isNew: true, mergedInto: null, splitInto: null,
                _comboOpen: false, _parentSearch: '',
            });
        },

        removeUnit(key) {
            this.units = this.units.filter(u => u.key !== key);
            this.selected = this.selected.filter(k => k !== key);
        },

        toggleSelect(key) {
            if (this.selected.includes(key)) {
                this.selected = this.selected.filter(k => k !== key);
            } else {
                this.selected.push(key);
            }
        },

        openGabung() {
            this.gabungForm = { nama_unit: '', level: this.levels[0], parent_key: '', mc_formasi: 0, keterangan: '' };
            this.showGabungModal = true;
        },

        openPecah() {
            this.pecahForm = { targets: [
                { nama_unit: '', level: this.levels[0], parent_key: '', mc_formasi: 0, keterangan: '' },
                { nama_unit: '', level: this.levels[0], parent_key: '', mc_formasi: 0, keterangan: '' },
            ] };
            this.showPecahModal = true;
        },

        confirmGabung() {
            const targetKey = 'g' + (++this.counter);
            const ids = [];
            this.selected.forEach(k => {
                const row = this.units.find(u => u.key === k);
                if (row) {
                    row.mergedInto = targetKey;
                    if (row.unit_organisasi_id) ids.push(row.unit_organisasi_id);
                }
            });
            const label = this.selected.map(k => this.unitLabel(k)).join(' + ') + ' → ' + this.gabungForm.nama_unit;
            this.units.push({
                key: targetKey, unit_organisasi_id: null,
                nama_unit: this.gabungForm.nama_unit, level: this.gabungForm.level,
                parent_key: this.gabungForm.parent_key, mc_formasi: this.gabungForm.mc_formasi,
                keterangan: this.gabungForm.keterangan, jenis_transisi: 'baru',
                isNew: true, mergedInto: null, splitInto: null,
                _comboOpen: false, _parentSearch: '',
            });
            this.gabungGroups.push({ unit_organisasi_ids: ids, target_key: targetKey, label: label });
            this.selected = [];
            this.showGabungModal = false;
        },

        confirmPecah() {
            const sourceKey = this.selected[0];
            const sourceRow = this.units.find(u => u.key === sourceKey);
            if (!sourceRow) return;
            const targetKeys = [];
            const names = [];
            this.pecahForm.targets.forEach(t => {
                const key = 'n' + (++this.counter);
                targetKeys.push(key);
                names.push(t.nama_unit);
                this.units.push({
                    key: key, unit_organisasi_id: null,
                    nama_unit: t.nama_unit, level: t.level, parent_key: t.parent_key,
                    mc_formasi: t.mc_formasi, keterangan: t.keterangan, jenis_transisi: 'baru',
                    isNew: true, mergedInto: null, splitInto: null,
                _comboOpen: false, _parentSearch: '',
                });
            });
            sourceRow.splitInto = targetKeys;
            this.pecahGroups.push({ unit_organisasi_id: sourceRow.unit_organisasi_id, targets: targetKeys, label: this.unitLabel(sourceKey) + ' → ' + names.join(', ') });
            this.selected = [];
            this.showPecahModal = false;
        },

        undoGabung(i) {
            const g = this.gabungGroups[i];
            this.units.forEach(u => { if (u.mergedInto === g.target_key) u.mergedInto = null; });
            this.units = this.units.filter(u => u.key !== g.target_key);
            this.gabungGroups.splice(i, 1);
        },

        undoPecah(i) {
            const g = this.pecahGroups[i];
            const source = this.units.find(u => u.unit_organisasi_id === g.unit_organisasi_id && u.splitInto);
            if (source) source.splitInto = null;
            this.units = this.units.filter(u => !g.targets.includes(u.key));
            this.pecahGroups.splice(i, 1);
        },

        onSubmit() {
            const bubarList = [];
            const finalUnits = [];
            this.activeUnits().forEach(u => {
                if (!this.isBaseline && !u.isNew && u.jenis_transisi === 'bubar') {
                    bubarList.push({ unit_organisasi_id: u.unit_organisasi_id });
                    return;
                }
                finalUnits.push({
                    key: u.key,
                    unit_organisasi_id: u.unit_organisasi_id,
                    nama_unit: u.nama_unit,
                    level: u.level,
                    parent_key: u.parent_key || null,
                    mc_formasi: parseInt(u.mc_formasi) || 0,
                    keterangan: u.keterangan || null,
                    jenis_transisi: u.isNew ? 'baru' : (u.jenis_transisi || 'lanjut'),
                });
            });

            const payload = {
                units: finalUnits,
                bubar: bubarList,
                pecah: this.pecahGroups.map(g => ({ unit_organisasi_id: g.unit_organisasi_id, targets: g.targets })),
                gabung: this.gabungGroups.map(g => ({ unit_organisasi_ids: g.unit_organisasi_ids, target_key: g.target_key })),
            };

            document.getElementById('payloadInput').value = JSON.stringify(payload);
        },
    };
}
</script>
@endpush
