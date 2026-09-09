<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTherapistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $therapist = $this->route('therapist');
        $therapistUserId = is_object($therapist) ? $therapist->id : (int) $therapist;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($therapistUserId)],
            'phone' => ['sometimes', 'required', 'string', 'phone:AUTO,EG', Rule::unique('users', 'phone')->ignore($therapistUserId)],
            'password' => ['nullable', 'string', 'min:8'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'specialization' => ['nullable', 'string', 'max:150'],
            'license_no' => ['nullable', 'string', 'max:100'],
            'bio' => ['nullable', 'string'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ];
    }
}
