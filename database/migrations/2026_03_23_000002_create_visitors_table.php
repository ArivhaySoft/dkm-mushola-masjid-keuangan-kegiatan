<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->index();
            $table->string('ip', 45);
            $table->text('user_agent')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();

            $table->unique(['tanggal', 'ip']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
