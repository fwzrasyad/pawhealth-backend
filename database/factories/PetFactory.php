<?php

namespace Database\Factories;

use App\Models\Pet;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PetFactory extends Factory
{
    protected $model = Pet::class;

    public function definition(): array
    {
        $species = fake()->randomElement(['Cat', 'Dog']);
        
        $catBreeds = ['Persian', 'Maine Coon', 'Siamese', 'Ragdoll', 'Domestic Shorthair'];
        $dogBreeds = ['Golden Retriever', 'Labrador', 'French Bulldog', 'Poodle', 'Mixed Breed'];
        
        $breed = $species === 'Cat' ? fake()->randomElement($catBreeds) : fake()->randomElement($dogBreeds);

        return [
            'pet_id'            => (string) Str::uuid(),
            // owner_id provided externally
            'name'              => fake()->firstName(),
            'species'           => $species,
            'breed'             => $breed,
            'age'               => fake()->numberBetween(1, 15),
            'gender'            => fake()->randomElement(['Male', 'Female']),
            'weight'            => fake()->randomFloat(2, 2, 35),
            'profile_image_url' => null,
        ];
    }
}
