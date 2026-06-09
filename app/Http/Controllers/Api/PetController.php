<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PetResource;
use App\Models\Pet;
use App\Services\FirebaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PetController extends Controller
{
    protected $storageService;

    public function __construct(FirebaseStorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    /**
     * GET /api/pets
     * List the authenticated owner's pets.
     */
    public function index(Request $request)
    {
        $pets = $request->user()
            ->pets()
            ->with(['healthJournals', 'vaccinations', 'recoveryPlans'])
            ->get();

        return PetResource::collection($pets);
    }

    /**
     * POST /api/pets
     * Create a new pet for the authenticated owner.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'species'           => 'required|string|max:100',
            'breed'             => 'required|string|max:100',
            'age'               => 'required|integer|min:0',
            'gender'            => 'required|string|max:20',
            'weight'            => 'required|numeric|min:0',
            'profile_image_url' => 'nullable|string|url|max:2048',
            'profile_image'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        // Handle file upload — takes priority over URL string
        if ($request->hasFile('profile_image')) {
            $validated['profile_image_url'] = $this->storageService->upload(
                $request->file('profile_image'),
                'pets'
            );
        }
        unset($validated['profile_image']);

        $pet = $request->user()->pets()->create($validated);

        return new PetResource($pet);
    }

    /**
     * GET /api/pets/{petId}
     * Show a specific pet with daily routines.
     */
    public function show(Request $request, string $petId)
    {
        $pet = $request->user()
            ->pets()
            ->with(['healthJournals', 'vaccinations', 'recoveryPlans'])
            ->where('pet_id', $petId)
            ->firstOrFail();

        return new PetResource($pet);
    }

    /**
     * PUT /api/pets/{petId}
     * Update a pet.
     */
    public function update(Request $request, string $petId)
    {
        $pet = $request->user()
            ->pets()
            ->where('pet_id', $petId)
            ->firstOrFail();

        $validated = $request->validate([
            'name'              => 'sometimes|string|max:255',
            'species'           => 'sometimes|string|max:100',
            'breed'             => 'sometimes|string|max:100',
            'age'               => 'sometimes|integer|min:0',
            'gender'            => 'sometimes|string|max:20',
            'weight'            => 'sometimes|numeric|min:0',
            'profile_image_url' => 'nullable|string|url|max:2048',
            'profile_image'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        // Handle file upload — takes priority over URL string
        if ($request->hasFile('profile_image')) {
            // Delete old image
            $this->storageService->delete($pet->profile_image_url);

            $validated['profile_image_url'] = $this->storageService->upload(
                $request->file('profile_image'),
                'pets'
            );
        }
        unset($validated['profile_image']);

        $pet->update($validated);

        return new PetResource($pet->fresh());
    }

    /**
     * DELETE /api/pets/{petId}
     * Delete a pet.
     */
    public function destroy(Request $request, string $petId)
    {
        $pet = $request->user()
            ->pets()
            ->where('pet_id', $petId)
            ->firstOrFail();

        // Clean up image from Firebase Storage
        $this->storageService->delete($pet->profile_image_url);

        $pet->delete();

        return response()->json(['message' => 'Pet deleted.'], Response::HTTP_OK);
    }
}

