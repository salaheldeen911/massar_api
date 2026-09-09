<?php

namespace App\Http\Resources\Business;

use App\Models\PatientProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TherapistResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $profile = $this->therapistProfile;
        $assignedPatientsCount = PatientProfile::where('therapist_id', $this->id)->count();

        return [
            'id' => $this->id,
            'therapist_profile_id' => $profile?->id,
            'center_id' => $this->center_id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'status' => $this->status,
            'specialization' => $profile?->specialization,
            'license_no' => $profile?->license_no,
            'bio' => $profile?->bio,
            'assigned_patients_count' => $assignedPatientsCount,
            'no_of_works' => $assignedPatientsCount,
            'avatar_url' => $this->getFirstMediaUrl('avatar') ?: null,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
