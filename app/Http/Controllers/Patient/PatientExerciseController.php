<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\LogExerciseProgressRequest;
use App\Http\Resources\Patient\ExerciseProgressOverviewResource;
use App\Http\Resources\Patient\PatientExerciseResource;
use App\Models\PatientExercise;
use App\Services\Patient\PatientExerciseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientExerciseController extends Controller
{
    public function __construct(
        protected PatientExerciseService $exerciseService
    ) {}

    /**
     * Display exercises program list and weekly progress overview.
     */
    public function index(Request $request): JsonResponse
    {
        $patientUser = $request->user();
        $exercises = $this->exerciseService->getAssignedExercises($patientUser);

        return $this->success(
            new ExerciseProgressOverviewResource(['exercises' => $exercises]),
            'Patient exercise program retrieved successfully.'
        );
    }

    /**
     * Log exercise progress (Count repeat / Set execution) for today.
     */
    public function log(LogExerciseProgressRequest $request, PatientExercise $patientExercise): JsonResponse
    {
        $patientUser = $request->user();
        $updatedExercise = $this->exerciseService->logProgress(
            $patientExercise,
            $request->validated(),
            $patientUser
        );

        return $this->success(
            new PatientExerciseResource($updatedExercise),
            'Exercise progress logged successfully.'
        );
    }

    /**
     * Mark exercise as 100% completed for today.
     */
    public function complete(Request $request, PatientExercise $patientExercise): JsonResponse
    {
        $patientUser = $request->user();
        $completedExercise = $this->exerciseService->completeExercise(
            $patientExercise,
            $patientUser
        );

        return $this->success(
            new PatientExerciseResource($completedExercise),
            'Exercise marked as completed.'
        );
    }
}
