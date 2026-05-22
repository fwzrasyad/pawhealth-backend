<?php

namespace Database\Seeders;

use App\Models\Clinic;
use App\Models\User;
use App\Models\Pet;
use App\Models\Appointment;
use App\Models\Veterinarian;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // 1. Create "Hero Accounts"
        // ==========================================

        // Hero Clinic
        $heroClinic = Clinic::factory()->create([
            'name' => 'Penang Central Vet',
            'city' => 'Georgetown',
            'state' => 'Penang',
            'latitude' => 5.4164,
            'longitude' => 100.3327,
        ]);

        // Hero Manager
        User::factory()->manager()->create([
            'name' => 'Main Manager',
            'email' => 'manager@test.com',
            'clinic_id' => $heroClinic->clinic_id,
        ]);

        // Pending Clinic
        $pendingClinic = Clinic::factory()->create([
            'name' => 'Pending Clinic Test',
            'status' => 'pending',
        ]);

        // Pending Manager
        User::factory()->manager()->create([
            'name' => 'Pending Manager',
            'email' => 'pending@test.com',
            'clinic_id' => $pendingClinic->clinic_id,
        ]);

        // Hero Vet
        $heroVet = User::factory()->vet()->create([
            'name' => 'Dr. Jane Smith',
            'email' => 'vet@test.com',
            'clinic_id' => $heroClinic->clinic_id,
        ]);
        
        Veterinarian::factory()->create([
            'vet_id' => $heroVet->user_id,
            'name'   => $heroVet->name,
            'specialties' => json_encode(['General Practice']),
        ]);

        // Hero Pet Owner
        $heroOwner = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'owner@test.com',
        ]);

        // Hero Pets
        $pet1 = Pet::factory()->create([
            'owner_id' => $heroOwner->user_id,
            'name' => 'Bella',
            'species' => 'Dog',
            'breed' => 'Golden Retriever',
        ]);
        $pet2 = Pet::factory()->create([
            'owner_id' => $heroOwner->user_id,
            'name' => 'Luna',
            'species' => 'Cat',
            'breed' => 'Persian',
        ]);

        // Hero Appointments
        Appointment::factory()->create([
            'clinic_id' => $heroClinic->clinic_id,
            'pet_id' => $pet1->pet_id,
            'pet_name' => $pet1->name,
            'vet_id' => $heroVet->user_id,
            'vet_name' => $heroVet->name,
            'status' => 'pending',
            'appointment_date' => Carbon::tomorrow(),
            'time_slot' => Carbon::tomorrow()->setHour(10)->setMinute(0),
        ]);

        Appointment::factory()->create([
            'clinic_id' => $heroClinic->clinic_id,
            'pet_id' => $pet2->pet_id,
            'pet_name' => $pet2->name,
            'vet_id' => $heroVet->user_id,
            'vet_name' => $heroVet->name,
            'status' => 'confirmed',
            'appointment_date' => Carbon::now()->addDays(3),
            'time_slot' => Carbon::now()->addDays(3)->setHour(14)->setMinute(30),
        ]);

        Appointment::factory()->create([
            'clinic_id' => $heroClinic->clinic_id,
            'pet_id' => $pet1->pet_id,
            'pet_name' => $pet1->name,
            'vet_id' => $heroVet->user_id,
            'vet_name' => $heroVet->name,
            'status' => 'completed',
            'appointment_date' => Carbon::now()->subDays(5),
            'time_slot' => Carbon::now()->subDays(5)->setHour(9)->setMinute(0),
        ]);

        // ==========================================
        // 2. Generate Background Data
        // ==========================================

        // Generate 5 random clinics
        $clinics = Clinic::factory(5)->create();
        $allVets = collect([$heroVet]);

        foreach ($clinics as $clinic) {
            // 1 Manager per clinic
            User::factory()->manager()->create([
                'clinic_id' => $clinic->clinic_id,
            ]);

            // 2-3 Vets per clinic
            $vetsCount = rand(2, 3);
            for ($i = 0; $i < $vetsCount; $i++) {
                $vet = User::factory()->vet()->create([
                    'clinic_id' => $clinic->clinic_id,
                ]);
                Veterinarian::factory()->create([
                    'vet_id' => $vet->user_id,
                    'name'   => $vet->name,
                ]);
                $allVets->push($vet);
            }
        }

        // Generate 10 random Pet Owners
        $owners = User::factory(10)->create();
        $allPets = collect([$pet1, $pet2]);

        foreach ($owners as $owner) {
            // 1-3 Pets per owner
            $petsCount = rand(1, 3);
            $pets = Pet::factory($petsCount)->create([
                'owner_id' => $owner->user_id,
            ]);
            $allPets = $allPets->merge($pets);
        }

        // Generate 20 random Appointments
        for ($i = 0; $i < 20; $i++) {
            $randomPet = $allPets->random();
            $randomVet = $allVets->random();
            
            Appointment::factory()->create([
                'clinic_id' => $randomVet->clinic_id, // Ensure appointment belongs to the vet's clinic
                'pet_id'    => $randomPet->pet_id,
                'pet_name'  => $randomPet->name,
                'vet_id'    => $randomVet->user_id,
                'vet_name'  => $randomVet->name,
            ]);
        }
    }
}
