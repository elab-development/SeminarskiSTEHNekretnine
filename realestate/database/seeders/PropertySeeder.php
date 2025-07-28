<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        $admins = User::where('role', 'admin')->pluck('id')->toArray();
        $types = PropertyType::pluck('id')->toArray();

        $properties = [
            [
                'title' => 'Luxury 2-Bedroom Apartment',
                'description' => 'Modern apartment in the heart of New York with great amenities.',
                'price' => 250000,
                'address' => '123 Main Street',
                'city' => 'New York',
                'status' => 'available',
            ],
            [
                'title' => 'Cozy Family House',
                'description' => 'Perfect 4-bedroom house with a large backyard in Los Angeles.',
                'price' => 450000,
                'address' => '456 Oak Avenue',
                'city' => 'Los Angeles',
                'status' => 'available',
            ],
            [
                'title' => 'Downtown Office Space',
                'description' => 'Commercial office for startups, fully furnished.',
                'price' => 150000,
                'address' => '789 Market Street',
                'city' => 'San Francisco',
                'status' => 'available',
            ],
            [
                'title' => 'Beachfront Villa',
                'description' => 'Exclusive villa with ocean view in Miami.',
                'price' => 1200000,
                'address' => '321 Ocean Drive',
                'city' => 'Miami',
                'status' => 'sold',
            ],
            [
                'title' => 'Vacant Land for Development',
                'description' => 'Perfect land for residential development.',
                'price' => 300000,
                'address' => '654 Greenfield Road',
                'city' => 'Austin',
                'status' => 'available',
            ],
        ];

        foreach ($properties as $property) {
            Property::create([
                ...$property,
                'property_type_id' => $types[array_rand($types)],
                'listed_by' => $admins[array_rand($admins)],
            ]);
        }
    }
}
