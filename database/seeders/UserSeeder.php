<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Section;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $adminRole = Role::where('name', 'Admin')->first();
        $managerRole = Role::where('name', 'Manager')->first();
        $procurementRole = Role::where('name', 'Procurement')->first();
        $storeKeeperRole = Role::where('name', 'Store Keeper')->first();
        $chefRole = Role::where('name', 'Chef')->first();
        $salesRole = Role::where('name', 'Frontline Sales')->first();

        // Get sections
        $eaterySection = Section::where('name', 'Eatery')->first();
        $cafeSection = Section::where('name', 'Café')->first();
        $loungeSection = Section::where('name', 'Lounge')->first();
        $grillsSection = Section::where('name', 'Grills')->first();

        // Create Admin user from environment variables
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@inventory.com')],
            [
                'name' => env('ADMIN_NAME', 'System Administrator'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
                'role_id' => $adminRole->id,
                'section_id' => null, // Admin has access to all sections
                'is_active' => true,
            ]
        );

        // Create Manager user
        User::firstOrCreate(
            ['email' => 'manager@inventory.com'],
            [
                'name' => 'John Manager',
                'password' => Hash::make('password'),
                'role_id' => $managerRole->id,
                'section_id' => null, // Manager has access to all sections
                'is_active' => true,
            ]
        );

        // Create Procurement user
        User::firstOrCreate(
            ['email' => 'procurement@inventory.com'],
            [
                'name' => 'Sarah Procurement',
                'password' => Hash::make('password'),
                'role_id' => $procurementRole->id,
                'section_id' => null, // Procurement works across all sections
                'is_active' => true,
            ]
        );

        // Create Store Keeper user
        User::firstOrCreate(
            ['email' => 'storekeeper@inventory.com'],
            [
                'name' => 'Mike Store',
                'password' => Hash::make('password'),
                'role_id' => $storeKeeperRole->id,
                'section_id' => null, // Store keeper manages all inventory
                'is_active' => true,
            ]
        );

        // Create Chef users for each section
        User::firstOrCreate(
            ['email' => 'chef.eatery@inventory.com'],
            [
                'name' => 'Chef David (Eatery)',
                'password' => Hash::make('password'),
                'role_id' => $chefRole->id,
                'section_id' => $eaterySection->id,
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'chef.cafe@inventory.com'],
            [
                'name' => 'Chef Emma (Café)',
                'password' => Hash::make('password'),
                'role_id' => $chefRole->id,
                'section_id' => $cafeSection->id,
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'chef.lounge@inventory.com'],
            [
                'name' => 'Chef James (Lounge)',
                'password' => Hash::make('password'),
                'role_id' => $chefRole->id,
                'section_id' => $loungeSection->id,
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'chef.grills@inventory.com'],
            [
                'name' => 'Chef Maria (Grills)',
                'password' => Hash::make('password'),
                'role_id' => $chefRole->id,
                'section_id' => $grillsSection->id,
                'is_active' => true,
            ]
        );

        // Create Frontline Sales users for each section
        User::firstOrCreate(
            ['email' => 'sales.eatery@inventory.com'],
            [
                'name' => 'Sales Rep - Eatery',
                'password' => Hash::make('password'),
                'role_id' => $salesRole->id,
                'section_id' => $eaterySection->id,
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'sales.cafe@inventory.com'],
            [
                'name' => 'Sales Rep - Café',
                'password' => Hash::make('password'),
                'role_id' => $salesRole->id,
                'section_id' => $cafeSection->id,
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'sales.lounge@inventory.com'],
            [
                'name' => 'Sales Rep - Lounge',
                'password' => Hash::make('password'),
                'role_id' => $salesRole->id,
                'section_id' => $loungeSection->id,
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'sales.grills@inventory.com'],
            [
                'name' => 'Sales Rep - Grills',
                'password' => Hash::make('password'),
                'role_id' => $salesRole->id,
                'section_id' => $grillsSection->id,
                'is_active' => true,
            ]
        );

        $this->command->info('✅ Users seeded successfully!');
        $this->command->info('');
        $this->command->info('📋 Test Accounts Created:');
        $this->command->info('');
        $this->command->info('👤 Admin:');
        $this->command->info('   Email: ' . env('ADMIN_EMAIL', 'admin@inventory.com'));
        $this->command->info('   Password: ' . env('ADMIN_PASSWORD', 'password'));
        $this->command->info('');
        $this->command->info('👤 Manager:');
        $this->command->info('   Email: manager@inventory.com');
        $this->command->info('   Password: password');
        $this->command->info('');
        $this->command->info('👤 Procurement:');
        $this->command->info('   Email: procurement@inventory.com');
        $this->command->info('   Password: password');
        $this->command->info('');
        $this->command->info('👤 Store Keeper:');
        $this->command->info('   Email: storekeeper@inventory.com');
        $this->command->info('   Password: password');
        $this->command->info('');
        $this->command->info('👨‍🍳 Chefs (one per section):');
        $this->command->info('   chef.eatery@inventory.com');
        $this->command->info('   chef.cafe@inventory.com');
        $this->command->info('   chef.lounge@inventory.com');
        $this->command->info('   chef.grills@inventory.com');
        $this->command->info('   Password: password (all)');
        $this->command->info('');
        $this->command->info('💰 Sales Reps (one per section):');
        $this->command->info('   sales.eatery@inventory.com');
        $this->command->info('   sales.cafe@inventory.com');
        $this->command->info('   sales.lounge@inventory.com');
        $this->command->info('   sales.grills@inventory.com');
        $this->command->info('   Password: password (all)');
    }
}
