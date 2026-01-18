<?php

namespace Database\Seeders;

use App\Models\Expense;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [1, 2, 3, 4];
        $managerId = 2;

        $expenseTypes = [
            'utilities' => ['Electricity Bill', 'Water Bill', 'Gas Bill'],
            'maintenance' => ['Equipment Repair', 'Facility Maintenance', 'Cleaning Services'],
            'supplies' => ['Cleaning Supplies', 'Office Supplies', 'Packaging Materials'],
            'other' => ['Staff Training', 'Marketing', 'Miscellaneous'],
        ];

        // Create 20 expenses
        for ($i = 0; $i < 20; $i++) {
            $category = array_rand($expenseTypes);
            $descriptions = $expenseTypes[$category];

            Expense::create([
                'manager_id' => $managerId,
                'section_id' => $sections[rand(0, 3)],
                'type' => $category,
                'description' => $descriptions[rand(0, count($descriptions) - 1)],
                'amount' => rand(5000, 50000),
                'expense_date' => Carbon::now()->subDays(rand(1, 30)),
            ]);
        }
    }
}
