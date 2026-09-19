<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\StoreTreatmentPlanRequest;
use App\Http\Requests\Business\UpdateTreatmentPlanRequest;
use App\Models\User;
use App\Services\Business\PatientTreatmentPlanService;
use Illuminate\Http\JsonResponse;

class PatientTreatmentPlanController extends Controller
{
    public function __construct(
        protected PatientTreatmentPlanService $treatmentPlanService
    ) {}

    public function store(StoreTreatmentPlanRequest $request, User $patient): JsonResponse
    {
        $treatmentPlan = $this->treatmentPlanService->storeTreatmentPlan($patient, $request->validated());

        return $this->success(
            $treatmentPlan,
            'Treatment plan saved successfully.',
            201
        );
    }

    public function update(UpdateTreatmentPlanRequest $request, User $patient): JsonResponse
    {
        $treatmentPlan = $this->treatmentPlanService->updateTreatmentPlan($patient, $request->validated());

        return $this->success(
            $treatmentPlan,
            'Treatment plan updated successfully.'
        );
    }
}
