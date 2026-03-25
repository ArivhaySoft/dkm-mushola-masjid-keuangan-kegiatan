<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kegiatan_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_id')->constrained('kegiatan')->onDelete('cascade');
            $table->string('path');
            $table->boolean('is_headline')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Migrate existing foto data
        $rows = DB::table('kegiatan')->whereNotNull('foto')->where('foto', '!=', '')->get(['id', 'foto']);
        foreach ($rows as $row) {
            DB::table('kegiatan_fotos')->insert([
                'kegiatan_id' => $row->id,
                'path'        => $row->foto,
                'is_headline' => true,
                'sort_order'  => 0,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        Schema::table('kegiatan', function (Blueprint $table) {
            $table->dropColumn('foto');
        });
    }

    public function down(): void
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('konten');
        });

        // Migrate back
        $fotos = DB::table('kegiatan_fotos')->where('is_headline', true)->get();
        foreach ($fotos as $foto) {
            DB::table('kegiatan')->where('id', $foto->kegiatan_id)->update(['foto' => $foto->path]);
        }

        Schema::dropIfExists('kegiatan_fotos');
    }
};
