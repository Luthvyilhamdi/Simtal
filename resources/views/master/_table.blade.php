@push('styles')
<style>
    .master-wrap { display:grid;grid-template-columns:340px 1fr;gap:20px;align-items:start; }
    .form-card { background:white;border-radius:14px;border:1px solid #e5e7eb;padding:24px;position:sticky;top:24px; }
    .form-card-title { font-size:15px;font-weight:700;color:#111827;margin-bottom:4px; }
    .form-card-sub { font-size:12px;color:#9ca3af;margin-bottom:20px; }
    .form-group { display:flex;flex-direction:column;gap:6px;margin-bottom:14px; }
    .form-label { font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.5px; }
    .form-input { padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:9px;font-size:13px;font-family:inherit;color:#111827;background:#fafafa;outline:none;transition:all 0.15s;width:100%; }
    .form-input:focus { border-color:#16a34a;background:white;box-shadow:0 0 0 3px rgba(22,163,74,0.08); }
    .error-msg { font-size:11px;color:#ef4444; }
    .btn-save { width:100%;padding:11px;background:#15803d;color:white;border:none;border-radius:9px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;transition:background 0.15s; }
    .btn-save:hover { background:#166534; }

    .table-card { background:white;border-radius:14px;border:1px solid #e5e7eb;overflow:hidden; }
    .table-header { display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f3f4f6; }
    .table-title { font-size:14px;font-weight:700;color:#111827; }
    .table-count { font-size:12px;color:#9ca3af; }
    table { width:100%;border-collapse:collapse;font-size:13px; }
    thead th { padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid #f3f4f6;background:#f9fafb; }
    tbody td { padding:13px 16px;border-bottom:1px solid #f3f4f6;color:#374151;vertical-align:middle; }
    tbody tr:last-child td { border-bottom:none; }
    tbody tr:hover td { background:#fafaf8; }

    .action-btns { display:flex;gap:6px; }
    .btn-edit-inline { display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:7px;border:1px solid #e5e7eb;background:white;color:#374151;font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;transition:all 0.12s; }
    .btn-edit-inline:hover { background:#f0fdf4;border-color:#bbf7d0;color:#15803d; }
    .btn-del { width:30px;height:30px;border-radius:7px;border:1px solid #e5e7eb;background:white;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.12s; }
    .btn-del:hover { background:#fef2f2;border-color:#fecaca; }
    .btn-del svg { width:13px;height:13px;stroke:#ef4444;fill:none;stroke-width:2; }

    .empty-state { text-align:center;padding:40px 20px;color:#9ca3af;font-size:13px; }

    /* Modal Edit */
    .modal-backdrop { position:fixed;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(3px);z-index:1000;display:none;align-items:center;justify-content:center; }
    .modal-backdrop.show { display:flex; }
    .modal-box { background:white;border-radius:16px;padding:28px;width:100%;max-width:420px;margin:16px;box-shadow:0 20px 60px rgba(0,0,0,0.2);animation:modalIn 0.25s cubic-bezier(0.4,0,0.2,1); }
    .modal-title { font-size:16px;font-weight:700;color:#111827;margin-bottom:16px; }
    .modal-actions { display:flex;gap:10px;margin-top:20px; }
    .modal-btn { flex:1;padding:11px;border-radius:10px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all 0.15s; }
    .modal-btn.cancel { background:#f9fafb;color:#374151;border:1px solid #e5e7eb; }
    .modal-btn.save { background:#15803d;color:white; }
    .modal-btn.save:hover { background:#166534; }

    /* Modal Hapus */
    .modal-box.center { text-align:center; }
    .modal-icon-wrap { width:56px;height:56px;border-radius:50%;background:#fef2f2;display:flex;align-items:center;justify-content:center;margin:0 auto 16px; }
    .modal-icon-wrap svg { width:26px;height:26px;stroke:#ef4444;fill:none;stroke-width:1.8; }
    .modal-btn.danger { background:#ef4444;color:white; }
    .modal-btn.danger:hover { background:#dc2626; }
    @keyframes modalIn { from{opacity:0;transform:scale(0.92);}to{opacity:1;transform:scale(1);} }

    /* Toast */
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

    /* Pagination */
    .table-footer { display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-top:1px solid #f3f4f6;font-size:12px;color:#6b7280;flex-wrap:wrap;gap:10px; }
    .pagination-wrap { display:flex;align-items:center;gap:4px; }
    .page-btn { width:30px;height:30px;border-radius:7px;border:1px solid #e5e7eb;background:white;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:500;color:#374151;cursor:pointer;text-decoration:none;transition:all 0.12s; }
    .page-btn:hover { background:#f5f5f0; }
    .page-btn.active { background:#15803d;color:white;border-color:#15803d; }
    .page-btn.disabled { opacity:0.4;pointer-events:none; }
    .page-btn svg { width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2; }

    @media (max-width:768px) {
        .master-wrap { grid-template-columns:1fr; }
        .form-card { position:static; }
    }
</style>
@endpush

{{-- Toast --}}
@if(session('success'))
<div class="toast-wrap" id="toastWrap">
    <div class="toast" id="toast">
        <div class="toast-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
        <div>{{ session('success') }}</div>
        <button class="toast-close" onclick="closeToast()">×</button>
        <div class="toast-progress"></div>
    </div>
</div>
@endif

{{-- Modal Edit --}}
<div class="modal-backdrop" id="modalEdit">
    <div class="modal-box">
        <div class="modal-title">✏️ Edit {{ $title }}</div>
        <form method="POST" id="formEdit">
            @csrf @method('PUT')
            <div class="form-group">
                <label class="form-label">{{ $placeholder }} *</label>
                <input type="text" name="{{ $field }}" id="editInput" class="form-input" required />
            </div>
            <div class="modal-actions">
                <button type="button" class="modal-btn cancel" onclick="closeEditModal()">Batal</button>
                <button type="submit" class="modal-btn save">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Hapus --}}
<div class="modal-backdrop" id="modalHapus">
    <div class="modal-box center">
        <div class="modal-icon-wrap">
            <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
        </div>
        <div class="modal-title">Hapus Data?</div>
        <p style="font-size:13px;color:#6b7280;margin-bottom:24px;" id="hapusDesc">Tindakan ini tidak dapat dibatalkan.</p>
        <div class="modal-actions">
            <button type="button" class="modal-btn cancel" onclick="closeHapusModal()">Batal</button>
            <button type="button" class="modal-btn danger" onclick="submitHapus()">Ya, Hapus</button>
        </div>
    </div>
</div>
<form id="formHapus" method="POST" style="display:none">@csrf @method('DELETE')</form>

<div class="master-wrap">
    {{-- Form Tambah --}}
    <div class="form-card">
        <div class="form-card-title">➕ Tambah {{ $title }}</div>
        <div class="form-card-sub">{{ $subtitle }}</div>
        <form method="POST" action="{{ $routeStore }}">
            @csrf
            <div class="form-group">
                <label class="form-label">{{ $placeholder }} *</label>
                <input type="text" name="{{ $field }}" value="{{ old($field) }}"
                       class="form-input {{ $errors->has($field) ? 'error-input' : '' }}"
                       placeholder="Masukkan {{ strtolower($placeholder) }}..." />
                @error($field)<div class="error-msg">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn-save">Simpan {{ $title }}</button>
        </form>
    </div>

    {{-- Tabel --}}
    <div class="table-card">
        <div class="table-header">
            <div class="table-title">Daftar {{ $title }}</div>
            <div class="table-count">{{ $data->total() }} data</div>
        </div>
        <table>
            <thead>
                <tr>
                    <th style="width:60px">#</th>
                    <th>{{ $placeholder }}</th>
                    <th style="width:130px">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $item)
                <tr>
                    <td style="color:#9ca3af;">{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                    <td style="font-weight:600;">{{ $item->$field }}</td>
                    <td>
                        <div class="action-btns">
                            <button type="button" class="btn-edit-inline"
                                data-val="{{ $item->$field }}"
                                data-url="{{ $routeUpdate($item) }}"
                                onclick="openEditModal(this.dataset.val, this.dataset.url)">
                                ✏️ Edit
                            </button>
                            <button type="button" class="btn-del"
                                data-url="{{ $routeDestroy($item) }}"
                                data-val="{{ $item->$field }}"
                                onclick="openHapusModal(this.dataset.url, this.dataset.val)">
                                <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3">
                        <div class="empty-state">Belum ada data {{ strtolower($title) }}</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($data->hasPages())
        <div class="table-footer">
            <span>Menampilkan <strong>{{ $data->firstItem() }}</strong>–<strong>{{ $data->lastItem() }}</strong> dari <strong>{{ $data->total() }}</strong></span>
            <div class="pagination-wrap">
                @if($data->onFirstPage())
                    <span class="page-btn disabled"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></span>
                @else
                    <a href="{{ $data->previousPageUrl() }}" class="page-btn"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></a>
                @endif

                @php
                    $cur  = $data->currentPage();
                    $last = $data->lastPage();
                    $s    = max(1, $cur - 2);
                    $e    = min($last, $cur + 2);
                @endphp

                @if($s > 1)
                    <a href="{{ $data->url(1) }}" class="page-btn">1</a>
                    @if($s > 2)
                        <span class="page-btn disabled" style="border:none;background:transparent;">…</span>
                    @endif
                @endif

                @for($i = $s; $i <= $e; $i++)
                    <a href="{{ $data->url($i) }}" class="page-btn {{ $i == $cur ? 'active' : '' }}">{{ $i }}</a>
                @endfor

                @if($e < $last)
                    @if($e < $last - 1)
                        <span class="page-btn disabled" style="border:none;background:transparent;">…</span>
                    @endif
                    <a href="{{ $data->url($last) }}" class="page-btn">{{ $last }}</a>
                @endif

                @if($data->hasMorePages())
                    <a href="{{ $data->nextPageUrl() }}" class="page-btn"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></a>
                @else
                    <span class="page-btn disabled"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    function closeToast() {
        const t = document.getElementById('toast');
        if (!t) return;
        t.classList.add('hiding');
        setTimeout(() => document.getElementById('toastWrap')?.remove(), 300);
    }
    window.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('toast')) setTimeout(() => closeToast(), 3000);
    });

    function openEditModal(val, url) {
        document.getElementById('editInput').value = val;
        document.getElementById('formEdit').action = url;
        document.getElementById('modalEdit').classList.add('show');
        document.body.style.overflow = 'hidden';
        setTimeout(() => document.getElementById('editInput').focus(), 100);
    }
    function closeEditModal() {
        document.getElementById('modalEdit').classList.remove('show');
        document.body.style.overflow = '';
    }

    let hapusUrl = '';
    function openHapusModal(url, val) {
        hapusUrl = url;
        document.getElementById('hapusDesc').innerHTML =
            'Kamu akan menghapus <strong>' + val + '</strong>. Tindakan ini tidak dapat dibatalkan.';
        document.getElementById('modalHapus').classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    function closeHapusModal() {
        document.getElementById('modalHapus').classList.remove('show');
        document.body.style.overflow = '';
    }
    function submitHapus() {
        document.getElementById('formHapus').action = hapusUrl;
        document.getElementById('formHapus').submit();
    }

    ['modalEdit','modalHapus'].forEach(id => {
        document.getElementById(id).addEventListener('click', function(e) {
            if (e.target === this) { this.classList.remove('show'); document.body.style.overflow = ''; }
        });
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') { closeEditModal(); closeHapusModal(); }
    });
</script>
@endpush