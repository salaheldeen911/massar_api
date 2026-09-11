<?php

namespace App\Http\Requests\Landlord;

use App\Enums\CenterStatus;
use App\Enums\CenterSubscriptionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCenterRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'phone:AUTO,EG'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_phone' => ['required', 'string', 'phone:AUTO,EG', 'unique:users,phone'],
            'admin_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'admin_password' => ['required', 'string', 'min:8'],
            'specialty' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'therapists_count' => ['nullable', 'integer', 'min:1'],
            'branches_count' => ['nullable', 'integer', 'min:1'],
            'referral_source' => ['nullable', 'string', 'max:255'],
            'terms_accepted' => ['nullable', 'boolean'],
            'status' => ['nullable', Rule::enum(CenterStatus::class)],
            'subscription_status' => ['nullable', Rule::enum(CenterSubscriptionStatus::class)],
            'facebook' => ['nullable', 'url', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:255'],
            'instagram' => ['nullable', 'url', 'max:255'],
            'linkedin' => ['nullable', 'url', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'license_document' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
        ];
    }
}
