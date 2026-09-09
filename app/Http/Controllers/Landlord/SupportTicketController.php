<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Http\Requests\Landlord\ReplySupportTicketRequest;
use App\Http\Resources\Landlord\SupportTicketResource;
use App\Models\SupportTicket;
use App\Services\Landlord\LandlordSupportTicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function __construct(
        protected LandlordSupportTicketService $supportTicketService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tickets = $this->supportTicketService->listAllTickets($request->all());

        return $this->success(
            SupportTicketResource::collection($tickets),
            'All support tickets retrieved successfully.'
        );
    }

    public function show(SupportTicket $supportTicket): JsonResponse
    {
        $ticket = $this->supportTicketService->getTicketDetails($supportTicket);

        return $this->success(
            new SupportTicketResource($ticket),
            'Support ticket details retrieved successfully.'
        );
    }

    public function reply(ReplySupportTicketRequest $request, SupportTicket $supportTicket): JsonResponse
    {
        $ticket = $this->supportTicketService->replyToTicket($supportTicket, $request->validated());

        return $this->success(
            new SupportTicketResource($ticket),
            'Reply sent successfully to support ticket.'
        );
    }
}
