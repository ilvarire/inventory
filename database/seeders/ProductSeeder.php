<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // Main Dishes
            ['name' => 'Grilled Chicken Breast', 'category' => 'main_dish', 'selling_price' => 2500.00],
            ['name' => 'Beef Steak', 'category' => 'main_dish', 'selling_price' => 4500.00],
            ['name' => 'Salmon Fillet', 'category' => 'main_dish', 'selling_price' => 5000.00],
            ['name' => 'Jollof Rice with Chicken', 'category' => 'main_dish', 'selling_price' => 1800.00],
            ['name' => 'Fried Rice', 'category' => 'main_dish', 'selling_price' => 1500.00],

            // Appetizers
            ['name' => 'Spring Rolls', 'category' => 'appetizer', 'selling_price' => 800.00],
            ['name' => 'Chicken Wings', 'category' => 'appetizer', 'selling_price' => 1200.00],
            ['name' => 'Samosa', 'category' => 'appetizer', 'selling_price' => 600.00],

            // Soups
            ['name' => 'Chicken Soup', 'category' => 'soup', 'selling_price' => 1000.00],
            ['name' => 'Seafood Chowder', 'category' => 'soup', 'selling_price' => 1500.00],

            // Desserts
            ['name' => 'Chocolate Cake', 'category' => 'dessert', 'selling_price' => 1200.00],
            ['name' => 'Ice Cream', 'category' => 'dessert', 'selling_price' => 500.00],

            // Beverages
            ['name' => 'Fresh Orange Juice', 'category' => 'beverage', 'selling_price' => 600.00],
            ['name' => 'Soft Drink', 'category' => 'beverage', 'selling_price' => 300.00],
            ['name' => 'Bottled Water', 'category' => 'beverage', 'selling_price' => 200.00],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
