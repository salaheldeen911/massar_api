<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCenterDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['sometimes', 'required', 'string', 'max:50'],
            'facebook' => ['nullable', 'string', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'linkedin' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:5120'],
        ];
    }
}
