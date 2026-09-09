<?php

namespace App\Http\Resources\Business;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'role' => $this['role'] ?? null,
            'stats' => $this['stats'] ?? [],
            'recent_patients' => $this['recent_patients'] ?? [],
        ];

        if (isset($this['recent_therapists'])) {
            $data['recent_therapists'] = $this['recent_therapists'];
        }

        return $data;
    }
}
