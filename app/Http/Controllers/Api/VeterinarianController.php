<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VeterinarianResource;
use App\Models\Veterinarian;
use App\Services\FirebaseStorageService;
use Illuminate\Http\Request;

class VeterinarianController extends Controller
{
    protected $storageService;

    public function __construct(FirebaseStorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    /**
     * GET /api/veterinarians
     * List all veterinarians.
     */
    public function index()
    {
        $vets = Veterinarian::with('availableSlots')->get();

        return VeterinarianResource::collection($vets);
    }

    /**
     * POST /api/veterinarians
     * Create a vet profile linked to the authenticated user.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Prevent duplicate profiles
        if (Veterinarian::where('vet_id', $user->user_id)->exists()) {
            return response()->json([
                'error' => 'Veterinarian profile already exists for this user.',
            ], 409);
        }

        $vet = Veterinarian::create([
            'vet_id'          => $user->user_id,
            'name'            => $request->input('name', $user->name),
            'bio'             => $request->input('bio', ''),
            'working_hours'   => $request->input('working_hours', '9:00 AM - 5:00 PM'),
            'specialties'     => $request->input('specialties', []),
            'weekly_schedule'  => $request->input('weekly_schedule', []),
        ]);

        return new VeterinarianResource($vet);
    }

    /**
     * GET /api/veterinarians/me
     * Return the authenticated vet's own profile.
     */
    public function me(Request $request)
    {
        $user = $request->user();

        $vet = Veterinarian::with('availableSlots')
            ->where('vet_id', $user->user_id)
            ->firstOrFail();

        return new VeterinarianResource($vet);
    }

    /**
     * PUT /api/veterinarians/{id}
     * Update a vet profile (owner only, partial updates supported).
     */
    public function update(Request $request, string $id)
    {
        $user = $request->user();
        $vet  = Veterinarian::where('vet_id', $id)->firstOrFail();

        // Authorization: only the vet themselves can update
        if ($vet->vet_id !== $user->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $updateData = $request->only([
            'name',
            'bio',
            'working_hours',
            'specialties',
            'weekly_schedule',
            'profile_image_url',
        ]);

        // Handle profile image file upload
        if ($request->hasFile('profile_image')) {
            $request->validate([
                'profile_image' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
            ]);

            // Delete old image
            $this->storageService->delete($vet->profile_image_url);

            $updateData['profile_image_url'] = $this->storageService->upload(
                $request->file('profile_image'),
                'vets'
            );
        }

        $vet->update($updateData);

        return new VeterinarianResource($vet->fresh());
    }

    /**
     * GET /api/veterinarians/{vetId}
     * Show a single veterinarian profile with available slots.
     */
    public function show(string $vetId)
    {
        $vet = Veterinarian::with('availableSlots')
            ->where('vet_id', $vetId)
            ->firstOrFail();

        return new VeterinarianResource($vet);
    }
}
