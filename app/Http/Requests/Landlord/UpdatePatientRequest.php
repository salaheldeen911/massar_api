<?php

namespace App\Http\Requests\Landlord;

use App\Models\PatientProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePatientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole('landlord') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $patient = $this->route('patient');
        $userId = $patient instanceof PatientProfile ? $patient->user_id : null;

        return [
            'center_id' => ['sometimes', 'required', 'integer', 'exists:centers,id'],
            'therapist_id' => ['sometimes', 'required', 'integer', 'exists:users,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => [
                'sometimes',
                'required',
                'string',
                'phone:AUTO,EG',
                Rule::unique('users', 'phone')->ignore($userId),
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'birth_date' => ['sometimes', 'required', 'date', 'before:today'],
            'current_week' => ['nullable', 'integer', 'min:1'],
            'patient_history' => ['nullable', 'string'],
            'chief_complain' => ['nullable', 'string'],
            'diagnosis' => ['nullable', 'string'],
            'special_tests_notes' => ['nullable', 'string'],
            'objective_findings' => ['nullable', 'string'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'special_tests_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
        ];
    }
}
