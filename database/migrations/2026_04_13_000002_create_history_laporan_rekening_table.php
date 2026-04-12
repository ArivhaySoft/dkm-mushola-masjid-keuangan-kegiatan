<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('history_laporan_rekening', function (Blueprint $table) {
            $table->id();
            $table->foreignId('history_laporan_id')->constrained('history_laporan')->onDelete('cascade');
            $table->foreignId('rekening_id')->constrained('rekening')->onDelete('restrict');
            $table->decimal('saldo_awal', 15, 2)->default(0);
            $table->decimal('masuk', 15, 2)->default(0);
            $table->decimal('keluar', 15, 2)->default(0);
            $table->decimal('saldo_akhir', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('history_laporan_rekening');
    }
};
