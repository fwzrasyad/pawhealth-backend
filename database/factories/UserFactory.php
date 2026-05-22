<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;
    
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'user_id'      => (string) Str::uuid(),
            'name'         => fake()->name(),
            'email'        => fake()->unique()->safeEmail(),
            'password'     => static::$password ??= Hash::make('password'),
            'role'         => 'owner', // Default role
            'phone_number' => fake()->phoneNumber(),
            'clinic_id'    => null,
        ];
    }

    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'manager',
        ]);
    }

    public function vet(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'vet',
        ]);
    }
}
