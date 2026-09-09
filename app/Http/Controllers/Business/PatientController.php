<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\GetPatientsRequest;
use App\Http\Requests\Business\StorePatientRequest;
use App\Http\Requests\Business\UpdatePatientRequest;
use App\Http\Resources\Business\PatientDetailResource;
use App\Http\Resources\Business\PatientResource;
use App\Models\User;
use App\Services\Business\PatientManagementService;
use Illuminate\Http\JsonResponse;

class PatientController extends Controller
{
    public function __construct(
        protected PatientManagementService $patientService
    ) {}

    public function index(GetPatientsRequest $request): JsonResponse
    {
        $patients = $this->patientService->listCenterPatients($request->validated());

        return $this->success(
            PatientResource::collection($patients),
            'Center patient list retrieved successfully.'
        );
    }

    public function myPatients(GetPatientsRequest $request): JsonResponse
    {
        $patients = $this->patientService->listMyPatients($request->validated());

        return $this->success(
            PatientResource::collection($patients),
            'My assigned patients retrieved successfully.'
        );
    }

    public function store(StorePatientRequest $request): JsonResponse
    {
        $patient = $this->patientService->createPatient($request->validated());

        return $this->success(
            new PatientResource($patient),
            'Patient created successfully.',
            201
        );
    }

    public function show(User $patient): JsonResponse
    {
        $patient = $this->patientService->getPatientDetails($patient);

        return $this->success(
            new PatientDetailResource($patient),
            'Patient details retrieved successfully.'
        );
    }

    public function update(UpdatePatientRequest $request, User $patient): JsonResponse
    {
        $patient = $this->patientService->updatePatient($patient, $request->validated());

        return $this->success(
            new PatientResource($patient),
            'Patient updated successfully.'
        );
    }

    public function destroy(User $patient): JsonResponse
    {
        $this->patientService->deletePatient($patient);

        return $this->success(
            null,
            'Patient deleted successfully.'
        );
    }
}
