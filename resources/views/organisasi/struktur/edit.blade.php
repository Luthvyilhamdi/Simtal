@extends('layouts.app')
@section('title', 'Edit Versi Struktur Organisasi')
@section('breadcrumb-parent', 'Riwayat Struktur Organisasi')
@section('breadcrumb', 'Edit ' . $versi->nomor_sk)

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color .12s; }
    .back-link:hover { color:#15803d; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }
    .page-header { display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:20px; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:4px; }

    .mode-banner { display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:10px;font-size:13px;font-weight:600;margin-bottom:16px; }
    .mode-banner.draft { background:#fffbeb;color:#92400e;border:1px solid #fde68a; }
    .mode-banner.final { background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0; }
    .mode-banner.error { background:#fef2f2;color:#dc2626;border:1px solid #fecaca;display:block; }

    .btn-finalize { display:inline-flex;align-items:center;gap:6px;background:#111827;color:white;padding:9px 16px;border-radius:9px;font-size:13px;font-weight:600;border:none;cursor:pointer;font-family:inherit;white-space:nowrap; }
    .btn-finalize:hover { background:#1f2937; }
    .btn-finalize svg { width:13px;height:13px;stroke:white;fill:none;stroke-width:2; }

    .form-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:28px;margin-bottom:16px; }
    .section-header { display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid #f3f4f6;flex-wrap:wrap; }
    .section-title { font-size:14px;font-weight:700;color:#111827; }
    .section-sub { font-size:12px;color:#9ca3af;margin-top:1px; }

    .form-grid { display:grid;grid-template-columns:1fr 1fr;gap:16px; }
    .form-group { display:flex;flex-direction:column;gap:6px; }
    .form-group.full { grid-column:1/-1; }
    .form-label { font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.5px; }
    .req { color:#ef4444; }
    .form-input, .form-select { padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:9px;font-size:13px;font-family:inherit;color:#111827;background:#fafafa;outline:none;transition:all .15s;width:100%; }
    .form-input:focus, .form-select:focus { border-color:#16a34a;background:white;box-shadow:0 0 0 3px rgba(22,163,74,.08); }
    .form-input.error-input { border-color:#ef4444; }
    .form-input[readonly] { background:#f3f4f6;color:#374151;cursor:default; }
    .error-msg { font-size:11px;color:#ef4444; }
    .form-hint { font-size:11px;color:#9ca3af;margin-top:2px; }

    .btn-add-row { display:inline-flex;align-items:center;gap:6px;background:#eff6ff;color:#1d4ed8;padding:8px 14px;border-radius:8px;font-size:12px;font-weight:600;border:1px solid #bfdbfe;cursor:pointer;font-family:inherit; }
    .btn-add-row:hover { background:#dbeafe; }
    .btn-add-row svg { width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2; }

    .units-table-wrap { overflow-x:auto;overflow-y:visible;border:1px solid #f3f4f6;border-radius:10px; }
    table.units-table { width:100%;border-collapse:collapse;font-size:12.5px;min-width:760px; }
    table.units-table thead th { padding:9px 10px;text-align:left;font-size:10.5px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.4px;border-bottom:1px solid #f3f4f6;background:#f9fafb;white-space:nowrap; }
    table.units-table tbody td { padding:7px 10px;border-bottom:1px solid #f3f4f6;vertical-align:middle; }
    table.units-table tbody tr:last-child td { border-bottom:none; }
    table.units-table input, table.units-table select { padding:7px 9px;border:1.3px solid #e5e7eb;border-radius:7px;font-size:12.5px;font-family:inherit;width:100%;background:white; }
    table.units-table input:focus, table.units-table select:focus { border-color:#16a34a;outline:none; }
    .col-mc { width:90px; }
    .col-aksi { width:70px; }
    .col-parent { width:170px; }

    /* Combobox parent: cuma render pilihan yg cocok pencarian & di-cap, bukan semua unit
       sekaligus — lihat catatan di script bawah soal kenapa <select> biasa gak dipakai lagi. */
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
    .btn-row-remove { background:none;border:none;color:#ef4444;cursor:pointer;font-size:12px;font-weight:600;padding:4px 6px; }
    .empty-units { text-align:center;padding:24px;color:#9ca3af;font-size:12.5px; }

    .form-actions-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:20px 28px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px; }
    .btn-cancel { display:inline-flex;align-items:center;gap:8px;background:white;color:#374151;padding:10px 20px;border-radius:9px;font-size:13px;font-weight:600;border:1.5px solid #e5e7eb;text-decoration:none;transition:all .15s; }
    .btn-cancel:hover { background:#f9fafb; }
    .btn-save { display:inline-flex;align-items:center;gap:8px;background:#15803d;color:white;padding:10px 24px;border-radius:9px;font-size:13px;font-weight:600;border:none;cursor:pointer;font-family:inherit;transition:all .15s; }
    .btn-save:hover { background:#166534; }
    .btn-save svg,.btn-cancel svg { width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2; }
    .btn-save svg { stroke:white; }

    .modal-backdrop { position:fixed;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(3px);z-index:1000;display:flex;align-items:center;justify-content:center; }
    .modal-box { background:white;border-radius:16px;padding:26px;width:100%;max-width:460px;margin:16px;box-shadow:0 20px 60px rgba(0,0,0,0.2); }
    .modal-box.center { text-align:center; }
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

    @media (max-width:640px) {
        .form-grid { grid-template-columns:1fr; }
        .form-group.full { grid-column:1; }
    }
</style>
@endpush

@section('content')
<a href="{{ route('organisasi.struktur.show', $versi) }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Detail Versi
</a>

<div x-data="{ showFinalizeModal: false }">

<div class="page-header">
    <div>
        <div class="page-title">Edit Versi — {{ $versi->nomor_sk }}</div>
        <div class="page-sub">
            @if($versi->isDraft())
                Versi ini masih <strong>draft</strong> — data SK dan seluruh roster unit masih bisa dikoreksi bebas.
            @else
                Versi ini sudah <strong>final</strong> — hanya data administratif SK yang bisa diubah.
            @endif
        </div>
    </div>
    @if($versi->isDraft())
    <button type="button" class="btn-finalize" @click="showFinalizeModal = true">
        <svg viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg>
        Finalisasi Versi
    </button>
    @endif
</div>

@if($versi->isFinal())
<div class="mode-banner final">
    🔒 Versi ini sudah final. Roster unit terkunci — perubahan struktur hanya bisa dilakukan lewat versi baru.
</div>
@endif

@if($errors->any())
<div class="mode-banner error">
    <strong>Periksa kembali isian form:</strong>
    <ul style="margin:6px 0 0 18px;">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@if($versi->isDraft())
{{-- ===== DRAFT: header + roster unit, satu form, satu payload JSON ===== --}}
<div x-data="rosterForm({{ $existingUnits->toJson() }}, {{ Illuminate\Support\Js::from($levels) }})">
<form method="POST" action="{{ route('organisasi.struktur.update', $versi) }}" @submit="onSubmit">
    @csrf
    @method('PUT')
    <input type="hidden" name="payload" id="payloadInput">

    <div class="form-card">
        <div class="section-header">
            <div>
                <div class="section-title">Data SK</div>
            </div>
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Nomor SK <span class="req">*</span></label>
                <input type="text" name="nomor_sk" value="{{ old('nomor_sk', $versi->nomor_sk) }}" class="form-input @error('nomor_sk') error-input @enderror" required maxlength="100">
                @error('nomor_sk')<div class="error-msg">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Tanggal SK <span class="req">*</span></label>
                <input type="date" name="tanggal_sk" value="{{ old('tanggal_sk', $versi->tanggal_sk->format('Y-m-d')) }}" class="form-input @error('tanggal_sk') error-input @enderror" required>
                @error('tanggal_sk')<div class="error-msg">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Tanggal Mulai Berlaku <span class="req">*</span></label>
                <input type="date" name="tanggal_mulai_berlaku" value="{{ old('tanggal_mulai_berlaku', $versi->tanggal_mulai_berlaku->format('Y-m-d')) }}" class="form-input @error('tanggal_mulai_berlaku') error-input @enderror" required>
                <div class="form-hint">Setelah versi ini difinalisasi, tanggal ini akan ikut terkunci.</div>
                @error('tanggal_mulai_berlaku')<div class="error-msg">{{ $message }}</div>@enderror
            </div>
            <div class="form-group full">
                <label class="form-label">Keterangan</label>
                <textarea name="keterangan" class="form-input" rows="2">{{ old('keterangan', $versi->keterangan) }}</textarea>
            </div>
        </div>
    </div>

    <div class="form-card">
        <div class="section-header">
            <div>
                <div class="section-title">Daftar Unit Organisasi</div>
                <div class="section-sub">Koreksi bebas — tambah, hapus, atau ubah unit apa pun selama versi ini masih draft.</div>
            </div>
            <button type="button" class="btn-add-row" @click="addUnit()">
                <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                Tambah Unit
            </button>
        </div>

        <div class="units-table-wrap">
            <table class="units-table">
                <thead>
                    <tr>
                        <th>Nama Unit</th>
                        <th>Level</th>
                        <th class="col-parent">Parent</th>
                        <th class="col-mc">MC Formasi</th>
                        <th>Keterangan</th>
                        <th class="col-aksi"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="row in units" :key="row.key">
                        <tr>
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
                                            <template x-for="opt in parentMatches(row)" :key="opt.key">
                                                <div class="parent-option" x-text="opt.nama_unit" @click="selectParent(row, opt.key)"></div>
                                            </template>
                                            <div class="parent-empty" x-show="parentMatches(row).length === 0" x-cloak>Tidak ada hasil</div>
                                        </div>
                                    </div>
                                </template>
                            </td>
                            <td class="col-mc"><input type="number" min="0" x-model.number="row.mc_formasi"></td>
                            <td><input type="text" x-model="row.keterangan"></td>
                            <td class="col-aksi">
                                <button type="button" class="btn-row-remove" @click="removeUnit(row.key)">Hapus</button>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="units.length === 0">
                        <td colspan="6" class="empty-units">Belum ada unit. Klik "Tambah Unit" untuk mulai.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="form-actions-card">
        <a href="{{ route('organisasi.struktur.show', $versi) }}" class="btn-cancel">Batal</a>
        <button type="submit" class="btn-save">
            <svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            Simpan Perubahan
        </button>
    </div>
</form>
</div>
@else
{{-- ===== FINAL: hanya header SK, roster tidak ditampilkan sama sekali ===== --}}
<form method="POST" action="{{ route('organisasi.struktur.update', $versi) }}">
    @csrf
    @method('PUT')

    <div class="form-card">
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Nomor SK <span class="req">*</span></label>
                <input type="text" name="nomor_sk" value="{{ old('nomor_sk', $versi->nomor_sk) }}" class="form-input @error('nomor_sk') error-input @enderror" required maxlength="100">
                @error('nomor_sk')<div class="error-msg">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Tanggal SK <span class="req">*</span></label>
                <input type="date" name="tanggal_sk" value="{{ old('tanggal_sk', $versi->tanggal_sk->format('Y-m-d')) }}" class="form-input @error('tanggal_sk') error-input @enderror" required>
                @error('tanggal_sk')<div class="error-msg">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Tanggal Mulai Berlaku</label>
                <input type="date" value="{{ $versi->tanggal_mulai_berlaku->format('Y-m-d') }}" class="form-input" readonly>
                <div class="form-hint">Terkunci — versi sudah final.</div>
            </div>
            <div class="form-group full">
                <label class="form-label">Keterangan</label>
                <textarea name="keterangan" class="form-input" rows="3">{{ old('keterangan', $versi->keterangan) }}</textarea>
            </div>
        </div>
    </div>

    <div class="form-actions-card">
        <a href="{{ route('organisasi.struktur.show', $versi) }}" class="btn-cancel">Batal</a>
        <button type="submit" class="btn-save">
            <svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            Simpan Perubahan
        </button>
    </div>
</form>
@endif

{{-- ===== MODAL KONFIRMASI FINALISASI ===== --}}
@if($versi->isDraft())
<div class="modal-backdrop" x-show="showFinalizeModal" x-cloak @keydown.escape.window="showFinalizeModal = false">
    <div class="modal-box center" @click.outside="showFinalizeModal = false">
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
@endsection

@if($versi->isDraft())
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function rosterForm(existingUnits, levels) {
    return {
        levels: levels,
        // _comboOpen/_parentSearch: state UI semata, tidak ikut terkirim (onSubmit whitelist field secara eksplisit).
        units: existingUnits.map(u => ({ ...u, _comboOpen: false, _parentSearch: '' })),
        counter: 0,

        addUnit() {
            this.units.push({
                key: 'n' + (++this.counter), unit_organisasi_id: null,
                nama_unit: '', level: this.levels[0], parent_key: '',
                mc_formasi: 0, keterangan: '', _comboOpen: false, _parentSearch: '',
            });
        },

        removeUnit(key) {
            this.units = this.units.filter(u => u.key !== key);
        },

        // ===== Combobox parent =====
        // Dropdown parent SEBELUMNYA me-render <option> utk SEMUA unit lain di SETIAP baris
        // (x-for bersarang) — utk ratusan unit ini jadi ~n² elemen DOM sekaligus (mis. 360
        // baris x 359 opsi = ~129rb <option>) dan freeze browser. Sekarang: baris yang combo-nya
        // TERTUTUP cuma render 1 div teks (nama parent terpilih) tanpa opsi apa pun; opsi hanya
        // dirender saat combo itu DIBUKA, dan dibatasi (slice) ke 30 hasil pencarian teratas —
        // jadi biar unit makin banyak, jumlah elemen yang dirender tetap terbatas/konstan.
        openParentCombo(row) {
            row._parentSearch = '';
            row._comboOpen = true;
        },

        selectParent(row, key) {
            row.parent_key = key;
            row._comboOpen = false;
        },

        parentLabel(row) {
            if (!row.parent_key) return '(Tidak ada / Root)';
            const found = this.units.find(u => u.key === row.parent_key);
            return found ? found.nama_unit : '(Tidak ada / Root)';
        },

        // Kandidat parent: unit lain (bukan diri sendiri), levelnya harus lebih tinggi (rank
        // lebih kecil di this.levels) drpd level baris ini — cocok dgn validasi server-side
        // di validateParentHierarchy(). Pengecualian: same-level diperbolehkan KHUSUS kalau
        // kandidat itu sendiri root (tanpa parent) — mis. "Utama" membawahi beberapa direktorat
        // lain yg level-nya sama krn cuma 1 nilai enum utk 2 tingkat riil. Dicocokkan ke
        // pencarian teks, lalu di-cap 30 hasil supaya render tetap ringan brp pun total unit-nya.
        parentMatches(row) {
            const selfRank = this.levels.indexOf(row.level);
            const query = (row._parentSearch || '').toLowerCase();
            return this.units
                .filter(u => u.key !== row.key)
                .filter(u => {
                    const rank = this.levels.indexOf(u.level);
                    if (selfRank === -1 || rank === -1) return true;
                    if (rank < selfRank) return true;
                    if (rank === selfRank) return !u.parent_key;
                    return false;
                })
                .filter(u => !query || u.nama_unit.toLowerCase().includes(query))
                .slice(0, 30);
        },

        onSubmit() {
            const payload = {
                units: this.units.map(u => ({
                    key: u.key,
                    unit_organisasi_id: u.unit_organisasi_id,
                    nama_unit: u.nama_unit,
                    level: u.level,
                    parent_key: u.parent_key || null,
                    mc_formasi: parseInt(u.mc_formasi) || 0,
                    keterangan: u.keterangan || null,
                })),
            };
            document.getElementById('payloadInput').value = JSON.stringify(payload);
        },
    };
}
</script>
@endpush
@else
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush
@endif
