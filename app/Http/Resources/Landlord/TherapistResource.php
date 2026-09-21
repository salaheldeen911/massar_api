<?php

namespace App\Http\Resources\Landlord;

use App\Http\Resources\CenterResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TherapistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $profile = $this->therapistProfile;
        $assignedPatientsCount = $this->assigned_patients_count ?? $this->assignedPatients()->count();

        return [
            'id' => $this->id,
            'therapist_profile_id' => $profile?->id,
            'center_id' => $this->center_id,
            'center' => new CenterResource($this->whenLoaded('center')),
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'status' => $this->status,
            'role' => 'therapist',
            'specialization' => $profile?->specialization,
            'license_no' => $profile?->license_no,
            'bio' => $profile?->bio,
            'assigned_patients_count' => $assignedPatientsCount,
            'avatar_url' => $this->hasMedia('avatar') ? $this->getFirstMediaUrl('avatar') : null,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
