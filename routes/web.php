<?php

use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\ExportController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Auth
Route::get('/login', function () {
    if (auth()->check()) return redirect()->route('dashboard');
    return view('auth.login');
})->name('login');

Route::get('/auth/google',          [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
Route::get('/admin-setup',          [GoogleAuthController::class, 'adminSetup'])->name('admin-setup');
Route::post('/admin-setup',         [GoogleAuthController::class, 'adminSetupStore'])->name('admin-setup.store');
Route::post('/logout',              [GoogleAuthController::class, 'logout'])->name('logout');

// Public
Volt::route('/', 'home')->name('home');
Volt::route('/kegiatan/{id}', 'kegiatan-detail')->name('kegiatan.detail');

// Visitor geolocation update
Route::post('/visitor/geo', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'lat' => 'required|numeric|between:-90,90',
        'lng' => 'required|numeric|between:-180,180',
    ]);

    $ip = $request->header('X-Forwarded-For')
        ? trim(explode(',', $request->header('X-Forwarded-For'))[0])
        : $request->ip();

    \App\Models\Visitor::where('tanggal', today())
        ->where('ip', $ip)
        ->whereNull('latitude')
        ->update([
            'latitude'  => $request->input('lat'),
            'longitude' => $request->input('lng'),
        ]);

    return response()->json(['ok' => true]);
})->name('visitor.geo');

// Protected
Route::middleware('auth')->group(function () {
    Volt::route('/dashboard',        'dashboard')->name('dashboard');
    Volt::route('/arus-kas',         'arus-kas.index')->name('arus-kas');
    Volt::route('/transfer',         'transfer.index')->name('transfer.index');
    Volt::route('/laporan/periodik', 'laporan.periodik')->name('laporan.periodik');
    Volt::route('/laporan/bulanan',  'laporan.bulanan')->name('laporan.bulanan');
    Volt::route('/laporan/tahunan',  'laporan.tahunan')->name('laporan.tahunan');
    Volt::route('/kegiatan',         'kegiatan.index')->name('kegiatan');
    Volt::route('/master/jenis-kegiatan', 'master.jenis-kegiatan')->name('master.jenis-kegiatan');
    Volt::route('/profile',          'profile')->name('profile');
    Volt::route('/master/rekening',  'master.rekening')->name('master.rekening');
    Volt::route('/master/kategori',  'master.kategori')->name('master.kategori');
    Volt::route('/hak-akses',        'hak-akses')->name('hak-akses');
    Volt::route('/reset-data',        'reset-data.index')->name('reset-data');
    Volt::route('/pengaturan',         'pengaturan.index')->name('pengaturan');

    // Excel
    Route::get('/export/keuangan',            [ExportController::class, 'keuangan'])->name('export.keuangan');
    Route::get('/export/laporan-periodik',    [ExportController::class, 'laporanPeriodik'])->name('export.laporan-periodik');
    Route::get('/export/laporan-bulanan',     [ExportController::class, 'laporanBulanan'])->name('export.laporan-bulanan');
    Route::get('/export/laporan-tahunan',     [ExportController::class, 'laporanTahunan'])->name('export.laporan-tahunan');
    Route::get('/export/transfer-rekening',   [ExportController::class, 'transferRekening'])->name('export.transfer-rekening');
    Route::get('/export/kegiatan',            [ExportController::class, 'kegiatan'])->name('export.kegiatan');
    Route::get('/export/template-import',       [ExportController::class, 'templateImport'])->name('export.template-import');

    // PDF
    Route::get('/export/laporan-pdf',         [ExportController::class, 'laporanPdf'])->name('export.laporan-pdf');
    Route::get('/export/laporan-pdf-tahunan', [ExportController::class, 'laporanPdfTahunan'])->name('export.laporan-pdf-tahunan');
});
