<?php

namespace App\Http\Resources\Patient;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientNutritionPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $latestNutrition = $this->nutritionPlans->first();

        return [
            'nutrition_program' => $latestNutrition ? [
                'id' => $latestNutrition->id,
                'breakfast' => $latestNutrition->breakfast,
                'lunch' => $latestNutrition->lunch,
                'dinner' => $latestNutrition->dinner,
                'snacks' => $latestNutrition->snacks,
                'supplements' => $latestNutrition->supplements,
                'foods_to_avoid' => $latestNutrition->foods_to_avoid,
                'updated_at' => $latestNutrition->updated_at?->toISOString(),
            ] : null,
        ];
    }
}
