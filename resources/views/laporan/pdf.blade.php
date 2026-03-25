<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1f2937; }
        .header { background: #1b4d3a; color: white; padding: 16px 20px; margin-bottom: 16px; }
        .header-wrap { display: flex; align-items: center; gap: 10px; }
        .header-logo { width: 42px; height: 42px; border-radius: 8px; object-fit: cover; border: 1px solid rgba(255,255,255,0.35); }
        .header-text { flex: 1; }
        .header h1 { font-size: 16px; font-weight: bold; }
        .header p { font-size: 10px; opacity: 0.8; margin-top: 2px; }
        .section { padding: 0 20px; margin-bottom: 16px; }
        .section-title { font-size: 11px; font-weight: bold; color: #1b4d3a; border-bottom: 2px solid #1b4d3a; padding-bottom: 4px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        table { width: 100%; border-collapse: collapse; font-size: 9px; }
        th { background: #f3f4f6; padding: 6px 8px; text-align: left; font-weight: bold; color: #374151; }
        th.right, td.right { text-align: right; }
        td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; }
        tr.total { background: #f0f9f4; font-weight: bold; }
        tr.total td { border-top: 2px solid #1b4d3a; }
        .summary-grid { display: flex; gap: 12px; margin-bottom: 16px; padding: 0 20px; }
        .summary-box { flex: 1; border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px; text-align: center; }
        .summary-box .label { font-size: 8px; color: #6b7280; margin-bottom: 3px; }
        .summary-box .value { font-size: 13px; font-weight: bold; }
        .masuk { color: #059669; }
        .keluar { color: #ef4444; }
        .saldo { color: #2563eb; }
        .page-break { page-break-before: always; }
        .footer { position: fixed; bottom: 10px; left: 20px; right: 20px; font-size: 8px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 6px; display: flex; justify-content: space-between; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 10px; font-size: 8px; font-weight: bold; background: #d1fae5; color: #065f46; }
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
    <p>
        @if($tipe === 'bulanan')
            Laporan Bulanan ·
        @elseif($tipe === 'tahunan')
            Laporan Tahunan ·
        @else
            Laporan Periodik ·
        @endif
        Periode: {{ $from ? \Carbon\Carbon::parse($from)->isoFormat('D MMMM Y') : '-' }}
        – {{ $to ? \Carbon\Carbon::parse($to)->isoFormat('D MMMM Y') : '-' }}
    </p>
    <p>Dicetak: {{ now()->isoFormat('D MMMM Y, HH:mm') }}</p>
</div>

{{-- Summary --}}
<div class="summary-grid">
    <div class="summary-box">
        <div class="label">Total Masuk</div>
        <div class="value masuk">Rp {{ number_format($totalMasuk, 0, ',', '.') }}</div>
    </div>
    <div class="summary-box">
        <div class="label">Total Keluar</div>
        <div class="value keluar">Rp {{ number_format($totalKeluar, 0, ',', '.') }}</div>
    </div>
    <div class="summary-box">
        <div class="label">Saldo Akhir</div>
        <div class="value saldo">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</div>
    </div>
</div>

{{-- Per Kategori --}}
<div class="section">
    <div class="section-title">Ringkasan per Kategori</div>
    <table>
        <thead>
            <tr>
                <th>Kategori</th>
                <th class="right">Saldo Awal</th>
                <th class="right">Masuk</th>
                <th class="right">Keluar</th>
                <th class="right">Saldo Akhir</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dataKategori as $dk)
            <tr>
                <td>{{ $dk['nama'] }}</td>
                <td class="right">Rp {{ number_format($dk['saldo_awal'], 0, ',', '.') }}</td>
                <td class="right masuk">Rp {{ number_format($dk['masuk'], 0, ',', '.') }}</td>
                <td class="right keluar">Rp {{ number_format($dk['keluar'], 0, ',', '.') }}</td>
                <td class="right saldo" style="font-weight:bold">Rp {{ number_format($dk['saldo_akhir'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr class="total">
                <td>TOTAL</td>
                <td class="right">Rp {{ number_format(collect($dataKategori)->sum('saldo_awal'), 0, ',', '.') }}</td>
                <td class="right masuk">Rp {{ number_format($totalMasuk, 0, ',', '.') }}</td>
                <td class="right keluar">Rp {{ number_format($totalKeluar, 0, ',', '.') }}</td>
                <td class="right saldo">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</div>

{{-- Per Rekening --}}
<div class="section">
    <div class="section-title">Saldo per Rekening</div>
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
                <td class="right masuk">Rp {{ number_format($dr['masuk'], 0, ',', '.') }}</td>
                <td class="right keluar">Rp {{ number_format($dr['keluar'], 0, ',', '.') }}</td>
                <td class="right saldo" style="font-weight:bold">Rp {{ number_format($dr['saldo_akhir'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Detail Per Kategori --}}
@foreach($detailPerKategori as $detail)
@if(count($detail['transaksi']) > 0)
<div class="page-break">
    <div class="header">
        <h1>Detail Transaksi: {{ $detail['kategori'] }}</h1>
        <p>Periode: {{ $from ? \Carbon\Carbon::parse($from)->isoFormat('D MMMM Y') : '-' }} – {{ $to ? \Carbon\Carbon::parse($to)->isoFormat('D MMMM Y') : '-' }}</p>
    </div>
    <div class="section">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th class="right">Masuk</th>
                    <th class="right">Keluar</th>
                </tr>
            </thead>
            <tbody>
                @foreach($detail['transaksi'] as $idx => $trx)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($trx->tanggal ?? $trx['tanggal'])->format('d/m/Y') }}</td>
                    <td>{{ $trx->keterangan ?? $trx['keterangan'] ?? '-' }}</td>
                    <td class="right masuk">{{ ($trx->masuk ?? $trx['masuk'] ?? 0) > 0 ? 'Rp '.number_format($trx->masuk ?? $trx['masuk'], 0, ',', '.') : '' }}</td>
                    <td class="right keluar">{{ ($trx->keluar ?? $trx['keluar'] ?? 0) > 0 ? 'Rp '.number_format($trx->keluar ?? $trx['keluar'], 0, ',', '.') : '' }}</td>
                </tr>
                @endforeach
                <tr class="total">
                    <td colspan="3">Total {{ $detail['kategori'] }}</td>
                    <td class="right masuk">Rp {{ number_format(collect($detail['transaksi'])->sum(fn($t) => $t->masuk ?? $t['masuk'] ?? 0), 0, ',', '.') }}</td>
                    <td class="right keluar">Rp {{ number_format(collect($detail['transaksi'])->sum(fn($t) => $t->keluar ?? $t['keluar'] ?? 0), 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endif
@endforeach

<div class="footer">
    <span>Mushola Al-Ikhlas · Sistem Keuangan Digital</span>
    <span>Dicetak: {{ now()->isoFormat('D MMMM Y, HH:mm') }}</span>
</div>

</body>
</html>
