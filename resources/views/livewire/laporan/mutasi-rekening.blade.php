<?php

use App\Models\Keuangan;
use App\Models\Rekening;
use App\Models\TransferRekening;
use Livewire\Volt\Component;

new class extends Component
{
    public function render(): mixed
    {
        return parent::render()->title('Laporan Mutasi Rekening');
    }

    public string $from = '';
    public string $to   = '';
    public string $rekening_id = ''; // Empty = all
    public bool $hasData = false;

    public array $dataMutasi = [];

    public function mount(): void
    {
        $this->from = now()->startOfMonth()->format('Y-m-d');
        $this->to   = now()->format('Y-m-d');
        $this->rekening_id = ''; // Default: semua rekening
    }

    public function generate(): void
    {
        $this->validate([
            'from' => 'required|date',
            'to'   => 'required|date|after_or_equal:from',
        ]);

        $this->dataMutasi = [];
        
        if ($this->rekening_id) {
            // Specific rekening
            $rekening = Rekening::find((int) $this->rekening_id);
            if ($rekening) {
                $transaksi = $this->getTransaksiRekening($rekening);
                if (count($transaksi) > 0) {
                    $summary = $rekening->reportBalanceSummary($this->from, $this->to);
                    $this->dataMutasi[] = [
                        'rekening' => $rekening,
                        'nama_rek' => $rekening->nama_rek,
                        'atas_nama' => $rekening->atas_nama,
                        'no_rek' => $rekening->no_rek,
                        'saldo_awal' => (float) $summary['saldo_awal'],
                        'masuk' => (float) $summary['masuk'],
                        'keluar' => (float) $summary['keluar'],
                        'saldo_akhir' => (float) $summary['saldo_akhir'],
                        'transaksi' => $transaksi,
                    ];
                }
            }
        } else {
            // All rekening
            $rekeningList = Rekening::all();
            foreach ($rekeningList as $rekening) {
                $transaksi = $this->getTransaksiRekening($rekening);
                if (count($transaksi) > 0) {
                    $summary = $rekening->reportBalanceSummary($this->from, $this->to);
                    $this->dataMutasi[] = [
                        'rekening' => $rekening,
                        'nama_rek' => $rekening->nama_rek,
                        'atas_nama' => $rekening->atas_nama,
                        'no_rek' => $rekening->no_rek,
                        'saldo_awal' => (float) $summary['saldo_awal'],
                        'masuk' => (float) $summary['masuk'],
                        'keluar' => (float) $summary['keluar'],
                        'saldo_akhir' => (float) $summary['saldo_akhir'],
                        'transaksi' => $transaksi,
                    ];
                }
            }
        }

        $this->hasData = count($this->dataMutasi) > 0;
    }

    private function getTransaksiRekening(Rekening $rekening): array
    {
        $transaksi = [];
        $saldoRunning = $rekening->calculateSaldoSebelum($this->from);

        // Get Keuangan entries
        $keuanganData = Keuangan::where('id_rekening', $rekening->id)
            ->with(['kategori'])
            ->when($this->from, fn($q) => $q->whereDate('tanggal', '>=', $this->from))
            ->when($this->to, fn($q) => $q->whereDate('tanggal', '<=', $this->to))
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        foreach ($keuanganData as $k) {
            $tipe = (float) $k->masuk > 0 ? 'Pemasukan' : 'Pengeluaran';
            $masuk = (float) $k->masuk;
            $keluar = (float) $k->keluar;

            $transaksi[] = [
                'tanggal' => $k->tanggal->format('d/m/Y'),
                'keterangan' => $k->keterangan ?? '-',
                'tipe' => $tipe,
                'masuk' => $masuk,
                'keluar' => $keluar,
                'kategori_ref' => $k->kategori->nama ?? '-',
                'sort_date' => $k->tanggal,
                'sort_id' => $k->id,
            ];
        }

        // Get Transfer Masuk
        $transferMasuk = TransferRekening::where('ke_rekening', $rekening->id)
            ->with(['dariRekening', 'kategori'])
            ->when($this->from, fn($q) => $q->whereDate('tanggal', '>=', $this->from))
            ->when($this->to, fn($q) => $q->whereDate('tanggal', '<=', $this->to))
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        foreach ($transferMasuk as $t) {
            $masuk = (float) $t->jumlah;

            $transaksi[] = [
                'tanggal' => $t->tanggal->format('d/m/Y'),
                'keterangan' => 'Transfer dari ' . ($t->dariRekening->nama_rek ?? '-'),
                'tipe' => 'Transfer Masuk',
                'masuk' => $masuk,
                'keluar' => 0,
                'kategori_ref' => $t->kategori->nama ?? '-',
                'sort_date' => $t->tanggal,
                'sort_id' => 'tr-' . $t->id,
            ];
        }

        // Get Transfer Keluar
        $transferKeluar = TransferRekening::where('dari_rekening', $rekening->id)
            ->with(['keRekening', 'kategori'])
            ->when($this->from, fn($q) => $q->whereDate('tanggal', '>=', $this->from))
            ->when($this->to, fn($q) => $q->whereDate('tanggal', '<=', $this->to))
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        foreach ($transferKeluar as $t) {
            $keluar = (float) $t->jumlah;

            $transaksi[] = [
                'tanggal' => $t->tanggal->format('d/m/Y'),
                'keterangan' => 'Transfer ke ' . ($t->keRekening->nama_rek ?? '-'),
                'tipe' => 'Transfer Keluar',
                'masuk' => 0,
                'keluar' => $keluar,
                'kategori_ref' => $t->kategori->nama ?? '-',
                'sort_date' => $t->tanggal,
                'sort_id' => 'tr-' . $t->id,
            ];
        }

        // Sort by date and ID
        usort($transaksi, function ($a, $b) {
            if ($a['sort_date'] != $b['sort_date']) {
                return $a['sort_date'] <=> $b['sort_date'];
            }
            return strcmp((string)$a['sort_id'], (string)$b['sort_id']);
        });

        // Recalculate running saldo after all transaksi are merged and sorted.
        foreach ($transaksi as &$t) {
            $saldoRunning += ((float) ($t['masuk'] ?? 0)) - ((float) ($t['keluar'] ?? 0));
            $t['saldo'] = $saldoRunning;
            unset($t['sort_date'], $t['sort_id']);
        }
        unset($t);

        return $transaksi;
    }

    public function downloadExcel(): void
    {
        $params = http_build_query([
            'from' => $this->from,
            'to' => $this->to,
            'rekening_id' => $this->rekening_id,
        ]);
        $this->redirect('/export/laporan-mutasi-rekening?' . $params);
    }

    public function downloadPdf(): void
    {
        $params = http_build_query([
            'from' => $this->from,
            'to' => $this->to,
            'rekening_id' => $this->rekening_id,
        ]);
        $this->redirect('/export/laporan-mutasi-rekening-pdf?' . $params);
    }
}; ?>


