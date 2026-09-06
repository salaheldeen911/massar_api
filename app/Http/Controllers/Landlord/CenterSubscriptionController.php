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

        return response()->json([
            'data' => CenterResource::collection($centers),
            'meta' => [
                'current_page' => $centers->currentPage(),
                'last_page' => $centers->lastPage(),
                'per_page' => $centers->perPage(),
                'total' => $centers->total(),
            ],
        ]);
    }

    /**
     * Approve center application.
     */
    public function approve(Center $center): JsonResponse
    {
        $approvedCenter = $this->subscriptionService->approveCenter($center);

        return response()->json([
            'message' => 'Center subscription approved and activated successfully.',
            'center' => new CenterResource($approvedCenter),
        ]);
    }

    /**
     * Reject center application.
     */
    public function reject(RejectCenterRequest $request, Center $center): JsonResponse
    {
        $rejectedCenter = $this->subscriptionService->rejectCenter($center, $request->input('reason'));

        return response()->json([
            'message' => 'Center subscription request rejected.',
            'center' => new CenterResource($rejectedCenter),
        ]);
    }
}
