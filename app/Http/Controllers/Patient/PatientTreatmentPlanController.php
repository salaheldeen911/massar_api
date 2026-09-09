<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Resources\Patient\PatientTreatmentPlanResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientTreatmentPlanController extends Controller
{
    /**
     * Display the authenticated patient's medical details & treatment plan.
     */
    public function show(Request $request): JsonResponse
    {
        $patientUser = $request->user()->load([
            'patientProfile.therapist.therapistProfile',
            'patientProfile.diagnosisModel',
            'treatmentPlans' => function ($q) {
                $q->latest();
            },
        ]);

        return $this->success(
            new PatientTreatmentPlanResource($patientUser),
            'Patient treatment plan retrieved successfully.'
        );
    }
}
