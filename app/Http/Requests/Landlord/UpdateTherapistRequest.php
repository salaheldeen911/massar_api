<?php

namespace App\Http\Requests\Landlord;

use App\Enums\UserStatus;
use App\Http\Requests\Traits\NormalizesPhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTherapistRequest extends FormRequest
{
    use NormalizesPhone;

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
        $therapist = $this->route('therapist');
        $therapistUserId = is_object($therapist) ? $therapist->id : (int) $therapist;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($therapistUserId)],
            'phone' => ['sometimes', 'required', 'string', 'phone:AUTO', Rule::unique('users', 'phone')->ignore($therapistUserId)],
            'password' => ['nullable', 'string', 'min:8'],
            'status' => ['nullable', Rule::enum(UserStatus::class)],
            'specialization' => ['nullable', 'string', 'max:150'],
            'license_no' => ['nullable', 'string', 'max:100'],
            'bio' => ['nullable', 'string'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ];
    }
}
