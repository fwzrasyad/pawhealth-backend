<?php

namespace Database\Seeders;

use App\Models\Clinic;
use App\Models\User;
use App\Models\Veterinarian;
use Illuminate\Database\Seeder;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Kreait\Firebase\Exception\Auth\EmailExists;

class PenangSeeder extends Seeder
{
    public function run(): void
    {
        $auth = Firebase::auth();

        $clinicsData = [
            [
                'name' => 'Georgetown Veterinary Clinic',
                'address' => '45 Macalister Road',
                'city' => 'Georgetown',
                'state' => 'Penang',
                'latitude' => 5.4164,
                'longitude' => 100.3327,
            ],
            [
                'name' => 'Bayan Lepas Animal Hospital',
                'address' => '12 Jalan Mahsuri',
                'city' => 'Bayan Lepas',
                'state' => 'Penang',
                'latitude' => 5.3222,
                'longitude' => 100.2828,
            ],
            [
                'name' => 'Tanjung Tokong Pet Care',
                'address' => '88 Jalan Tanjung Tokong',
                'city' => 'Tanjung Tokong',
                'state' => 'Penang',
                'latitude' => 5.4542,
                'longitude' => 100.3056,
            ]
        ];

        $specialtiesList = [
            ['General Practice', 'Preventive Care'],
            ['Surgery', 'Orthopedics'],
            ['Internal Medicine', 'Cardiology'],
            ['Dentistry', 'Dermatology'],
            ['Exotic Pets', 'Avian Medicine'],
            ['Emergency Care', 'Critical Care']
        ];

        foreach ($clinicsData as $index => $cData) {
            // Check if clinic already exists by name to avoid duplicates
            $clinic = Clinic::firstOrCreate(
                ['name' => $cData['name']],
                [
                    'address' => $cData['address'],
                    'city' => $cData['city'],
                    'state' => $cData['state'],
                    'latitude' => $cData['latitude'],
                    'longitude' => $cData['longitude'],
                    'phone' => '04-' . rand(2000000, 8999999),
                    'description' => "Serving the {$cData['city']} area with top-tier veterinary services.",
                    'status' => 'approved',
                ]
            );

            // Create Manager
            $managerEmail = 'manager' . ($index + 1) . '@penangvets.com';
            $managerUid = $this->createFirebaseUser($auth, $managerEmail, 'iamcool', 'Manager ' . $cData['city']);
            
            if ($managerUid) {
                User::updateOrCreate(
                    ['user_id' => $managerUid],
                    [
                        'name' => 'Manager ' . $cData['city'],
                        'email' => $managerEmail,
                        'password' => '', // Managed by Firebase
                        'role' => 'manager',
                        'phone_number' => '01' . rand(10000000, 99999999),
                        'clinic_id' => $clinic->clinic_id,
                        'profile_image_url' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . urlencode('Manager ' . $cData['city']),
                    ]
                );
            }

            $firstNames = ['Ahmad', 'Siti', 'Wei', 'Mei', 'Ramesh', 'Priya', 'Ali', 'Nur'];
            $lastNames = ['Tan', 'Lim', 'Abdullah', 'Ismail', 'Wong', 'Lee', 'Muthu', 'Kumar'];

            // Create 4 Vets
            for ($i = 1; $i <= 4; $i++) {
                $fName = $firstNames[array_rand($firstNames)];
                $lName = $lastNames[array_rand($lastNames)];
                $vetName = 'Dr. ' . $lName . ' ' . $fName;
                $vetEmail = 'vet' . $i . '_' . $index . '@penangvets.com';
                $vetUid = $this->createFirebaseUser($auth, $vetEmail, 'iamcool', $vetName);
                
                if ($vetUid) {
                    $vetUser = User::updateOrCreate(
                        ['user_id' => $vetUid],
                        [
                            'name' => $vetName,
                            'email' => $vetEmail,
                            'password' => '',
                            'role' => 'vet',
                            'phone_number' => '01' . rand(10000000, 99999999),
                            'clinic_id' => $clinic->clinic_id,
                            'profile_image_url' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . urlencode($vetName) . '&backgroundColor=c0aede,b6e3f4',
                        ]
                    );

                    $specs = $specialtiesList[array_rand($specialtiesList)];
                    $bio = "{$vetName} is an experienced veterinarian based in {$cData['city']}, Penang. Specializing in " . implode(' and ', $specs) . ", they provide compassionate and expert care to all their furry patients.";

                    Veterinarian::updateOrCreate(
                        ['vet_id' => $vetUid],
                        [
                            'name' => $vetName,
                            'profile_image_url' => $vetUser->profile_image_url,
                            'working_hours' => 'Mon-Fri 9:00 AM - 5:00 PM',
                            'specialties' => $specs,
                            'bio' => $bio,
                            'status' => 'approved',
                            'consultation_fee' => rand(5000, 15000) / 100,
                            'weekly_schedule' => [
                                'Monday' => ['09:00-13:00', '14:00-17:00'],
                                'Tuesday' => ['09:00-13:00', '14:00-17:00'],
                                'Wednesday' => ['09:00-13:00'],
                                'Thursday' => ['09:00-13:00', '14:00-17:00'],
                                'Friday' => ['09:00-13:00', '14:00-17:00'],
                                'Saturday' => ['09:00-13:00'],
                                'Sunday' => []
                            ],
                        ]
                    );
                }
            }
            echo "Seeded {$cData['name']} with manager and 4 vets.\n";
        }
    }

    private function createFirebaseUser($auth, $email, $password, $displayName)
    {
        try {
            $createdUser = $auth->createUser([
                'email' => $email,
                'emailVerified' => true,
                'password' => $password,
                'displayName' => $displayName,
            ]);
            return $createdUser->uid;
        } catch (EmailExists $e) {
            $user = $auth->getUserByEmail($email);
            // Optionally update password to make sure it's 'iamcool'
            $auth->changeUserPassword($user->uid, $password);
            return $user->uid;
        } catch (\Exception $e) {
            echo "Error creating Firebase user {$email}: " . $e->getMessage() . "\n";
            return null;
        }
    }
}
