<?php

namespace App\Http\Requests\Landlord;

use Illuminate\Foundation\Http\FormRequest;

class ReplySupportTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reply' => ['required', 'string'],
            'status' => ['nullable', 'string', 'in:replied,closed'],
        ];
    }
}
