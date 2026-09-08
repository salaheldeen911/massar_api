<?php

namespace App\Services\Landlord;

use App\Models\Center;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Support\Collection;

class DashboardService
{
    public function getDashboardData(array $filters = []): array
    {
        $search = $filters['search'] ?? null;
        $limit = isset($filters['limit']) ? (int) $filters['limit'] : 10;

        return [
            'stats' => $this->buildStatsData(),
            'quick_actions' => $this->buildQuickActionsData(),
            'recent_activities' => $this->buildRecentActivitiesData(),
            'recent_centers' => $this->buildRecentCentersData($search, $limit),
        ];
    }

    private function buildStatsData(): array
    {
        return [
            'pending_requests' => $this->calculateMetricStats(
                Center::query()->where('status', 'pending')
            ),
            'total_centers' => $this->calculateMetricStats(
                Center::query()
            ),
            'total_users' => $this->calculateMetricStats(
                User::query()
            ),
        ];
    }

    private function calculateMetricStats($query): array
    {
        $totalCount = (clone $query)->count();
        $startOfToday = now()->startOfDay();

        $yesterdayCount = (clone $query)
            ->where('created_at', '<', $startOfToday)
            ->count();

        $newTodayCount = (clone $query)
            ->where('created_at', '>=', $startOfToday)
            ->count();

        $changePercentage = 0.0;
        if ($yesterdayCount > 0) {
            $changePercentage = round(($newTodayCount / $yesterdayCount) * 100, 1);
        } elseif ($newTodayCount > 0) {
            $changePercentage = 100.0;
        }

        return [
            'count' => $totalCount,
            'change_percentage' => $changePercentage,
            'period' => 'from yesterday',
        ];
    }

    private function buildQuickActionsData(): array
    {
        $pendingCentersCount = Center::where('status', 'pending')->count();
        $openTicketsCount = SupportTicket::where('status', 'open')->count();

        return [
            'add_new_center' => [
                'title' => 'Add New Center',
                'description' => 'Create a new center from any city',
            ],
            'review_requests' => [
                'title' => 'Review Requests',
                'count' => $pendingCentersCount,
                'label' => $pendingCentersCount . ' pending requests',
            ],
            'center_support' => [
                'title' => 'Center Support',
                'count' => $openTicketsCount,
                'label' => $openTicketsCount . ' new messages',
            ],
        ];
    }

    private function buildRecentActivitiesData(): Collection
    {
        $recentCenters = Center::latest('created_at')
            ->take(5)
            ->get()
            ->map(function (Center $center) {
                $isPending = $center->status === 'pending';
                $isApproved = $center->status === 'active';

                $title = $isPending ? 'New Center Request' : ($isApproved ? 'Request Approved' : 'Center Status Updated');
                $type = $isPending ? 'new_center_request' : ($isApproved ? 'request_approved' : 'center_updated');

                return [
                    'id' => 'center_' . $center->id,
                    'type' => $type,
                    'title' => $title,
                    'description' => $center->name,
                    'created_at' => $center->created_at?->toISOString(),
                    'time_ago' => $center->created_at?->diffForHumans(),
                ];
            });

        $recentTickets = SupportTicket::latest('created_at')
            ->take(5)
            ->get()
            ->map(function (SupportTicket $ticket) {
                return [
                    'id' => 'ticket_' . $ticket->id,
                    'type' => 'support_ticket',
                    'title' => 'New Support Ticket',
                    'description' => $ticket->subject ?? $ticket->sender_name ?? 'Support Inquiry',
                    'created_at' => $ticket->created_at?->toISOString(),
                    'time_ago' => $ticket->created_at?->diffForHumans(),
                ];
            });

        return $recentCenters->concat($recentTickets)
            ->sortByDesc('created_at')
            ->values()
            ->take(10);
    }

    private function buildRecentCentersData(?string $search, int $limit): Collection
    {
        $query = Center::with(['media', 'users'])
            ->latest('created_at');

        if ($search !== null && trim($search) !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%")
                  ->orWhere('specialty', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return $query->take($limit)
            ->get()
            ->map(function (Center $center) {
                $firstUser = $center->users->first();
                $contact = $firstUser?->email ?? $center->phone;

                return [
                    'id' => $center->id,
                    'name' => $center->name,
                    'contact' => $contact,
                    'phone' => $center->phone,
                    'location' => trim(($center->city ? $center->city . ', ' : '') . $center->country),
                    'city' => $center->city,
                    'country' => $center->country,
                    'speciality' => $center->specialty,
                    'status' => $center->status,
                    'logo_url' => $center->getFirstMediaUrl('logo') ?: null,
                    'created_at' => $center->created_at?->toISOString(),
                ];
            });
    }
}
