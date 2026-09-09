<?php

namespace App\Http\Resources\Patient;

use App\Http\Resources\DiagnosisResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientTreatmentPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $profile = $this->patientProfile;
        $latestTreatment = $this->treatmentPlans->first();

        return [
            'header_info' => [
                'name' => $this->name,
                'current_week' => $profile?->current_week ?? 1,
                'no_of_weeks' => $profile?->current_week ?? 1,
                'date' => $this->created_at?->format('d/m/Y'),
                'therapist_name' => $profile?->therapist?->name,
                'assigned_therapist' => $profile?->therapist ? [
                    'name' => $profile->therapist->name,
                    'phone' => $profile->therapist->phone,
                    'email' => $profile->therapist->email,
                    'specialization' => $profile->therapist->therapistProfile?->specialization,
                ] : null,
                'patient_history' => $profile?->patient_history,
                'chief_complain' => $profile?->chief_complain,
                'diagnosis_id' => $profile?->diagnosis_id,
                'diagnosis' => $profile?->diagnosis,
                'diagnosis_info' => $profile?->diagnosisModel ? new DiagnosisResource($profile->diagnosisModel) : null,
                'special_tests_notes' => $profile?->special_tests_notes,
                'objective_findings' => $profile?->objective_findings,
                'special_tests_file_url' => $profile?->getFirstMediaUrl('special_tests') ?: null,
            ],
            'treatment_plan' => $latestTreatment ? [
                'id' => $latestTreatment->id,
                'medications' => $latestTreatment->medications,
                'manual_therapy' => $latestTreatment->manual_therapy,
                'electrotherapy' => $latestTreatment->electrotherapy,
                'goals' => $latestTreatment->goals,
                'updated_at' => $latestTreatment->updated_at?->toISOString(),
            ] : null,
        ];
    }
}
