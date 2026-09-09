<?php

namespace App\Http\Resources\Business;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExerciseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'center_id' => $this->center_id,
            'therapist_id' => $this->therapist_id,
            'title' => $this->title,
            'default_sets' => $this->default_sets,
            'default_repeats' => $this->default_repeats,
            'default_duration' => $this->default_duration,
            'therapist_notes' => $this->therapist_notes,
            'is_global' => $this->center_id === null,
            'is_center_public' => $this->center_id !== null && $this->therapist_id === null,
            'is_therapist_private' => $this->therapist_id !== null,
            'therapist' => $this->therapist ? [
                'id' => $this->therapist->id,
                'name' => $this->therapist->name,
            ] : null,
            'video_url' => $this->getFirstMediaUrl('video') ?: null,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
