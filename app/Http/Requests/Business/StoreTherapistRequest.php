<?php

namespace App\Http\Requests\Business;

use App\Enums\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTherapistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'phone:AUTO,EG', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8'],
            'status' => ['nullable', Rule::enum(UserStatus::class)],
            'specialization' => ['nullable', 'string', 'max:150'],
            'license_no' => ['nullable', 'string', 'max:100'],
            'bio' => ['nullable', 'string'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ];
    }
}
