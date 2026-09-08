<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Http\Requests\Landlord\StoreCenterRequest;
use App\Http\Requests\Landlord\UpdateCenterRequest;
use App\Http\Resources\CenterResource;
use App\Http\Resources\UserResource;
use App\Models\Center;
use App\Services\Landlord\CenterManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CenterController extends Controller
{
    public function __construct(
        protected CenterManagementService $centerService
    ) {}

    /**
     * Display a paginated list of centers with optional filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'subscription_status']);
        $perPage = $request->integer('per_page', 15);

        $centers = $this->centerService->getPaginatedCenters($filters, $perPage);

        return $this->success(
            CenterResource::collection($centers),
            'Centers retrieved successfully.'
        );
    }

    /**
     * Store a newly created center in storage.
     */
    public function store(StoreCenterRequest $request): JsonResponse
    {
        $center = $this->centerService->createCenter(
            $request->validated(),
            $request->file('logo'),
            $request->file('license_document')
        );

        return $this->success(
            new CenterResource($center),
            'Center created successfully.',
            201
        );
    }

    /**
     * Display the specified center details.
     */
    public function show(Center $center): JsonResponse
    {
        $detailedCenter = $this->centerService->getCenterDetails($center);

        return $this->success(
            new CenterResource($detailedCenter),
            'Center details retrieved successfully.'
        );
    }

    /**
     * Update the specified center in storage.
     */
    public function update(UpdateCenterRequest $request, Center $center): JsonResponse
    {
        $updatedCenter = $this->centerService->updateCenter(
            $center,
            $request->validated(),
            $request->file('logo'),
            $request->file('license_document')
        );

        return $this->success(
            new CenterResource($updatedCenter),
            'Center updated successfully.'
        );
    }

    /**
     * Display staff users (admins & therapists) for the specified center.
     */
    public function staff(Center $center): JsonResponse
    {
        $staff = $this->centerService->getCenterStaff($center);

        return $this->success(
            UserResource::collection($staff),
            'Center staff retrieved successfully.'
        );
    }

    /**
     * Remove the specified center from storage.
     */
    public function destroy(Center $center): JsonResponse
    {
        $this->centerService->deleteCenter($center);

        return $this->success(
            null,
            'Center deleted successfully.'
        );
    }
}
