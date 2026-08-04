<?php

namespace Database\Seeders;

use App\Models\PropertyType;
use App\Models\Bed;
use App\Models\Bath;
use Illuminate\Database\Seeder;

class PropertyTypeBedBathSeeder extends Seeder
{
    public function run(): void
    {
        // Map: property type title => [bed titles], [bath titles]
        $map = [
            'Terraced House' => [
                'beds' => ['One', 'One-Two', 'Two', 'Two-Three', 'Three', 'Three-Four', 'Four'],
                'baths' => ['One', 'Two', 'Three'],
            ],
            'Studio Flat' => [
                'beds' => ['Studio'],
                'baths' => ['One'],
            ],
            'Flat' => [
                'beds' => ['One', 'One-Two', 'Two', 'Two-Three', 'Three', 'Four'],
                'baths' => ['One', 'Two', 'Three'],
            ],
            'House' => [
                'beds' => ['Two', 'Three', 'Three-Four', 'Four', 'Four-Five', 'Five', 'Five-Six', 'Six'],
                'baths' => ['One', 'Two', 'Three', 'Four', 'Five'],
            ],
            'Bungalow' => [
                'beds' => ['Two', 'Three', 'Four', 'Five'],
                'baths' => ['One', 'Two', 'Three'],
            ],
            'Shop' => [
                'beds' => [],
                'baths' => ['One'],
            ],
            'Residential Plot' => [
                'beds' => [],
                'baths' => [],
            ],
            'Commercial Plot' => [
                'beds' => [],
                'baths' => [],
            ],
        ];

        foreach ($map as $typeTitle => $rel) {
            $propertyType = PropertyType::where('title', $typeTitle)->first();

            if (! $propertyType) {
                continue; // skip if not seeded yet
            }

            $bedIds = Bed::whereIn('title', $rel['beds'])->pluck('id');
            $bathIds = Bath::whereIn('title', $rel['baths'])->pluck('id');

            $propertyType->beds()->sync($bedIds);
            $propertyType->baths()->sync($bathIds);
        }
    }
}
