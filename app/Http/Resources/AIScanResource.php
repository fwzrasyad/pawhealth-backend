<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AIScanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'scan_id'          => $this->scan_id,
            'pet_id'           => $this->pet_id,
            'scan_date'        => $this->scan_date?->toIso8601String(),
            'image_url'        => $this->image_url,
            'ai_result_label'  => $this->ai_result_label,
            'confidence_score' => $this->confidence_score,
            'created_at'       => $this->created_at?->toIso8601String(),
            'updated_at'       => $this->updated_at?->toIso8601String(),
        ];
    }
}
