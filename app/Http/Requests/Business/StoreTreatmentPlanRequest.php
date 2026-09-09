<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;

class StoreTreatmentPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'manual_therapy' => ['nullable', 'string'],
            'electrotherapy' => ['nullable', 'string'],
            'medications' => ['nullable', 'string'],
            'goals' => ['nullable', 'string'],
        ];
    }
}
