<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExerciseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'default_sets' => ['nullable', 'integer', 'min:1'],
            'default_repeats' => ['nullable', 'integer', 'min:1'],
            'default_duration' => ['nullable', 'integer', 'min:1'],
            'therapist_notes' => ['nullable', 'string'],
            'is_center_public' => ['nullable', 'boolean'],
            'video' => ['nullable', 'file', 'mimes:mp4,mov,avi,webm,mkv,m4v,jpeg,png,jpg,webp,gif', 'max:102400'],
            'media' => ['nullable', 'file', 'mimes:mp4,mov,avi,webm,mkv,m4v,jpeg,png,jpg,webp,gif', 'max:102400'],
            'file' => ['nullable', 'file', 'mimes:mp4,mov,avi,webm,mkv,m4v,jpeg,png,jpg,webp,gif', 'max:102400'],
        ];
    }
}
