<?php

namespace App\Services\Landlord;

use App\Models\Center;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CenterSubscriptionService
{
    /**
     * Get paginated pending center applications.
     */
    public function getPendingCenters(int $perPage = 15): LengthAwarePaginator
    {
        return Center::where('status', 'pending')
            ->with(['users' => function ($query) {
                $query->role('admin');
            }])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Approve center application and activate its center admin user.
     */
    public function approveCenter(Center $center): Center
    {
        return DB::transaction(function () use ($center) {
            $this->activateCenterRecord($center);
            $this->activateCenterUsers($center);

            return $center->fresh(['users']);
        });
    }

    /**
     * Reject center application and deactivate its center admin user.
     */
    public function rejectCenter(Center $center, ?string $reason = null): Center
    {
        return DB::transaction(function () use ($center) {
            $this->rejectCenterRecord($center);
            $this->deactivateCenterUsers($center);

            return $center->fresh(['users']);
        });
    }

    /**
     * Helper: Set center status to active and start trial period.
     */
    private function activateCenterRecord(Center $center): void
    {
        $center->update([
            'status' => 'active',
            'subscription_status' => 'active',
            'trial_starts_at' => now(),
            'trial_ends_at' => now()->addDays(14),
        ]);
    }

    /**
     * Helper: Set pending users belonging to center to active status.
     */
    private function activateCenterUsers(Center $center): void
    {
        $center->users()
            ->where('status', 'pending')
            ->update(['status' => 'active']);
    }

    /**
     * Helper: Set center status to rejected.
     */
    private function rejectCenterRecord(Center $center): void
    {
        $center->update([
            'status' => 'rejected',
            'subscription_status' => 'canceled',
        ]);
    }

    /**
     * Helper: Set users belonging to center to inactive status.
     */
    private function deactivateCenterUsers(Center $center): void
    {
        $center->users()->update(['status' => 'inactive']);
    }
}
