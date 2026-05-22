<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HealthJournalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'pet_id'       => $this->pet_id,
            'date'         => $this->date?->toIso8601String(),
            'symptom_tags' => $this->symptom_tags ?? [],
            'notes'        => $this->notes,
            'photo_url'    => $this->photo_url,
            'created_at'   => $this->created_at?->toIso8601String(),
            'updated_at'   => $this->updated_at?->toIso8601String(),
        ];
    }
}
