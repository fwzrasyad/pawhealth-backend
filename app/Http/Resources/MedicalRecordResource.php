<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicalRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'record_id'              => $this->record_id,
            'pet_id'                 => $this->pet_id,
            'vet_id'                 => $this->vet_id,
            'appointment_id'         => $this->appointment_id,
            'diagnosis'              => $this->diagnosis,
            'doctor_notes'           => $this->doctor_notes,
            'medications_prescribed' => $this->medications_prescribed ?? [],
            'follow_up_instructions' => $this->follow_up_instructions,
            'vet_name'               => $this->whenLoaded('veterinarian', fn() => $this->veterinarian?->name),
            'created_at'             => $this->created_at?->toIso8601String(),
            'updated_at'             => $this->updated_at?->toIso8601String(),
        ];
    }
}
