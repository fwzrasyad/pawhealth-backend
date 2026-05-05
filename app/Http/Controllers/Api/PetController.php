<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PetResource;
use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PetController extends Controller
{
    /**
     * GET /api/pets
     * List the authenticated owner's pets.
     */
    public function index(Request $request)
    {
        $pets = $request->user()
            ->pets()
            ->with('dailyRoutineLogs')
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
        ]);

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
            ->with('dailyRoutineLogs')
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
        ]);

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

        $pet->delete();

        return response()->json(['message' => 'Pet deleted.'], Response::HTTP_OK);
    }
}
