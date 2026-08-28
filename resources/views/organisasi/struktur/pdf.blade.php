<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Struktur Organisasi - {{ $versi->nomor_sk }}</title>
    <style>
        /* Font di-set di body & diwarisi turunannya — hindari selector universal '*' yang
           harus dicocokkan dompdf ke SETIAP frame satu-satu (mahal di tabel ratusan baris). */
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; margin: 0; }
        h1 { font-size: 16px; margin: 0 0 8px; color: #15803d; }

        .info-box { border: 1px solid #d1d5db; border-radius: 4px; padding: 8px 12px; margin-bottom: 14px; }
        .info-row { margin-bottom: 3px; }
        .info-label { display: inline-block; width: 130px; color: #6b7280; font-size: 9px; }
        .info-val { display: inline; font-size: 9px; font-weight: bold; color: #111827; }
        .status-final { color: #1d4ed8; }
        .status-draft { color: #92400e; }

        /* table-layout: fixed + lebar kolom eksplisit: dompdf tidak perlu pass "auto" (ukur
           konten semua baris dulu sebelum render) — ini yang paling berpengaruh utk tabel besar. */
        table { width: 100%; table-layout: fixed; border-collapse: collapse; }
        th, td { border-bottom: 1px solid #d1d5db; padding: 4px 6px; text-align: left; vertical-align: top; font-size: 9px; overflow: hidden; }
        th { background: #15803d; color: #fff; font-size: 9px; border-bottom: 1px solid #15803d; }
        tr.row-even td { background: #f9fafb; }

        .col-nama { width: 28%; }
        .col-level { width: 9%; }
        .col-parent { width: 18%; }
        .col-formasi, .col-bawahan, .col-grand { width: 9%; text-align: center; }
        .col-grand { font-weight: bold; }
        .col-keterangan { width: 19%; }

        .footer { margin-top: 10px; font-size: 8px; color: #9ca3af; text-align: right; }
    </style>
</head>
<body>
    <h1>Struktur Organisasi — SK {{ $versi->nomor_sk }}</h1>

    <div class="info-box">
        <div class="info-row"><span class="info-label">Nomor SK</span><span class="info-val">{{ $versi->nomor_sk }}</span></div>
        <div class="info-row"><span class="info-label">Tanggal SK</span><span class="info-val">{{ $versi->tanggal_sk->translatedFormat('d F Y') }}</span></div>
        <div class="info-row"><span class="info-label">Mulai Berlaku</span><span class="info-val">{{ $versi->tanggal_mulai_berlaku->translatedFormat('d F Y') }}</span></div>
        <div class="info-row">
            <span class="info-label">Status</span>
            <span class="info-val {{ $versi->isFinal() ? 'status-final' : 'status-draft' }}">
                {{ $versi->isFinal() ? 'FINAL' : 'DRAFT (belum final, data bisa berubah)' }}
            </span>
        </div>
        @if($versi->keterangan)
        <div class="info-row"><span class="info-label">Keterangan</span><span class="info-val" style="font-weight:normal;">{{ $versi->keterangan }}</span></div>
        @endif
    </div>

    <table>
        <colgroup>
            <col class="col-nama"><col class="col-level"><col class="col-parent">
            <col class="col-formasi"><col class="col-bawahan"><col class="col-grand"><col class="col-keterangan">
        </colgroup>
        <thead>
            <tr>
                <th>Nama Unit</th>
                <th>Level</th>
                <th>Parent</th>
                <th>Formasi Unit</th>
                <th>Total Bawahan</th>
                <th>Grand Total</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr class="{{ $loop->even ? 'row-even' : '' }}">
                    <td>{!! str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $row['depth']) . ($row['depth'] > 0 ? '&#8618; ' : '') !!}{{ $row['nama_unit'] }}</td>
                    <td>{{ $row['level'] }}</td>
                    <td>{{ $row['parent'] }}</td>
                    <td class="col-formasi">{{ $row['formasi_unit'] }}</td>
                    <td class="col-bawahan">{{ $row['total_bawahan'] }}</td>
                    <td class="col-grand">{{ $row['grand_total'] }}</td>
                    <td>{{ $row['keterangan'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center; color:#9ca3af;">Belum ada unit di versi ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">SiMental &middot; Riwayat Struktur Organisasi &middot; Dicetak: {{ now()->translatedFormat('d F Y H:i') }}</div>
</body>
</html>
