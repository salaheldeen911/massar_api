<?php

namespace App\Services\Auth;

use App\Models\Center;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Register a new Center and its pending Admin User.
     */
    public function registerCenterAdmin(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $center = $this->createPendingCenter($data);
            $this->attachLicenseDocumentIfProvided($center, $data);
            $user = $this->createPendingAdminUser($center, $data);

            return [
                'center' => $center,
                'user' => $user,
            ];
        });
    }

    /**
     * Authenticate user credentials and return user + sanctum token.
     */
    public function login(array $credentials): array
    {
        $user = $this->findUserByIdentity($credentials['identity']);

        $this->verifyPassword($user, $credentials['password']);
        $this->verifyUserAndCenterStatus($user);

        $token = $this->createSanctumToken($user);

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Revoke current user authentication token.
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    /**
     * Helper: Create a new pending Center record.
     */
    private function createPendingCenter(array $data): Center
    {
        return Center::create([
            'name' => $data['center_name'],
            'phone' => $data['center_phone'],
            'specialty' => $data['specialty'] ?? null,
            'country' => $data['country'] ?? 'Egypt',
            'city' => $data['city'] ?? 'Cairo',
            'therapists_count' => $data['therapists_count'] ?? 1,
            'branches_count' => $data['branches_count'] ?? 1,
            'referral_source' => $data['referral_source'] ?? null,
            'terms_accepted' => isset($data['terms_accepted']) ? filter_var($data['terms_accepted'], FILTER_VALIDATE_BOOLEAN) : true,
            'status' => 'pending',
            'subscription_status' => 'trialing',
        ]);
    }

    /**
     * Helper: Attach license document to center via Spatie MediaLibrary if provided.
     */
    private function attachLicenseDocumentIfProvided(Center $center, array $data): void
    {
        if (isset($data['license_document']) && $data['license_document'] instanceof UploadedFile) {
            $center->addMedia($data['license_document'])
                ->toMediaCollection('license_document');
        }
    }

    /**
     * Helper: Create a new pending Admin User for the center.
     */
    private function createPendingAdminUser(Center $center, array $data): User
    {
        $user = User::create([
            'center_id' => $center->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'status' => 'pending',
        ]);

        $user->assignRole('admin');

        return $user;
    }

    /**
     * Helper: Find user by email or phone.
     */
    private function findUserByIdentity(string $identity): User
    {
        $user = User::where('email', $identity)
            ->orWhere('phone', $identity)
            ->orWhere('username', $identity)
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'identity' => [__('auth.failed')],
            ]);
        }

        return $user;
    }

    /**
     * Helper: Verify password hash.
     */
    private function verifyPassword(User $user, string $password): void
    {
        if (! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'identity' => [__('auth.failed')],
            ]);
        }
    }

    /**
     * Helper: Verify user and center account status.
     */
    private function verifyUserAndCenterStatus(User $user): void
    {
        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'identity' => ['Your account is currently ' . $user->status . '. Please contact support.'],
            ]);
        }

        if ($user->center_id && $user->center) {
            $centerStatus = $user->center->status;
            if ($centerStatus !== 'active') {
                throw ValidationException::withMessages([
                    'identity' => ['Your center is currently ' . $centerStatus . '. Please contact administrator.'],
                ]);
            }
        }
    }

    /**
     * Helper: Create Sanctum access token for user.
     */
    private function createSanctumToken(User $user): string
    {
        return $user->createToken('massar_auth_token')->plainTextToken;
    }
}
