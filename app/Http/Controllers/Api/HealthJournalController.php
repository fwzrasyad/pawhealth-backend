<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HealthJournalResource;
use App\Models\HealthJournal;
use Illuminate\Http\Request;

class HealthJournalController extends Controller
{
    /**
     * GET /api/pets/{petId}/health-journals
     * List health journal entries for a pet (acute symptom tracking).
     */
    public function index(Request $request, string $petId)
    {
        // Ensure the pet belongs to the authenticated user
        $pet = $request->user()
            ->pets()
            ->where('pet_id', $petId)
            ->firstOrFail();

        $journals = $pet->healthJournals()
            ->orderByDesc('date')
            ->get();

        return HealthJournalResource::collection($journals);
    }

    /**
     * POST /api/pets/{petId}/health-journals
     * Create a new health journal entry for acute recovery tracking.
     */
    public function store(Request $request, string $petId)
    {
        $pet = $request->user()
            ->pets()
            ->where('pet_id', $petId)
            ->firstOrFail();

        $validated = $request->validate([
            'date'         => 'required|date',
            'symptom_tags' => 'required|array',
            'symptom_tags.*' => 'string|max:100',
            'notes'        => 'nullable|string',
            'photo_url'    => 'nullable|string|max:500',
        ]);

        $journal = $pet->healthJournals()->create($validated);

        return new HealthJournalResource($journal);
    }
}
