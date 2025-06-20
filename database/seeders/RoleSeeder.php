<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = ['Super Admin', 'Admin', 'Organizer', 'User'];
        foreach ($role as $key => $value) {
            Role::create([
                'name' => $value,
                'guard_name' => 'web',
            ]);
        }
    }
}
