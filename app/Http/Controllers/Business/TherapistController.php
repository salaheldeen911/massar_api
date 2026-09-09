<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\GetTherapistsRequest;
use App\Http\Requests\Business\StoreTherapistRequest;
use App\Http\Requests\Business\UpdateTherapistRequest;
use App\Http\Resources\Business\TherapistResource;
use App\Models\User;
use App\Services\Business\TherapistManagementService;
use Illuminate\Http\JsonResponse;

class TherapistController extends Controller
{
    public function __construct(
        protected TherapistManagementService $therapistService
    ) {}

    public function index(GetTherapistsRequest $request): JsonResponse
    {
        $therapists = $this->therapistService->listTherapists($request->validated());

        return $this->success(
            TherapistResource::collection($therapists),
            'Therapist list retrieved successfully.'
        );
    }

    public function store(StoreTherapistRequest $request): JsonResponse
    {
        $therapist = $this->therapistService->createTherapist($request->validated());

        return $this->success(
            new TherapistResource($therapist),
            'Therapist created successfully.',
            201
        );
    }

    public function show(User $therapist): JsonResponse
    {
        $therapist = $this->therapistService->getTherapistDetails($therapist);

        return $this->success(
            new TherapistResource($therapist),
            'Therapist details retrieved successfully.'
        );
    }

    public function update(UpdateTherapistRequest $request, User $therapist): JsonResponse
    {
        $therapist = $this->therapistService->updateTherapist($therapist, $request->validated());

        return $this->success(
            new TherapistResource($therapist),
            'Therapist updated successfully.'
        );
    }

    public function destroy(User $therapist): JsonResponse
    {
        $this->therapistService->deleteTherapist($therapist);

        return $this->success(
            null,
            'Therapist deleted successfully.'
        );
    }
}
