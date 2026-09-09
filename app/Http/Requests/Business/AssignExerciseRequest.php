<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;

class AssignExerciseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'exercise_id' => ['required', 'integer', 'exists:exercises,id'],
            'sets' => ['nullable', 'integer', 'min:1'],
            'repeats' => ['nullable', 'integer', 'min:1'],
            'duration' => ['nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
