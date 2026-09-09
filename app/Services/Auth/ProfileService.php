<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileService
{
    public function updateProfile(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $userData = array_filter([
                'name' => $data['name'] ?? null,
                'email' => array_key_exists('email', $data) ? $data['email'] : null,
                'phone' => $data['phone'] ?? null,
            ], fn ($val) => $val !== null);

            if (! empty($userData)) {
                $user->update($userData);
            }

            if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
                $user->addMedia($data['avatar'])->toMediaCollection('avatar');
            }

            return $user->fresh(['media', 'center']);
        });
    }

    public function changePassword(User $user, array $data): void
    {
        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided current password does not match your account password.'],
            ]);
        }

        DB::transaction(function () use ($user, $data) {
            $user->update([
                'password' => bcrypt($data['new_password']),
            ]);
        });
    }
}
