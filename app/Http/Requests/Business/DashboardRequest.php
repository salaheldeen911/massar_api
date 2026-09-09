<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;

class DashboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'therapists_limit' => ['nullable', 'integer', 'min:1', 'max:50'],
            'patients_limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}
