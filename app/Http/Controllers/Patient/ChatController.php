<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\SendMessageRequest;
use App\Http\Resources\ChatMessageResource;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(
        protected ChatService $chatService
    ) {}

    /**
     * Get message history with assigned caregiver.
     */
    public function messages(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 20);
        $paginator = $this->chatService->getPatientCaregiverMessageHistory(currentUser(), $perPage);

        return $this->success(ChatMessageResource::collection($paginator), 'Messages retrieved successfully.');
    }

    /**
     * Send a message to assigned caregiver.
     */
    public function store(SendMessageRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $attachment = $request->file('attachment');

        $message = $this->chatService->sendPatientMessage(
            currentUser(),
            isset($validated['receiver_id']) ? (int) $validated['receiver_id'] : null,
            $validated['message'] ?? null,
            $attachment
        );

        return $this->success(new ChatMessageResource($message), 'Message sent successfully.', 201);
    }

    /**
     * Mark caregiver messages as read.
     */
    public function markRead(): JsonResponse
    {
        $patientProfile = currentUser()->patientProfile;

        if (!$patientProfile || !$patientProfile->therapist_id) {
            return $this->failed('No assigned caregiver found.', 404);
        }

        $updatedCount = $this->chatService->markAsRead(currentUser(), $patientProfile->therapist_id);

        return $this->success(['updated_count' => $updatedCount], 'Messages marked as read successfully.');
    }
}
