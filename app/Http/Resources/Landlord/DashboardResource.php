<?php

namespace App\Http\Resources\Landlord;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'stats' => $this['stats'] ?? [],
            'quick_actions' => $this['quick_actions'] ?? [],
            'recent_activities' => $this['recent_activities'] ?? [],
            'recent_centers' => $this['recent_centers'] ?? [],
        ];
    }
}
