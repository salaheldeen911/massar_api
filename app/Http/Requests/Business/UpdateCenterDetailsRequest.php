<?php

namespace App\Http\Requests\Business;

use App\Http\Requests\Traits\NormalizesPhone;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCenterDetailsRequest extends FormRequest
{
    use NormalizesPhone;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['sometimes', 'required', 'string', 'phone:AUTO'],
            'facebook' => ['nullable', 'string', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'linkedin' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:5120'],
        ];
    }
}
