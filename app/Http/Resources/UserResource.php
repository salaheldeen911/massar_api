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
            'username' => $this->username,
            'phone' => $this->phone,
            'email' => $this->email,
            'locale' => $this->locale,
            'status' => $this->status,
            'roles' => $this->getRoleNames(),
            'avatar_url' => $this->hasMedia('avatar') ? $this->getFirstMediaUrl('avatar') : null,
            'center' => new CenterResource($this->whenLoaded('center')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
