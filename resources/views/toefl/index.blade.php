@extends('layouts.app')
@section('title', 'Nilai TOEFL')
@section('breadcrumb-parent', $karyawan->nama)
@section('breadcrumb', 'TOEFL')

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color 0.12s; }
    .back-link:hover { color:#15803d; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }

    .profile-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:20px 24px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px; }
    .profile-left { display:flex;align-items:center;gap:14px; }
    .profile-avatar { width:52px;height:52px;border-radius:50%;background:#dcfce7;color:#15803d;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:700;flex-shrink:0;overflow:hidden;border:2px solid #bbf7d0; }
    .profile-avatar img { width:100%;height:100%;object-fit:cover; }
    .profile-name { font-size:16px;font-weight:700;color:#111827; }
    .profile-meta { font-size:12px;color:#6b7280;margin-top:2px; }
    .profile-tags { display:flex;gap:6px;flex-wrap:wrap;margin-top:6px; }
    .profile-tag { padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:#f3f4f6;color:#374151; }
    .profile-tag.green { background:#dcfce7;color:#15803d; }
    .profile-stats { display:flex;gap:24px; }
    .stat { text-align:center; }
    .stat-num { font-size:22px;font-weight:700;color:#111827; }
    .stat-badge { display:inline-flex;align-items:center;padding:4px 12px;border-radius:20px;font-size:14px;font-weight:800;background:#eef2ff;color:#4338ca; }
    .stat-label { font-size:11px;color:#9ca3af;margin-top:2px; }

    .page-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px; }
    .page-title { font-size:18px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:3px; }

    .btn-primary { display:inline-flex;align-items:center;gap:8px;background:#15803d;color:white;padding:10px 18px;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;transition:background 0.15s;white-space:nowrap;border:none;cursor:pointer;font-family:inherit; }
    .btn-primary:hover { background:#166534; }
    .btn-primary svg { width:14px;height:14px;stroke:white;fill:none;stroke-width:2.5; }

    .tf-list { display:flex;flex-direction:column;gap:12px; }
    .tf-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:16px 20px;display:flex;align-items:center;gap:16px;transition:box-shadow 0.15s; }
    .tf-card:hover { box-shadow:0 4px 16px rgba(0,0,0,0.06); }
    .tf-skor { flex-shrink:0;min-width:70px;text-align:center; }
    .tf-skor-num { font-size:24px;font-weight:800;color:#4338ca;line-height:1; }
    .tf-skor-label { font-size:10px;color:#9ca3af;font-weight:600;text-transform:uppercase;letter-spacing:0.3px;margin-top:3px; }
    .tf-body { flex:1;min-width:0; }
    .tf-line1 { font-size:14px;font-weight:700;color:#111827;display:flex;align-items:center;gap:8px;flex-wrap:wrap; }
    .tf-jenis { font-size:11px;font-weight:700;background:#eef2ff;color:#4338ca;padding:2px 9px;border-radius:20px; }
    .tf-meta { font-size:12.5px;color:#6b7280;margin-top:4px;display:flex;gap:10px;flex-wrap:wrap; }
    .tf-ket { font-size:12.5px;color:#6b7280;margin-top:5px;font-style:italic; }
    .tf-actions { display:flex;gap:8px;flex-shrink:0; }

    .icon-btn { width:32px;height:32px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:all .12s;flex-shrink:0;text-decoration:none; }
    .icon-btn svg { width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2; }
    .icon-btn.open { color:#15803d;border-color:#bbf7d0;background:#f0fdf4; }
    .icon-btn.open:hover { background:#dcfce7; }
    .icon-btn.edit { color:#374151; }
    .icon-btn.edit:hover { background:#f9fafb;border-color:#d1d5db; }
    .icon-btn.del { color:#ef4444; }
    .icon-btn.del:hover { background:#fef2f2;border-color:#fecaca; }

    .empty-state { text-align:center;padding:60px 20px;background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow); }
    .empty-state svg { width:48px;height:48px;margin:0 auto 12px;display:block;stroke:#d1d5db;fill:none;stroke-width:1.5; }
    .empty-state p { font-size:14px;font-weight:600;color:#6b7280;margin-bottom:4px; }

    .toast-wrap { position:fixed;top:20px;right:20px;z-index:9999;pointer-events:none; }
    .toast { display:flex;align-items:center;gap:10px;background:white;border:1px solid #bbf7d0;border-left:4px solid #16a34a;border-radius:12px;padding:14px 16px;box-shadow:0 8px 32px rgba(0,0,0,0.12);font-size:13px;color:#15803d;font-weight:500;min-width:280px;position:relative;overflow:hidden;pointer-events:all;animation:toastIn 0.35s cubic-bezier(0.4,0,0.2,1) forwards; }
    .toast.hiding { animation:toastOut 0.3s forwards; }
    .toast-icon { width:22px;height:22px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .toast-icon svg { width:12px;height:12px;stroke:#16a34a;fill:none;stroke-width:2.5; }
    .toast-close { border:none;background:transparent;color:#9ca3af;cursor:pointer;font-size:18px;padding:0;margin-left:auto; }
    .toast-progress { position:absolute;bottom:0;left:0;height:3px;background:#16a34a;animation:toastProgress 3s linear forwards; }
    @keyframes toastIn { from{opacity:0;transform:translateX(110%);}to{opacity:1;transform:translateX(0);} }
    @keyframes toastOut { from{opacity:1;}to{opacity:0;transform:translateX(110%);} }
    @keyframes toastProgress { from{width:100%;}to{width:0%;} }

    .modal-backdrop { position:fixed;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(3px);z-index:1000;display:none;align-items:center;justify-content:center; }
    .modal-backdrop.show { display:flex; }
    .modal-box { background:white;border-radius:16px;padding:28px;width:100%;max-width:460px;margin:16px;box-shadow:0 20px 60px rgba(0,0,0,0.2);animation:modalIn 0.25s cubic-bezier(0.4,0,0.2,1);max-height:92vh;overflow-y:auto; }
    .modal-box.center { text-align:center;max-width:400px; }
    .modal-icon-wrap { width:56px;height:56px;border-radius:50%;background:#fef2f2;display:flex;align-items:center;justify-content:center;margin:0 auto 16px; }
    .modal-icon-wrap svg { width:26px;height:26px;stroke:#ef4444;fill:none;stroke-width:1.8; }
    .modal-title { font-size:17px;font-weight:700;color:#111827;margin-bottom:8px; }
    .modal-desc { font-size:13px;color:#6b7280;line-height:1.6;margin-bottom:24px; }
    .modal-actions { display:flex;gap:10px; }
    .modal-btn { flex:1;padding:11px;border-radius:10px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all 0.15s; }
    .modal-btn.cancel { background:#f9fafb;color:#374151;border:1px solid #e5e7eb; }
    .modal-btn.danger { background:#ef4444;color:white; }
    .modal-btn.danger:hover { background:#dc2626; }
    .modal-btn.green { background:#15803d;color:white; }
    .modal-btn.green:hover { background:#166534; }
    @keyframes modalIn { from{opacity:0;transform:scale(0.92);}to{opacity:1;transform:scale(1);} }

    .form-grid { display:grid;grid-template-columns:1fr 1fr;gap:12px; }
    .form-field { margin-bottom:12px;text-align:left; }
    .form-field.full { grid-column:1/-1; }
    .form-field label { display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:6px; }
    .form-field input, .form-field select, .form-field textarea { width:100%;border:1px solid #d1d5db;border-radius:9px;padding:10px 12px;font-size:13px;font-family:inherit;background:#fff; }
    .form-field textarea { resize:vertical;min-height:60px; }
    .form-field input:focus, .form-field select:focus, .form-field textarea:focus { outline:none;border-color:#15803d;box-shadow:0 0 0 3px rgba(21,128,61,0.1); }

    @media (max-width:640px) {
        .profile-card { flex-direction:column; }
        .profile-stats { width:100%;justify-content:space-around;border-top:1px solid #f3f4f6;padding-top:12px; }
        .form-grid { grid-template-columns:1fr; }
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

<a href="{{ route('karyawan.show', $karyawan) }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Profil {{ $karyawan->nama }}
</a>

<div class="profile-card">
    <div class="profile-left">
        <div class="profile-avatar">
            @if($karyawan->foto)<img src="{{ Storage::url($karyawan->foto) }}" alt="">@else{{ initials($karyawan->nama) }}@endif
        </div>
        <div>
            <div class="profile-name">{{ $karyawan->nama }}</div>
            <div class="profile-meta">{{ $karyawan->jabatan_saat_ini ?? $karyawan->jabatan->nama_jabatan ?? '-' }}</div>
            <div class="profile-tags">
                <span class="profile-tag green">NIK {{ $karyawan->nik }}</span>
                <span class="profile-tag">{{ $karyawan->jobGrade->job_grade ?? '-' }}</span>
                <span class="profile-tag">{{ $karyawan->personGrade->person_grade ?? '-' }}</span>
            </div>
        </div>
    </div>
    <div class="profile-stats">
        <div class="stat">
            <div class="stat-num">{{ $toefls->count() }}</div>
            <div class="stat-label">Jumlah Tes</div>
        </div>
        <div class="stat">
            <div class="stat-badge">{{ optional($toefls->first())->skor ?? '-' }}</div>
            <div class="stat-label" style="margin-top:6px;">Skor Terbaru</div>
        </div>
    </div>
</div>

<div class="page-header">
    <div>
        <div class="page-title">Nilai TOEFL</div>
        <div class="page-sub">Kelola hasil tes TOEFL {{ $karyawan->nama }} (bisa lebih dari satu).</div>
    </div>
    <button type="button" class="btn-primary" onclick="openForm('add')">
        <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Nilai TOEFL
    </button>
</div>

@if($toefls->count() > 0)
<div class="tf-list">
    @foreach($toefls as $t)
    <div class="tf-card">
        <div class="tf-skor">
            <div class="tf-skor-num">{{ $t->skor }}</div>
            <div class="tf-skor-label">Skor</div>
        </div>
        <div class="tf-body">
            <div class="tf-line1">
                @if($t->jenis)<span class="tf-jenis">{{ $t->jenis }}</span>@endif
                {{ $t->lembaga ?: 'Tanpa lembaga' }}
            </div>
            <div class="tf-meta">
                <span>📅 {{ $t->tanggal_tes ? $t->tanggal_tes->format('d M Y') : 'Tanpa tanggal' }}</span>
            </div>
            @if($t->keterangan)<div class="tf-ket">{{ $t->keterangan }}</div>@endif
        </div>
        <div class="tf-actions">
            @if($t->link_file)
            <a href="{{ $t->link_file }}" target="_blank" rel="noopener" class="icon-btn open" title="Buka sertifikat">
                <svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            </a>
            @endif
            <button type="button" class="icon-btn edit" title="Ubah"
                data-id="{{ $t->id }}" data-skor="{{ $t->skor }}" data-jenis="{{ $t->jenis }}"
                data-tanggal="{{ optional($t->tanggal_tes)->format('Y-m-d') }}" data-lembaga="{{ $t->lembaga }}"
                data-keterangan="{{ $t->keterangan }}" data-link="{{ $t->link_file }}"
                onclick="openForm('edit', this.dataset)">
                <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </button>
            <button type="button" class="icon-btn del" title="Hapus"
                data-url="{{ route('toefl.destroy', [$karyawan, $t]) }}" data-label="Skor {{ $t->skor }}{{ $t->jenis ? ' ('.$t->jenis.')' : '' }}"
                onclick="openHapus(this.dataset.url, this.dataset.label)">
                <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
            </button>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="empty-state">
    <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
    <p>Belum ada nilai TOEFL</p>
    <span style="font-size:12px;color:#9ca3af;">Klik "Tambah Nilai TOEFL" untuk menambahkan</span>
</div>
@endif

{{-- MODAL TAMBAH / EDIT --}}
<div class="modal-backdrop" id="modalForm">
    <div class="modal-box">
        <div class="modal-title" id="formTitle">Tambah Nilai TOEFL</div>
        <form id="tfForm" method="POST" style="margin-top:14px;">
            @csrf
            <input type="hidden" name="_method" id="tfMethod" value="POST">
            <div class="form-grid">
                <div class="form-field">
                    <label>Skor <span style="color:#dc2626;">*</span></label>
                    <input type="number" name="skor" id="fSkor" min="0" max="677" step="0.5" required placeholder="cth: 550 (TOEFL) / 6.5 (IELTS)">
                </div>
                <div class="form-field">
                    <label>Jenis</label>
                    <select name="jenis" id="fJenis">
                        <option value="">-- Pilih --</option>
                        @foreach(\App\Models\Toefl::JENIS as $j)<option value="{{ $j }}">{{ $j }}</option>@endforeach
                    </select>
                </div>
                <div class="form-field">
                    <label>Tanggal Tes</label>
                    <input type="date" name="tanggal_tes" id="fTanggal">
                </div>
                <div class="form-field">
                    <label>Lembaga / Tempat Tes</label>
                    <input type="text" name="lembaga" id="fLembaga" maxlength="255" placeholder="cth: ETS / Kampus">
                </div>
                <div class="form-field full">
                    <label>Link Sertifikat (opsional)</label>
                    <input type="url" name="link_file" id="fLink" maxlength="2048" placeholder="https://drive.google.com/...">
                </div>
                <div class="form-field full">
                    <label>Keterangan (opsional)</label>
                    <textarea name="keterangan" id="fKeterangan" maxlength="1000" placeholder="Catatan tambahan"></textarea>
                </div>
            </div>
            <div class="modal-actions" style="margin-top:14px;">
                <button type="button" class="modal-btn cancel" onclick="closeForm()">Batal</button>
                <button type="submit" class="modal-btn green">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL HAPUS --}}
<div class="modal-backdrop" id="modalHapus">
    <div class="modal-box center">
        <div class="modal-icon-wrap"><svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg></div>
        <div class="modal-title">Hapus Nilai TOEFL?</div>
        <div class="modal-desc" id="hapusDesc">Tindakan ini tidak dapat dibatalkan.</div>
        <div class="modal-actions">
            <button class="modal-btn cancel" onclick="closeHapus()">Batal</button>
            <button class="modal-btn danger" onclick="submitHapus()">Ya, Hapus</button>
        </div>
    </div>
</div>
<form id="formHapus" method="POST" style="display:none">@csrf @method('DELETE')</form>

@endsection

@push('scripts')
<script>
const STORE_URL  = "{{ route('toefl.store', $karyawan) }}";
const UPDATE_TPL = "{{ route('toefl.update', [$karyawan, '__ID__']) }}";

function closeToast() {
    const t = document.getElementById('toast'); if (!t) return;
    t.classList.add('hiding'); setTimeout(() => document.getElementById('toastWrap')?.remove(), 300);
}
window.addEventListener('DOMContentLoaded', () => { if (document.getElementById('toast')) setTimeout(() => closeToast(), 3000); });

function openForm(mode, data = {}) {
    const form = document.getElementById('tfForm');
    if (mode === 'edit') {
        document.getElementById('formTitle').textContent = 'Ubah Nilai TOEFL';
        document.getElementById('tfMethod').value = 'PUT';
        form.action = UPDATE_TPL.replace('__ID__', data.id);
        document.getElementById('fSkor').value       = data.skor || '';
        document.getElementById('fJenis').value      = data.jenis || '';
        document.getElementById('fTanggal').value    = data.tanggal || '';
        document.getElementById('fLembaga').value    = data.lembaga || '';
        document.getElementById('fKeterangan').value = data.keterangan || '';
        document.getElementById('fLink').value       = data.link || '';
    } else {
        document.getElementById('formTitle').textContent = 'Tambah Nilai TOEFL';
        document.getElementById('tfMethod').value = 'POST';
        form.action = STORE_URL;
        form.reset();
    }
    document.getElementById('modalForm').classList.add('show'); document.body.style.overflow = 'hidden';
    setTimeout(() => document.getElementById('fSkor').focus(), 50);
}
function closeForm() { document.getElementById('modalForm').classList.remove('show'); document.body.style.overflow = ''; }
document.getElementById('modalForm').addEventListener('click', function(e) { if (e.target === this) closeForm(); });

let deleteUrl = '';
function openHapus(url, label) {
    deleteUrl = url;
    document.getElementById('hapusDesc').innerHTML = 'Kamu akan menghapus <strong>' + label + '</strong>.<br>Tindakan ini tidak dapat dibatalkan.';
    document.getElementById('modalHapus').classList.add('show'); document.body.style.overflow = 'hidden';
}
function closeHapus() { document.getElementById('modalHapus').classList.remove('show'); document.body.style.overflow = ''; }
function submitHapus() { const f = document.getElementById('formHapus'); f.action = deleteUrl; f.submit(); }
document.getElementById('modalHapus').addEventListener('click', function(e) { if (e.target === this) closeHapus(); });

@if($errors->any())
    openForm('{{ old('_method') === 'PUT' ? 'edit' : 'add' }}');
    document.getElementById('fSkor').value       = @json(old('skor'));
    document.getElementById('fJenis').value      = @json(old('jenis'));
    document.getElementById('fTanggal').value    = @json(old('tanggal_tes'));
    document.getElementById('fLembaga').value    = @json(old('lembaga'));
    document.getElementById('fKeterangan').value = @json(old('keterangan'));
    document.getElementById('fLink').value       = @json(old('link_file'));
@endif

document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeForm(); closeHapus(); } });
</script>
@endpush
