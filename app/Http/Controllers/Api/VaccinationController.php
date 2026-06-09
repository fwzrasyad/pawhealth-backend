<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VaccinationRecord;
use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VaccinationController extends Controller
{
    public function index($petId)
    {
        $pet = Pet::where('pet_id', $petId)->first();
        if (!$pet) {
            return response()->json(['message' => 'Pet not found'], 404);
        }

        $vaccinations = VaccinationRecord::where('pet_id', $petId)
            ->orderBy('date_administered', 'desc')
            ->get();

        return response()->json(['data' => $vaccinations]);
    }

    public function store(Request $request, $petId)
    {
        $validator = Validator::make($request->all(), [
            'vaccine_name' => 'required|string',
            'is_core' => 'boolean',
            'date_administered' => 'required|date',
            'next_due_date' => 'nullable|date',
            'record_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $pet = Pet::where('pet_id', $petId)->first();
        if (!$pet) {
            return response()->json(['message' => 'Pet not found'], 404);
        }

        $user = $request->user();
        $vetId = null;

        // If the user is a veterinarian, we can associate the vaccine to them
        if ($user->veterinarian) {
            $vetId = $user->veterinarian->vet_id;
        }

        $vaccination = VaccinationRecord::create([
            'pet_id' => $petId,
            'vaccine_name' => $request->vaccine_name,
            'is_core' => $request->is_core ?? false,
            'date_administered' => $request->date_administered,
            'next_due_date' => $request->next_due_date,
            'record_id' => $request->record_id,
            'administered_by_vet_id' => $vetId,
        ]);

        return response()->json(['data' => $vaccination], 201);
    }
}
