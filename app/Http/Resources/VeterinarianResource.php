<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VeterinarianResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'vet_id'            => $this->vet_id,
            'name'              => $this->name,
            'profile_image_url' => $this->profile_image_url,
            'working_hours'     => $this->working_hours,
            'specialties'       => $this->specialties ?? [],
            'bio'               => $this->bio,
            'weekly_schedule'   => $this->weekly_schedule ?? (object) [],
            'available_slots'   => $this->whenLoaded('availableSlots', function () {
                return $this->availableSlots
                    ->pluck('slot_datetime')
                    ->map(fn ($dt) => $dt->toIso8601String())
                    ->values()
                    ->all();
            }),
            'created_at'        => $this->created_at?->toIso8601String(),
            'updated_at'        => $this->updated_at?->toIso8601String(),
        ];
    }
}
