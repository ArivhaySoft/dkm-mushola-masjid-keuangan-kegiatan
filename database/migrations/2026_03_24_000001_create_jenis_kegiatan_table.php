<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jenis_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100)->unique();
            $table->string('warna', 50)->default('gray'); // tailwind color key
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_kegiatan');
    }
};
