<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and sections first
        $this->call([
            RoleSeeder::class,
            SectionSeeder::class,
        ]);

        // Optionally create test users
        // User::factory(10)->create();
    }
}
