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
                $status = ['pending', 'approved', 'fulfilled'][rand(0, 2)];

                $request = MaterialRequest::create([
                    'section_id' => $sectionId,
                    'requested_by' => $chefIds[$index],
                    'status' => $status,
                    'request_date' => Carbon::now()->subDays(rand(1, 15)),
                    'needed_by' => Carbon::now()->addDays(rand(1, 7)),
                    'notes' => 'Regular kitchen supplies needed',
                ]);

                // Add 2-4 items per request
                $itemCount = rand(2, 4);
                for ($j = 0; $j < $itemCount; $j++) {
                    MaterialRequestItem::create([
                        'material_request_id' => $request->id,
                        'raw_material_id' => rand(1, 29),
                        'quantity_requested' => rand(5, 50),
                        'quantity_approved' => $status !== 'pending' ? rand(5, 50) : null,
                        'quantity_fulfilled' => $status === 'fulfilled' ? rand(5, 50) : null,
                    ]);
                }
            }
        }
    }
}
