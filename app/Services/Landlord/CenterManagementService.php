<?php

namespace App\Services\Landlord;

use App\Models\Center;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CenterManagementService
{
    /**
     * Get paginated centers with optional search and status filter.
     */
    public function getPaginatedCenters(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Center::query()->with(['users']);

        $this->applyCenterFilters($query, $filters);

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new center and attach media files if provided.
     */
    public function createCenter(array $data, ?UploadedFile $logo = null, ?UploadedFile $licenseDocument = null): Center
    {
        return DB::transaction(function () use ($data, $logo, $licenseDocument) {
            $center = $this->saveCenterRecord($data);

            if (! empty($data['admin_name'])) {
                $this->createPrimaryCenterAdminUser($center, $data);
            }

            $this->attachCenterMedia($center, $logo, $licenseDocument);

            return $center->fresh(['users']);
        });
    }

    /**
     * Get detailed information for a single center.
     */
    public function getCenterDetails(Center $center): Center
    {
        return $this->loadCenterRelations($center);
    }

    /**
     * Get staff users (admins & therapists) for a specific center.
     */
    public function getCenterStaff(Center $center)
    {
        return $this->queryCenterStaffUsers($center);
    }

    /**
     * Update an existing center's details and media.
     */
    public function updateCenter(Center $center, array $data, ?UploadedFile $logo = null, ?UploadedFile $licenseDocument = null): Center
    {
        return DB::transaction(function () use ($center, $data, $logo, $licenseDocument) {
            $this->updateCenterRecord($center, $data);

            $this->updateCenterMedia($center, $logo, $licenseDocument);

            return $center->fresh(['users']);
        });
    }

    /**
     * Delete a center and deactivate its associated users.
     */
    public function deleteCenter(Center $center): void
    {
        DB::transaction(function () use ($center) {
            $this->deactivateCenterUsers($center);
            $this->deleteCenterRecord($center);
        });
    }

    /**
     * Private Helper: Apply search query and status filters.
     */
    private function applyCenterFilters($query, array $filters): void
    {
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['subscription_status'])) {
            $query->where('subscription_status', $filters['subscription_status']);
        }
    }

    /**
     * Private Helper: Save initial center database record.
     */
    private function saveCenterRecord(array $data): Center
    {
        return Center::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'specialty' => $data['specialty'] ?? null,
            'country' => $data['country'] ?? 'Egypt',
            'city' => $data['city'] ?? null,
            'therapists_count' => $data['therapists_count'] ?? 1,
            'branches_count' => $data['branches_count'] ?? 1,
            'referral_source' => $data['referral_source'] ?? null,
            'terms_accepted' => $data['terms_accepted'] ?? true,
            'status' => $data['status'] ?? 'active',
            'subscription_status' => $data['subscription_status'] ?? 'active',
            'trial_starts_at' => now(),
            'trial_ends_at' => now()->addDays(14),
            'facebook' => $data['facebook'] ?? null,
            'whatsapp' => $data['whatsapp'] ?? null,
            'instagram' => $data['instagram'] ?? null,
            'linkedin' => $data['linkedin'] ?? null,
        ]);
    }

    /**
     * Private Helper: Create primary admin user for newly created center.
     */
    private function createPrimaryCenterAdminUser(Center $center, array $data): User
    {
        $user = User::create([
            'center_id' => $center->id,
            'name' => $data['admin_name'],
            'phone' => $data['admin_phone'],
            'email' => $data['admin_email'],
            'password' => bcrypt($data['admin_password']),
            'status' => 'active',
        ]);

        $user->assignRole('admin');

        return $user;
    }

    /**
     * Private Helper: Attach logo and license document media to center.
     */
    private function attachCenterMedia(Center $center, ?UploadedFile $logo, ?UploadedFile $licenseDocument): void
    {
        if ($logo) {
            $center->addMedia($logo)->toMediaCollection('logo');
        }

        if ($licenseDocument) {
            $center->addMedia($licenseDocument)->toMediaCollection('license_document');
        }
    }

    /**
     * Private Helper: Update center Eloquent attributes.
     */
    private function updateCenterRecord(Center $center, array $data): void
    {
        $center->update(array_filter($data, function ($key) {
            return ! in_array($key, ['logo', 'license_document']);
        }, ARRAY_FILTER_USE_KEY));
    }

    /**
     * Private Helper: Update/Replace media files if provided.
     */
    private function updateCenterMedia(Center $center, ?UploadedFile $logo, ?UploadedFile $licenseDocument): void
    {
        if ($logo) {
            $center->addMedia($logo)->toMediaCollection('logo');
        }

        if ($licenseDocument) {
            $center->addMedia($licenseDocument)->toMediaCollection('license_document');
        }
    }

    /**
     * Private Helper: Eager load center relationships.
     */
    private function loadCenterRelations(Center $center): Center
    {
        return $center->load(['users' => function ($query) {
            $query->latest();
        }]);
    }

    /**
     * Private Helper: Deactivate users belonging to center before deletion.
     */
    private function deactivateCenterUsers(Center $center): void
    {
        $center->users()->update(['status' => 'inactive']);
    }

    /**
     * Private Helper: Delete center record from database.
     */
    private function deleteCenterRecord(Center $center): void
    {
        $center->delete();
    }

    /**
     * Private Helper: Query users with admin or therapist roles belonging to center.
     */
    private function queryCenterStaffUsers(Center $center)
    {
        return $center->users()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['admin', 'therapist']);
            })
            ->latest()
            ->get();
    }
}
