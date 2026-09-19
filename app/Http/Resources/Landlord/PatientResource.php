<?php

namespace App\Http\Resources\Landlord;

use App\Http\Resources\Business\PatientExerciseResource;
use App\Http\Resources\CenterResource;
use App\Http\Resources\DiagnosisResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $this->user;
        $treatmentPlan = $user?->relationLoaded('treatmentPlan') ? $user->treatmentPlan : null;
        $nutritionPlan = $user?->relationLoaded('nutritionPlan') ? $user->nutritionPlan : null;
        $assignedExercises = $user?->relationLoaded('assignedExercises') ? $user->assignedExercises : null;

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->user?->name,
            'email' => $this->user?->email,
            'phone' => $this->user?->phone,
            'status' => $this->user?->status,
            'avatar_url' => $this->user?->hasMedia('avatar') ? $this->user->getFirstMediaUrl('avatar') : null,
            'birth_date' => $this->birth_date?->toDateString(),
            'age' => $this->birth_date ? $this->birth_date->age : null,
            'current_week' => (int) $this->current_week,
            'patient_history' => $this->patient_history,
            'chief_complain' => $this->chief_complain,
            'diagnosis_id' => $this->diagnosis_id,
            'diagnosis' => $this->diagnosis,
            'diagnosis_info' => $this->whenLoaded('diagnosisModel', fn () => new DiagnosisResource($this->diagnosisModel)),
            'special_tests_notes' => $this->special_tests_notes,
            'objective_findings' => $this->objective_findings,
            'special_tests_file_url' => $this->hasMedia('special_tests') ? $this->getFirstMediaUrl('special_tests') : null,
            'center' => new CenterResource($this->whenLoaded('center')),
            'therapist' => $this->whenLoaded('therapist', fn () => [
                'id' => $this->therapist->id,
                'name' => $this->therapist->name,
                'email' => $this->therapist->email,
                'phone' => $this->therapist->phone,
            ]),
            'treatment_plan' => $treatmentPlan ? [
                'id' => $treatmentPlan->id,
                'manual_therapy' => $treatmentPlan->manual_therapy,
                'manual_therapy_date' => $treatmentPlan->manual_therapy_date?->toDateString(),
                'electrotherapy' => $treatmentPlan->electrotherapy,
                'electrotherapy_date' => $treatmentPlan->electrotherapy_date?->toDateString(),
                'medications' => $treatmentPlan->medications,
                'goals' => $treatmentPlan->goals,
                'updated_at' => $treatmentPlan->updated_at?->toIso8601String(),
            ] : null,
            'nutrition_program' => $nutritionPlan ? [
                'id' => $nutritionPlan->id,
                'breakfast' => $nutritionPlan->breakfast,
                'lunch' => $nutritionPlan->lunch,
                'dinner' => $nutritionPlan->dinner,
                'snacks' => $nutritionPlan->snacks,
                'supplements' => $nutritionPlan->supplements,
                'foods_to_avoid' => $nutritionPlan->foods_to_avoid,
                'updated_at' => $nutritionPlan->updated_at?->toIso8601String(),
            ] : null,
            'assigned_exercises' => $assignedExercises ? PatientExerciseResource::collection($assignedExercises) : [],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

