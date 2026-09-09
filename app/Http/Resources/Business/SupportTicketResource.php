<?php

namespace App\Http\Resources\Business;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupportTicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'center_id' => $this->center_id,
            'user_id' => $this->user_id,
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
