<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'pet_id'            => $this->pet_id,
            'owner_id'          => $this->owner_id,
            'name'              => $this->name,
            'species'           => $this->species,
            'breed'             => $this->breed,
            'age'               => $this->age,
            'gender'            => $this->gender,
            'weight'            => $this->weight,
            'profile_image_url' => $this->profile_image_url,
            'health_journals'   => HealthJournalResource::collection($this->whenLoaded('healthJournals')),
            'vaccinations'      => VaccinationResource::collection($this->whenLoaded('vaccinations')),
            'recovery_plans'    => RecoveryPlanResource::collection($this->whenLoaded('recoveryPlans')),
            'created_at'        => $this->created_at?->toIso8601String(),
            'updated_at'        => $this->updated_at?->toIso8601String(),
        ];
    }
}
