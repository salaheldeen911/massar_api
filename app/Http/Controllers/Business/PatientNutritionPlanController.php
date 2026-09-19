<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\StoreNutritionPlanRequest;
use App\Http\Requests\Business\UpdateNutritionPlanRequest;
use App\Models\User;
use App\Services\Business\PatientNutritionPlanService;
use Illuminate\Http\JsonResponse;

class PatientNutritionPlanController extends Controller
{
    public function __construct(
        protected PatientNutritionPlanService $nutritionPlanService
    ) {}

    public function store(StoreNutritionPlanRequest $request, User $patient): JsonResponse
    {
        $nutritionPlan = $this->nutritionPlanService->storeNutritionPlan($patient, $request->validated());

        return $this->success(
            $nutritionPlan,
            'Nutrition program saved successfully.',
            201
        );
    }

    public function update(UpdateNutritionPlanRequest $request, User $patient): JsonResponse
    {
        $nutritionPlan = $this->nutritionPlanService->updateNutritionPlan($patient, $request->validated());

        return $this->success(
            $nutritionPlan,
            'Nutrition program updated successfully.'
        );
    }
}
