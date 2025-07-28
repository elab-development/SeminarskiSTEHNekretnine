<?php

namespace Database\Factories;

use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 50000, 1000000),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'status' => $this->faker->randomElement(['available', 'sold', 'rented']),
            'property_type_id' => PropertyType::inRandomOrder()->first()?->id ?? PropertyType::factory(),
            'listed_by' =>  User::where('role', 'admin')->inRandomOrder()->first()?->id ?? User::factory()->create(['role' => 'admin'])->id
        ];
    }
}
