<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::where('name', 'Admin')->first();

        if (!$role) {
            $role = Role::create(['name' => 'Admin']);
        }

        User::updateOrCreate(
            ['email' => 'testadmin@inventory.com'],
            [
                'name' => 'Test Admin',
                'password' => Hash::make('password'),
                'role_id' => $role->id,
                'is_active' => true,
            ]
        );

        $this->command->info('Test Admin user created successfully.');
    }
}
