<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'center_id' => $this->center_id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'status' => $this->status,
            'roles' => $this->getRoleNames(),
            'avatar_url' => $this->hasMedia('avatar') ? $this->getFirstMediaUrl('avatar') : null,
            'therapist_name' => $this->when($this->hasRole('patient'), fn () => $this->patientProfile?->therapist?->name),
            'center' => new CenterResource($this->whenLoaded('center')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
