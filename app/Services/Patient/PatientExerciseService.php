<?php

namespace App\Services\Patient;

use App\Models\PatientExercise;
use App\Models\PatientExerciseLog;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class PatientExerciseService
{
    /**
     * Retrieve assigned exercises for the given patient.
     */
    public function getAssignedExercises(User $patientUser): Collection
    {
        return PatientExercise::where('patient_id', $patientUser->id)
            ->with(['exercise', 'logs'])
            ->orderBy('sort_order', 'asc')
            ->get();
    }

    /**
     * Log exercise progress (Count Repeat / Sets execution) for today.
     */
    public function logProgress(PatientExercise $patientExercise, array $data, User $patientUser): PatientExercise
    {
        $this->ensureExerciseBelongsToPatient($patientExercise, $patientUser);

        return DB::transaction(function () use ($patientExercise, $data, $patientUser) {
            $today = now()->toDateString();

            $log = PatientExerciseLog::firstOrNew([
                'patient_exercise_id' => $patientExercise->id,
                'patient_id' => $patientUser->id,
                'logged_at' => $today,
            ]);

            $completedSets = isset($data['completed_sets'])
                ? (int) $data['completed_sets']
                : ($log->exists ? $log->completed_sets + 1 : 1);

            $completedRepeats = isset($data['completed_repeats'])
                ? (int) $data['completed_repeats']
                : ($log->exists ? $log->completed_repeats + 1 : 1);

            $durationSpent = isset($data['duration_spent'])
                ? (int) $data['duration_spent']
                : ($log->exists ? $log->duration_spent : $patientExercise->duration);

            $isCompleted = isset($data['is_completed'])
                ? filter_var($data['is_completed'], FILTER_VALIDATE_BOOLEAN)
                : ($completedSets >= $patientExercise->sets);

            $log->completed_sets = $completedSets;
            $log->completed_repeats = $completedRepeats;
            $log->duration_spent = $durationSpent;
            $log->is_completed = $isCompleted;
            $log->save();

            $newStatus = 'pending';
            if ($isCompleted || $completedSets >= $patientExercise->sets) {
                $newStatus = 'completed';
            } elseif ($completedSets > 0 || $completedRepeats > 0) {
                $newStatus = 'in_progress';
            }

            $patientExercise->update(['status' => $newStatus]);

            return $patientExercise->fresh(['exercise', 'logs']);
        });
    }

    /**
     * Mark an assigned exercise as 100% completed for today.
     */
    public function completeExercise(PatientExercise $patientExercise, User $patientUser): PatientExercise
    {
        $this->ensureExerciseBelongsToPatient($patientExercise, $patientUser);

        return DB::transaction(function () use ($patientExercise, $patientUser) {
            $today = now()->toDateString();

            $log = PatientExerciseLog::firstOrNew([
                'patient_exercise_id' => $patientExercise->id,
                'patient_id' => $patientUser->id,
                'logged_at' => $today,
            ]);

            $log->completed_sets = $patientExercise->sets;
            $log->completed_repeats = $patientExercise->repeats;
            $log->duration_spent = $patientExercise->duration;
            $log->is_completed = true;
            $log->save();

            $patientExercise->update(['status' => 'completed']);

            return $patientExercise->fresh(['exercise', 'logs']);
        });
    }

    /**
     * Helper: Ensure the exercise belongs to the authenticated patient.
     */
    private function ensureExerciseBelongsToPatient(PatientExercise $patientExercise, User $patientUser): void
    {
        if ((int) $patientExercise->patient_id !== (int) $patientUser->id) {
            throw new AccessDeniedHttpException('You do not have permission to access this exercise.');
        }
    }
}
