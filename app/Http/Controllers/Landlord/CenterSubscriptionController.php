<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Http\Requests\Landlord\RejectCenterRequest;
use App\Http\Resources\CenterResource;
use App\Models\Center;
use App\Services\Landlord\CenterSubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CenterSubscriptionController extends Controller
{
    public function __construct(
        protected CenterSubscriptionService $subscriptionService
    ) {}

    /**
     * List pending center registration requests.
     */
    public function pending(Request $request): JsonResponse
    {
        $centers = $this->subscriptionService->getPendingCenters();

        return $this->success(
            CenterResource::collection($centers),
            'Pending center applications retrieved successfully.'
        );
    }

    /**
     * Approve center application.
     */
    public function approve(Center $center): JsonResponse
    {
        $approvedCenter = $this->subscriptionService->approveCenter($center);

        return $this->success(
            new CenterResource($approvedCenter),
            'Center subscription approved and activated successfully.'
        );
    }

    /**
     * Reject center application.
     */
    public function reject(RejectCenterRequest $request, Center $center): JsonResponse
    {
        $rejectedCenter = $this->subscriptionService->rejectCenter($center, $request->input('reason'));

        return $this->success(
            new CenterResource($rejectedCenter),
            'Center subscription request rejected.'
        );
    }
}
