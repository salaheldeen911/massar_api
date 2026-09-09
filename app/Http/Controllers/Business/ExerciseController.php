<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\AssignExerciseRequest;
use App\Http\Requests\Business\StoreExerciseRequest;
use App\Http\Resources\Business\ExerciseResource;
use App\Http\Resources\Business\PatientExerciseResource;
use App\Models\Exercise;
use App\Models\PatientExercise;
use App\Models\User;
use App\Services\Business\ExerciseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExerciseController extends Controller
{
    public function __construct(
        protected ExerciseService $exerciseService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $exercises = $this->exerciseService->listLibraryExercises($request->all());

        return $this->success(
            ExerciseResource::collection($exercises),
            'Exercise library retrieved successfully.'
        );
    }

    public function store(StoreExerciseRequest $request): JsonResponse
    {
        $exercise = $this->exerciseService->createExercise($request->validated());

        return $this->success(
            new ExerciseResource($exercise),
            'Exercise created successfully in library.',
            201
        );
    }

    public function show(Exercise $exercise): JsonResponse
    {
        return $this->success(
            new ExerciseResource($exercise->load(['media', 'therapist'])),
            'Exercise details retrieved successfully.'
        );
    }

    public function assign(AssignExerciseRequest $request, User $patient): JsonResponse
    {
        $patientExercise = $this->exerciseService->assignExerciseToPatient($patient, $request->validated());

        return $this->success(
            new PatientExerciseResource($patientExercise),
            'Exercise assigned to patient successfully.',
            201
        );
    }

    public function unassign(User $patient, PatientExercise $patientExercise): JsonResponse
    {
        $this->exerciseService->unassignExerciseFromPatient($patient, $patientExercise);

        return $this->success(
            null,
            'Exercise unassigned from patient successfully.'
        );
    }
}
