<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyRoutineLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'pet_id'         => $this->pet_id,
            'date'           => $this->date?->toIso8601String(),
            'weight'         => $this->weight,
            'diet_notes'     => $this->diet_notes,
            'activity_level' => $this->activity_level,
            'created_at'     => $this->created_at?->toIso8601String(),
            'updated_at'     => $this->updated_at?->toIso8601String(),
        ];
    }
}
