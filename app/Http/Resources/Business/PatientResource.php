<?php

namespace App\Http\Resources\Business;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $profile = $this->patientProfile;
        $age = $profile?->birth_date ? $profile->birth_date->age : null;
        $currentWeek = $profile?->current_week ?? 1;

        return [
            'id' => $this->id,
            'patient_profile_id' => $profile?->id,
            'center_id' => $this->center_id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'age' => $age,
            'birth_date' => $profile?->birth_date?->format('Y-m-d'),
            'current_week' => $currentWeek,
            'no_of_weeks' => $currentWeek,
            'status' => $this->status,
            'assigned_therapist' => $profile?->therapist ? [
                'id' => $profile->therapist->id,
                'name' => $profile->therapist->name,
                'phone' => $profile->therapist->phone,
                'email' => $profile->therapist->email,
            ] : null,
            'patient_history' => $profile?->patient_history,
            'chief_complain' => $profile?->chief_complain,
            'diagnosis_id' => $profile?->diagnosis_id,
            'diagnosis' => $profile?->diagnosis,
            'diagnosis_info' => $profile?->diagnosisModel ? new \App\Http\Resources\DiagnosisResource($profile->diagnosisModel) : null,
            'special_tests_notes' => $profile?->special_tests_notes,
            'objective_findings' => $profile?->objective_findings,
            'avatar_url' => $this->getFirstMediaUrl('avatar') ?: null,
            'special_tests_file_url' => $profile?->getFirstMediaUrl('special_tests') ?: null,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
