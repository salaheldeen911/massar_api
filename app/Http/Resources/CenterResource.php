<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CenterResource extends JsonResource
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
            'name' => $this->name,
            'phone' => $this->phone,
            'specialty' => $this->specialty,
            'country' => $this->country,
            'city' => $this->city,
            'status' => $this->status,
            'trial_starts_at' => $this->trial_starts_at?->toIso8601String(),
            'trial_ends_at' => $this->trial_ends_at?->toIso8601String(),
            'subscription_status' => $this->subscription_status,
            'facebook' => $this->facebook,
            'whatsapp' => $this->whatsapp,
            'instagram' => $this->instagram,
            'linkedin' => $this->linkedin,
            'logo_url' => $this->hasMedia('logo') ? $this->getFirstMediaUrl('logo') : null,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
