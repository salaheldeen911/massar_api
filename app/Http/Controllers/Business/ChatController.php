<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\SendMessageRequest;
use App\Http\Resources\ChatMessageResource;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(
        protected ChatService $chatService
    ) {}

    /**
     * Get list of conversations with assigned patients.
     */
    public function conversations(): JsonResponse
    {
        $conversations = $this->chatService->getConversations(currentUser());

        return $this->success($conversations, 'Conversations retrieved successfully.');
    }

    /**
     * Get message history with a specific patient.
     */
    public function messages(Request $request, User $patient): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 20);
        $paginator = $this->chatService->getMessageHistory(currentUser(), $patient->id, $perPage);

        return $this->success(ChatMessageResource::collection($paginator), 'Messages retrieved successfully.');
    }

    /**
     * Send a message to a patient.
     */
    public function store(SendMessageRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $attachment = $request->file('attachment');

        $message = $this->chatService->sendMessage(
            currentUser(),
            (int) $validated['receiver_id'],
            $validated['message'] ?? null,
            $attachment
        );

        return $this->success(new ChatMessageResource($message), 'Message sent successfully.', 201);
    }

    /**
     * Mark conversation with patient as read.
     */
    public function markRead(User $patient): JsonResponse
    {
        $updatedCount = $this->chatService->markAsRead(currentUser(), $patient->id);

        return $this->success(['updated_count' => $updatedCount], 'Messages marked as read successfully.');
    }
}
