<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTreatmentPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'manual_therapy' => ['sometimes', 'nullable', 'string'],
            'electrotherapy' => ['sometimes', 'nullable', 'string'],
            'medications' => ['sometimes', 'nullable', 'string'],
            'goals' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
