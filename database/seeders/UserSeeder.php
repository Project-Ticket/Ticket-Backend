<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = User::factory()->create([
            'email' => 'superadmin@mailinator.com',
        ]);

        $superAdmin->assignRole('Super Admin');

        $admin = User::factory()->create([
            'email' => 'admin@mailinator.com',
        ]);
        $admin->assignRole('Admin');

        $user = User::factory()->create([
            'email' => 'user@mailinator.com',
        ]);
        $user->assignRole('User');
    }
}
