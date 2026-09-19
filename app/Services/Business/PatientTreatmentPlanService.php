<?php

namespace App\Services\Business;

use App\Models\TreatmentPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class PatientTreatmentPlanService
{
    public function storeTreatmentPlan(User $patient, array $data): TreatmentPlan
    {
        $this->ensurePatientBelongsToCurrentCenter($patient);
        $this->ensureUserCanManagePatientPlans($patient);
        $this->ensureNoExistingTreatmentPlan($patient);

        $currentUser = currentUser();

        return DB::transaction(function () use ($patient, $data, $currentUser) {
            return TreatmentPlan::create([
                'patient_id' => $patient->id,
                'therapist_id' => $currentUser?->id,
                'manual_therapy' => $data['manual_therapy'] ?? null,
                'electrotherapy' => $data['electrotherapy'] ?? null,
                'medications' => $data['medications'] ?? null,
                'goals' => $data['goals'] ?? null,
            ]);
        });
    }

    public function updateTreatmentPlan(User $patient, array $data): TreatmentPlan
    {
        $this->ensurePatientBelongsToCurrentCenter($patient);
        $this->ensureUserCanManagePatientPlans($patient);

        $treatmentPlan = $this->getExistingTreatmentPlan($patient);
        $currentUser = currentUser();

        return DB::transaction(function () use ($treatmentPlan, $data, $currentUser) {
            $treatmentPlan->update([
                'therapist_id' => $currentUser?->id,
                'manual_therapy' => array_key_exists('manual_therapy', $data) ? $data['manual_therapy'] : $treatmentPlan->manual_therapy,
                'electrotherapy' => array_key_exists('electrotherapy', $data) ? $data['electrotherapy'] : $treatmentPlan->electrotherapy,
                'medications' => array_key_exists('medications', $data) ? $data['medications'] : $treatmentPlan->medications,
                'goals' => array_key_exists('goals', $data) ? $data['goals'] : $treatmentPlan->goals,
            ]);

            return $treatmentPlan->fresh();
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

    private function ensureNoExistingTreatmentPlan(User $patient): void
    {
        if ($patient->treatmentPlan()->exists()) {
            throw ValidationException::withMessages([
                'treatment_plan' => ['A treatment plan already exists for this patient. Please update the existing plan instead.'],
            ]);
        }
    }

    private function getExistingTreatmentPlan(User $patient): TreatmentPlan
    {
        $treatmentPlan = $patient->treatmentPlan;
        if (! $treatmentPlan) {
            throw ValidationException::withMessages([
                'treatment_plan' => ['No treatment plan found for this patient. Please create one first.'],
            ]);
        }

        return $treatmentPlan;
    }
}