<div>
    <div class="card mb-5">
        <h2 class="text-sm font-bold text-gray-700 mb-4">Filter Periode</h2>
        <div class="grid grid-cols-2 sm:flex sm:flex-row gap-3 items-end">
            <div class="col-span-1 sm:flex-1">
                <label class="label">Dari</label>
                <input type="date" wire:model="from" class="input" />
            </div>
            <div class="col-span-1 sm:flex-1">
                <label class="label">Sampai</label>
                <input type="date" wire:model="to" class="input" />
            </div>
            <div class="col-span-2 sm:flex-1">
                <label class="label">Rekening</label>
                <select wire:model="rekening_id" class="input">
                    <option value="">Semua Rekening</option>
                    @foreach(\App\Models\Rekening::all() as $rek)
                        <option value="{{ $rek->id }}">{{ $rek->nama_rek }}</option>
                    @endforeach
                </select>
            </div>
            <button wire:click="generate" class="col-span-2 btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Tampilkan
            </button>
        </div>
    </div>

    @if($hasData)
    <div class="flex justify-end gap-2 mb-4">
        <button wire:click="downloadExcel" class="btn-secondary text-xs">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Export Excel
        </button>
        <button wire:click="downloadPdf" class="btn-primary text-xs">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Cetak PDF
        </button>
    </div>

    {{-- Data Per Rekening --}}
    @foreach($dataMutasi as $dm)
    <div class="card mb-6">
        <div class="mb-4 pb-3 border-b border-gray-200">
            <h3 class="text-sm font-bold text-gray-800">{{ $dm['nama_rek'] }}</h3>
            <p class="text-xs text-gray-500">
                Atas Nama: {{ $dm['atas_nama'] }} | No. Rek: {{ $dm['no_rek'] }}
            </p>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
            <div class="bg-gray-50 rounded-lg p-3 text-center">
                <p class="text-xs text-gray-500">Saldo Awal</p>
                <p class="text-xs font-bold text-gray-700">Rp {{ number_format($dm['saldo_awal'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-emerald-50 rounded-lg p-3 text-center">
                <p class="text-xs text-gray-500">Masuk</p>
                <p class="text-xs font-bold text-emerald-600">Rp {{ number_format($dm['masuk'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-red-50 rounded-lg p-3 text-center">
                <p class="text-xs text-gray-500">Keluar</p>
                <p class="text-xs font-bold text-red-500">Rp {{ number_format($dm['keluar'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-blue-50 rounded-lg p-3 text-center">
                <p class="text-xs text-gray-500">Saldo Akhir</p>
                <p class="text-xs font-bold text-blue-600">Rp {{ number_format($dm['saldo_akhir'], 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Mobile Transaction List --}}
        <div class="sm:hidden divide-y divide-gray-100 border rounded-lg overflow-hidden">
            @foreach($dm['transaksi'] as $t)
            <div class="px-3 py-2 space-y-1 {{ str_contains($t['tipe'], 'Transfer') ? 'bg-amber-50' : '' }}">
                <div class="flex justify-between items-center">
                    <p class="text-xs font-semibold text-gray-800">{{ $t['tanggal'] }}</p>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $t['tipe'] == 'Pemasukan' || $t['tipe'] == 'Transfer Masuk' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                        {{ $t['tipe'] }}
                    </span>
                </div>
                <p class="text-xs text-gray-600">{{ $t['keterangan'] }}</p>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500">{{ $t['kategori_ref'] }}</span>
                    <span class="font-semibold text-gray-800">
                        @if($t['masuk'] > 0)
                            <span class="text-emerald-600">+Rp {{ number_format($t['masuk'], 0, ',', '.') }}</span>
                        @else
                            <span class="text-red-500">-Rp {{ number_format($t['keluar'], 0, ',', '.') }}</span>
                        @endif
                    </span>
                </div>
                <div class="text-xs font-bold text-blue-600 border-t border-gray-100 pt-1">
                    Saldo: Rp {{ number_format($t['saldo'], 0, ',', '.') }}
                </div>
            </div>
            @endforeach
        </div>

        {{-- Desktop Transaction Table --}}
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-3 py-2 font-bold text-gray-600">Tanggal</th>
                        <th class="text-left px-3 py-2 font-bold text-gray-600">Keterangan</th>
                        <th class="text-left px-3 py-2 font-bold text-gray-600">Tipe</th>
                        <th class="text-left px-3 py-2 font-bold text-gray-600">Kategori</th>
                        <th class="text-right px-3 py-2 font-bold text-gray-600">Masuk (Rp)</th>
                        <th class="text-right px-3 py-2 font-bold text-gray-600">Keluar (Rp)</th>
                        <th class="text-right px-3 py-2 font-bold text-gray-600">Saldo (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($dm['transaksi'] as $t)
                    <tr class="{{ str_contains($t['tipe'], 'Transfer') ? 'bg-amber-50 hover:bg-amber-100/70' : 'hover:bg-gray-50' }}">
                        <td class="px-3 py-2 text-gray-700 font-semibold">{{ $t['tanggal'] }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ substr($t['keterangan'], 0, 35) }}{{ strlen($t['keterangan']) > 35 ? '...' : '' }}</td>
                        <td class="px-3 py-2">
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $t['tipe'] == 'Pemasukan' || $t['tipe'] == 'Transfer Masuk' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                {{ $t['tipe'] }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-gray-600">{{ $t['kategori_ref'] }}</td>
                        <td class="px-3 py-2 text-right">
                            @if($t['masuk'] > 0)
                                <span class="text-emerald-600 font-semibold">Rp {{ number_format($t['masuk'], 0, ',', '.') }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right">
                            @if($t['keluar'] > 0)
                                <span class="text-red-500 font-semibold">Rp {{ number_format($t['keluar'], 0, ',', '.') }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right font-bold text-blue-600">Rp {{ number_format($t['saldo'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
    @endif
</div>
