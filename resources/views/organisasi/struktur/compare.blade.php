@extends('layouts.app')
@section('title', 'Perbandingan Versi Struktur Organisasi')
@section('breadcrumb-parent', 'Riwayat Struktur Organisasi')
@section('breadcrumb', 'Perbandingan Versi')

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color .12s; }
    .back-link:hover { color:#15803d; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }

    .page-header { margin-bottom:8px; }
    .page-title { font-size:20px;font-weight:700;color:#111827;display:flex;align-items:center;gap:10px;flex-wrap:wrap; }
    .page-title .arrow { color:#9ca3af;font-weight:400; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:6px; }

    .hop-trail { font-size:11.5px;color:#9ca3af;margin-bottom:20px;display:flex;align-items:center;gap:6px;flex-wrap:wrap; }
    .hop-chip { background:#f3f4f6;border-radius:20px;padding:3px 10px;color:#374151;font-weight:600; }

    .ringkasan-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px; }
    .ringkasan-item { background:white;border-radius:12px;border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:14px 16px;text-align:center; }
    .ringkasan-num { font-size:22px;font-weight:800;color:#111827; }
    .ringkasan-label { font-size:11px;color:#6b7280;margin-top:2px; }
    .ringkasan-item.anomali .ringkasan-num { color:#d97706; }

    .unchanged-note { background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;border-radius:10px;padding:12px 16px;font-size:13px;font-weight:600;margin-bottom:24px;display:flex;align-items:center;gap:8px; }

    .section-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:22px 26px;margin-bottom:16px; }
    .section-title { display:flex;align-items:center;gap:8px;font-size:14px;font-weight:700;color:#111827;margin-bottom:14px; }
    .section-badge { font-size:11px;font-weight:700;padding:2px 9px;border-radius:20px;background:#f3f4f6;color:#374151; }

    .diff-list { display:flex;flex-direction:column;gap:8px; }
    .diff-row { display:flex;align-items:center;gap:10px;padding:10px 14px;background:#fafafa;border-radius:9px;font-size:13px;flex-wrap:wrap; }
    .diff-name { font-weight:600;color:#111827; }
    .diff-arrow { color:#9ca3af; }
    .diff-old { color:#9ca3af;text-decoration:line-through; }
    .diff-new { color:#15803d;font-weight:600; }
    .diff-list-multi { display:flex;flex-wrap:wrap;gap:6px; }
    .diff-pill { background:white;border:1px solid #e5e7eb;border-radius:7px;padding:3px 10px;font-size:12px;color:#374151; }

    .anomali-row { padding:12px 14px;background:#fffbeb;border:1px solid #fde68a;border-radius:9px; }
    .anomali-name { font-weight:700;color:#92400e;font-size:13px;margin-bottom:6px; }
    .anomali-detail { font-size:12px;color:#78350f;margin-left:16px;list-style:disc; }

    .section-card.section-baru .section-badge { background:#eff6ff;color:#1d4ed8; }
    .section-card.section-bubar .section-badge { background:#fef2f2;color:#dc2626; }
    .section-card.section-anomali .section-badge { background:#fffbeb;color:#92400e; }

    @media (max-width:900px) {
        .ringkasan-grid { grid-template-columns:repeat(2,1fr); }
    }
</style>
@endpush

@section('content')
<a href="{{ route('organisasi.struktur.index') }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Riwayat Struktur Organisasi
</a>

<div class="page-header">
    <div class="page-title">
        <a href="{{ route('organisasi.struktur.show', $lama) }}" style="color:inherit;text-decoration:none;">{{ $lama->nomor_sk }}</a>
        <span class="arrow">→</span>
        <a href="{{ route('organisasi.struktur.show', $baru) }}" style="color:inherit;text-decoration:none;">{{ $baru->nomor_sk }}</a>
    </div>
    <div class="page-sub">
        {{ $lama->tanggal_mulai_berlaku->translatedFormat('d F Y') }} dibandingkan dengan {{ $baru->tanggal_mulai_berlaku->translatedFormat('d F Y') }}
    </div>
</div>

@if($hops->count() > 1)
<div class="hop-trail">
    Mencakup {{ $hops->count() }} versi dalam rentang ini:
    @foreach($hops as $hop)
        <span class="hop-chip">{{ $hop->nomor_sk }}</span>
        @if(!$loop->last) → @endif
    @endforeach
</div>
@endif

<div class="ringkasan-grid">
    <div class="ringkasan-item">
        <div class="ringkasan-num">{{ count($hasil['baru']) }}</div>
        <div class="ringkasan-label">Unit Baru</div>
    </div>
    <div class="ringkasan-item">
        <div class="ringkasan-num">{{ count($hasil['rename']) }}</div>
        <div class="ringkasan-label">Rename</div>
    </div>
    <div class="ringkasan-item">
        <div class="ringkasan-num">{{ count($hasil['pindah_induk']) }}</div>
        <div class="ringkasan-label">Pindah Induk</div>
    </div>
    <div class="ringkasan-item">
        <div class="ringkasan-num">{{ count($hasil['ganti_level']) }}</div>
        <div class="ringkasan-label">Ganti Level</div>
    </div>
    <div class="ringkasan-item">
        <div class="ringkasan-num">{{ count($hasil['pecah']) }}</div>
        <div class="ringkasan-label">Pecah</div>
    </div>
    <div class="ringkasan-item">
        <div class="ringkasan-num">{{ count($hasil['gabung']) }}</div>
        <div class="ringkasan-label">Gabung</div>
    </div>
    <div class="ringkasan-item">
        <div class="ringkasan-num">{{ count($hasil['bubar']) }}</div>
        <div class="ringkasan-label">Bubar</div>
    </div>
    <div class="ringkasan-item">
        <div class="ringkasan-num">{{ count($hasil['formasi_berubah']) }}</div>
        <div class="ringkasan-label">Formasi Berubah</div>
    </div>
    <div class="ringkasan-item anomali">
        <div class="ringkasan-num">{{ count($hasil['anomali']) }}</div>
        <div class="ringkasan-label">Anomali Data</div>
    </div>
</div>

<div class="unchanged-note">
    ✓ {{ $hasil['unchanged_count'] }} unit tidak mengalami perubahan berarti antara kedua versi ini.
</div>

@if(count($hasil['baru']))
<div class="section-card section-baru">
    <div class="section-title">🆕 Unit Baru <span class="section-badge">{{ count($hasil['baru']) }}</span></div>
    <div class="diff-list-multi">
        @foreach($hasil['baru'] as $u)
        <span class="diff-pill">{{ $u['nama'] }}</span>
        @endforeach
    </div>
</div>
@endif

@if(count($hasil['rename']))
<div class="section-card">
    <div class="section-title">✏️ Rename <span class="section-badge">{{ count($hasil['rename']) }}</span></div>
    <div class="diff-list">
        @foreach($hasil['rename'] as $r)
        <div class="diff-row">
            <span class="diff-old">{{ $r['dari'] }}</span>
            <span class="diff-arrow">→</span>
            <span class="diff-new">{{ $r['ke'] }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif

@if(count($hasil['pindah_induk']))
<div class="section-card">
    <div class="section-title">🔀 Pindah Induk <span class="section-badge">{{ count($hasil['pindah_induk']) }}</span></div>
    <div class="diff-list">
        @foreach($hasil['pindah_induk'] as $p)
        <div class="diff-row">
            <span class="diff-name">{{ $p['nama'] }}</span>
            <span class="diff-arrow">·</span>
            <span class="diff-old">{{ $p['dari'] }}</span>
            <span class="diff-arrow">→</span>
            <span class="diff-new">{{ $p['ke'] }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif

@if(count($hasil['ganti_level']))
<div class="section-card">
    <div class="section-title">🔡 Ganti Level <span class="section-badge">{{ count($hasil['ganti_level']) }}</span></div>
    <div class="diff-list">
        @foreach($hasil['ganti_level'] as $g)
        <div class="diff-row">
            <span class="diff-name">{{ $g['nama'] }}</span>
            <span class="diff-arrow">·</span>
            <span class="diff-old">{{ ucfirst($g['dari']) }}</span>
            <span class="diff-arrow">→</span>
            <span class="diff-new">{{ ucfirst($g['ke']) }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif

@if(count($hasil['pecah']))
<div class="section-card">
    <div class="section-title">✂️ Pecah <span class="section-badge">{{ count($hasil['pecah']) }}</span></div>
    <div class="diff-list">
        @foreach($hasil['pecah'] as $p)
        <div class="diff-row">
            <span class="diff-old">{{ $p['dari'] }}</span>
            <span class="diff-arrow">→</span>
            <div class="diff-list-multi">
                @foreach($p['ke'] as $nama)
                <span class="diff-pill">{{ $nama }}</span>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@if(count($hasil['gabung']))
<div class="section-card">
    <div class="section-title">🔗 Gabung <span class="section-badge">{{ count($hasil['gabung']) }}</span></div>
    <div class="diff-list">
        @foreach($hasil['gabung'] as $g)
        <div class="diff-row">
            <div class="diff-list-multi">
                @foreach($g['dari'] as $nama)
                <span class="diff-pill">{{ $nama }}</span>
                @endforeach
            </div>
            <span class="diff-arrow">→</span>
            <span class="diff-new">{{ $g['ke'] }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif

@if(count($hasil['bubar']))
<div class="section-card section-bubar">
    <div class="section-title">🗑️ Bubar <span class="section-badge">{{ count($hasil['bubar']) }}</span></div>
    <div class="diff-list-multi">
        @foreach($hasil['bubar'] as $u)
        <span class="diff-pill">{{ $u['nama'] }}</span>
        @endforeach
    </div>
</div>
@endif

@if(count($hasil['formasi_berubah']))
<div class="section-card">
    <div class="section-title">📊 Perubahan Formasi <span class="section-badge">{{ count($hasil['formasi_berubah']) }}</span></div>
    <div class="diff-list">
        @foreach($hasil['formasi_berubah'] as $f)
        <div class="diff-row">
            <span class="diff-name">{{ $f['nama'] }}</span>
            <span class="diff-arrow">·</span>
            <span class="diff-old">{{ $f['dari'] }}</span>
            <span class="diff-arrow">→</span>
            <span class="diff-new">{{ $f['ke'] }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif

@if(count($hasil['reorganisasi']))
<div class="section-card">
    <div class="section-title">🧩 Reorganisasi Kompleks <span class="section-badge">{{ count($hasil['reorganisasi']) }}</span></div>
    <div class="section-sub" style="font-size:11.5px;color:#9ca3af;margin-bottom:12px;">
        Sekelompok unit lama & baru saling terkait lewat beberapa pecah/gabung sekaligus dalam rentang ini — sistem tidak bisa memasangkan satu-satu secara otomatis, jadi ditampilkan sebagai satu kelompok.
    </div>
    <div class="diff-list">
        @foreach($hasil['reorganisasi'] as $r)
        <div class="diff-row">
            <div class="diff-list-multi">
                @foreach($r['dari'] as $nama)<span class="diff-pill">{{ $nama }}</span>@endforeach
            </div>
            <span class="diff-arrow">→</span>
            <div class="diff-list-multi">
                @foreach($r['ke'] as $nama)<span class="diff-pill">{{ $nama }}</span>@endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@if(count($hasil['anomali']))
<div class="section-card section-anomali">
    <div class="section-title">⚠️ Anomali / Belum Terdeklarasi <span class="section-badge">{{ count($hasil['anomali']) }}</span></div>
    <div class="section-sub" style="font-size:11.5px;color:#9ca3af;margin-bottom:12px;">
        Unit ini berubah tanpa pernah tercatat sebagai transisi resmi (rename/pindah induk/dst) di sepanjang rentang versi ini — kemungkinan luput didokumentasikan tim OD saat input. Perlu dicek ulang.
    </div>
    <div class="diff-list">
        @foreach($hasil['anomali'] as $a)
        <div class="anomali-row">
            <div class="anomali-name">{{ $a['nama'] }}</div>
            <ul class="anomali-detail">
                @foreach($a['detail'] as $d)
                <li>{{ $d }}</li>
                @endforeach
            </ul>
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection
