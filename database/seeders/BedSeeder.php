<?php

namespace Database\Seeders;

use App\Models\Bed;
use Illuminate\Database\Seeder;

class BedSeeder extends Seeder
{
    public function run(): void
    {
        $titles = [
            'Studio', 'One', 'One-Two', 'Two', 'Two-Three', 'Three',
            'Three-Four', 'Four', 'Four-Five', 'Five', 'Five-Six', 'Six',
            'Six-Seven', 'Seven', 'Eight', 'Nine', 'Ten',
        ];

        foreach ($titles as $i => $title) {
            Bed::updateOrCreate(
                ['title' => $title],
                ['sort_order' => ($i + 1) * 10]
            );
        }
    }
}
