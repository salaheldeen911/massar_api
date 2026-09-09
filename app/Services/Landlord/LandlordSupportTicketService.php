<?php

namespace App\Services\Landlord;

use App\Models\SupportTicket;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class LandlordSupportTicketService
{
    public function listAllTickets(array $filters = []): LengthAwarePaginator
    {
        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 10;

        $query = SupportTicket::query()
            ->withoutGlobalScope('center_scope')
            ->with(['center.media', 'user', 'repliedByUser']);

        $this->applyFilters($query, $filters);

        return $query->latest('created_at')->paginate($perPage);
    }

    public function getTicketDetails(SupportTicket $ticket): SupportTicket
    {
        return $ticket->load(['center.media', 'user', 'repliedByUser']);
    }

    public function replyToTicket(SupportTicket $ticket, array $data): SupportTicket
    {
        $currentUser = currentUser();
        $status = $data['status'] ?? 'replied';

        return DB::transaction(function () use ($ticket, $data, $currentUser, $status) {
            $ticket->update([
                'reply' => $data['reply'],
                'replied_by' => $currentUser?->id,
                'replied_at' => now(),
                'status' => $status,
            ]);

            return $ticket->fresh(['center.media', 'user', 'repliedByUser']);
        });
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function (Builder $q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%")
                  ->orWhereHas('center', fn ($cq) => $cq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if (! empty($filters['center_id'])) {
            $query->where('center_id', $filters['center_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }
}
