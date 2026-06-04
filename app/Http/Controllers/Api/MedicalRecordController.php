<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MedicalRecordResource;
use App\Models\MedicalRecord;
use Illuminate\Http\Request;

class MedicalRecordController extends Controller
{
    /**
     * GET /api/pets/{petId}/medical-records
     * List medical records for a specific pet.
     */
    public function index(Request $request, string $petId)
    {
        $user = $request->user();

        if ($user->role === 'vet') {
            // Vets can access medical records for pets they have appointments with
            $hasAppointment = \App\Models\Appointment::where('vet_id', $user->user_id)
                ->where('pet_id', $petId)
                ->exists();

            if (!$hasAppointment) {
                return response()->json(['message' => 'Forbidden — you have no appointments with this pet.'], 403);
            }

            $pet = \App\Models\Pet::where('pet_id', $petId)->firstOrFail();
        } else {
            // Pet owners can only access their own pets
            $pet = $user->pets()
                ->where('pet_id', $petId)
                ->firstOrFail();
        }

        $records = $pet->medicalRecords()
            ->with(['veterinarian', 'appointment'])
            ->orderByDesc('created_at')
            ->get();

        return MedicalRecordResource::collection($records);
    }

    /**
     * POST /api/medical-records
     * Create a medical record (vet only).
     * Automatically assigns the logged-in Vet's ID.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'vet') {
            return response()->json(['message' => 'Forbidden — only vets can create medical records.'], 403);
        }

        $validated = $request->validate([
            'pet_id'                 => 'required|string|exists:pets,pet_id',
            'appointment_id'         => 'nullable|string|exists:appointments,appointment_id',
            'diagnosis'              => 'required|string',
            'doctor_notes'           => 'nullable|string',
            'medications_prescribed' => 'nullable|array',
            'follow_up_instructions' => 'nullable|string',
        ]);

        // Auto-assign the authenticated vet's ID
        $validated['vet_id'] = $user->user_id;

        $record = MedicalRecord::create($validated);

        return new MedicalRecordResource($record->load(['veterinarian', 'appointment']));
    }

    /**
     * PUT /api/medical-records/{id}
     * Update a medical record (vet only — must own the record).
     */
    public function update(Request $request, string $id)
    {
        $user = $request->user();

        if ($user->role !== 'vet') {
            return response()->json(['message' => 'Forbidden — only vets can update medical records.'], 403);
        }

        $record = MedicalRecord::where('record_id', $id)
            ->where('vet_id', $user->user_id)
            ->firstOrFail();

        $validated = $request->validate([
            'diagnosis'              => 'sometimes|string',
            'doctor_notes'           => 'nullable|string',
            'medications_prescribed' => 'nullable|array',
            'follow_up_instructions' => 'nullable|string',
        ]);

        $record->update($validated);

        return new MedicalRecordResource($record->fresh()->load(['veterinarian', 'appointment']));
    }
}
