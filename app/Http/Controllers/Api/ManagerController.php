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

class ManagerController extends Controller
{
    /**
     * GET /api/manager/stats
     * Dashboard statistics for the manager portal.
     */
    public function dashboardStats()
    {
        return response()->json([
            'total_users'          => User::where('role', 'owner')->count(),
            'total_vets'           => Veterinarian::count(),
            'total_pets'           => Pet::count(),
            'pending_appointments' => Appointment::where('status', 'pending')->count(),
            'approved_vets'        => Veterinarian::where('status', 'approved')->count(),
            'pending_vets'         => Veterinarian::where('status', 'pending')->count(),
        ]);
    }

    /**
     * GET /api/manager/users
     * List all pet-owner users with pet count.
     */
    public function listUsers()
    {
        $users = User::where('role', 'owner')
            ->withCount('pets')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => $users->map(function ($user) {
                return [
                    'user_id'      => $user->user_id,
                    'name'         => $user->name,
                    'email'        => $user->email,
                    'phone_number' => $user->phone_number,
                    'role'         => $user->role,
                    'pets_count'   => $user->pets_count,
                    'created_at'   => $user->created_at?->toIso8601String(),
                    'updated_at'   => $user->updated_at?->toIso8601String(),
                ];
            }),
        ]);
    }

    /**
     * GET /api/manager/users/{id}/pets
     * List pets for a specific user.
     */
    public function userPets(string $id)
    {
        $user = User::where('user_id', $id)->firstOrFail();
        $pets = $user->pets()->get();

        return PetResource::collection($pets);
    }

    /**
     * DELETE /api/manager/users/{id}
     * Delete a user and their associated data.
     */
    public function deleteUser(string $id)
    {
        $user = User::where('user_id', $id)->firstOrFail();

        // Cascade: delete user's pets (and related records)
        foreach ($user->pets as $pet) {
            $pet->appointments()->delete();
            $pet->medicalRecords()->delete();
            $pet->dailyRoutineLogs()->delete();
            $pet->aiScans()->delete();
            $pet->delete();
        }

        $user->delete();

        return response()->json(['message' => 'User and associated data deleted.'], Response::HTTP_OK);
    }

    /**
     * GET /api/manager/veterinarians
     * List all veterinarians with user info.
     */
    public function listVeterinarians()
    {
        $vets = Veterinarian::with(['user', 'availableSlots'])->get();

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
                    'weekly_schedule'   => $vet->weekly_schedule ?? [],
                    'created_at'        => $vet->created_at?->toIso8601String(),
                    'updated_at'        => $vet->updated_at?->toIso8601String(),
                ];
            }),
        ]);
    }

    /**
     * PUT /api/manager/veterinarians/{id}
     * Manager can update any vet's status and other fields.
     */
    public function updateVeterinarian(Request $request, string $id)
    {
        $vet = Veterinarian::where('vet_id', $id)->firstOrFail();

        $vet->update($request->only([
            'name',
            'bio',
            'working_hours',
            'specialties',
            'status',
        ]));

        return response()->json([
            'message' => 'Veterinarian updated.',
            'data'    => [
                'vet_id'            => $vet->vet_id,
                'name'              => $vet->name,
                'status'            => $vet->status,
                'bio'               => $vet->bio,
                'working_hours'     => $vet->working_hours,
                'specialties'       => $vet->specialties ?? [],
                'updated_at'        => $vet->updated_at?->toIso8601String(),
            ],
        ]);
    }
}
