<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['name' => 'manager', 'description' => 'Manager role']);
        Role::create(['name' => 'user', 'description' => 'Regular user role']);
    }
}
