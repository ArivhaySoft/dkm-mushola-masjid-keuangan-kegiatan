<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rekening', function (Blueprint $table) {
            $table->id();
            $table->string('nama_rek');
            $table->string('atas_nama');
            $table->string('no_rek');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekening');
    }
};
