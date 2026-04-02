<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'appointment_id'   => $this->appointment_id,
            'pet_id'           => $this->pet_id,
            'pet_name'         => $this->pet_name,
            'vet_id'           => $this->vet_id,
            'vet_name'         => $this->vet_name,
            'reason'           => $this->reason,
            'appointment_date' => $this->appointment_date?->toIso8601String(),
            'time_slot'        => $this->time_slot?->toIso8601String(),
            'status'           => $this->status,
            'created_at'       => $this->created_at?->toIso8601String(),
            'updated_at'       => $this->updated_at?->toIso8601String(),
        ];
    }
}
