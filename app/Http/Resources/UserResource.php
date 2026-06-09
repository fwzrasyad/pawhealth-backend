<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user_id'           => $this->user_id,
            'name'              => $this->name,
            'email'             => $this->email,
            'role'              => strtolower($this->role),
            'phone_number'      => $this->phone_number,
            'profile_image_url' => $this->profile_image_url,
            'created_at'   => $this->created_at?->toIso8601String(),
            'updated_at'   => $this->updated_at?->toIso8601String(),
            'clinic'       => $this->whenLoaded('clinic', function () {
                return [
                    'clinic_id' => $this->clinic->clinic_id,
                    'name'      => $this->clinic->name,
                    'status'    => $this->clinic->status,
                ];
            }),
        ];
    }
}
