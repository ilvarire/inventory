<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'Fresh Farms Ltd',
                'contact_info' => 'John Okafor - 08012345678 - contact@freshfarms.ng - 123 Farm Road, Ikeja, Lagos'
            ],
            [
                'name' => 'Ocean Catch Seafood',
                'contact_info' => 'Mary Adeleke - 08098765432 - sales@oceancatch.ng - 45 Marina Street, Victoria Island, Lagos'
            ],
            [
                'name' => 'Green Valley Produce',
                'contact_info' => 'Ahmed Ibrahim - 08123456789 - info@greenvalley.ng - 78 Market Road, Yaba, Lagos'
            ],
            [
                'name' => 'Golden Grains Supplies',
                'contact_info' => 'Chioma Nwosu - 08087654321 - orders@goldengrains.ng - 12 Industrial Avenue, Apapa, Lagos'
            ],
            [
                'name' => 'Premium Bakery Supplies',
                'contact_info' => 'David Mensah - 08034567890 - sales@premiumbakery.ng - 56 Allen Avenue, Ikeja, Lagos'
            ],
            [
                'name' => 'Dairy Fresh Nigeria',
                'contact_info' => 'Grace Eze - 08076543210 - contact@dairyfresh.ng - 34 Lekki-Epe Expressway, Lekki, Lagos'
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}
