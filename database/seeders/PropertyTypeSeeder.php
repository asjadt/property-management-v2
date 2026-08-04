<?php

namespace Database\Seeders;

use App\Models\PropertyType;
use Illuminate\Database\Seeder;

class PropertyTypeSeeder extends Seeder
{
    public function run(): void
    {
        $titles = [
            'Terraced House', 'Studio Flat', 'Flat', 'House',
            'Bungalow', 'Residential Plot', 'Commercial Plot', 'Shop',
        ];

        foreach ($titles as $i => $title) {
            PropertyType::updateOrCreate(
                ['title' => $title],
                ['sort_order' => ($i + 1) * 10]
            );
        }
    }
}
