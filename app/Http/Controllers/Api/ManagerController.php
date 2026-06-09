<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PetResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\VeterinarianResource;
use App\Models\Appointment;
use App\Models\Pet;
use App\Models\User;
use App\Models\Veterinarian;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\FirebaseStorageService;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\FirebaseException;

class ManagerController extends Controller
{
    protected $storageService;

    public function __construct(FirebaseStorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    /**
     * POST /api/manager/register
     * Register a new clinic and its manager.
     */
    public function registerClinic(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'address'      => 'required|string|max:255',
            'city'         => 'required|string|max:100',
            'state'        => 'required|string|max:100',
            'phone'        => 'nullable|string|max:50',
            'password'     => 'required|string|min:6',
            'license_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $auth = app('firebase.auth');

        try {
            $firebaseUser = $auth->createUser([
                'email'       => $validated['email'],
                'password'    => $validated['password'],
                'displayName' => $validated['name'],
            ]);
        } catch (AuthException | FirebaseException $e) {
            return response()->json(['message' => 'Firebase Auth Error: ' . $e->getMessage()], 400);
        }

        try {
            DB::beginTransaction();

            // Store the uploaded license file to Firebase Storage
            $licensePath = $this->storageService->upload(
                $request->file('license_file'),
                'licenses'
            );

            $clinic = \App\Models\Clinic::create([
                'name'              => $validated['name'],
                'address'           => $validated['address'],
                'city'              => $validated['city'],
                'state'             => $validated['state'],
                'phone'             => $validated['phone'] ?? '',
                'status'            => 'pending',
                'license_file_path' => $licensePath,
            ]);

            $user = User::create([
                'user_id'   => $firebaseUser->uid,
                'name'      => $validated['name'],
                'email'     => $validated['email'],
                'password'  => Hash::make($validated['password']),
                'role'      => 'manager',
                'clinic_id' => $clinic->clinic_id,
            ]);

            DB::commit();

            return response()->json(['message' => 'Registration successful'], Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            DB::rollBack();
            try {
                $auth->deleteUser($firebaseUser->uid);
            } catch (\Throwable $deleteEx) {}
            return response()->json(['message' => 'Local database error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/manager/stats
     * Dashboard statistics scoped to the manager's own clinic.
     */
    public function dashboardStats(Request $request)
    {
        $clinicId = $request->user()->clinic_id;

        // Vet user IDs belonging to this clinic
        $vetIds = User::where('clinic_id', $clinicId)
            ->where('role', 'vet')
            ->pluck('user_id');

        // Owners who have at least one pet with an appointment at this clinic
        $clinicOwnerCount = User::where('role', 'owner')
            ->whereHas('pets.appointments', function ($q) use ($clinicId) {
                $q->where('clinic_id', $clinicId);
            })->count();

        // Pets that have at least one appointment at this clinic
        $clinicPetCount = Pet::whereHas('appointments', function ($q) use ($clinicId) {
            $q->where('clinic_id', $clinicId);
        })->count();

        return response()->json([
            'total_users'          => $clinicOwnerCount,
            'total_vets'           => Veterinarian::whereIn('vet_id', $vetIds)->count(),
            'total_pets'           => $clinicPetCount,
            'pending_appointments' => Appointment::where('clinic_id', $clinicId)->where('status', 'pending')->count(),
            'approved_vets'        => Veterinarian::whereIn('vet_id', $vetIds)->where('status', 'approved')->count(),
            'pending_vets'         => Veterinarian::whereIn('vet_id', $vetIds)->where('status', 'pending')->count(),
        ]);
    }

    /**
     * GET /api/manager/users
     * List pet-owner users who have at least one pet with an appointment at this clinic.
     * SECURITY: Strict multi-tenant scoping — no global queries.
     */
    public function listUsers(Request $request)
    {
        $clinicId = $request->user()->clinic_id;

        $users = User::where('role', 'owner')
            ->whereHas('pets.appointments', function ($q) use ($clinicId) {
                $q->where('clinic_id', $clinicId);
            })
            ->withCount('pets')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => $users->map(function ($user) {
                return [
                    'user_id'           => $user->user_id,
                    'name'              => $user->name,
                    'email'             => $user->email,
                    'phone_number'      => $user->phone_number,
                    'profile_image_url' => $user->profile_image_url,
                    'role'              => $user->role,
                    'pets_count'        => $user->pets_count,
                    'created_at'        => $user->created_at?->toIso8601String(),
                    'updated_at'        => $user->updated_at?->toIso8601String(),
                ];
            }),
        ]);
    }

    /**
     * GET /api/manager/users/{id}/pets
     * List pets for a specific user, only if they have appointments at this clinic.
     * SECURITY: Strict multi-tenant scoping.
     */
    public function userPets(Request $request, string $id)
    {
        $clinicId = $request->user()->clinic_id;

        // Ensure the user has at least one pet with an appointment at this clinic
        $user = User::where('user_id', $id)
            ->where('role', 'owner')
            ->whereHas('pets.appointments', function ($q) use ($clinicId) {
                $q->where('clinic_id', $clinicId);
            })
            ->firstOrFail();

        // Return only pets that have appointments at this clinic
        $pets = $user->pets()
            ->whereHas('appointments', function ($q) use ($clinicId) {
                $q->where('clinic_id', $clinicId);
            })
            ->get();

        return PetResource::collection($pets);
    }

    /**
     * DELETE /api/manager/users/{id}
     * Delete a user and their associated data.
     * SECURITY: Only allow deletion of users who have appointments at this clinic.
     */
    public function deleteUser(Request $request, string $id)
    {
        $clinicId = $request->user()->clinic_id;

        // Ensure the user is a patient of this clinic
        $user = User::where('user_id', $id)
            ->where('role', 'owner')
            ->whereHas('pets.appointments', function ($q) use ($clinicId) {
                $q->where('clinic_id', $clinicId);
            })
            ->firstOrFail();

        // Cascade: delete user's pets (and related records)
        foreach ($user->pets as $pet) {
            $pet->appointments()->delete();
            $pet->medicalRecords()->delete();
            $pet->healthJournals()->delete();
            $pet->aiScans()->delete();
            $pet->delete();
        }

        $user->delete();

        return response()->json(['message' => 'User and associated data deleted.'], Response::HTTP_OK);
    }

    /**
     * POST /api/manager/veterinarians
     * Register a new veterinarian.
     */
    public function storeVeterinarian(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'phone_number' => 'required|string|max:20',
            'password'     => 'required|string|min:6',
            'specialties'  => 'required', // string or array
        ]);

        $specialties = is_array($validated['specialties'])
            ? $validated['specialties']
            : array_map('trim', explode(',', $validated['specialties']));

        $auth = app('firebase.auth');

        try {
            $firebaseUser = $auth->createUser([
                'email'         => $validated['email'],
                'password'      => $validated['password'],
                'displayName'   => $validated['name'],
                // Firebase might require E.164 format for phoneNumber, if validation fails it will throw exception
                // We'll omit phoneNumber from Firebase creation to avoid format issues if it's not strictly required
                // or we could include it, but let's stick to email/password/name.
            ]);
        } catch (AuthException | FirebaseException $e) {
            return response()->json(['message' => 'Firebase Auth Error: ' . $e->getMessage()], 400);
        }

        try {
            DB::beginTransaction();

            $user = User::create([
                'user_id'      => $firebaseUser->uid,
                'name'         => $validated['name'],
                'email'        => $validated['email'],
                'phone_number' => $validated['phone_number'],
                'password'     => Hash::make($validated['password']),
                'role'         => 'vet',
                'clinic_id'    => $request->user()->clinic_id,
            ]);

            $vet = Veterinarian::create([
                'vet_id'      => $firebaseUser->uid,
                'name'        => $validated['name'],
                'specialties' => $specialties,
                'status'      => 'approved',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Veterinarian registered successfully.',
                'data'    => [
                    'vet_id'            => $vet->vet_id,
                    'name'              => $vet->name,
                    'email'             => $user->email,
                    'phone_number'      => $user->phone_number,
                    'specialties'       => $vet->specialties,
                    'status'            => $vet->status,
                ],
            ], Response::HTTP_CREATED);

        } catch (\Throwable $e) {
            DB::rollBack();
            try {
                $auth->deleteUser($firebaseUser->uid);
            } catch (\Throwable $deleteEx) {
                // Log or ignore
            }
            return response()->json(['message' => 'Local database error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/manager/veterinarians
     * List veterinarians belonging to this clinic only.
     * SECURITY: Strict multi-tenant scoping.
     */
    public function listVeterinarians(Request $request)
    {
        $clinicId = $request->user()->clinic_id;

        $vetIds = User::where('clinic_id', $clinicId)
            ->where('role', 'vet')
            ->pluck('user_id');

        $vets = Veterinarian::whereIn('vet_id', $vetIds)
            ->with(['user', 'availableSlots'])
            ->get();

        return response()->json([
            'data' => $vets->map(function ($vet) {
                return [
                    'vet_id'            => $vet->vet_id,
                    'name'              => $vet->name,
                    'email'             => $vet->user?->email ?? '',
                    'phone_number'      => $vet->user?->phone_number ?? '',
                    'profile_image_url' => $vet->profile_image_url,
                    'working_hours'     => $vet->working_hours,
                    'specialties'       => $vet->specialties ?? [],
                    'bio'               => $vet->bio,
                    'status'            => $vet->status ?? 'pending',
                    'consultation_fee'  => $vet->consultation_fee,
                    'weekly_schedule'   => $vet->weekly_schedule ?? [],
                    'created_at'        => $vet->created_at?->toIso8601String(),
                    'updated_at'        => $vet->updated_at?->toIso8601String(),
                ];
            }),
        ]);
    }

    /**
     * PUT /api/manager/veterinarians/{id}
     * Manager can update a vet belonging to their clinic.
     * SECURITY: Strict multi-tenant scoping.
     */
    public function updateVeterinarian(Request $request, string $id)
    {
        $clinicId = $request->user()->clinic_id;

        // Ensure the vet belongs to this clinic
        $vetUser = User::where('user_id', $id)
            ->where('clinic_id', $clinicId)
            ->where('role', 'vet')
            ->firstOrFail();

        $vet = Veterinarian::where('vet_id', $id)->firstOrFail();

        $vet->update($request->only([
            'name',
            'bio',
            'working_hours',
            'specialties',
            'status',
            'consultation_fee',
        ]));

        return response()->json([
            'message' => 'Veterinarian updated.',
            'data'    => [
                'vet_id'            => $vet->vet_id,
                'name'              => $vet->name,
                'status'            => $vet->status,
                'bio'               => $vet->bio,
                'working_hours'     => $vet->working_hours,
                'consultation_fee'  => $vet->consultation_fee,
                'specialties'       => $vet->specialties ?? [],
                'updated_at'        => $vet->updated_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * PUT /api/manager/appointments/{id}/assign
     * Assign a veterinarian to a pending appointment at this clinic.
     * SECURITY: Strict multi-tenant scoping.
     */
    public function assignVet(Request $request, string $id)
    {
        $clinicId = $request->user()->clinic_id;

        $validated = $request->validate([
            'vet_id' => 'required|string|exists:veterinarians,vet_id',
        ]);

        // Ensure appointment belongs to this clinic
        $appointment = Appointment::where('appointment_id', $id)
            ->where('clinic_id', $clinicId)
            ->firstOrFail();

        // Ensure vet belongs to this clinic
        User::where('user_id', $validated['vet_id'])
            ->where('clinic_id', $clinicId)
            ->where('role', 'vet')
            ->firstOrFail();

        $vet = Veterinarian::where('vet_id', $validated['vet_id'])->firstOrFail();

        $appointment->update([
            'vet_id'   => $vet->vet_id,
            'vet_name' => $vet->name,
            'status'   => 'assigned',
        ]);

        return response()->json([
            'message' => 'Veterinarian assigned successfully.',
            'data'    => new \App\Http\Resources\AppointmentResource($appointment->fresh()),
        ]);
    }

    /**
     * GET /api/manager/appointments
     * List all appointments at this clinic, including doctor info.
     * SECURITY: Strict multi-tenant scoping.
     */
    public function listAppointments(Request $request)
    {
        $clinicId = $request->user()->clinic_id;

        $appointments = Appointment::where('clinic_id', $clinicId)
            ->with(['pet.owner', 'veterinarian', 'medicalRecord'])
            ->orderByDesc('appointment_date')
            ->get();

        return \App\Http\Resources\AppointmentResource::collection($appointments);
    }

    /**
     * GET /api/manager/clinic
     * Get the current manager's clinic profile.
     */
    public function getClinic(Request $request)
    {
        $clinicId = $request->user()->clinic_id;
        $clinic = \App\Models\Clinic::where('clinic_id', $clinicId)->firstOrFail();

        return response()->json([
            'data' => [
                'clinic_id'   => $clinic->clinic_id,
                'name'        => $clinic->name,
                'address'     => $clinic->address,
                'city'        => $clinic->city,
                'state'       => $clinic->state,
                'phone'       => $clinic->phone,
                'description' => $clinic->description,
                'image_url'   => $clinic->image_url,
                'latitude'    => $clinic->latitude,
                'longitude'   => $clinic->longitude,
                'google_maps_url' => $clinic->google_maps_url,
            ]
        ]);
    }

    /**
     * POST /api/manager/clinic
     * Update the current manager's clinic profile (supports multipart).
     */
    public function updateClinic(Request $request)
    {
        $clinicId = $request->user()->clinic_id;
        $clinic = \App\Models\Clinic::where('clinic_id', $clinicId)->firstOrFail();

        $validated = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'address'     => 'sometimes|string|max:255',
            'city'        => 'sometimes|string|max:100',
            'state'       => 'sometimes|string|max:100',
            'phone'       => 'sometimes|string|max:50',
            'description' => 'sometimes|nullable|string',
            'latitude'    => 'sometimes|nullable|numeric',
            'longitude'   => 'sometimes|nullable|numeric',
            'google_maps_url' => 'sometimes|nullable|string|max:1000',
            'image'       => 'sometimes|image|mimes:jpeg,png,jpg,webp|max:5120', // 5MB max
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if it exists and is on Firebase Storage
            if ($clinic->image_url && str_contains($clinic->image_url, 'storage.googleapis.com')) {
                $this->storageService->delete($clinic->image_url);
            }

            $validated['image_url'] = $this->storageService->upload(
                $request->file('image'),
                'clinics'
            );
        }

        unset($validated['image']);
        $clinic->update($validated);

        return response()->json([
            'message' => 'Clinic updated successfully',
            'data' => [
                'clinic_id'   => $clinic->clinic_id,
                'name'        => $clinic->name,
                'address'     => $clinic->address,
                'city'        => $clinic->city,
                'state'       => $clinic->state,
                'phone'       => $clinic->phone,
                'description' => $clinic->description,
                'image_url'   => $clinic->image_url,
                'latitude'    => $clinic->latitude,
                'longitude'   => $clinic->longitude,
                'google_maps_url' => $clinic->google_maps_url,
            ]
        ]);
    }
}
