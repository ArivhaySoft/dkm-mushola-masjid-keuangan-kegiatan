<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_pengajian', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('ustadz')->nullable();
            $table->enum('frekuensi', ['2_minggu', 'bulanan']); // 2 minggu sekali atau tiap awal bulan
            $table->date('tanggal_mulai'); // tanggal pertama kali pengajian dimulai
            $table->string('hari'); // Senin, Selasa, etc.
            $table->time('jam_mulai');
            $table->time('jam_selesai')->nullable();
            $table->string('lokasi')->nullable();
            $table->text('keterangan')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_pengajian');
    }
};
