<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\DashboardRequest;
use App\Http\Resources\Business\DashboardResource;
use App\Services\Business\DashboardService;
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
            'Center admin dashboard data retrieved successfully.'
        );
    }
}
