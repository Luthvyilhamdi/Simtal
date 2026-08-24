@extends('layouts.app')
@section('title', 'Backup Database')
@section('breadcrumb-parent', 'Administrasi')
@section('breadcrumb', 'Backup Database')

@push('styles')
<style>
    .page-header { display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:24px;gap:12px;flex-wrap:wrap; }
    .page-title { font-size:22px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:4px;max-width:640px; }

    .bk-alert { display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:var(--radius);font-size:13px;font-weight:600;margin-bottom:18px; }
    .bk-alert.ok { background:#dcfce7;color:#15803d;border:1px solid #bbf7d0; }
    .bk-alert.err { background:#fef2f2;color:#dc2626;border:1px solid #fecaca; }
    .bk-alert svg { width:18px;height:18px;flex-shrink:0; }

    .bk-cta { background:white;border:1px solid var(--card-border);border-radius:var(--radius);box-shadow:var(--card-shadow);padding:20px 22px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:18px; }
    .bk-cta-info { display:flex;align-items:center;gap:15px; }
    .bk-cta-icon { width:46px;height:46px;border-radius:12px;background:#dcfce7;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .bk-cta-icon svg { width:24px;height:24px;stroke:#15803d;fill:none;stroke-width:1.8; }
    .bk-cta-title { font-size:15px;font-weight:700;color:#111827; }
    .bk-cta-desc { font-size:12.5px;color:#6b7280;margin-top:2px; }
    .btn-backup { display:inline-flex;align-items:center;gap:8px;padding:11px 20px;border-radius:10px;border:none;background:#16a34a;color:white;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit;transition:background .15s; }
    .btn-backup:hover { background:#15803d; }
    .btn-backup:disabled { opacity:.65;cursor:progress; }
    .btn-backup svg { width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2; }

    .sec-title { font-size:13px;font-weight:700;color:#374151;margin:0 0 12px;display:flex;align-items:center;gap:8px; }
    .sec-title .count { font-size:11px;font-weight:700;color:#6b7280;background:#f3f4f6;padding:2px 9px;border-radius:20px; }

    .bk-table-wrap { background:white;border:1px solid var(--card-border);border-radius:var(--radius);box-shadow:var(--card-shadow);overflow-x:auto; }
    table.bk-table { width:100%;border-collapse:collapse;font-size:13px; }
    .bk-table th { text-align:left;padding:12px 16px;font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.03em;border-bottom:1px solid #f0f0ec;white-space:nowrap; }
    .bk-table td { padding:13px 16px;border-bottom:1px solid #f5f5f0;color:#374151;vertical-align:middle; }
    .bk-table tr:last-child td { border-bottom:none; }
    .bk-file { font-weight:700;color:#111827;display:flex;align-items:center;gap:9px; }
    .bk-file svg { width:16px;height:16px;stroke:#16a34a;fill:none;stroke-width:1.8;flex-shrink:0; }
    .bk-size { font-variant-numeric:tabular-nums;color:#6b7280; }
    .bk-date { color:#6b7280;white-space:nowrap; }
    .bk-actions { display:flex;gap:8px;justify-content:flex-end; }
    .btn-sm { display:inline-flex;align-items:center;gap:6px;padding:7px 13px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;text-decoration:none;border:1px solid transparent; }
    .btn-dl { background:#eff6ff;color:#1d4ed8;border-color:#dbeafe; }
    .btn-dl:hover { background:#dbeafe; }
    .btn-del { background:#fef2f2;color:#dc2626;border-color:#fecaca; }
    .btn-del:hover { background:#fee2e2; }
    .btn-sm svg { width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2; }

    .bk-empty { padding:44px 20px;text-align:center;color:#9ca3af; }
    .bk-empty svg { width:40px;height:40px;stroke:#d1d5db;fill:none;stroke-width:1.5;margin-bottom:10px; }
    .bk-empty-title { font-size:14px;font-weight:600;color:#6b7280; }
    .bk-empty-sub { font-size:12.5px;margin-top:3px; }
</style>
@endpush

@section('content')

@if(session('success'))
<div class="bk-alert ok">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="20 6 9 17 4 12"/></svg>
    <div>{{ session('success') }}</div>
</div>
@endif
@if(session('error'))
<div class="bk-alert err">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <div>{{ session('error') }}</div>
</div>
@endif

<div class="page-header">
    <div>
        <div class="page-title">Backup Database</div>
        <div class="page-sub">Cadangkan <strong>database + seluruh file upload</strong> (foto, surat, dll) ke satu file <strong>.zip</strong> — berisi dump <strong>.sql</strong> dan folder <strong>storage/app</strong>, plus petunjuk restore. Sistem menyimpan otomatis <strong>10 backup terakhir</strong>; yang lebih lama dihapus agar tidak memakan ruang disk.</div>
    </div>
</div>

<div class="bk-cta">
    <div class="bk-cta-info">
        <div class="bk-cta-icon">
            <svg viewBox="0 0 24 24"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14a9 3 0 0 0 18 0V5"/><path d="M3 12a9 3 0 0 0 18 0"/></svg>
        </div>
        <div>
            <div class="bk-cta-title">Buat backup sekarang</div>
            <div class="bk-cta-desc">Dump database + salin semua file upload, lalu dikompres ke .zip. Bisa beberapa detik s/d menit tergantung jumlah file.</div>
        </div>
    </div>
    <form method="POST" action="{{ route('backup.store') }}" id="backupForm">
        @csrf
        <button type="submit" class="btn-backup" id="btnBackup">
            <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            <span id="btnBackupText">Backup Sekarang</span>
        </button>
    </form>
</div>

<div class="sec-title">
    Riwayat Backup
    <span class="count">{{ $backups->count() }}</span>
</div>

<div class="bk-table-wrap">
    @if($backups->isEmpty())
        <div class="bk-empty">
            <svg viewBox="0 0 24 24"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14a9 3 0 0 0 18 0V5"/><path d="M3 12a9 3 0 0 0 18 0"/></svg>
            <div class="bk-empty-title">Belum ada backup</div>
            <div class="bk-empty-sub">Klik "Backup Sekarang" untuk membuat cadangan pertama.</div>
        </div>
    @else
        <table class="bk-table">
            <thead>
                <tr>
                    <th>Nama File</th>
                    <th>Tanggal</th>
                    <th>Ukuran</th>
                    <th style="text-align:right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($backups as $b)
                <tr>
                    <td>
                        <span class="bk-file">
                            <svg viewBox="0 0 24 24"><path d="M21 8v13H3V8"/><path d="M1 3h22v5H1z"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
                            {{ $b['name'] }}
                        </span>
                    </td>
                    <td class="bk-date">{{ \Carbon\Carbon::createFromTimestamp($b['time'], config('app.timezone'))->translatedFormat('d M Y, H:i') }}</td>
                    <td class="bk-size">
                        @if($b['size'] >= 1048576)
                            {{ number_format($b['size'] / 1048576, 1) }} MB
                        @else
                            {{ number_format($b['size'] / 1024, 1) }} KB
                        @endif
                    </td>
                    <td>
                        <div class="bk-actions">
                            <a href="{{ route('backup.download', $b['name']) }}" class="btn-sm btn-dl">
                                <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                Unduh
                            </a>
                            <form method="POST" action="{{ route('backup.destroy', $b['name']) }}" onsubmit="return confirm('Hapus backup ini secara permanen?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-sm btn-del">
                                    <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

@push('scripts')
<script>
    document.getElementById('backupForm').addEventListener('submit', function () {
        var btn = document.getElementById('btnBackup');
        btn.disabled = true;
        document.getElementById('btnBackupText').textContent = 'Memproses...';
    });
</script>
@endpush

@endsection
