<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\MedicalRecordResource;
use App\Http\Resources\PetResource;
use App\Http\Resources\UserResource;
class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'appointment_id'   => $this->appointment_id,
            'clinic_id'        => $this->clinic_id,
            'pet_id'           => $this->pet_id,
            'pet_name'         => $this->pet_name,
            'pet'              => new PetResource($this->whenLoaded('pet')),
            'owner'            => $this->whenLoaded('pet', fn() => $this->pet->relationLoaded('owner') && $this->pet->owner ? new UserResource($this->pet->owner) : null),
            'vet_id'           => $this->vet_id,
            'vet_name'         => $this->vet_name,
            'veterinarian'     => $this->whenLoaded('veterinarian', fn() => [
                'vet_id' => $this->veterinarian->vet_id,
                'name' => $this->veterinarian->name,
                'profile_image_url' => $this->veterinarian->profile_image_url,
            ]),
            'reason'           => $this->reason,
            'appointment_date' => $this->appointment_date?->toIso8601String(),
            'time_slot'        => $this->time_slot?->toIso8601String(),
            'status'                => $this->status,
            'amount'                => $this->amount,
            'payment_intent_id'     => $this->payment_intent_id,
            'payment_status'        => $this->payment_status,
            'consultation_type'     => $this->consultation_type,
            'video_call_channel'    => $this->video_call_channel,
            'video_call_status'     => $this->video_call_status,
            'video_call_started_at' => $this->video_call_started_at?->toIso8601String(),
            'video_call_ended_at'   => $this->video_call_ended_at?->toIso8601String(),
            'medical_record'        => $this->whenLoaded('medicalRecord', fn() => new MedicalRecordResource($this->medicalRecord)),
            'created_at'            => $this->created_at?->toIso8601String(),
            'updated_at'            => $this->updated_at?->toIso8601String(),
        ];
    }
}
