<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Http\Requests\Landlord\GetPatientsRequest;
use App\Http\Requests\Landlord\StorePatientRequest;
use App\Http\Requests\Landlord\UpdatePatientRequest;
use App\Http\Resources\Landlord\PatientResource;
use App\Models\PatientProfile;
use App\Services\Landlord\PatientManagementService;
use Illuminate\Http\JsonResponse;

class PatientController extends Controller
{
    public function __construct(
        protected PatientManagementService $patientService
    ) {}

    /**
     * Display a paginated list of patients with cross-center and therapist filtering.
     */
    public function index(GetPatientsRequest $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);

        $patients = $this->patientService->getPaginatedPatients(
            $request->validated(),
            $perPage
        );

        return $this->success(
            PatientResource::collection($patients),
            'Patients retrieved successfully.'
        );
    }

    /**
     * Store a newly created patient account and profile.
     */
    public function store(StorePatientRequest $request): JsonResponse
    {
        $patient = $this->patientService->createPatient(
            $request->validated(),
            $request->file('avatar'),
            $request->file('special_tests_file')
        );

        return $this->success(
            new PatientResource($patient),
            'Patient created and assigned successfully.',
            201
        );
    }

    /**
     * Display the specified patient profile details.
     */
    public function show(PatientProfile $patient): JsonResponse
    {
        $detailedPatient = $this->patientService->getPatientDetails($patient);

        return $this->success(
            new PatientResource($detailedPatient),
            'Patient details retrieved successfully.'
        );
    }

    /**
     * Update the specified patient profile details and assigned therapist.
     */
    public function update(UpdatePatientRequest $request, PatientProfile $patient): JsonResponse
    {
        $updatedPatient = $this->patientService->updatePatient(
            $patient,
            $request->validated(),
            $request->file('avatar'),
            $request->file('special_tests_file')
        );

        return $this->success(
            new PatientResource($updatedPatient),
            'Patient updated successfully.'
        );
    }

    /**
     * Remove the specified patient profile and user account from storage.
     */
    public function destroy(PatientProfile $patient): JsonResponse
    {
        $this->patientService->deletePatient($patient);

        return $this->success(
            null,
            'Patient deleted successfully.'
        );
    }
}
