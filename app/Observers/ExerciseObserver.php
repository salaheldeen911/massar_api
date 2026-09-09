<?php

namespace App\Observers;

use App\Models\Exercise;
use Illuminate\Validation\ValidationException;

class ExerciseObserver
{
    /**
     * Handle the Exercise "deleting" event.
     * Prevents deletion of default system-wide exercises.
     *
     * @throws ValidationException
     */
    public function deleting(Exercise $exercise): void
    {
        if ($exercise->is_system || $exercise->center_id === null) {
            throw ValidationException::withMessages([
                'exercise' => ['Default system exercises are protected and cannot be deleted.'],
            ]);
        }
    }
}
