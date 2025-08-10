<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create initial admin user with forced password change
        User::create([
            'name' => 'Admin DSP',
            'email' => 'admin@dsp.com',
            'password' => Hash::make('admin123'),
            'role' => UserRole::ADMIN,
            'force_password_change' => true,
        ]);
    }
}
