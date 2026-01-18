<?php

namespace Database\Seeders;

use App\Models\RawMaterial;
use Illuminate\Database\Seeder;

class RawMaterialSeeder extends Seeder
{
    public function run(): void
    {
        $rawMaterials = [
            // Proteins
            ['name' => 'Chicken Breast', 'unit' => 'kg', 'category' => 'protein', 'min_quantity' => 10, 'reorder_quantity' => 20, 'preferred_supplier_id' => 1],
            ['name' => 'Beef', 'unit' => 'kg', 'category' => 'protein', 'min_quantity' => 15, 'reorder_quantity' => 30, 'preferred_supplier_id' => 1],
            ['name' => 'Salmon', 'unit' => 'kg', 'category' => 'protein', 'min_quantity' => 5, 'reorder_quantity' => 10, 'preferred_supplier_id' => 2],
            ['name' => 'Eggs', 'unit' => 'piece', 'category' => 'protein', 'min_quantity' => 100, 'reorder_quantity' => 200, 'preferred_supplier_id' => 1],
            ['name' => 'Shrimp', 'unit' => 'kg', 'category' => 'protein', 'min_quantity' => 3, 'reorder_quantity' => 8, 'preferred_supplier_id' => 2],

            // Vegetables
            ['name' => 'Tomatoes', 'unit' => 'kg', 'category' => 'vegetable', 'min_quantity' => 20, 'reorder_quantity' => 40, 'preferred_supplier_id' => 3],
            ['name' => 'Onions', 'unit' => 'kg', 'category' => 'vegetable', 'min_quantity' => 15, 'reorder_quantity' => 30, 'preferred_supplier_id' => 3],
            ['name' => 'Bell Peppers', 'unit' => 'kg', 'category' => 'vegetable', 'min_quantity' => 10, 'reorder_quantity' => 20, 'preferred_supplier_id' => 3],
            ['name' => 'Lettuce', 'unit' => 'kg', 'category' => 'vegetable', 'min_quantity' => 5, 'reorder_quantity' => 10, 'preferred_supplier_id' => 3],
            ['name' => 'Carrots', 'unit' => 'kg', 'category' => 'vegetable', 'min_quantity' => 8, 'reorder_quantity' => 15, 'preferred_supplier_id' => 3],

            // Grains & Staples
            ['name' => 'Rice', 'unit' => 'kg', 'category' => 'grain', 'min_quantity' => 50, 'reorder_quantity' => 100, 'preferred_supplier_id' => 4],
            ['name' => 'Flour', 'unit' => 'kg', 'category' => 'grain', 'min_quantity' => 30, 'reorder_quantity' => 60, 'preferred_supplier_id' => 4],
            ['name' => 'Pasta', 'unit' => 'kg', 'category' => 'grain', 'min_quantity' => 15, 'reorder_quantity' => 30, 'preferred_supplier_id' => 4],
            ['name' => 'Bread', 'unit' => 'piece', 'category' => 'grain', 'min_quantity' => 20, 'reorder_quantity' => 50, 'preferred_supplier_id' => 5],

            // Dairy
            ['name' => 'Milk', 'unit' => 'liter', 'category' => 'dairy', 'min_quantity' => 20, 'reorder_quantity' => 40, 'preferred_supplier_id' => 6],
            ['name' => 'Cheese', 'unit' => 'kg', 'category' => 'dairy', 'min_quantity' => 5, 'reorder_quantity' => 10, 'preferred_supplier_id' => 6],
            ['name' => 'Butter', 'unit' => 'kg', 'category' => 'dairy', 'min_quantity' => 8, 'reorder_quantity' => 15, 'preferred_supplier_id' => 6],
            ['name' => 'Cream', 'unit' => 'liter', 'category' => 'dairy', 'min_quantity' => 10, 'reorder_quantity' => 20, 'preferred_supplier_id' => 6],

            // Oils & Condiments
            ['name' => 'Vegetable Oil', 'unit' => 'liter', 'category' => 'oil', 'min_quantity' => 15, 'reorder_quantity' => 30, 'preferred_supplier_id' => 4],
            ['name' => 'Olive Oil', 'unit' => 'liter', 'category' => 'oil', 'min_quantity' => 5, 'reorder_quantity' => 10, 'preferred_supplier_id' => 4],
            ['name' => 'Salt', 'unit' => 'kg', 'category' => 'condiment', 'min_quantity' => 10, 'reorder_quantity' => 20, 'preferred_supplier_id' => 4],
            ['name' => 'Black Pepper', 'unit' => 'kg', 'category' => 'condiment', 'min_quantity' => 2, 'reorder_quantity' => 5, 'preferred_supplier_id' => 4],
            ['name' => 'Garlic', 'unit' => 'kg', 'category' => 'condiment', 'min_quantity' => 5, 'reorder_quantity' => 10, 'preferred_supplier_id' => 3],

            // Beverages
            ['name' => 'Coffee Beans', 'unit' => 'kg', 'category' => 'beverage', 'min_quantity' => 10, 'reorder_quantity' => 20, 'preferred_supplier_id' => 5],
            ['name' => 'Tea Leaves', 'unit' => 'kg', 'category' => 'beverage', 'min_quantity' => 5, 'reorder_quantity' => 10, 'preferred_supplier_id' => 5],
            ['name' => 'Orange Juice', 'unit' => 'liter', 'category' => 'beverage', 'min_quantity' => 15, 'reorder_quantity' => 30, 'preferred_supplier_id' => 3],

            // Dessert Ingredients
            ['name' => 'Sugar', 'unit' => 'kg', 'category' => 'dessert', 'min_quantity' => 20, 'reorder_quantity' => 40, 'preferred_supplier_id' => 4],
            ['name' => 'Chocolate', 'unit' => 'kg', 'category' => 'dessert', 'min_quantity' => 5, 'reorder_quantity' => 10, 'preferred_supplier_id' => 5],
            ['name' => 'Vanilla Extract', 'unit' => 'liter', 'category' => 'dessert', 'min_quantity' => 2, 'reorder_quantity' => 5, 'preferred_supplier_id' => 5],
        ];

        foreach ($rawMaterials as $material) {
            RawMaterial::create($material);
        }
    }
}
