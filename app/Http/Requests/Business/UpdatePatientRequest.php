<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $patient = $this->route('patient');
        $patientUserId = is_object($patient) ? $patient->id : (int) $patient;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['sometimes', 'required', 'string', 'phone:AUTO,EG', Rule::unique('users', 'phone')->ignore($patientUserId)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($patientUserId)],
            'password' => ['nullable', 'string', 'min:8'],
            'birth_date' => ['sometimes', 'required', 'date', 'before:today'],
            'therapist_id' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'current_week' => ['nullable', 'integer', 'min:1'],
            'patient_history' => ['nullable', 'string'],
            'chief_complain' => ['nullable', 'string'],
            'diagnosis_id' => ['nullable', 'integer', 'exists:diagnoses,id'],
            'diagnosis' => ['nullable', 'string'],
            'special_tests_notes' => ['nullable', 'string'],
            'objective_findings' => ['nullable', 'string'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'special_tests_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:25600'],
        ];
    }
}
