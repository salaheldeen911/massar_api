<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Http\Requests\Landlord\GetTherapistsRequest;
use App\Http\Requests\Landlord\StoreTherapistRequest;
use App\Http\Requests\Landlord\UpdateTherapistRequest;
use App\Http\Resources\Landlord\TherapistResource;
use App\Models\User;
use App\Services\Landlord\TherapistManagementService;
use Illuminate\Http\JsonResponse;

class TherapistController extends Controller
{
    public function __construct(
        protected TherapistManagementService $therapistService
    ) {}

    /**
     * Display a paginated list of therapists across centers with filtering.
     */
    public function index(GetTherapistsRequest $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);

        $therapists = $this->therapistService->getPaginatedTherapists(
            $request->validated(),
            $perPage
        );

        return $this->success(
            TherapistResource::collection($therapists),
            'Therapists retrieved successfully.'
        );
    }

    /**
     * Store a newly created therapist for a center.
     */
    public function store(StoreTherapistRequest $request): JsonResponse
    {
        $therapist = $this->therapistService->createTherapist(
            $request->validated(),
            $request->file('avatar')
        );

        return $this->success(
            new TherapistResource($therapist),
            'Therapist created successfully.',
            201
        );
    }

    /**
     * Display the specified therapist profile details.
     */
    public function show(User $therapist): JsonResponse
    {
        $detailedTherapist = $this->therapistService->getTherapistDetails($therapist);

        return $this->success(
            new TherapistResource($detailedTherapist),
            'Therapist details retrieved successfully.'
        );
    }

    /**
     * Update the specified therapist profile details.
     */
    public function update(UpdateTherapistRequest $request, User $therapist): JsonResponse
    {
        $updatedTherapist = $this->therapistService->updateTherapist(
            $therapist,
            $request->validated(),
            $request->file('avatar')
        );

        return $this->success(
            new TherapistResource($updatedTherapist),
            'Therapist updated successfully.'
        );
    }

    /**
     * Remove the specified therapist from storage.
     */
    public function destroy(User $therapist): JsonResponse
    {
        $this->therapistService->deleteTherapist($therapist);

        return $this->success(
            null,
            'Therapist deleted successfully.'
        );
    }
}
