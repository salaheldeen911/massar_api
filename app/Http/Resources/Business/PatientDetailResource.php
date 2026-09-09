<?php

namespace App\Http\Resources\Business;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $profile = $this->patientProfile;
        $latestTreatment = $this->treatmentPlans->first();
        $latestNutrition = $this->nutritionPlans->first();

        return [
            'header_info' => [
                'id' => $this->id,
                'patient_profile_id' => $profile?->id,
                'center_id' => $this->center_id,
                'name' => $this->name,
                'phone' => $this->phone,
                'email' => $this->email,
                'birth_date' => $profile?->birth_date?->format('Y-m-d'),
                'age' => $profile?->birth_date ? $profile->birth_date->age : null,
                'current_week' => $profile?->current_week ?? 1,
                'no_of_weeks' => $profile?->current_week ?? 1,
                'status' => $this->status,
                'assigned_therapist' => $profile?->therapist ? [
                    'id' => $profile->therapist->id,
                    'name' => $profile->therapist->name,
                    'phone' => $profile->therapist->phone,
                    'email' => $profile->therapist->email,
                ] : null,
                'avatar_url' => $this->getFirstMediaUrl('avatar') ?: null,
            ],
            'patient_details' => [
                'patient_history' => $profile?->patient_history,
                'chief_complain' => $profile?->chief_complain,
                'diagnosis_id' => $profile?->diagnosis_id,
                'diagnosis' => $profile?->diagnosis,
                'diagnosis_info' => $profile?->diagnosisModel ? new \App\Http\Resources\DiagnosisResource($profile->diagnosisModel) : null,
                'special_tests_notes' => $profile?->special_tests_notes,
                'objective_findings' => $profile?->objective_findings,
                'special_tests_file_url' => $profile?->getFirstMediaUrl('special_tests') ?: null,
            ],
            'treatment_plan' => $latestTreatment ? [
                'id' => $latestTreatment->id,
                'manual_therapy' => $latestTreatment->manual_therapy,
                'electrotherapy' => $latestTreatment->electrotherapy,
                'medications' => $latestTreatment->medications,
                'goals' => $latestTreatment->goals,
                'updated_at' => $latestTreatment->updated_at?->toISOString(),
            ] : null,
            'nutrition_program' => $latestNutrition ? [
                'id' => $latestNutrition->id,
                'breakfast' => $latestNutrition->breakfast,
                'lunch' => $latestNutrition->lunch,
                'dinner' => $latestNutrition->dinner,
                'snacks' => $latestNutrition->snacks,
                'supplements' => $latestNutrition->supplements,
                'foods_to_avoid' => $latestNutrition->foods_to_avoid,
                'updated_at' => $latestNutrition->updated_at?->toISOString(),
            ] : null,
            'exercises_list' => PatientExerciseResource::collection($this->assignedExercises),
        ];
    }
}
