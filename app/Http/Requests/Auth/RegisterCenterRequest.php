<?php

namespace App\Http\Requests\Auth;

use App\Enums\CenterType;
use App\Http\Requests\Traits\NormalizesPhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterCenterRequest extends FormRequest
{
    use NormalizesPhone;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Center Information
            'center_name' => ['required', 'string', 'max:191'],
            'type' => ['nullable', 'string', Rule::enum(CenterType::class)],
            'specialty' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'therapists_count' => ['required', 'integer', 'min:1'],
            'branches_count' => ['required', 'integer', 'min:1'],
            'referral_source' => ['nullable', 'string', 'max:191'],
            'terms_accepted' => ['required', 'accepted'],
            'license_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],

            // Admin User Information
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'string', 'email', 'max:191', 'unique:users,email'],
            'phone' => ['required', 'string', 'phone:AUTO', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.enum' => 'The selected center type is invalid. Allowed values: ' . implode(', ', CenterType::values()),
        ];
    }
}
