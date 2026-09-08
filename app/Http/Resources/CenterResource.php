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
            'therapists_count' => $this->therapists_count,
            'branches_count' => $this->branches_count,
            'referral_source' => $this->referral_source,
            'terms_accepted' => (bool) $this->terms_accepted,
            'status' => $this->status,
            'trial_starts_at' => $this->trial_starts_at?->toIso8601String(),
            'trial_ends_at' => $this->trial_ends_at?->toIso8601String(),
            'subscription_status' => $this->subscription_status,
            'facebook' => $this->facebook,
            'whatsapp' => $this->whatsapp,
            'instagram' => $this->instagram,
            'linkedin' => $this->linkedin,
            'logo_url' => $this->hasMedia('logo') ? $this->getFirstMediaUrl('logo') : null,
            'license_document_url' => $this->hasMedia('license_document') ? $this->getFirstMediaUrl('license_document') : null,
            'users' => UserResource::collection($this->whenLoaded('users')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
