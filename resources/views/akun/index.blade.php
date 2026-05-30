@extends('layouts.app')
@section('title', 'Manajemen Akun')
@section('breadcrumb', 'Manajemen Akun')

@push('styles')
<style>
    .page-header { display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:24px;gap:12px;flex-wrap:wrap; }
    .page-title { font-size:22px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:4px; }

    .master-wrap { display:grid;grid-template-columns:360px 1fr;gap:20px;align-items:start; }

    .form-card { background:white;border-radius:14px;border:1px solid #e5e7eb;padding:24px;position:sticky;top:24px; }
    .form-card-title { font-size:15px;font-weight:700;color:#111827;margin-bottom:4px; }
    .form-card-sub { font-size:12px;color:#9ca3af;margin-bottom:20px; }
    .form-group { display:flex;flex-direction:column;gap:6px;margin-bottom:14px; }
    .form-label { font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.5px; }
    .form-input { padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:9px;font-size:13px;font-family:inherit;color:#111827;background:#fafafa;outline:none;transition:all 0.15s;width:100%; }
    .form-input:focus { border-color:#16a34a;background:white;box-shadow:0 0 0 3px rgba(22,163,74,0.08); }
    .error-msg { font-size:11px;color:#ef4444; }
    .form-hint { font-size:11px;color:#9ca3af;margin-top:2px; }

    .select-wrap { position:relative; }
    .select-wrap::after { content:\'\';position:absolute;right:14px;top:50%;transform:translateY(-50%);width:0;height:0;border-left:4px solid transparent;border-right:4px solid transparent;border-top:5px solid #9ca3af;pointer-events:none; }
    .select-wrap select { appearance:none;-webkit-appearance:none;padding-right:36px;cursor:pointer;width:100%; }

    .btn-save { width:100%;padding:11px;background:#15803d;color:white;border:none;border-radius:9px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;transition:background 0.15s; }
    .btn-save:hover { background:#166534; }

    /* Role info box */
    .role-info { background:#f0fdf4;border:1px solid #bbf7d0;border-radius:9px;padding:10px 12px;margin-top:6px;font-size:11px;color:#374151;line-height:1.7; }
    .role-info strong { color:#15803d; }

    .table-card { background:white;border-radius:14px;border:1px solid #e5e7eb;overflow:hidden; }
    .table-header { display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f3f4f6; }
    .table-title { font-size:14px;font-weight:700;color:#111827; }
    table { width:100%;border-collapse:collapse;font-size:13px; }
    thead th { padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid #f3f4f6;background:#f9fafb; }
    tbody td { padding:13px 16px;border-bottom:1px solid #f3f4f6;color:#374151;vertical-align:middle; }
    tbody tr:last-child td { border-bottom:none; }
    tbody tr:hover td { background:#fafaf8; }

    .user-info { display:flex;align-items:center;gap:10px; }
    .user-avatar { width:36px;height:36px;border-radius:50%;background:#16a34a;color:white;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0; }
    .user-name { font-weight:600;color:#111827;font-size:13px; }
    .user-email { font-size:11px;color:#9ca3af;margin-top:1px; }

    .role-badge { display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700; }
    .role-super { background:#fef3c7;color:#d97706; }
    .role-admin { background:#eff6ff;color:#1d4ed8; }
    .role-user  { background:#f3f4f6;color:#6b7280; }
    .you-badge { display:inline-flex;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:700;background:#dcfce7;color:#15803d;margin-left:6px; }

    .action-btns { display:flex;gap:6px; }
    .btn-edit-sm { display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:7px;border:1px solid #e5e7eb;background:white;color:#374151;font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;transition:all 0.12s; }
    .btn-edit-sm:hover { background:#f0fdf4;border-color:#bbf7d0;color:#15803d; }
    .btn-del-sm { width:30px;height:30px;border-radius:7px;border:1px solid #e5e7eb;background:white;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.12s; }
    .btn-del-sm:hover { background:#fef2f2;border-color:#fecaca; }
    .btn-del-sm svg { width:13px;height:13px;stroke:#ef4444;fill:none;stroke-width:2; }
    .btn-del-sm.disabled { opacity:0.4;cursor:not-allowed;pointer-events:none; }

    .toast-wrap { position:fixed;top:20px;right:20px;z-index:9999;pointer-events:none; }
    .toast { display:flex;align-items:center;gap:10px;background:white;border:1px solid #bbf7d0;border-left:4px solid #16a34a;border-radius:12px;padding:14px 16px;box-shadow:0 8px 32px rgba(0,0,0,0.12);font-size:13px;color:#15803d;font-weight:500;min-width:280px;position:relative;overflow:hidden;pointer-events:all;animation:toastIn 0.35s cubic-bezier(0.4,0,0.2,1) forwards; }
    .toast.error { border-color:#fecaca;border-left-color:#ef4444;color:#dc2626; }
    .toast.hiding { animation:toastOut 0.3s forwards; }
    .toast-icon { width:22px;height:22px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .toast.error .toast-icon { background:#fef2f2; }
    .toast-icon svg { width:12px;height:12px;stroke:#16a34a;fill:none;stroke-width:2.5; }
    .toast.error .toast-icon svg { stroke:#ef4444; }
    .toast-close { border:none;background:transparent;color:#9ca3af;cursor:pointer;font-size:18px;padding:0;margin-left:auto; }
    .toast-progress { position:absolute;bottom:0;left:0;height:3px;background:#16a34a;animation:toastProgress 3s linear forwards; }
    .toast.error .toast-progress { background:#ef4444; }
    @keyframes toastIn { from{opacity:0;transform:translateX(110%);}to{opacity:1;transform:translateX(0);} }
    @keyframes toastOut { from{opacity:1;}to{opacity:0;transform:translateX(110%);} }
    @keyframes toastProgress { from{width:100%;}to{width:0%;} }

    .modal-backdrop { position:fixed;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(3px);z-index:1000;display:none;align-items:center;justify-content:center; }
    .modal-backdrop.show { display:flex; }
    .modal-box { background:white;border-radius:16px;padding:28px;width:100%;max-width:460px;margin:16px;box-shadow:0 20px 60px rgba(0,0,0,0.2);animation:modalIn 0.25s cubic-bezier(0.4,0,0.2,1); }
    .modal-title { font-size:16px;font-weight:700;color:#111827;margin-bottom:16px; }
    .modal-actions { display:flex;gap:10px;margin-top:20px; }
    .modal-btn { flex:1;padding:11px;border-radius:10px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all 0.15s; }
    .modal-btn.cancel { background:#f9fafb;color:#374151;border:1px solid #e5e7eb; }
    .modal-btn.save { background:#15803d;color:white; }
    .modal-btn.save:hover { background:#166534; }
    .modal-box.center { text-align:center; }
    .modal-icon-wrap { width:56px;height:56px;border-radius:50%;background:#fef2f2;display:flex;align-items:center;justify-content:center;margin:0 auto 16px; }
    .modal-icon-wrap svg { width:26px;height:26px;stroke:#ef4444;fill:none;stroke-width:1.8; }
    .modal-btn.danger { background:#ef4444;color:white; }
    .modal-btn.danger:hover { background:#dc2626; }
    @keyframes modalIn { from{opacity:0;transform:scale(0.92);}to{opacity:1;transform:scale(1);} }

    .table-footer { display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-top:1px solid #f3f4f6;font-size:12px;color:#6b7280;flex-wrap:wrap;gap:10px; }
    .pagination-wrap { display:flex;align-items:center;gap:4px; }
    .page-btn { width:28px;height:28px;border-radius:7px;border:1px solid #e5e7eb;background:white;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:500;color:#374151;cursor:pointer;text-decoration:none;transition:all 0.12s; }
    .page-btn:hover { background:#f5f5f0; }
    .page-btn.active { background:#15803d;color:white;border-color:#15803d; }
    .page-btn.disabled { opacity:0.4;pointer-events:none; }
    .page-btn svg { width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2; }
    .empty-state { text-align:center;padding:40px 20px;color:#9ca3af;font-size:13px; }

    @media (max-width:768px) {
        .master-wrap { grid-template-columns:1fr; }
        .form-card { position:static; }
    }
</style>
@endpush

@section('content')

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

@if(session('error'))
<div class="toast-wrap" id="toastWrap">
    <div class="toast error" id="toast">
        <div class="toast-icon"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></div>
        <div>{{ session('error') }}</div>
        <button class="toast-close" onclick="closeToast()">×</button>
        <div class="toast-progress"></div>
    </div>
</div>
@endif

{{-- Modal Edit --}}
<div class="modal-backdrop" id="modalEdit">
    <div class="modal-box">
        <div class="modal-title">✏️ Edit Akun</div>
        <form method="POST" id="formEdit">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label class="form-label">Nama</label>
                <input type="text" name="name" id="editName" class="form-input" required />
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" id="editEmail" class="form-input" required />
            </div>
            <div class="form-group">
                <label class="form-label">Role</label>
                <div class="select-wrap">
                    <select name="role" id="editRole" class="form-input">
                        <option value="user">User (Hanya Struktur Organisasi)</option>
                        <option value="admin">Admin</option>
                        <option value="super_admin">Super Admin</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Password Baru</label>
                <input type="password" name="password" class="form-input" placeholder="Kosongkan jika tidak diubah" />
                <span class="form-hint">Kosongkan jika tidak ingin mengubah password</span>
            </div>
            <div class="form-group">
                <label class="form-label">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" class="form-input" placeholder="Ulangi password baru" />
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
        <div class="modal-title">Hapus Akun?</div>
        <p style="font-size:13px;color:#6b7280;margin-bottom:24px;" id="hapusDesc">Tindakan ini tidak dapat dibatalkan.</p>
        <div class="modal-actions">
            <button type="button" class="modal-btn cancel" onclick="closeHapusModal()">Batal</button>
            <button type="button" class="modal-btn danger" onclick="submitHapus()">Ya, Hapus</button>
        </div>
    </div>
</div>
<form id="formHapus" method="POST" style="display:none">
    @csrf
    @method('DELETE')
</form>

<div class="page-header">
    <div>
        <div class="page-title">Manajemen Akun</div>
        <div class="page-sub">Kelola akun pengguna SIMTAL</div>
    </div>
</div>

<div class="master-wrap">

    {{-- Form Tambah --}}
    <div class="form-card">
        <div class="form-card-title">➕ Tambah Akun Baru</div>
        <div class="form-card-sub">Buat akun untuk pengguna SIMTAL</div>

        <form method="POST" action="{{ route('akun.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Nama *</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="form-input" placeholder="Nama lengkap" required />
                @error('name')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label class="form-label">Email *</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="form-input" placeholder="email@perusahaan.com" required />
                @error('email')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label class="form-label">Role *</label>
                <div class="select-wrap">
                    <select name="role" id="roleSelect" class="form-input" onchange="updateRoleInfo(this.value)">
                        <option value="user"        {{ old('role') == 'user'        ? 'selected' : '' }}>User</option>
                        <option value="admin"       {{ old('role') == 'admin'       ? 'selected' : '' }}>Admin</option>
                        <option value="super_admin" {{ old('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    </select>
                </div>
                <div class="role-info" id="roleInfo">
                    <strong>User</strong> — Hanya dapat melihat dan mengekspor Struktur Organisasi.
                </div>
                @error('role')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label class="form-label">Password *</label>
                <input type="password" name="password"
                       class="form-input" placeholder="Min. 8 karakter" required />
                @error('password')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label class="form-label">Konfirmasi Password *</label>
                <input type="password" name="password_confirmation"
                       class="form-input" placeholder="Ulangi password" required />
            </div>
            <button type="submit" class="btn-save">Simpan Akun</button>
        </form>
    </div>

    {{-- Tabel Akun --}}
    <div class="table-card">
        <div class="table-header">
            <div class="table-title">Daftar Akun</div>
            <span style="font-size:12px;color:#9ca3af;">{{ $users->total() }} akun terdaftar</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Pengguna</th>
                    <th>Role</th>
                    <th>Terdaftar</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td style="color:#9ca3af;">{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                    <td>
                        <div class="user-info">
                            <div class="user-avatar" style="background:{{ $user->role === 'user' ? '#6b7280' : ($user->isSuperAdmin() ? '#d97706' : '#1d4ed8') }};">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="user-name">
                                    {{ $user->name }}
                                    @if($user->id === auth()->id())
                                        <span class="you-badge">Kamu</span>
                                    @endif
                                </div>
                                <div class="user-email">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($user->isSuperAdmin())
                            <span class="role-badge role-super">⭐ Super Admin</span>
                        @elseif($user->role === 'user')
                            <span class="role-badge role-user">👤 User</span>
                        @else
                            <span class="role-badge role-admin">🔵 Admin</span>
                        @endif
                    </td>
                    <td style="font-size:12px;">{{ $user->created_at->format('d M Y') }}</td>
                    <td>
                        <div class="action-btns">
                            <button type="button" class="btn-edit-sm"
                                data-id="{{ $user->id }}"
                                data-name="{{ $user->name }}"
                                data-email="{{ $user->email }}"
                                data-role="{{ $user->role }}"
                                data-url="{{ route('akun.update', $user) }}"
                                onclick="openEditModal(this)">
                                ✏️ Edit
                            </button>
                            <button type="button"
                                class="btn-del-sm {{ $user->id === auth()->id() ? 'disabled' : '' }}"
                                data-url="{{ route('akun.destroy', $user) }}"
                                data-name="{{ $user->name }}"
                                onclick="openHapusModal(this.dataset.url, this.dataset.name)"
                                title="{{ $user->id === auth()->id() ? 'Tidak bisa hapus akun sendiri' : 'Hapus' }}">
                                <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5"><div class="empty-state">Belum ada akun</div></td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($users->hasPages())
        <div class="table-footer">
            <span>Menampilkan <strong>{{ $users->firstItem() }}</strong>–<strong>{{ $users->lastItem() }}</strong> dari <strong>{{ $users->total() }}</strong></span>
            <div class="pagination-wrap">
                @if($users->onFirstPage())
                    <span class="page-btn disabled"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></span>
                @else
                    <a href="{{ $users->previousPageUrl() }}" class="page-btn"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></a>
                @endif
                @php $cur=$users->currentPage();$last=$users->lastPage();$s=max(1,$cur-2);$e=min($last,$cur+2); @endphp
                @if($s > 1)
                    <a href="{{ $users->url(1) }}" class="page-btn">1</a>
                    @if($s > 2)<span class="page-btn disabled" style="border:none;background:transparent;">…</span>@endif
                @endif
                @for($i = $s; $i <= $e; $i++)
                    <a href="{{ $users->url($i) }}" class="page-btn {{ $i == $cur ? 'active' : '' }}">{{ $i }}</a>
                @endfor
                @if($e < $last)
                    @if($e < $last - 1)<span class="page-btn disabled" style="border:none;background:transparent;">…</span>@endif
                    <a href="{{ $users->url($last) }}" class="page-btn">{{ $last }}</a>
                @endif
                @if($users->hasMorePages())
                    <a href="{{ $users->nextPageUrl() }}" class="page-btn"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></a>
                @else
                    <span class="page-btn disabled"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Role info helper
    const roleDesc = {
        user:        '<strong>User</strong> — Hanya dapat melihat dan mengekspor Struktur Organisasi.',
        admin:       '<strong>Admin</strong> — Akses penuh ke semua fitur kecuali Master Data dan Manajemen Akun.',
        super_admin: '<strong>Super Admin</strong> — Akses penuh ke seluruh sistem termasuk Master Data dan Manajemen Akun.',
    };
    function updateRoleInfo(val) {
        document.getElementById('roleInfo').innerHTML = roleDesc[val] || '';
    }
    window.addEventListener('DOMContentLoaded', () => {
        const sel = document.getElementById('roleSelect');
        if (sel) updateRoleInfo(sel.value);
    });

    // Toast
    function closeToast() {
        const t = document.getElementById('toast');
        if (!t) return;
        t.classList.add('hiding');
        setTimeout(() => document.getElementById('toastWrap')?.remove(), 300);
    }
    window.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('toast')) setTimeout(() => closeToast(), 3000);
        const sel = document.getElementById('roleSelect');
        if (sel) updateRoleInfo(sel.value);
    });

    // Modal Edit
    function openEditModal(btn) {
        document.getElementById('editName').value  = btn.dataset.name;
        document.getElementById('editEmail').value = btn.dataset.email;
        document.getElementById('editRole').value  = btn.dataset.role;
        document.getElementById('formEdit').action = btn.dataset.url;
        document.getElementById('modalEdit').classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    function closeEditModal() {
        document.getElementById('modalEdit').classList.remove('show');
        document.body.style.overflow = '';
    }

    // Modal Hapus
    let hapusUrl = '';
    function openHapusModal(url, nama) {
        hapusUrl = url;
        document.getElementById('hapusDesc').innerHTML =
            'Kamu akan menghapus akun <strong>' + nama + '</strong>. Tindakan ini tidak dapat dibatalkan.';
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

    ['modalEdit', 'modalHapus'].forEach(id => {
        document.getElementById(id).addEventListener('click', function(e) {
            if (e.target === this) { this.classList.remove('show'); document.body.style.overflow = ''; }
        });
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') { closeEditModal(); closeHapusModal(); }
    });
</script>
@endpush