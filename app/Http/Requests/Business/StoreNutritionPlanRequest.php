<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;

class StoreNutritionPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'breakfast' => ['nullable', 'string'],
            'lunch' => ['nullable', 'string'],
            'dinner' => ['nullable', 'string'],
            'snacks' => ['nullable', 'string'],
            'supplements' => ['nullable', 'string'],
            'foods_to_avoid' => ['nullable', 'string'],
        ];
    }
}
