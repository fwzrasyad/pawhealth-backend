<?php

namespace Database\Factories;

use App\Models\Clinic;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ClinicFactory extends Factory
{
    protected $model = Clinic::class;

    public function definition(): array
    {
        return [
            'clinic_id'   => (string) Str::uuid(),
            'name'        => fake()->company() . ' Veterinary Clinic',
            'address'     => fake()->streetAddress(),
            'city'        => 'Georgetown',
            'state'       => 'Penang',
            // Realistic latitude/longitude around Penang (5.4164, 100.3327)
            'latitude'    => fake()->randomFloat(6, 5.2, 5.5),
            'longitude'   => fake()->randomFloat(6, 100.2, 100.5),
            'phone'       => fake()->phoneNumber(),
            'description' => fake()->paragraph(),
        ];
    }
}
