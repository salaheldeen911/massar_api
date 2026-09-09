<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'phone:AUTO,EG', 'unique:users,phone'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'birth_date' => ['required', 'date', 'before:today'],
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
