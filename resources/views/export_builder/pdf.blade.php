<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Export Data</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 10px; color: #1f2937; margin: 0; }
        h1 { font-size: 16px; margin: 0 0 2px; color: #15803d; }
        .meta { font-size: 9px; color: #6b7280; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 4px 6px; text-align: left; vertical-align: top; }
        th { background: #15803d; color: #fff; font-size: 9px; }
        tr:nth-child(even) td { background: #f3f4f6; }
        .footer { margin-top: 10px; font-size: 8px; color: #9ca3af; text-align: right; }
    </style>
</head>
<body>
    <h1>Export Data Karyawan</h1>
    <div class="meta">
        Dicetak: {{ $tanggal }}
        &middot; Periode: {{ $periode }}
        &middot; Total: {{ $jumlah }} baris
    </div>

    <table>
        <thead>
            <tr>
                @foreach($headings as $h)
                    <th>{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    @foreach($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headings) }}" style="text-align:center; color:#9ca3af;">
                        Tidak ada data yang cocok dengan filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">SIMTAL &middot; Export Builder</div>
</body>
</html>
