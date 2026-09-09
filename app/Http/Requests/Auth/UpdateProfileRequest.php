<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['sometimes', 'required', 'string', 'phone:AUTO,EG', Rule::unique('users', 'phone')->ignore($userId)],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ];
    }
}
