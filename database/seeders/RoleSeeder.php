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
        $roles = [
            ['name' => 'Admin', 'description' => 'Full system access'],
            ['name' => 'Manager', 'description' => 'Manage all sections, approve requests'],
            ['name' => 'Procurement', 'description' => 'Handle procurement and supplier management'],
            ['name' => 'Store Keeper', 'description' => 'Manage inventory and fulfill material requests'],
            ['name' => 'Chef', 'description' => 'Create recipes, log production, request materials'],
            ['name' => 'Frontline Sales', 'description' => 'Record sales transactions'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                ['description' => $role['description']]
            );
        }
    }
}
