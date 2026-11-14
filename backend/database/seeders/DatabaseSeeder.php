<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $username = env('ADMIN_USERNAME', 'admin');
        $password = env('ADMIN_PASSWORD');

        if (! $password) {
            $this->command->warn('ADMIN_PASSWORD not set in .env — skipping admin user.');

            return;
        }

        User::updateOrCreate(
            ['username' => $username],
            [
                'name' => 'Administrator',
                'password' => Hash::make($password),
            ],
        );

        $this->command->info("Admin user '{$username}' is ready.");
    }
}
