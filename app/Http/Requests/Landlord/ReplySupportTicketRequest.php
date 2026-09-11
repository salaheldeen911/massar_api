<?php

namespace App\Http\Requests\Landlord;

use App\Enums\SupportTicketStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'status' => ['nullable', Rule::enum(SupportTicketStatus::class)],
        ];
    }
}
