<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdmin extends Command
{
    protected $signature = 'make:admin {username} {--name=Administrator}';

    protected $description = 'Create or reset a dashboard login (prompts for the password)';

    public function handle(): int
    {
        $username = $this->argument('username');
        $password = $this->secret('Password');

        if (! $password || strlen($password) < 6) {
            $this->error('Password must be at least 6 characters.');

            return self::FAILURE;
        }

        User::updateOrCreate(
            ['username' => $username],
            ['name' => $this->option('name'), 'password' => Hash::make($password)],
        );

        $this->info("User '{$username}' saved.");

        return self::SUCCESS;
    }
}
