<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AIScanResource;
use App\Models\AIScan;
use Illuminate\Http\Request;

class AIScanController extends Controller
{
    /**
     * GET /api/pets/{petId}/ai-scans
     * List AI scans for a specific pet.
     */
    public function index(Request $request, string $petId)
    {
        $pet = $request->user()
            ->pets()
            ->where('pet_id', $petId)
            ->firstOrFail();

        $scans = $pet->aiScans()
            ->orderByDesc('scan_date')
            ->get();

        return AIScanResource::collection($scans);
    }

    /**
     * POST /api/pets/{petId}/ai-scans
     * Create a new AI scan result.
     */
    public function store(Request $request, string $petId)
    {
        $pet = $request->user()
            ->pets()
            ->where('pet_id', $petId)
            ->firstOrFail();

        $validated = $request->validate([
            'scan_date'        => 'required|date',
            'image_url'        => 'required|string|max:500',
            'ai_result_label'  => 'required|string|max:255',
            'confidence_score' => 'required|numeric|between:0,1',
        ]);

        $scan = $pet->aiScans()->create($validated);

        return new AIScanResource($scan);
    }
}
