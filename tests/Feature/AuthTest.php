<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function halaman_login_bisa_diakses(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    /** @test */
    public function pengguna_yang_belum_login_bisa_akses_beranda_publik(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Belum ada Administrator');
    }

    /** @test */
    public function pengguna_yang_sudah_login_bisa_akses_beranda(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);
    }
}
