<?php

namespace Database\Seeders;

use App\Models\Bath;
use Illuminate\Database\Seeder;

class BathSeeder extends Seeder
{
    public function run(): void
    {
        $titles = ['One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten'];

        foreach ($titles as $i => $title) {
            Bath::updateOrCreate(
                ['title' => $title],
                ['sort_order' => ($i + 1) * 10]
            );
        }
    }
}
