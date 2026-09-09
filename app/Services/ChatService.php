<?php

namespace App\Services;

use App\Events\MessageSent;
use App\Models\ChatMessage;
use App\Models\PatientProfile;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ChatService
{
    /**
     * Send a message from sender to receiver with optional media attachment.
     */
    public function sendMessage(User $sender, int $receiverId, ?string $message, mixed $file = null): ChatMessage
    {
        $receiver = User::findOrFail($receiverId);

        $this->validateCenterIsolation($sender, $receiver);
        $this->validateAssignmentAuthorization($sender, $receiver);

        return DB::transaction(function () use ($sender, $receiver, $message, $file) {
            $chatMessage = $this->createMessageRecord($sender, $receiver, $message);

            if ($file) {
                $this->attachMediaIfPresent($chatMessage, $file);
            }

            $this->broadcastMessageSentEvent($chatMessage);

            return $chatMessage->fresh(['sender', 'receiver', 'media']);
        });
    }

    /**
     * Get list of chat conversations (assigned patients) for a staff member (Therapist or Admin).
     */
    public function getConversations(User $staffUser): array
    {
        $assignedPatientProfiles = PatientProfile::with(['user'])
            ->where('therapist_id', $staffUser->id)
            ->get();

        $conversations = [];

        foreach ($assignedPatientProfiles as $profile) {
            if (!$profile->user) {
                continue;
            }

            $patientUser = $profile->user;

            $lastMessage = ChatMessage::where(function (Builder $query) use ($staffUser, $patientUser) {
                $query->where('sender_id', $staffUser->id)
                      ->where('receiver_id', $patientUser->id);
            })->orWhere(function (Builder $query) use ($staffUser, $patientUser) {
                $query->where('sender_id', $patientUser->id)
                      ->where('receiver_id', $staffUser->id);
            })->latest('created_at')->latest('id')->first();

            $unreadCount = ChatMessage::where('sender_id', $patientUser->id)
                ->where('receiver_id', $staffUser->id)
                ->whereNull('read_at')
                ->count();

            $conversations[] = $this->formatConversationSummary($patientUser, $lastMessage, $unreadCount);
        }

        usort($conversations, function ($a, $b) {
            $timeA = $a['last_message_at'] ?? '';
            $timeB = $b['last_message_at'] ?? '';
            return strcmp($timeB, $timeA);
        });

        return $conversations;
    }

    /**
     * Get paginated message history between two users, marking incoming unread messages as read.
     */
    public function getMessageHistory(User $user, int $otherUserId, int $perPage = 20): LengthAwarePaginator
    {
        $otherUser = User::findOrFail($otherUserId);

        $this->validateCenterIsolation($user, $otherUser);
        $this->validateAssignmentAuthorization($user, $otherUser);

        $this->markAsRead($user, $otherUserId);

        return ChatMessage::with(['sender', 'receiver', 'media'])
            ->where(function (Builder $query) use ($user, $otherUserId) {
                $query->where('sender_id', $user->id)
                      ->where('receiver_id', $otherUserId);
            })
            ->orWhere(function (Builder $query) use ($user, $otherUserId) {
                $query->where('sender_id', $otherUserId)
                      ->where('receiver_id', $user->id);
            })
            ->latest('created_at')
            ->latest('id')
            ->paginate($perPage);
    }

    /**
     * Get message history for patient with their assigned caregiver (Therapist/Admin).
     */
    public function getPatientCaregiverMessageHistory(User $patientUser, int $perPage = 20): LengthAwarePaginator
    {
        $patientProfile = $patientUser->patientProfile;

        if (!$patientProfile || !$patientProfile->therapist_id) {
            throw new NotFoundHttpException('No assigned caregiver found for this patient.');
        }

        return $this->getMessageHistory($patientUser, $patientProfile->therapist_id, $perPage);
    }

    /**
     * Send message from Patient to their assigned caregiver.
     */
    public function sendPatientMessage(User $patientUser, ?int $receiverId, ?string $message, mixed $file = null): ChatMessage
    {
        $patientProfile = $patientUser->patientProfile;

        if (!$patientProfile || !$patientProfile->therapist_id) {
            throw new AccessDeniedHttpException('Patient has no assigned caregiver to chat with.');
        }

        $targetReceiverId = $receiverId ?? $patientProfile->therapist_id;

        if ((int) $targetReceiverId !== (int) $patientProfile->therapist_id) {
            throw new AccessDeniedHttpException('Patient can only message their assigned caregiver.');
        }

        return $this->sendMessage($patientUser, $targetReceiverId, $message, $file);
    }

    /**
     * Mark all incoming messages from a specific sender as read.
     */
    public function markAsRead(User $receiver, int $senderId): int
    {
        return ChatMessage::where('sender_id', $senderId)
            ->where('receiver_id', $receiver->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Private helper to enforce multi-tenant Center isolation.
     */
    private function validateCenterIsolation(User $user1, User $user2): void
    {
        if (isLandlord()) {
            return;
        }

        if ($user1->center_id !== $user2->center_id) {
            throw new AccessDeniedHttpException('Cross-center communication is not allowed.');
        }
    }

    /**
     * Private helper to enforce caregiver-patient assignment authorization.
     */
    private function validateAssignmentAuthorization(User $sender, User $receiver): void
    {
        if (isLandlord()) {
            return;
        }

        $isSenderPatient = $sender->hasRole('patient');
        $isReceiverPatient = $receiver->hasRole('patient');

        if ($isSenderPatient && $isReceiverPatient) {
            throw new AccessDeniedHttpException('Direct patient-to-patient communication is not allowed.');
        }

        if ($isSenderPatient) {
            $patientProfile = $sender->patientProfile;
            if (!$patientProfile || (int) $patientProfile->therapist_id !== (int) $receiver->id) {
                throw new AccessDeniedHttpException('You are not authorized to message this caregiver.');
            }
        } elseif ($isReceiverPatient) {
            $patientProfile = $receiver->patientProfile;
            if (!$patientProfile || (int) $patientProfile->therapist_id !== (int) $sender->id) {
                throw new AccessDeniedHttpException('You are not authorized to message this patient.');
            }
        } else {
            throw new AccessDeniedHttpException('Direct staff-to-staff messaging is not permitted via this endpoint.');
        }
    }

    /**
     * Private helper to create a ChatMessage Eloquent record.
     */
    private function createMessageRecord(User $sender, User $receiver, ?string $message): ChatMessage
    {
        return ChatMessage::create([
            'center_id' => $sender->center_id,
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'message' => $message,
            'created_at' => now(),
        ]);
    }

    /**
     * Private helper to attach media using Spatie MediaLibrary.
     */
    private function attachMediaIfPresent(ChatMessage $chatMessage, mixed $file): void
    {
        $chatMessage->addMedia($file)->toMediaCollection('attachment');
    }

    /**
     * Private helper to dispatch real-time broadcast event.
     */
    private function broadcastMessageSentEvent(ChatMessage $chatMessage): void
    {
        event(new MessageSent($chatMessage));
    }

    /**
     * Private helper to format conversation summary data array.
     */
    private function formatConversationSummary(User $patientUser, ?ChatMessage $lastMessage, int $unreadCount): array
    {
        return [
            'patient' => [
                'id' => $patientUser->id,
                'name' => $patientUser->name,
                'email' => $patientUser->email,
                'phone' => $patientUser->phone,
                'avatar_url' => $patientUser->hasMedia('avatar') ? $patientUser->getFirstMediaUrl('avatar') : null,
            ],
            'unread_count' => $unreadCount,
            'last_message' => $lastMessage ? $lastMessage->message : null,
            'last_message_has_attachment' => $lastMessage ? $lastMessage->hasMedia('attachment') : false,
            'last_message_at' => $lastMessage?->created_at?->toIso8601String(),
        ];
    }
}
