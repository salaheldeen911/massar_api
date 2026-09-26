<?php

namespace App\Services\Business;

use Illuminate\Support\Facades\Hash;

use App\Models\PatientProfile;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class PatientManagementService
{
    public function listCenterPatients(array $filters = []): LengthAwarePaginator
    {
        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 10;
        $centerId = currentCenterId();

        $query = User::query()
            ->where('center_id', $centerId)
            ->role('patient')
            ->with(['patientProfile.therapist', 'patientProfile.diagnosisModel', 'media']);

        $this->applyFilters($query, $filters);

        return $query->latest('created_at')->paginate($perPage);
    }

    public function listMyPatients(array $filters = []): LengthAwarePaginator
    {
        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 10;
        $centerId = currentCenterId();
        $currentUser = currentUser();

        $query = User::query()
            ->where('center_id', $centerId)
            ->role('patient')
            ->whereHas('patientProfile', function (Builder $pq) use ($currentUser) {
                $pq->where('therapist_id', $currentUser?->id);
            })
            ->with(['patientProfile.therapist', 'patientProfile.diagnosisModel', 'media']);

        $this->applyFilters($query, $filters);

        return $query->latest('created_at')->paginate($perPage);
    }

    public function createPatient(array $data): User
    {
        $centerId = currentCenterId();

        return DB::transaction(function () use ($data, $centerId) {
            $user = $this->createPatientUser($data, $centerId);
            $profile = $this->createPatientProfile($user, $data, $centerId);

            $this->handleMediaAttachments($user, $profile, $data);

            return $user->load(['patientProfile.therapist', 'patientProfile.diagnosisModel', 'media']);
        });
    }

    public function getPatientDetails(User $patient): User
    {
        $this->ensurePatientBelongsToCurrentCenter($patient);

        return $patient->load([
            'patientProfile.therapist',
            'patientProfile.diagnosisModel',
            'patientProfile.media',
            'treatmentPlan',
            'nutritionPlan',
            'assignedExercises' => fn ($q) => $q->with(['exercise.media', 'assignedBy', 'logs'])->latest('created_at'),
            'media',
        ]);
    }

    public function updatePatient(User $patient, array $data): User
    {
        $this->ensurePatientBelongsToCurrentCenter($patient);

        return DB::transaction(function () use ($patient, $data) {
            $this->updatePatientUser($patient, $data);
            $this->updatePatientProfile($patient, $data);

            $profile = $patient->patientProfile;
            if ($profile) {
                $this->handleMediaAttachments($patient, $profile, $data);
            }

            return $patient->fresh(['patientProfile.therapist', 'patientProfile.diagnosisModel', 'media']);
        });
    }

    public function deletePatient(User $patient): void
    {
        $this->ensurePatientBelongsToCurrentCenter($patient);

        DB::transaction(function () use ($patient) {
            $patient->patientProfile()?->delete();
            $patient->delete();
        });
    }



    private function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('patientProfile', function (Builder $pq) use ($search) {
                      $pq->where('diagnosis', 'like', "%{$search}%")
                        ->orWhere('chief_complain', 'like', "%{$search}%");
                  });
            });
        }

        if (! empty($filters['therapist_id'])) {
            $query->whereHas('patientProfile', function (Builder $pq) use ($filters) {
                $pq->where('therapist_id', $filters['therapist_id']);
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

    private function createPatientUser(array $data, ?int $centerId): User
    {
        $user = User::create([
            'center_id' => $centerId,
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'password' => Hash::make($data['password']),
            'status' => $data['status'] ?? 'active',
        ]);

        $user->assignRole('patient');

        return $user;
    }

    private function createPatientProfile(User $user, array $data, ?int $centerId): PatientProfile
    {
        return PatientProfile::create([
            'user_id' => $user->id,
            'center_id' => $centerId,
            'therapist_id' => $data['therapist_id'] ?? null,
            'birth_date' => $data['birth_date'],
            'current_week' => $data['current_week'] ?? 1,
            'patient_history' => $data['patient_history'] ?? null,
            'chief_complain' => $data['chief_complain'] ?? null,
            'diagnosis_id' => $data['diagnosis_id'] ?? null,
            'diagnosis' => $data['diagnosis'] ?? null,
            'special_tests_notes' => $data['special_tests_notes'] ?? null,
            'objective_findings' => $data['objective_findings'] ?? null,
        ]);
    }

    private function updatePatientUser(User $user, array $data): void
    {
        $userData = array_filter([
            'name' => $data['name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => array_key_exists('email', $data) ? $data['email'] : null,
            'status' => $data['status'] ?? null,
        ], fn ($val) => $val !== null);

        if (! empty($data['password'])) {
            $userData['password'] = Hash::make($data['password']);
        }

        if (array_key_exists('email', $data)) {
            $userData['email'] = $data['email'];
        }

        $user->update($userData);
    }

    private function updatePatientProfile(User $user, array $data): void
    {
        $profile = $user->patientProfile;
        if (! $profile) {
            return;
        }

        $profileData = array_filter([
            'therapist_id' => $data['therapist_id'] ?? null,
            'birth_date' => $data['birth_date'] ?? null,
            'current_week' => $data['current_week'] ?? null,
            'patient_history' => $data['patient_history'] ?? null,
            'chief_complain' => $data['chief_complain'] ?? null,
            'diagnosis_id' => array_key_exists('diagnosis_id', $data) ? $data['diagnosis_id'] : null,
            'diagnosis' => $data['diagnosis'] ?? null,
            'special_tests_notes' => $data['special_tests_notes'] ?? null,
            'objective_findings' => $data['objective_findings'] ?? null,
        ], fn ($val) => $val !== null);

        $profile->update($profileData);
    }

    private function handleMediaAttachments(User $user, PatientProfile $profile, array $data): void
    {
        if (isset($data['avatar']) && $data['avatar'] instanceof \Illuminate\Http\UploadedFile) {
            $user->addMedia($data['avatar'])->toMediaCollection('avatar');
        }

        if (isset($data['special_tests_file']) && $data['special_tests_file'] instanceof \Illuminate\Http\UploadedFile) {
            $profile->addMedia($data['special_tests_file'])->toMediaCollection('special_tests');
        }
    }

    private function ensurePatientBelongsToCurrentCenter(User $patient): void
    {
        $currentCenterId = currentCenterId();
        if ($patient->center_id !== $currentCenterId || ! $patient->hasRole('patient')) {
            throw ValidationException::withMessages([
                'patient' => ['Patient record not found.'],
            ]);
        }
    }
}
