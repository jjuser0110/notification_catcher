<?php

namespace Database\Seeders;

use App\Models\Bank;
use App\Models\MasterSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Master rate bands (payin 100/98/95, payout 0/2/5).
        MasterSetting::current();

        // Malaysian banks.
        $banks = [
            'Maybank', 'CIMB Bank', 'Public Bank', 'Hong Leong Bank', 'RHB Bank',
            'AmBank', 'Bank Islam', 'Bank Rakyat', 'Affin Bank', 'Alliance Bank',
            'OCBC Bank', 'HSBC Bank', 'Standard Chartered', 'UOB Bank', 'Agrobank',
        ];
        foreach ($banks as $name) {
            Bank::firstOrCreate(['name' => $name], ['is_active' => true]);
        }

        // Super admin login (from .env ADMIN_USERNAME / ADMIN_PASSWORD).
        $username = env('ADMIN_USERNAME', 'superadmin');
        $password = env('ADMIN_PASSWORD');

        if (! $password) {
            $this->command->warn('ADMIN_PASSWORD not set in .env — skipping superadmin.');
            return;
        }

        User::updateOrCreate(
            ['username' => $username],
            [
                'name' => 'Super Admin',
                'password' => Hash::make($password),
                'role' => User::ROLE_SUPERADMIN,
                'is_active' => true,
            ],
        );

        $this->command->info("Superadmin '{$username}' ready; ".count($banks).' banks seeded.');
    }
}
