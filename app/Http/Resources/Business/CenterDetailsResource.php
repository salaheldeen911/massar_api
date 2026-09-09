<?php

namespace App\Http\Resources\Business;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CenterDetailsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'facebook' => $this->facebook,
            'whatsapp' => $this->whatsapp,
            'instagram' => $this->instagram,
            'linkedin' => $this->linkedin,
            'logo_url' => $this->getFirstMediaUrl('logo') ?: null,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
