<?php

namespace Database\Seeders;

use App\Models\Section;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = [
            ['name' => 'Eatery', 'description' => 'Main restaurant section'],
            ['name' => 'Café', 'description' => 'Coffee and light meals'],
            ['name' => 'Lounge', 'description' => 'Bar and lounge area'],
            ['name' => 'Grills', 'description' => 'Grilled specialties'],
        ];

        foreach ($sections as $section) {
            Section::firstOrCreate(
                ['name' => $section['name']],
                ['description' => $section['description']]
            );
        }
    }
}
