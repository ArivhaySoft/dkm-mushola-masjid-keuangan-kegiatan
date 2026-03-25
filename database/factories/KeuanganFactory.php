<?php

namespace Database\Factories;

use App\Models\Kategori;
use App\Models\Rekening;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class KeuanganFactory extends Factory
{
    public function definition(): array
    {
        $isMasuk = fake()->boolean(60);
        return [
            'masuk'       => $isMasuk ? fake()->numberBetween(10000, 5000000) : 0,
            'keluar'      => !$isMasuk ? fake()->numberBetween(10000, 2000000) : 0,
            'keterangan'  => fake()->sentence(4),
            'id_rekening' => Rekening::inRandomOrder()->first()?->id ?? 1,
            'id_kategori' => Kategori::inRandomOrder()->first()?->id ?? 1,
            'created_by'  => User::inRandomOrder()->first()?->id ?? 1,
            'tanggal'     => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
        ];
    }
}
