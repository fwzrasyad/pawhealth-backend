<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DailyRoutineLogResource;
use App\Models\DailyRoutineLog;
use Illuminate\Http\Request;

class DailyRoutineLogController extends Controller
{
    /**
     * GET /api/pets/{petId}/daily-routines
     * List daily routines for a pet.
     */
    public function index(Request $request, string $petId)
    {
        // Ensure the pet belongs to the authenticated user
        $pet = $request->user()
            ->pets()
            ->where('pet_id', $petId)
            ->firstOrFail();

        $logs = $pet->dailyRoutineLogs()
            ->orderByDesc('date')
            ->get();

        return DailyRoutineLogResource::collection($logs);
    }

    /**
     * POST /api/pets/{petId}/daily-routines
     * Create a new daily routine log.
     */
    public function store(Request $request, string $petId)
    {
        $pet = $request->user()
            ->pets()
            ->where('pet_id', $petId)
            ->firstOrFail();

        $validated = $request->validate([
            'date'           => 'required|date',
            'weight'         => 'required|numeric|min:0',
            'diet_notes'     => 'required|string',
            'activity_level' => 'required|string|max:50',
        ]);

        $log = $pet->dailyRoutineLogs()->create($validated);

        return new DailyRoutineLogResource($log);
    }
}
