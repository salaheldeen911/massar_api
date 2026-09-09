<?php

namespace App\Services\Business;

use App\Models\TherapistProfile;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TherapistManagementService
{
    public function listTherapists(array $filters = []): LengthAwarePaginator
    {
        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 10;
        $query = $this->queryCenterTherapists();

        $this->applyFilters($query, $filters);

        return $query->latest('created_at')->paginate($perPage);
    }

    public function createTherapist(array $data): User
    {
        $centerId = currentCenterId();

        return DB::transaction(function () use ($data, $centerId) {
            $user = $this->createTherapistUser($data, $centerId);
            $profile = $this->createTherapistProfile($user, $data);

            $this->handleAvatarMedia($user, $data);

            return $user->load(['therapistProfile', 'media']);
        });
    }

    public function getTherapistDetails(User $therapist): User
    {
        $this->ensureTherapistBelongsToCurrentCenter($therapist);

        return $therapist->load(['therapistProfile', 'media']);
    }

    public function updateTherapist(User $therapist, array $data): User
    {
        $this->ensureTherapistBelongsToCurrentCenter($therapist);

        return DB::transaction(function () use ($therapist, $data) {
            $this->updateTherapistUser($therapist, $data);
            $this->updateTherapistProfile($therapist, $data);

            $this->handleAvatarMedia($therapist, $data);

            return $therapist->fresh(['therapistProfile', 'media']);
        });
    }

    public function deleteTherapist(User $therapist): void
    {
        $this->ensureTherapistBelongsToCurrentCenter($therapist);

        DB::transaction(function () use ($therapist) {
            $therapist->therapistProfile()?->delete();
            $therapist->delete();
        });
    }

    private function queryCenterTherapists(): Builder
    {
        $centerId = currentCenterId();

        return User::query()
            ->where('center_id', $centerId)
            ->role('therapist')
            ->with(['therapistProfile', 'media']);
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('therapistProfile', function (Builder $tq) use ($search) {
                      $tq->where('specialization', 'like', "%{$search}%")
                        ->orWhere('license_no', 'like', "%{$search}%");
                  });
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
    }

    private function createTherapistUser(array $data, ?int $centerId): User
    {
        $user = User::create([
            'center_id' => $centerId,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => bcrypt($data['password']),
            'status' => $data['status'] ?? 'active',
        ]);

        $user->assignRole('therapist');

        return $user;
    }

    private function createTherapistProfile(User $user, array $data): TherapistProfile
    {
        return TherapistProfile::create([
            'user_id' => $user->id,
            'specialization' => $data['specialization'] ?? null,
            'license_no' => $data['license_no'] ?? null,
            'bio' => $data['bio'] ?? null,
        ]);
    }

    private function updateTherapistUser(User $user, array $data): void
    {
        $userData = array_filter([
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'] ?? null,
        ], fn ($val) => $val !== null);

        if (! empty($data['password'])) {
            $userData['password'] = bcrypt($data['password']);
        }

        $user->update($userData);
    }

    private function updateTherapistProfile(User $user, array $data): void
    {
        $profile = $user->therapistProfile;

        $profileData = array_filter([
            'specialization' => $data['specialization'] ?? null,
            'license_no' => $data['license_no'] ?? null,
            'bio' => $data['bio'] ?? null,
        ], fn ($val) => $val !== null);

        if ($profile) {
            $profile->update($profileData);
        } else {
            $user->therapistProfile()->create($profileData);
        }
    }

    private function handleAvatarMedia(User $user, array $data): void
    {
        if (isset($data['avatar']) && $data['avatar'] instanceof \Illuminate\Http\UploadedFile) {
            $user->addMedia($data['avatar'])->toMediaCollection('avatar');
        }
    }

    private function ensureTherapistBelongsToCurrentCenter(User $therapist): void
    {
        $currentCenterId = currentCenterId();
        if ($therapist->center_id !== $currentCenterId || ! $therapist->hasRole('therapist')) {
            throw ValidationException::withMessages([
                'therapist' => ['Therapist record not found or does not belong to your center.'],
            ]);
        }
    }
}
