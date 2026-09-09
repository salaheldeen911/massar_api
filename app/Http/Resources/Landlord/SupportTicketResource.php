<?php

namespace App\Http\Resources\Landlord;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupportTicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'center_id' => $this->center_id,
            'center' => $this->center ? [
                'id' => $this->center->id,
                'name' => $this->center->name,
                'phone' => $this->center->phone,
                'city' => $this->center->city,
                'logo_url' => $this->center->getFirstMediaUrl('logo') ?: null,
            ] : null,
            'sender_user' => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'phone' => $this->user->phone,
                'email' => $this->user->email,
            ] : null,
            'subject' => $this->subject,
            'message' => $this->message,
            'reply' => $this->reply,
            'replied_at' => $this->replied_at?->toISOString(),
            'replied_by' => $this->repliedByUser ? [
                'id' => $this->repliedByUser->id,
                'name' => $this->repliedByUser->name,
            ] : null,
            'status' => $this->status,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
