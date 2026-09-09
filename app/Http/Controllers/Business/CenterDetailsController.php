<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\UpdateCenterDetailsRequest;
use App\Http\Resources\Business\CenterDetailsResource;
use App\Services\Business\CenterDetailsService;
use Illuminate\Http\JsonResponse;

class CenterDetailsController extends Controller
{
    public function __construct(
        protected CenterDetailsService $centerDetailsService
    ) {}

    public function show(): JsonResponse
    {
        $center = $this->centerDetailsService->getCenterDetails();

        return $this->success(
            new CenterDetailsResource($center),
            'Center details retrieved successfully.'
        );
    }

    public function update(UpdateCenterDetailsRequest $request): JsonResponse
    {
        $center = $this->centerDetailsService->updateCenterDetails($request->validated());

        return $this->success(
            new CenterDetailsResource($center),
            'Center details updated successfully.'
        );
    }
}
