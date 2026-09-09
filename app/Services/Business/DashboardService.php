<?php

namespace App\Services\Business;

use App\Models\Exercise;
use App\Models\PatientProfile;
use App\Models\User;
use Illuminate\Support\Collection;

class DashboardService
{
    public function getDashboardData(array $filters = []): array
    {
        $centerId = currentCenterId();
        $currentUser = currentUser();
        $isTherapist = $currentUser && $currentUser->hasRole('therapist');

        $therapistsLimit = isset($filters['therapists_limit']) ? (int) $filters['therapists_limit'] : 5;
        $patientsLimit = isset($filters['patients_limit']) ? (int) $filters['patients_limit'] : 5;

        $data = [
            'role' => $isTherapist ? 'therapist' : 'admin',
            'stats' => $this->buildStatsData($centerId, $isTherapist ? $currentUser?->id : null),
            'recent_patients' => $this->buildRecentPatientsData($centerId, $patientsLimit, $isTherapist ? $currentUser?->id : null),
        ];

        if (! $isTherapist) {
            $data['recent_therapists'] = $this->buildRecentTherapistsData($centerId, $therapistsLimit);
        }

        return $data;
    }

    private function buildStatsData(?int $centerId, ?int $therapistId = null): array
    {
        $patientsQuery = PatientProfile::query()->where('center_id', $centerId);
        if ($therapistId !== null) {
            $patientsQuery->where('therapist_id', $therapistId);
        }

        return [
            'total_patients' => $this->calculateMetricStats($patientsQuery),
            'total_therapists' => $this->calculateMetricStats(
                User::query()->where('center_id', $centerId)->role('therapist')
            ),
            'total_exercises' => $this->calculateMetricStats(
                Exercise::query()->where(function ($q) use ($centerId) {
                    $q->whereNull('center_id')->orWhere('center_id', $centerId);
                })
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

    private function buildRecentTherapistsData(?int $centerId, int $limit): Collection
    {
        if (! $centerId) {
            return collect();
        }

        return User::query()
            ->where('center_id', $centerId)
            ->role('therapist')
            ->with(['therapistProfile', 'media'])
            ->latest('created_at')
            ->take($limit)
            ->get()
            ->map(function (User $therapist) {
                $assignedPatientsCount = PatientProfile::where('therapist_id', $therapist->id)->count();

                return [
                    'id' => $therapist->id,
                    'name' => $therapist->name,
                    'age' => null,
                    'phone' => $therapist->phone,
                    'email' => $therapist->email,
                    'specialization' => $therapist->therapistProfile?->specialization,
                    'assigned_patients_count' => $assignedPatientsCount,
                    'no_of_works' => $assignedPatientsCount,
                    'status' => $therapist->status,
                    'avatar_url' => $therapist->getFirstMediaUrl('avatar') ?: null,
                    'created_at' => $therapist->created_at?->toISOString(),
                ];
            });
    }

    private function buildRecentPatientsData(?int $centerId, int $limit, ?int $therapistId = null): Collection
    {
        if (! $centerId) {
            return collect();
        }

        $query = User::query()
            ->where('center_id', $centerId)
            ->role('patient');

        if ($therapistId !== null) {
            $query->whereHas('patientProfile', fn ($pq) => $pq->where('therapist_id', $therapistId));
        }

        return $query->with(['patientProfile.therapist', 'media'])
            ->latest('created_at')
            ->take($limit)
            ->get()
            ->map(function (User $patientUser) {
                $profile = $patientUser->patientProfile;
                $age = $profile?->birth_date ? $profile->birth_date->age : null;
                $currentWeek = $profile?->current_week ?? 1;

                return [
                    'id' => $patientUser->id,
                    'patient_profile_id' => $profile?->id,
                    'name' => $patientUser->name,
                    'age' => $age,
                    'phone' => $patientUser->phone,
                    'email' => $patientUser->email,
                    'current_week' => $currentWeek,
                    'no_of_weeks' => $currentWeek,
                    'status' => $patientUser->status,
                    'assigned_therapist' => $profile?->therapist ? [
                        'id' => $profile->therapist->id,
                        'name' => $profile->therapist->name,
                    ] : null,
                    'avatar_url' => $patientUser->getFirstMediaUrl('avatar') ?: null,
                    'created_at' => $patientUser->created_at?->toISOString(),
                ];
            });
    }
}
