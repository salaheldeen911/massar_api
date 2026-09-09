<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class LogExerciseProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'completed_sets' => ['nullable', 'integer', 'min:0'],
            'completed_repeats' => ['nullable', 'integer', 'min:0'],
            'duration_spent' => ['nullable', 'integer', 'min:0'],
            'is_completed' => ['nullable', 'boolean'],
        ];
    }
}
