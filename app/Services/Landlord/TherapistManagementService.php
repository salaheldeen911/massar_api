<?php

namespace App\Services\Landlord;

use Illuminate\Support\Facades\Hash;

use App\Models\TherapistProfile;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TherapistManagementService
{
    /**
     * Get paginated therapists with cross-center filtering options.
     */
    public function getPaginatedTherapists(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->buildTherapistsQuery($filters);

        return $query->latest('created_at')->paginate($perPage);
    }

    /**
     * Create a new therapist user account and profile record within a database transaction.
     */
    public function createTherapist(array $data, ?UploadedFile $avatar = null): User
    {
        return DB::transaction(function () use ($data, $avatar) {
            $user = $this->saveTherapistUserRecord($data);

            $this->saveTherapistProfileRecord($user, $data);

            $this->attachAvatarMedia($user, $avatar);

            return $user->fresh(['center', 'therapistProfile', 'media']);
        });
    }

    /**
     * Get detailed information for a single therapist.
     */
    public function getTherapistDetails(User $therapist): User
    {
        $this->ensureUserIsTherapist($therapist);

        return $therapist->load(['center', 'therapistProfile', 'media'])->loadCount('assignedPatients');
    }

    /**
     * Update an existing therapist user account and profile record within a database transaction.
     */
    public function updateTherapist(User $therapist, array $data, ?UploadedFile $avatar = null): User
    {
        $this->ensureUserIsTherapist($therapist);

        return DB::transaction(function () use ($therapist, $data, $avatar) {
            $this->updateTherapistUserRecord($therapist, $data);

            $this->updateTherapistProfileRecord($therapist, $data);

            $this->attachAvatarMedia($therapist, $avatar);

            return $therapist->fresh(['center', 'therapistProfile', 'media']);
        });
    }

    /**
     * Delete a therapist profile and user account within a database transaction.
     */
    public function deleteTherapist(User $therapist): void
    {
        $this->ensureUserIsTherapist($therapist);

        DB::transaction(function () use ($therapist) {
            $therapist->therapistProfile()?->delete();
            $therapist->delete();
        });
    }

    /**
     * Private Helper: Build base therapists query with relationship eager loading and filter application.
     */
    private function buildTherapistsQuery(array $filters): Builder
    {
        $query = User::query()
            ->role('therapist')
            ->with(['center', 'therapistProfile', 'media'])
            ->withCount('assignedPatients');

        $this->applyCenterFilter($query, $filters);
        $this->applySearchFilter($query, $filters);
        $this->applyStatusFilter($query, $filters);
        $this->applyDateRangeFilter($query, $filters);

        return $query;
    }

    /**
     * Private Helper: Save therapist user account and assign 'therapist' role.
     */
    private function saveTherapistUserRecord(array $data): User
    {
        $user = User::create([
            'center_id' => $data['center_id'],
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'status' => $data['status'] ?? 'active',
        ]);

        $user->assignRole('therapist');

        return $user;
    }

    /**
     * Private Helper: Save therapist profile details.
     */
    private function saveTherapistProfileRecord(User $user, array $data): TherapistProfile
    {
        return TherapistProfile::create([
            'user_id' => $user->id,
            'specialization' => $data['specialization'] ?? null,
            'license_no' => $data['license_no'] ?? null,
            'bio' => $data['bio'] ?? null,
        ]);
    }

    /**
     * Private Helper: Update therapist user account fields.
     */
    private function updateTherapistUserRecord(User $user, array $data): void
    {
        $userData = array_filter([
            'name' => $data['name'] ?? null,
            'email' => array_key_exists('email', $data) ? $data['email'] : null,
            'phone' => array_key_exists('phone', $data) ? $data['phone'] : null,
            'status' => $data['status'] ?? null,
        ], fn ($val) => $val !== null);

        if (! empty($data['password'])) {
            $userData['password'] = Hash::make($data['password']);
        }

        if (! empty($userData)) {
            $user->update($userData);
        }
    }

    /**
     * Private Helper: Update therapist profile fields.
     */
    private function updateTherapistProfileRecord(User $user, array $data): void
    {
        $profile = $user->therapistProfile;

        $profileData = array_filter([
            'specialization' => $data['specialization'] ?? null,
            'license_no' => $data['license_no'] ?? null,
            'bio' => $data['bio'] ?? null,
        ], fn ($val) => $val !== null);

        if ($profile) {
            $profile->update($profileData);
        } elseif (! empty($profileData)) {
            $user->therapistProfile()->create($profileData);
        }
    }

    /**
     * Private Helper: Attach avatar file if present.
     */
    private function attachAvatarMedia(User $user, ?UploadedFile $avatar): void
    {
        if ($avatar) {
            $user->addMedia($avatar)->toMediaCollection('avatar');
        }
    }

    /**
     * Private Helper: Apply center_id filter if provided.
     */
    private function applyCenterFilter(Builder $query, array $filters): void
    {
        if (! empty($filters['center_id'])) {
            $query->where('center_id', $filters['center_id']);
        }
    }

    /**
     * Private Helper: Apply search filter on therapist user fields and profile fields.
     */
    private function applySearchFilter(Builder $query, array $filters): void
    {
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhereHas('therapistProfile', function (Builder $tq) use ($search) {
                      $tq->where('specialization', 'like', "%{$search}%")
                        ->orWhere('license_no', 'like', "%{$search}%");
                  });
            });
        }
    }

    /**
     * Private Helper: Apply status filter on therapist user status.
     */
    private function applyStatusFilter(Builder $query, array $filters): void
    {
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }

    /**
     * Private Helper: Apply creation date range filters.
     */
    private function applyDateRangeFilter(Builder $query, array $filters): void
    {
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
    }

    /**
     * Private Helper: Ensure given user is a therapist.
     */
    private function ensureUserIsTherapist(User $user): void
    {
        if (! $user->hasRole('therapist')) {
            throw ValidationException::withMessages([
                'therapist' => ['The requested user is not a therapist.'],
            ]);
        }
    }
}
