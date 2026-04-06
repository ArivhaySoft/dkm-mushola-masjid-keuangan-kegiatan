<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Mutasi Rekening</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.3;
        }
        .header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #1b4d3a;
            padding-bottom: 10px;
        }
        .logo {
            width: 60px;
            height: 60px;
            margin-right: 15px;
        }
        .header-text h1 {
            font-size: 16px;
            color: #1b4d3a;
            margin: 0 0 5px 0;
        }
        .header-text p {
            font-size: 11px;
            color: #666;
            margin: 2px 0;
        }
        .header-info {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: #666;
            margin-bottom: 15px;
        }
        .rekening-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .rekening-header {
            background-color: #f0f0f0;
            padding: 10px;
            border-left: 4px solid #1b4d3a;
            margin-bottom: 10px;
        }
        .rekening-header h3 {
            font-size: 13px;
            color: #1b4d3a;
            margin: 0 0 5px 0;
        }
        .rekening-header p {
            font-size: 11px;
            color: #666;
            margin: 2px 0;
        }
        .summary-boxes {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 15px;
        }
        .summary-box {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
            border-radius: 4px;
        }
        .summary-box-label {
            font-size: 10px;
            color: #999;
            margin-bottom: 3px;
        }
        .summary-box-value {
            font-size: 12px;
            font-weight: bold;
            color: #1b4d3a;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10px;
        }
        table thead {
            background-color: #1b4d3a;
            color: white;
        }
        table th {
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #1b4d3a;
        }
        table td {
            padding: 6px 8px;
            border: 1px solid #ddd;
        }
        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .tipe-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .tipe-masuk {
            background-color: #d4edda;
            color: #155724;
        }
        .tipe-keluar {
            background-color: #f8d7da;
            color: #721c24;
        }
        .footer {
            margin-top: 30px;
            font-size: 11px;
            color: #999;
            text-align: right;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        @if($logoDataUri)
            <img src="{{ $logoDataUri }}" class="logo" alt="Logo">
        @endif
        <div class="header-text">
            <h1>{{ $appName }}</h1>
            <p>{{ $namaMushola }}</p>
        </div>
    </div>

    <div class="header-info">
        <div>
            <strong>Laporan Mutasi Rekening</strong><br>
            @if($from && $to)
                Periode: {{ date('d/m/Y', strtotime($from)) }} - {{ date('d/m/Y', strtotime($to)) }}
            @endif
        </div>
        <div>
            Dicetak: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    {{-- Per Rekening --}}
    @foreach($dataMutasi as $index => $dm)
        {{-- Rekening Header --}}
        <div class="rekening-header">
            <h3>{{ $dm['nama_rek'] }}</h3>
            <p>Atas Nama: {{ $dm['atas_nama'] }} | No. Rekening: {{ $dm['no_rek'] }}</p>
        </div>

        {{-- Summary Boxes --}}
        <div class="summary-boxes">
            <div class="summary-box">
                <div class="summary-box-label">Saldo Awal</div>
                <div class="summary-box-value">Rp {{ number_format($dm['saldo_awal'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-box">
                <div class="summary-box-label">Total Masuk</div>
                <div class="summary-box-value" style="color: #28a745;">Rp {{ number_format($dm['masuk'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-box">
                <div class="summary-box-label">Total Keluar</div>
                <div class="summary-box-value" style="color: #dc3545;">Rp {{ number_format($dm['keluar'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-box">
                <div class="summary-box-label">Saldo Akhir</div>
                <div class="summary-box-value" style="color: #007bff;">Rp {{ number_format($dm['saldo_akhir'], 0, ',', '.') }}</div>
            </div>
        </div>

        {{-- Transaction Table --}}
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th class="text-center">Tipe</th>
                    <th>Kategori</th>
                    <th class="text-right">Masuk (Rp)</th>
                    <th class="text-right">Keluar (Rp)</th>
                    <th class="text-right">Saldo (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dm['transaksi'] as $t)
                <tr>
                    <td>{{ $t['tanggal'] }}</td>
                    <td>{{ substr($t['keterangan'], 0, 40) }}{{ strlen($t['keterangan']) > 40 ? '...' : '' }}</td>
                    <td class="text-center">
                        <span class="tipe-badge {{ strpos($t['tipe'], 'Masuk') !== false || $t['tipe'] == 'Pemasukan' ? 'tipe-masuk' : 'tipe-keluar' }}">
                            {{ substr($t['tipe'], 0, 10) }}
                        </span>
                    </td>
                    <td>{{ $t['kategori_ref'] }}</td>
                    <td class="text-right">
                        @if($t['masuk'] > 0)
                            {{ number_format($t['masuk'], 0, ',', '.') }}
                        @endif
                    </td>
                    <td class="text-right">
                        @if($t['keluar'] > 0)
                            {{ number_format($t['keluar'], 0, ',', '.') }}
                        @endif
                    </td>
                    <td class="text-right" style="background-color: #f0f0f0; font-weight: bold;">
                        {{ number_format($t['saldo'], 0, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Page Break --}}
        @if($index != count($dataMutasi) - 1)
            <div class="page-break"></div>
        @endif
    @endforeach

    <div class="footer">
        <p>Laporan tercetak otomatis dari sistem</p>
    </div>
</body>
</html>
