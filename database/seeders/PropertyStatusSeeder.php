<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Status;

class PropertyStatusSeeder extends Seeder
{
    public function run()
    {
        $statuses = [
            'Available / Vacant',
            'Occupied / Tenanted',
            'Reserved / Pending',
            'Under Maintenance / Repairs',
            'Off Market',
            'Eviction / Legal Process',
            'Notice Given',
            'Under Offer / Sold',
            'Blocked / Held'
        ];

        foreach ($statuses as $status) {
            Status::firstOrCreate(['name' => $status]);
        }
    }
}
