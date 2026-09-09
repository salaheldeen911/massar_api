<?php

namespace App\Http\Resources\Landlord;

use App\Http\Resources\CenterResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
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
            'user_id' => $this->user_id,
            'name' => $this->user?->name,
            'email' => $this->user?->email,
            'phone' => $this->user?->phone,
            'status' => $this->user?->status,
            'avatar_url' => $this->user?->hasMedia('avatar') ? $this->user->getFirstMediaUrl('avatar') : null,
            'birth_date' => $this->birth_date?->toDateString(),
            'age' => $this->birth_date ? $this->birth_date->age : null,
            'current_week' => (int) $this->current_week,
            'patient_history' => $this->patient_history,
            'chief_complain' => $this->chief_complain,
            'diagnosis' => $this->diagnosis,
            'special_tests_notes' => $this->special_tests_notes,
            'objective_findings' => $this->objective_findings,
            'center' => new CenterResource($this->whenLoaded('center')),
            'therapist' => $this->whenLoaded('therapist', fn () => [
                'id' => $this->therapist->id,
                'name' => $this->therapist->name,
                'email' => $this->therapist->email,
                'phone' => $this->therapist->phone,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
