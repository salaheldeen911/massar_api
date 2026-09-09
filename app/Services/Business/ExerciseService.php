<?php

namespace App\Services\Business;

use App\Models\Exercise;
use App\Models\PatientExercise;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExerciseService
{
    public function listLibraryExercises(array $filters = []): LengthAwarePaginator
    {
        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 10;
        $centerId = currentCenterId();
        $currentUser = currentUser();
        $therapistId = $currentUser?->hasRole('therapist') ? $currentUser->id : null;

        $query = Exercise::withoutGlobalScope('center_scope')
            ->with(['media', 'therapist']);

        $this->applyThreeTierScoping($query, $centerId, $therapistId);
        $this->applyFilters($query, $filters);

        return $query->latest('created_at')->paginate($perPage);
    }

    public function createExercise(array $data): Exercise
    {
        $centerId = currentCenterId();
        $currentUser = currentUser();
        $isCenterPublic = ! empty($data['is_center_public']);
        $therapistId = (! $isCenterPublic && $currentUser?->hasRole('therapist')) ? $currentUser->id : null;

        return DB::transaction(function () use ($data, $centerId, $therapistId) {
            $exercise = Exercise::create([
                'center_id' => $centerId,
                'therapist_id' => $therapistId,
                'title' => $data['title'],
                'default_sets' => $data['default_sets'] ?? 3,
                'default_repeats' => $data['default_repeats'] ?? 12,
                'default_duration' => $data['default_duration'] ?? 90,
                'therapist_notes' => $data['therapist_notes'] ?? null,
            ]);

            if (isset($data['video']) && $data['video'] instanceof \Illuminate\Http\UploadedFile) {
                $exercise->addMedia($data['video'])->toMediaCollection('video');
            }

            return $exercise->load(['media', 'therapist']);
        });
    }

    public function assignExerciseToPatient(User $patient, array $data): PatientExercise
    {
        $this->ensurePatientBelongsToCurrentCenter($patient);

        $exercise = Exercise::withoutGlobalScope('center_scope')->findOrFail($data['exercise_id']);

        return DB::transaction(function () use ($patient, $exercise, $data) {
            $currentUser = currentUser();

            return PatientExercise::create([
                'patient_id' => $patient->id,
                'exercise_id' => $exercise->id,
                'assigned_by' => $currentUser?->id,
                'sets' => $data['sets'] ?? $exercise->default_sets,
                'repeats' => $data['repeats'] ?? $exercise->default_repeats,
                'duration' => $data['duration'] ?? $exercise->default_duration,
                'notes' => $data['notes'] ?? $exercise->therapist_notes,
                'status' => 'pending',
            ])->load(['exercise.media', 'assignedBy']);
        });
    }

    public function unassignExerciseFromPatient(User $patient, PatientExercise $patientExercise): void
    {
        $this->ensurePatientBelongsToCurrentCenter($patient);

        if ($patientExercise->patient_id !== $patient->id) {
            throw ValidationException::withMessages([
                'exercise' => ['Exercise assignment does not belong to this patient.'],
            ]);
        }

        $patientExercise->delete();
    }

    private function applyThreeTierScoping(Builder $query, ?int $centerId, ?int $therapistId): void
    {
        $query->where(function (Builder $q) use ($centerId, $therapistId) {
            // 1. Global Exercises (available system-wide to all centers)
            $q->whereNull('exercises.center_id')
              // 2. Center Public Exercises (available to everyone in the center)
              ->orWhere(function (Builder $q2) use ($centerId) {
                  if ($centerId) {
                      $q2->where('exercises.center_id', $centerId)
                         ->whereNull('exercises.therapist_id');
                  }
              })
              // 3. Therapist Private Exercises (created by therapist in active center)
              ->orWhere(function (Builder $q3) use ($centerId, $therapistId) {
                  if ($centerId && $therapistId) {
                      $q3->where('exercises.center_id', $centerId)
                         ->where('exercises.therapist_id', $therapistId);
                  }
              });
        });
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where('title', 'like', "%{$search}%");
        }
    }

    private function ensurePatientBelongsToCurrentCenter(User $patient): void
    {
        $currentCenterId = currentCenterId();
        if ($patient->center_id !== $currentCenterId || ! $patient->hasRole('patient')) {
            throw ValidationException::withMessages([
                'patient' => ['Patient record not found or does not belong to your center.'],
            ]);
        }
    }
}
