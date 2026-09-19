<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Http\Requests\Landlord\GetExercisesRequest;
use App\Http\Requests\Landlord\StoreExerciseRequest;
use App\Http\Requests\Landlord\UpdateExerciseRequest;
use App\Http\Resources\Landlord\ExerciseResource;
use App\Models\Exercise;
use App\Services\Landlord\ExerciseManagementService;
use Illuminate\Http\JsonResponse;

class ExerciseController extends Controller
{
    public function __construct(
        protected ExerciseManagementService $exerciseService
    ) {}

    /**
     * Display a paginated list of exercises with global, center, and therapist filtering.
     */
    public function index(GetExercisesRequest $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);

        $exercises = $this->exerciseService->listPaginatedExercises(
            $request->validated(),
            $perPage
        );

        return $this->success(
            ExerciseResource::collection($exercises),
            'Exercises retrieved successfully.'
        );
    }

    /**
     * Store a newly created exercise (Global System, Center Public, or Therapist Private).
     */
    public function store(StoreExerciseRequest $request): JsonResponse
    {
        $exercise = $this->exerciseService->createExercise($request->validated());

        return $this->success(
            new ExerciseResource($exercise),
            'Exercise created successfully.',
            201
        );
    }

    /**
     * Display the specified exercise details.
     */
    public function show(Exercise $exercise): JsonResponse
    {
        $detailedExercise = $this->exerciseService->getExerciseDetails($exercise);

        return $this->success(
            new ExerciseResource($detailedExercise),
            'Exercise details retrieved successfully.'
        );
    }

    /**
     * Update the specified exercise.
     */
    public function update(UpdateExerciseRequest $request, Exercise $exercise): JsonResponse
    {
        $updatedExercise = $this->exerciseService->updateExercise(
            $exercise,
            $request->validated()
        );

        return $this->success(
            new ExerciseResource($updatedExercise),
            'Exercise updated successfully.'
        );
    }

    /**
     * Remove the specified exercise from storage.
     */
    public function destroy(Exercise $exercise): JsonResponse
    {
        $this->exerciseService->deleteExercise($exercise);

        return $this->success(
            null,
            'Exercise deleted successfully.'
        );
    }
}
