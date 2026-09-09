<?php

namespace App\Http\Resources\Patient;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExerciseProgressOverviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $exercises = $this['exercises'];
        $totalCount = $exercises->count();
        $completedCount = $exercises->where('status', 'completed')->count();

        $completionPercentage = $totalCount > 0 ? (int) round(($completedCount / $totalCount) * 100) : 0;

        return [
            'header_summary' => [
                'completion_percentage' => $completionPercentage,
                'completed_this_week_count' => $completedCount,
                'total_assigned_count' => $totalCount,
            ],
            'exercises_list' => PatientExerciseResource::collection($exercises),
        ];
    }
}
