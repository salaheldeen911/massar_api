<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;

class GetPatientsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'therapist_id' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['nullable', 'string', 'in:active,inactive,pending'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
