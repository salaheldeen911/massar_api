<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\StoreNutritionPlanRequest;
use App\Http\Requests\Business\StoreTreatmentPlanRequest;
use App\Models\User;
use App\Services\Business\PatientManagementService;
use Illuminate\Http\JsonResponse;

class PatientPlanController extends Controller
{
    public function __construct(
        protected PatientManagementService $patientService
    ) {}

    public function storeTreatmentPlan(StoreTreatmentPlanRequest $request, User $patient): JsonResponse
    {
        $treatmentPlan = $this->patientService->storeTreatmentPlan($patient, $request->validated());

        return $this->success(
            $treatmentPlan,
            'Treatment plan saved successfully.',
            201
        );
    }

    public function storeNutritionPlan(StoreNutritionPlanRequest $request, User $patient): JsonResponse
    {
        $nutritionPlan = $this->patientService->storeNutritionPlan($patient, $request->validated());

        return $this->success(
            $nutritionPlan,
            'Nutrition program saved successfully.',
            201
        );
    }
}
