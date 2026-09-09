<?php

namespace App\Http\Resources\Business;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientExerciseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $exercise = $this->exercise;

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
            'sort_order' => $this->sort_order ?? 0,
            'video_url' => $exercise?->getFirstMediaUrl('video') ?: null,
            'assigned_by' => $this->assignedBy ? [
                'id' => $this->assignedBy->id,
                'name' => $this->assignedBy->name,
            ] : null,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
