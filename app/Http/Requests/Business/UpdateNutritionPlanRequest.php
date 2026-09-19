<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNutritionPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'breakfast' => ['sometimes', 'nullable', 'string'],
            'lunch' => ['sometimes', 'nullable', 'string'],
            'dinner' => ['sometimes', 'nullable', 'string'],
            'snacks' => ['sometimes', 'nullable', 'string'],
            'supplements' => ['sometimes', 'nullable', 'string'],
            'foods_to_avoid' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
