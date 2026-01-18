<?php

namespace Database\Seeders;

use App\Models\WasteLog;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class WasteLogSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [1, 2, 3, 4];
        $users = [5, 6, 7, 8]; // Chefs

        // Create 15 waste logs
        for ($i = 0; $i < 15; $i++) {
            $sectionIndex = rand(0, 3);

            WasteLog::create([
                'section_id' => $sections[$sectionIndex],
                'raw_material_id' => rand(1, 29),
                'quantity' => rand(1, 10),
                'reason' => ['spoilage', 'expired', 'damaged', 'overproduction'][rand(0, 3)],
                'cost_amount' => rand(100, 5000),
                'logged_by' => $users[$sectionIndex],
                'approved_by' => 2, // Manager
            ]);
        }
    }
}
