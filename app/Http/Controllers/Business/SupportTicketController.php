<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\StoreSupportTicketRequest;
use App\Http\Resources\Business\SupportTicketResource;
use App\Models\SupportTicket;
use App\Services\Business\SupportTicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function __construct(
        protected SupportTicketService $supportTicketService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tickets = $this->supportTicketService->listTickets($request->all());

        return $this->success(
            SupportTicketResource::collection($tickets),
            'Support tickets retrieved successfully.'
        );
    }

    public function store(StoreSupportTicketRequest $request): JsonResponse
    {
        $ticket = $this->supportTicketService->createTicket($request->validated());

        return $this->success(
            new SupportTicketResource($ticket),
            'Support ticket submitted successfully.',
            201
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
}
