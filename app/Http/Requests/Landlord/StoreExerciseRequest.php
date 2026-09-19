<?php

namespace App\Http\Requests\Landlord;

use Illuminate\Foundation\Http\FormRequest;

class StoreExerciseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole('landlord') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'center_id' => ['nullable', 'integer', 'exists:centers,id'],
            'therapist_id' => ['nullable', 'integer', 'exists:users,id'],
            'default_sets' => ['nullable', 'integer', 'min:1'],
            'default_repeats' => ['nullable', 'integer', 'min:1'],
            'default_duration' => ['nullable', 'integer', 'min:1'],
            'therapist_notes' => ['nullable', 'string'],
            'video' => ['nullable', 'file', 'mimes:mp4,mov,avi,webm,mkv,m4v,jpeg,png,jpg,webp,gif', 'max:51200'],
            'exercise_media' => ['nullable', 'file', 'mimes:mp4,mov,avi,webm,mkv,m4v,jpeg,png,jpg,webp,gif', 'max:51200'],
            'media' => ['nullable', 'file', 'mimes:mp4,mov,avi,webm,mkv,m4v,jpeg,png,jpg,webp,gif', 'max:51200'],
            'file' => ['nullable', 'file', 'mimes:mp4,mov,avi,webm,mkv,m4v,jpeg,png,jpg,webp,gif', 'max:51200'],
        ];
    }
}
