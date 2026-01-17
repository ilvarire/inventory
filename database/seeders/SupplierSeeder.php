<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            ['name' => 'Fresh Farms Ltd', 'contact_info' => '08012345678'],
            ['name' => 'City Wholesale Market', 'contact_info' => 'Main Market, Lagos'],
            ['name' => 'Mama Pot Spices', 'contact_info' => '07098765432'],
            ['name' => 'Beverage Distributors', 'contact_info' => 'sales@bevd.com'],
            ['name' => 'Global Foods Import', 'contact_info' => 'Unit 4, Industrial Estate'],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::firstOrCreate(['name' => $supplier['name']], $supplier);
        }
    }
}
