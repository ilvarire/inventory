<?php

namespace Database\Seeders;

use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class MaterialRequestSeeder extends Seeder
{
    public function run(): void
    {
        // Create material requests from different sections
        $sections = [1, 2, 3, 4]; // Eatery, Cafe, Lounge, Grills
        $chefIds = [5, 6, 7, 8]; // Chef IDs for each section

        foreach ($sections as $index => $sectionId) {
            // Create 3 requests per section
            for ($i = 0; $i < 3; $i++) {
                $status = ['pending', 'approved', 'rejected'][rand(0, 2)];

                $request = MaterialRequest::create([
                    'chef_id' => $chefIds[$index],
                    'section_id' => $sectionId,
                    'status' => $status,
                ]);

                // Add 2-4 items per request
                $itemCount = rand(2, 4);
                for ($j = 0; $j < $itemCount; $j++) {
                    MaterialRequestItem::create([
                        'material_request_id' => $request->id,
                        'raw_material_id' => rand(1, 29),
                        'quantity' => rand(5, 50),
                    ]);
                }
            }
        }
    }
}
