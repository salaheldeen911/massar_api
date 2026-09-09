<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Resources\Patient\PatientNutritionPlanResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientNutritionPlanController extends Controller
{
    /**
     * Display the authenticated patient's diet plan / nutrition program.
     */
    public function show(Request $request): JsonResponse
    {
        $patientUser = $request->user()->load([
            'nutritionPlans' => function ($q) {
                $q->latest();
            },
        ]);

        return $this->success(
            new PatientNutritionPlanResource($patientUser),
            'Patient nutrition program retrieved successfully.'
        );
    }
}
