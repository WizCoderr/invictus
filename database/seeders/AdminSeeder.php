<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    // Placeholder login — change the password after the first sign-in on a live site.
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Organiser', 'password' => Hash::make('password'), 'email_verified_at' => now()],
        );
    }
}
