<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicalRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'record_id'        => $this->record_id,
            'pet_id'           => $this->pet_id,
            'vet_id'           => $this->vet_id,
            'diagnosis'        => $this->diagnosis,
            'treatment'        => $this->treatment,
            'vaccination_date' => $this->vaccination_date?->toIso8601String(),
            'next_due_date'    => $this->next_due_date?->toIso8601String(),
            'attachment_url'   => $this->attachment_url,
            'created_at'       => $this->created_at?->toIso8601String(),
            'updated_at'       => $this->updated_at?->toIso8601String(),
        ];
    }
}
