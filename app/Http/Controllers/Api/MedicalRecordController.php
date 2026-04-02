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
        $pet = $request->user()
            ->pets()
            ->where('pet_id', $petId)
            ->firstOrFail();

        $records = $pet->medicalRecords()
            ->orderByDesc('created_at')
            ->get();

        return MedicalRecordResource::collection($records);
    }

    /**
     * POST /api/medical-records
     * Create a medical record (vet only).
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'vet') {
            return response()->json(['message' => 'Forbidden — only vets can create medical records.'], 403);
        }

        $validated = $request->validate([
            'pet_id'           => 'required|string|exists:pets,pet_id',
            'diagnosis'        => 'required|string',
            'treatment'        => 'required|string',
            'vaccination_date' => 'nullable|date',
            'next_due_date'    => 'nullable|date',
            'attachment_url'   => 'nullable|string|max:500',
        ]);

        $validated['vet_id'] = $user->user_id;

        $record = MedicalRecord::create($validated);

        return new MedicalRecordResource($record);
    }

    /**
     * PUT /api/medical-records/{id}
     * Update a medical record (vet only).
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
            'diagnosis'        => 'sometimes|string',
            'treatment'        => 'sometimes|string',
            'vaccination_date' => 'nullable|date',
            'next_due_date'    => 'nullable|date',
            'attachment_url'   => 'nullable|string|max:500',
        ]);

        $record->update($validated);

        return new MedicalRecordResource($record->fresh());
    }
}
