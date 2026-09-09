<?php

namespace App\Http\Resources\Patient;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientExerciseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $exercise = $this->exercise;
        $latestLog = $this->logs()->whereDate('logged_at', now()->toDateString())->latest('id')->first();

        return [
            'id' => $this->id,
            'exercise_id' => $this->exercise_id,
            'title' => $exercise?->title ?? 'Exercise',
            'video_url' => $exercise?->getFirstMediaUrl('video') ?: null,
            'target_sets' => $this->sets,
            'target_repeats' => $this->repeats,
            'target_duration' => $this->duration,
            'therapist_notes' => $this->notes ?? $exercise?->therapist_notes,
            'status' => $this->status,
            'sort_order' => $this->sort_order,
            'today_progress' => [
                'completed_sets' => $latestLog?->completed_sets ?? 0,
                'completed_repeats' => $latestLog?->completed_repeats ?? 0,
                'duration_spent' => $latestLog?->duration_spent ?? 0,
                'is_completed' => (bool) ($latestLog?->is_completed ?? ($this->status === 'completed')),
                'logged_at' => $latestLog?->logged_at?->toDateString(),
            ],
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
