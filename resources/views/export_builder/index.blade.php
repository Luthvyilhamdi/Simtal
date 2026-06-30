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

    .card { background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px 20px; }
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
    .field select { width:100%;border:1px solid #d1d5db;border-radius:8px;padding:8px 10px;font-size:13px;background:#fff; }
    .field select:focus { outline:none;border-color:#15803d;box-shadow:0 0 0 2px rgba(21,128,61,.1); }

    .hint { font-size:11px;color:#9ca3af;margin-top:4px; }
    .err { font-size:12px;color:#dc2626;margin-top:6px; }

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

    .toolbar { display:flex;gap:12px;margin-bottom:12px; }
    .link-btn { font-size:12px;color:#15803d;background:none;border:none;cursor:pointer;font-weight:600;text-decoration:underline; }
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
            <div class="toolbar">
                <button type="button" class="link-btn" onclick="toggleAll(true)">Pilih semua</button>
                <button type="button" class="link-btn" onclick="toggleAll(false)">Kosongkan</button>
            </div>

            @foreach($grouped as $grup => $kolom)
                <div class="col-group">
                    <div class="col-group-label">{{ $grup }}</div>
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
        </div>

        {{-- Filter & Aksi --}}
        <div>
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

    function toggleAll(state) {
        document.querySelectorAll('input[name="columns[]"]').forEach(cb => cb.checked = state);
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
    }

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
