<?php

namespace Database\Seeders;

use App\Models\PropertyType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PropertyTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Apartment', 'description' => 'Residential units in buildings.'],
            ['name' => 'House', 'description' => 'Standalone residential homes.'],
            ['name' => 'Office', 'description' => 'Commercial office spaces.'],
            ['name' => 'Land', 'description' => 'Plots available for sale.'],
        ];

        foreach ($types as $type) {
            PropertyType::create($type);
        }
    }
}
