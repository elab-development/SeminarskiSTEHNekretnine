<?php

namespace Database\Seeders;

use App\Models\Inquiry;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InquirySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $properties = Property::pluck('id')->toArray();
        $users = User::where('role', 'user')->pluck('id')->toArray();

        $messages = [
            'I am very interested in this property, can we schedule a viewing?',
            'Is the price negotiable?',
            'Could you provide more details about the neighborhood?',
            'When will this property be available for move-in?',
            'Does this property come with parking space?'
        ];

        for ($i = 0; $i < 10; $i++) {
            Inquiry::create([
                'name' => fake()->name(),
                'email' => fake()->safeEmail(),
                'phone' => fake()->phoneNumber(),
                'message' => $messages[array_rand($messages)],
                'property_id' => $properties[array_rand($properties)],
                'user_id'  => $users[array_rand($users)],
            ]);
        }
    }
}
