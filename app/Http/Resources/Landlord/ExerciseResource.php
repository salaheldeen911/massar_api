<?php

namespace App\Http\Resources\Landlord;

use App\Http\Resources\CenterResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExerciseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
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
            'center' => new CenterResource($this->whenLoaded('center')),
            'therapist' => $this->whenLoaded('therapist', fn () => [
                'id' => $this->therapist->id,
                'name' => $this->therapist->name,
                'email' => $this->therapist->email,
                'phone' => $this->therapist->phone,
            ]),
            'video_url' => $this->getFirstMediaUrl('exercise_media') ?: null,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
