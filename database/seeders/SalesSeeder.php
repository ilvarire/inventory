<?php

namespace Database\Seeders;

use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SalesSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [1, 2, 3, 4]; // Eatery, Cafe, Lounge, Grills
        $salesReps = [9, 10, 11, 12]; // Sales rep IDs for each section

        // Create 20 sales transactions
        for ($i = 0; $i < 20; $i++) {
            $sectionIndex = rand(0, 3);
            $sectionId = $sections[$sectionIndex];
            $salesRepId = $salesReps[$sectionIndex];

            $sale = Sale::create([
                'section_id' => $sectionId,
                'sales_rep_id' => $salesRepId,
                'sale_date' => Carbon::now()->subDays(rand(1, 30)),
                'payment_method' => ['cash', 'card', 'transfer'][rand(0, 2)],
                'customer_name' => 'Customer ' . ($i + 1),
                'status' => 'completed',
            ]);

            // Add 1-5 items per sale
            $itemCount = rand(1, 5);
            $totalAmount = 0;

            for ($j = 0; $j < $itemCount; $j++) {
                // Use product IDs 1-15 (we'll need to create products)
                $quantity = rand(1, 3);
                $unitPrice = rand(500, 5000);
                $subtotal = $quantity * $unitPrice;
                $totalAmount += $subtotal;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => rand(1, 15),
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);
            }

            // Update sale total
            $sale->update(['total_amount' => $totalAmount]);
        }
    }
}
