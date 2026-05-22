<?php

namespace Database\Factories;

use App\Models\Veterinarian;
use Illuminate\Database\Eloquent\Factories\Factory;

class VeterinarianFactory extends Factory
{
    protected $model = Veterinarian::class;

    public function definition(): array
    {
        return [
            // vet_id maps to user_id, must be provided externally when seeding
            'name'              => fake()->name(),
            'profile_image_url' => '',
            'working_hours'     => '9:00 AM - 5:00 PM',
            'specialties'       => json_encode([fake()->randomElement(['General Practice', 'Surgery', 'Dermatology', 'Dentistry'])]),
            'bio'               => fake()->paragraph(),
            'status'            => 'approved',
        ];
    }
}
