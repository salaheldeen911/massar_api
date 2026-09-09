<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'receiver_id' => ['required', 'integer', 'exists:users,id'],
            'message' => ['required_without:attachment', 'nullable', 'string', 'max:5000'],
            'attachment' => ['required_without:message', 'nullable', 'file', 'mimes:jpg,jpeg,png,pdf,mp4,m4a,wav,mp3', 'max:10240'],
        ];
    }
}
