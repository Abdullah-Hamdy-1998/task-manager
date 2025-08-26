<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userRole = Role::where('name', 'user')->first();
        $managerRole = Role::where('name', 'manager')->first();

        User::factory()->create([
            'name' => 'Test Manager',
            'email' => 'manager@example.com',
            'role_id' => $managerRole->id,
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role_id' => $userRole->id,
        ]);
    }
}
