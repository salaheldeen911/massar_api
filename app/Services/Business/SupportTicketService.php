<?php

namespace App\Services\Business;

use App\Models\SupportTicket;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SupportTicketService
{
    public function listTickets(array $filters = []): LengthAwarePaginator
    {
        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 10;
        $centerId = currentCenterId();

        $query = SupportTicket::query()
            ->where('center_id', $centerId)
            ->with(['repliedByUser']);

        $this->applyFilters($query, $filters);

        return $query->latest('created_at')->paginate($perPage);
    }

    public function createTicket(array $data): SupportTicket
    {
        $centerId = currentCenterId();
        $user = currentUser();

        return DB::transaction(function () use ($data, $centerId, $user) {
            return SupportTicket::create([
                'center_id' => $centerId,
                'user_id' => $user?->id,
                'subject' => $data['subject'],
                'message' => $data['message'],
                'status' => 'open',
            ]);
        });
    }

    public function getTicketDetails(SupportTicket $ticket): SupportTicket
    {
        $this->ensureTicketBelongsToCenter($ticket);

        return $ticket->load(['repliedByUser']);
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function (Builder $q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }

    private function ensureTicketBelongsToCenter(SupportTicket $ticket): void
    {
        $centerId = currentCenterId();
        if ($ticket->center_id !== $centerId) {
            throw ValidationException::withMessages([
                'support_ticket' => ['Support ticket not found or does not belong to your center.'],
            ]);
        }
    }
}
