<?php

namespace App\Services\Landlord;

use App\Models\PatientProfile;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class PatientManagementService
{
    /**
     * Get paginated patient profiles with cross-center filtering options.
     */
    public function getPaginatedPatients(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->buildPatientsQuery($filters);

        return $query->latest('created_at')->paginate($perPage);
    }

    /**
     * Get detailed information for a single patient profile.
     */
    public function getPatientDetails(PatientProfile $patientProfile): PatientProfile
    {
        return $this->loadPatientRelations($patientProfile);
    }

    /**
     * Create a new patient user account and profile record within a database transaction.
     */
    public function createPatient(array $data, ?UploadedFile $avatar = null, ?UploadedFile $specialTestsFile = null): PatientProfile
    {
        return DB::transaction(function () use ($data, $avatar, $specialTestsFile) {
            $user = $this->savePatientUserRecord($data);

            $profile = $this->savePatientProfileRecord($user, $data);

            $this->attachPatientMedia($user, $profile, $avatar, $specialTestsFile);

            return $profile->fresh(['user', 'center', 'therapist']);
        });
    }

    /**
     * Update an existing patient user account and profile record within a database transaction.
     */
    public function updatePatient(PatientProfile $patientProfile, array $data, ?UploadedFile $avatar = null, ?UploadedFile $specialTestsFile = null): PatientProfile
    {
        return DB::transaction(function () use ($patientProfile, $data, $avatar, $specialTestsFile) {
            $this->updatePatientUserRecord($patientProfile->user, $data);

            $this->updatePatientProfileRecord($patientProfile, $data);

            $this->attachPatientMedia($patientProfile->user, $patientProfile, $avatar, $specialTestsFile);

            return $patientProfile->fresh(['user', 'center', 'therapist']);
        });
    }

    /**
     * Delete a patient profile and associated user account within a database transaction.
     */
    public function deletePatient(PatientProfile $patientProfile): void
    {
        DB::transaction(function () use ($patientProfile) {
            $user = $patientProfile->user;
            $patientProfile->delete();

            if ($user) {
                $user->delete();
            }
        });
    }

    /**
     * Private Helper: Build base patients query with relationship eager loading and filter application.
     */
    private function buildPatientsQuery(array $filters)
    {
        $query = PatientProfile::query()->with(['user', 'center', 'therapist']);

        $this->applyCenterFilter($query, $filters);
        $this->applyTherapistFilter($query, $filters);
        $this->applySearchFilter($query, $filters);
        $this->applyStatusFilter($query, $filters);
        $this->applyDateRangeFilter($query, $filters);

        return $query;
    }

    /**
     * Private Helper: Save patient user account and assign 'patient' role.
     */
    private function savePatientUserRecord(array $data): User
    {
        $user = User::create([
            'center_id' => $data['center_id'],
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'password' => bcrypt($data['password']),
            'status' => 'active',
        ]);

        $user->assignRole('patient');

        return $user;
    }

    /**
     * Private Helper: Save patient profile details.
     */
    private function savePatientProfileRecord(User $user, array $data): PatientProfile
    {
        return PatientProfile::create([
            'user_id' => $user->id,
            'center_id' => $data['center_id'],
            'therapist_id' => $data['therapist_id'],
            'birth_date' => $data['birth_date'],
            'current_week' => $data['current_week'] ?? 1,
            'patient_history' => $data['patient_history'] ?? null,
            'chief_complain' => $data['chief_complain'] ?? null,
            'diagnosis' => $data['diagnosis'] ?? null,
            'special_tests_notes' => $data['special_tests_notes'] ?? null,
            'objective_findings' => $data['objective_findings'] ?? null,
        ]);
    }

    /**
     * Private Helper: Update patient user account fields.
     */
    private function updatePatientUserRecord(User $user, array $data): void
    {
        $userData = [];

        if (isset($data['center_id'])) {
            $userData['center_id'] = $data['center_id'];
        }
        if (isset($data['name'])) {
            $userData['name'] = $data['name'];
        }
        if (isset($data['phone'])) {
            $userData['phone'] = $data['phone'];
        }
        if (array_key_exists('email', $data)) {
            $userData['email'] = $data['email'];
        }
        if (! empty($data['password'])) {
            $userData['password'] = bcrypt($data['password']);
        }
        if (isset($data['status'])) {
            $userData['status'] = $data['status'];
        }

        if (! empty($userData)) {
            $user->update($userData);
        }
    }

    /**
     * Private Helper: Update patient profile fields.
     */
    private function updatePatientProfileRecord(PatientProfile $profile, array $data): void
    {
        $profileData = [];

        if (isset($data['center_id'])) {
            $profileData['center_id'] = $data['center_id'];
        }
        if (isset($data['therapist_id'])) {
            $profileData['therapist_id'] = $data['therapist_id'];
        }
        if (isset($data['birth_date'])) {
            $profileData['birth_date'] = $data['birth_date'];
        }
        if (isset($data['current_week'])) {
            $profileData['current_week'] = $data['current_week'];
        }
        if (array_key_exists('patient_history', $data)) {
            $profileData['patient_history'] = $data['patient_history'];
        }
        if (array_key_exists('chief_complain', $data)) {
            $profileData['chief_complain'] = $data['chief_complain'];
        }
        if (array_key_exists('diagnosis', $data)) {
            $profileData['diagnosis'] = $data['diagnosis'];
        }
        if (array_key_exists('special_tests_notes', $data)) {
            $profileData['special_tests_notes'] = $data['special_tests_notes'];
        }
        if (array_key_exists('objective_findings', $data)) {
            $profileData['objective_findings'] = $data['objective_findings'];
        }

        if (! empty($profileData)) {
            $profile->update($profileData);
        }
    }

    /**
     * Private Helper: Attach avatar and special tests files if present.
     */
    private function attachPatientMedia(User $user, PatientProfile $profile, ?UploadedFile $avatar, ?UploadedFile $specialTestsFile): void
    {
        if ($avatar) {
            $user->addMedia($avatar)->toMediaCollection('avatar');
        }

        if ($specialTestsFile) {
            $profile->addMedia($specialTestsFile)->toMediaCollection('special_tests');
        }
    }

    /**
     * Private Helper: Apply center_id filter if provided.
     */
    private function applyCenterFilter($query, array $filters): void
    {
        if (! empty($filters['center_id'])) {
            $query->where('center_id', $filters['center_id']);
        }
    }

    /**
     * Private Helper: Apply therapist_id filter if provided.
     */
    private function applyTherapistFilter($query, array $filters): void
    {
        if (! empty($filters['therapist_id'])) {
            $query->where('therapist_id', $filters['therapist_id']);
        }
    }

    /**
     * Private Helper: Apply search filter on patient user fields.
     */
    private function applySearchFilter($query, array $filters): void
    {
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
    }

    /**
     * Private Helper: Apply status filter on patient user status.
     */
    private function applyStatusFilter($query, array $filters): void
    {
        if (! empty($filters['status'])) {
            $status = $filters['status'];
            $query->whereHas('user', function ($q) use ($status) {
                $q->where('status', $status);
            });
        }
    }

    /**
     * Private Helper: Apply creation date range filters.
     */
    private function applyDateRangeFilter($query, array $filters): void
    {
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
    }

    /**
     * Private Helper: Eager load complete relations for patient detail view.
     */
    private function loadPatientRelations(PatientProfile $patientProfile): PatientProfile
    {
        return $patientProfile->load([
            'user',
            'center',
            'therapist',
        ]);
    }
}
