<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SectionSeeder::class,
            UserSeeder::class,
            SupplierSeeder::class,
            RawMaterialSeeder::class,
            ProductSeeder::class,
            ProcurementSeeder::class,
            MaterialRequestSeeder::class,
            SalesSeeder::class,
            WasteLogSeeder::class,
            ExpenseSeeder::class,
        ]);
    }
}
