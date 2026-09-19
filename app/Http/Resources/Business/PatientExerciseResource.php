<?php

namespace App\Http\Resources\Business;

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
            'patient_id' => $this->patient_id,
            'exercise_id' => $this->exercise_id,
            'title' => $exercise?->title,
            'sets' => $this->sets ?? $exercise?->default_sets,
            'repeats' => $this->repeats ?? $exercise?->default_repeats,
            'duration' => $this->duration ?? $exercise?->default_duration,
            'notes' => $this->notes ?? $exercise?->therapist_notes,
            'status' => $this->status ?? 'pending',
            'today_progress' => [
                'completed_sets' => $latestLog?->completed_sets ?? 0,
                'completed_repeats' => $latestLog?->completed_repeats ?? 0,
                'duration_spent' => $latestLog?->duration_spent ?? 0,
                'is_completed' => (bool) ($latestLog?->is_completed ?? ($this->status === 'completed')),
                'logged_at' => $latestLog?->logged_at?->toDateString(),
            ],
            'sort_order' => $this->sort_order ?? 0,
            'video_url' => $exercise?->getFirstMediaUrl('exercise_media') ?: null,
            'assigned_by' => $this->assignedBy ? [
                'id' => $this->assignedBy->id,
                'name' => $this->assignedBy->name,
            ] : null,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
