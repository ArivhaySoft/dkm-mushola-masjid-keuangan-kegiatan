<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class SetAdminCommand extends Command
{
    protected $signature   = 'mushola:set-admin {email}';
    protected $description = 'Jadikan pengguna sebagai Admin';

    public function handle(): void
    {
        $email = $this->argument('email');
        $user  = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Pengguna dengan email {$email} tidak ditemukan.");
            $this->info('Pastikan pengguna sudah pernah login terlebih dahulu.');
            return;
        }

        $adminRole = Role::where('name', 'admin')->firstOrCreate([
            'name'  => 'admin',
            'label' => 'Administrator',
        ]);

        $user->roles()->syncWithoutDetaching([$adminRole->id]);

        $this->info("✅ {$user->name} ({$email}) berhasil dijadikan Admin.");
    }
}
