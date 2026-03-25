<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'DejaVu Sans',sans-serif; font-size:10px; color:#1f2937; }
        .header { background:#1b4d3a; color:#fff; padding:16px 20px; margin-bottom:16px; }
        .header-wrap { display:flex; align-items:center; gap:10px; }
        .header-logo { width:40px; height:40px; border-radius:8px; object-fit:cover; border:1px solid rgba(255,255,255,0.35); }
        .header-text { flex:1; }
        .header h1 { font-size:15px; font-weight:bold; }
        .header p  { font-size:9px; opacity:0.8; margin-top:3px; }
        .section { padding:0 20px; margin-bottom:16px; }
        .section-title { font-size:10px; font-weight:bold; color:#1b4d3a; border-bottom:2px solid #1b4d3a; padding-bottom:3px; margin-bottom:8px; text-transform:uppercase; letter-spacing:0.5px; }
        table { width:100%; border-collapse:collapse; font-size:9px; }
        th { background:#f3f4f6; padding:5px 7px; text-align:left; font-weight:bold; color:#374151; }
        th.right, td.right { text-align:right; }
        td { padding:4px 7px; border-bottom:1px solid #e5e7eb; }
        tr.total td { background:#f0f9f4; font-weight:bold; border-top:2px solid #1b4d3a; }
        tr.zero td { color:#9ca3af; }
        .summary-boxes { display:flex; gap:10px; padding:0 20px; margin-bottom:16px; }
        .box { flex:1; border:1px solid #e5e7eb; border-radius:6px; padding:10px; text-align:center; }
        .box .lbl { font-size:8px; color:#6b7280; margin-bottom:3px; }
        .box .val { font-size:12px; font-weight:bold; }
        .green { color:#059669; } .red { color:#ef4444; } .blue { color:#2563eb; }
        .footer { position:fixed; bottom:10px; left:20px; right:20px; font-size:8px; color:#9ca3af; border-top:1px solid #e5e7eb; padding-top:5px; display:flex; justify-content:space-between; }
    </style>
</head>
<body>

<div class="header">
    <div class="header-wrap">
        @if(!empty($logoDataUri))
        <img src="{{ $logoDataUri }}" alt="Logo" class="header-logo" />
        @endif
        <div class="header-text">
            <h1>{{ $appName }} — {{ $namaMushola }}</h1>
        </div>
    </div>
    <p>Tahun {{ $tahun }}  |  Dicetak: {{ now()->isoFormat('D MMMM Y, HH:mm') }}</p>
</div>

<div class="summary-boxes">
    <div class="box">
        <div class="lbl">Total Masuk {{ $tahun }}</div>
        <div class="val green">Rp {{ number_format($totalMasuk, 0, ',', '.') }}</div>
    </div>
    <div class="box">
        <div class="lbl">Total Keluar {{ $tahun }}</div>
        <div class="val red">Rp {{ number_format($totalKeluar, 0, ',', '.') }}</div>
    </div>
    <div class="box">
        <div class="lbl">Surplus / Defisit</div>
        <div class="val blue">Rp {{ number_format($totalMasuk - $totalKeluar, 0, ',', '.') }}</div>
    </div>
</div>

<div class="section">
    <div class="section-title">Rekap per Bulan</div>
    <table>
        <thead>
            <tr>
                <th>Bulan</th>
                <th class="right">Saldo Awal (Rp)</th>
                <th class="right">Masuk (Rp)</th>
                <th class="right">Keluar (Rp)</th>
                <th class="right">Saldo Akhir (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dataPerBulan as $row)
            <tr class="{{ $row['masuk'] == 0 && $row['keluar'] == 0 ? 'zero' : '' }}">
                <td>{{ $row['bulan'] }}</td>
                <td class="right">Rp {{ number_format($row['saldo_awal'],0,',','.') }}</td>
                <td class="right green">{{ $row['masuk'] > 0 ? 'Rp '.number_format($row['masuk'],0,',','.') : '-' }}</td>
                <td class="right red">{{ $row['keluar'] > 0 ? 'Rp '.number_format($row['keluar'],0,',','.') : '-' }}</td>
                <td class="right {{ $row['saldo_akhir'] >= 0 ? 'blue' : 'red' }}" style="font-weight:bold">
                    Rp {{ number_format($row['saldo_akhir'],0,',','.') }}
                </td>
            </tr>
            @endforeach
            <tr class="total">
                <td>TOTAL {{ $tahun }}</td>
                <td class="right">-</td>
                <td class="right green">Rp {{ number_format($totalMasuk,0,',','.') }}</td>
                <td class="right red">Rp {{ number_format($totalKeluar,0,',','.') }}</td>
                <td class="right blue">Rp {{ number_format($totalMasuk - $totalKeluar,0,',','.') }}</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="section">
    <div class="section-title">Rekap per Kategori</div>
    <table>
        <thead>
            <tr>
                <th>Kategori</th>
                <th class="right">Saldo Awal (Rp)</th>
                <th class="right">Masuk (Rp)</th>
                <th class="right">Keluar (Rp)</th>
                <th class="right">Saldo Akhir (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dataKategori as $dk)
            <tr>
                <td style="font-weight:bold">{{ $dk['nama'] }}</td>
                <td class="right">Rp {{ number_format($dk['saldo_awal'],0,',','.') }}</td>
                <td class="right green">Rp {{ number_format($dk['masuk'],0,',','.') }}</td>
                <td class="right red">Rp {{ number_format($dk['keluar'],0,',','.') }}</td>
                <td class="right blue" style="font-weight:bold">Rp {{ number_format($dk['saldo_akhir'],0,',','.') }}</td>
            </tr>
            @endforeach
            <tr class="total">
                <td>TOTAL</td>
                <td class="right">Rp {{ number_format(collect($dataKategori)->sum('saldo_awal'),0,',','.') }}</td>
                <td class="right green">Rp {{ number_format(collect($dataKategori)->sum('masuk'),0,',','.') }}</td>
                <td class="right red">Rp {{ number_format(collect($dataKategori)->sum('keluar'),0,',','.') }}</td>
                <td class="right blue">Rp {{ number_format(collect($dataKategori)->sum('saldo_akhir'),0,',','.') }}</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="section">
    <div class="section-title">Rekap per Rekening</div>
    <table>
        <thead>
            <tr>
                <th>Rekening</th>
                <th>Atas Nama</th>
                <th>No. Rekening</th>
                <th class="right">Saldo Awal</th>
                <th class="right">Masuk</th>
                <th class="right">Keluar</th>
                <th class="right">Saldo Akhir</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dataRekening as $dr)
            <tr>
                <td style="font-weight:bold">{{ $dr['nama'] }}</td>
                <td>{{ $dr['atas_nama'] }}</td>
                <td>{{ $dr['no_rek'] }}</td>
                <td class="right">Rp {{ number_format($dr['saldo_awal'], 0, ',', '.') }}</td>
                <td class="right green">Rp {{ number_format($dr['masuk'], 0, ',', '.') }}</td>
                <td class="right red">Rp {{ number_format($dr['keluar'], 0, ',', '.') }}</td>
                <td class="right {{ $dr['saldo_akhir'] >= 0 ? 'blue' : 'red' }}" style="font-weight:bold">Rp {{ number_format($dr['saldo_akhir'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="footer">
    <span>Mushola Al-Ikhlas · Sistem Keuangan Digital</span>
    <span>Laporan Tahunan {{ $tahun }}</span>
</div>

</body>
</html>
