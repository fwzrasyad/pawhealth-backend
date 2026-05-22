<?php

namespace Database\Factories;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        $isPast = fake()->boolean(40); // 40% chance of being in the past
        
        $date = $isPast 
            ? fake()->dateTimeBetween('-2 months', '-1 days') 
            : fake()->dateTimeBetween('+1 days', '+2 months');

        $statusOptions = $isPast 
            ? ['completed', 'cancelled'] 
            : ['pending', 'confirmed'];

        return [
            'appointment_id'   => (string) Str::uuid(),
            // clinic_id, pet_id, pet_name, vet_id, vet_name provided externally
            'reason'           => fake()->randomElement(['Annual checkup', 'Vaccination', 'Skin irritation', 'Lethargy', 'Dental scaling']),
            'appointment_date' => Carbon::parse($date)->format('Y-m-d'),
            'time_slot'        => Carbon::parse($date)->setHour(fake()->numberBetween(9, 16))->setMinute(0)->format('Y-m-d H:i:s'),
            'status'           => fake()->randomElement($statusOptions),
        ];
    }
}
