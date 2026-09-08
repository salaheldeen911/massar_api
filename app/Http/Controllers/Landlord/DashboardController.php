<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Http\Requests\Landlord\DashboardRequest;
use App\Http\Resources\Landlord\DashboardResource;
use App\Services\Landlord\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function index(DashboardRequest $request): JsonResponse
    {
        $data = $this->dashboardService->getDashboardData($request->validated());

        return $this->success(
            new DashboardResource($data),
            'Landlord dashboard data retrieved successfully.'
        );
    }
}
