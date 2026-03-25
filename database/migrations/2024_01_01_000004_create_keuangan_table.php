<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('keuangan', function (Blueprint $table) {
            $table->id();
            $table->decimal('masuk', 15, 2)->default(0);
            $table->decimal('keluar', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->foreignId('id_rekening')->constrained('rekening')->onDelete('restrict');
            $table->foreignId('id_kategori')->constrained('kategori')->onDelete('restrict');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->date('tanggal')->default(now());
            $table->timestamps();
        });

        // Tabel untuk transfer/pindah rekening
        Schema::create('transfer_rekening', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dari_rekening')->constrained('rekening')->onDelete('restrict');
            $table->foreignId('ke_rekening')->constrained('rekening')->onDelete('restrict');
            $table->foreignId('id_kategori')->constrained('kategori')->onDelete('restrict');
            $table->decimal('jumlah', 15, 2);
            $table->text('keterangan')->nullable();
            $table->date('tanggal')->default(now());
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_rekening');
        Schema::dropIfExists('keuangan');
    }
};
