<?php

namespace App\Services\Landlord;

use App\Models\Exercise;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ExerciseManagementService
{
    /**
     * Get paginated exercises for Landlord with center, therapist, and global filters.
     */
    public function listPaginatedExercises(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Exercise::withoutGlobalScope('center_scope')
            ->with(['center', 'therapist', 'media']);

        $this->applyLandlordFilters($query, $filters);

        return $query->latest('created_at')->paginate($perPage);
    }

    /**
     * Get detailed information for a single exercise.
     */
    public function getExerciseDetails(Exercise $exercise): Exercise
    {
        return $exercise->load(['center', 'therapist', 'media']);
    }

    /**
     * Create a new exercise (Global System, Center Public, or Therapist Private).
     */
    public function createExercise(array $data): Exercise
    {
        return DB::transaction(function () use ($data) {
            $exercise = Exercise::create([
                'center_id' => $data['center_id'] ?? null,
                'therapist_id' => $data['therapist_id'] ?? null,
                'title' => $data['title'],
                'default_sets' => $data['default_sets'] ?? 3,
                'default_repeats' => $data['default_repeats'] ?? 12,
                'default_duration' => $data['default_duration'] ?? 90,
                'therapist_notes' => $data['therapist_notes'] ?? null,
            ]);

            $mediaFile = $data['video'] ?? $data['exercise_media'] ?? $data['media'] ?? $data['file'] ?? null;
            if ($mediaFile && $mediaFile instanceof UploadedFile) {
                $exercise->addMedia($mediaFile)->toMediaCollection('exercise_media');
            }

            return $exercise->load(['center', 'therapist', 'media']);
        });
    }

    /**
     * Update an existing exercise regardless of type or owner.
     */
    public function updateExercise(Exercise $exercise, array $data): Exercise
    {
        return DB::transaction(function () use ($exercise, $data) {
            $updateData = [];

            if (isset($data['title'])) {
                $updateData['title'] = $data['title'];
            }
            if (array_key_exists('center_id', $data)) {
                $updateData['center_id'] = $data['center_id'];
            }
            if (array_key_exists('therapist_id', $data)) {
                $updateData['therapist_id'] = $data['therapist_id'];
            }
            if (isset($data['default_sets'])) {
                $updateData['default_sets'] = $data['default_sets'];
            }
            if (isset($data['default_repeats'])) {
                $updateData['default_repeats'] = $data['default_repeats'];
            }
            if (isset($data['default_duration'])) {
                $updateData['default_duration'] = $data['default_duration'];
            }
            if (array_key_exists('therapist_notes', $data)) {
                $updateData['therapist_notes'] = $data['therapist_notes'];
            }

            if (! empty($updateData)) {
                $exercise->update($updateData);
            }

            $mediaFile = $data['video'] ?? $data['exercise_media'] ?? $data['media'] ?? $data['file'] ?? null;
            if ($mediaFile && $mediaFile instanceof UploadedFile) {
                $exercise->clearMediaCollection('exercise_media');
                $exercise->addMedia($mediaFile)->toMediaCollection('exercise_media');
            }

            return $exercise->fresh(['center', 'therapist', 'media']);
        });
    }

    /**
     * Delete an exercise regardless of type or owner.
     */
    public function deleteExercise(Exercise $exercise): void
    {
        DB::transaction(function () use ($exercise) {
            $exercise->clearMediaCollection('exercise_media');
            $exercise->delete();
        });
    }

    /**
     * Private Helper: Apply landlord exercise scoping and search filters.
     */
    private function applyLandlordFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['center_id'])) {
            $query->where('center_id', $filters['center_id']);

            if (! empty($filters['therapist_id'])) {
                $query->where('therapist_id', $filters['therapist_id']);
            }
        } elseif (! empty($filters['therapist_id'])) {
            $query->where('therapist_id', $filters['therapist_id']);
        } elseif (array_key_exists('is_global', $filters) && $filters['is_global'] !== null) {
            if (filter_var($filters['is_global'], FILTER_VALIDATE_BOOLEAN)) {
                $query->whereNull('center_id');
            } else {
                $query->whereNotNull('center_id');
            }
        } else {
            // Default when no center_id or therapist_id filter is passed: Return global system exercises
            $query->whereNull('center_id');
        }

        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where('title', 'like', "%{$search}%");
        }
    }
}
