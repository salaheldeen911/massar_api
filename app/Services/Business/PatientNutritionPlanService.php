<?php

namespace App\Services\Business;

use App\Models\NutritionPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class PatientNutritionPlanService
{
    public function storeNutritionPlan(User $patient, array $data): NutritionPlan
    {
        $this->ensurePatientBelongsToCurrentCenter($patient);
        $this->ensureUserCanManagePatientPlans($patient);
        $this->ensureNoExistingNutritionPlan($patient);

        $currentUser = currentUser();

        return DB::transaction(function () use ($patient, $data, $currentUser) {
            return NutritionPlan::create([
                'patient_id' => $patient->id,
                'center_id' => $patient->center_id,
                'therapist_id' => $currentUser?->id,
                'breakfast' => $data['breakfast'] ?? null,
                'lunch' => $data['lunch'] ?? null,
                'dinner' => $data['dinner'] ?? null,
                'snacks' => $data['snacks'] ?? null,
                'supplements' => $data['supplements'] ?? null,
                'foods_to_avoid' => $data['foods_to_avoid'] ?? null,
            ]);
        });
    }

    public function updateNutritionPlan(User $patient, array $data): NutritionPlan
    {
        $this->ensurePatientBelongsToCurrentCenter($patient);
        $this->ensureUserCanManagePatientPlans($patient);

        $nutritionPlan = $this->getExistingNutritionPlan($patient);
        $currentUser = currentUser();

        return DB::transaction(function () use ($nutritionPlan, $data, $currentUser) {
            $nutritionPlan->update([
                'therapist_id' => $currentUser?->id,
                'breakfast' => array_key_exists('breakfast', $data) ? $data['breakfast'] : $nutritionPlan->breakfast,
                'lunch' => array_key_exists('lunch', $data) ? $data['lunch'] : $nutritionPlan->lunch,
                'dinner' => array_key_exists('dinner', $data) ? $data['dinner'] : $nutritionPlan->dinner,
                'snacks' => array_key_exists('snacks', $data) ? $data['snacks'] : $nutritionPlan->snacks,
                'supplements' => array_key_exists('supplements', $data) ? $data['supplements'] : $nutritionPlan->supplements,
                'foods_to_avoid' => array_key_exists('foods_to_avoid', $data) ? $data['foods_to_avoid'] : $nutritionPlan->foods_to_avoid,
            ]);

            return $nutritionPlan->fresh();
        });
    }

    private function ensurePatientBelongsToCurrentCenter(User $patient): void
    {
        $currentCenterId = currentCenterId();
        if ($patient->center_id !== $currentCenterId || ! $patient->hasRole('patient')) {
            throw ValidationException::withMessages([
                'patient' => ['Patient record not found.'],
            ]);
        }
    }

    private function ensureUserCanManagePatientPlans(User $patient): void
    {
        $currentUser = currentUser();

        if (! $currentUser) {
            throw new AccessDeniedHttpException('Unauthenticated.');
        }

        if ($currentUser->hasRole('admin') || isLandlord()) {
            return;
        }

        $assignedTherapistId = $patient->patientProfile?->therapist_id;
        if (! $assignedTherapistId || (int) $assignedTherapistId !== (int) $currentUser->id) {
            throw new AccessDeniedHttpException('Only the assigned therapist or center admin can manage this patient\'s treatment or nutrition plan.');
        }
    }

    private function ensureNoExistingNutritionPlan(User $patient): void
    {
        if ($patient->nutritionPlan()->exists()) {
            throw ValidationException::withMessages([
                'nutrition_plan' => ['A nutrition plan already exists for this patient. Please update the existing plan instead.'],
            ]);
        }
    }

    private function getExistingNutritionPlan(User $patient): NutritionPlan
    {
        $nutritionPlan = $patient->nutritionPlan;
        if (! $nutritionPlan) {
            throw ValidationException::withMessages([
                'nutrition_plan' => ['No nutrition plan found for this patient. Please create one first.'],
            ]);
        }

        return $nutritionPlan;
    }
}
