<?php

use App\Models\Center;
use App\Models\User;

if (! function_exists('currentUser')) {
    /**
     * Get the currently authenticated user.
     */
    function currentUser(): ?User
    {
        return auth()->user();
    }
}

if (! function_exists('currentCenterId')) {
    /**
     * Get the current center ID from the authenticated user.
     */
    function currentCenterId(): ?int
    {
        $user = currentUser();

        return $user ? $user->center_id : null;
    }
}

if (! function_exists('currentCenter')) {
    /**
     * Get the current Center model from the authenticated user.
     */
    function currentCenter(): ?Center
    {
        $user = currentUser();

        if (! $user || ! $user->center_id) {
            return null;
        }

        return $user->center;
    }
}

if (! function_exists('isLandlord')) {
    /**
     * Check if the currently authenticated user has the landlord role.
     */
    function isLandlord(): bool
    {
        $user = currentUser();

        if (! $user) {
            return false;
        }

        return $user->hasRole('landlord');
    }
}

if (! function_exists('normalizePhoneNumber')) {
    /**
     * Normalize a phone number to international E.164 format with automatic country deduction.
     */
    function normalizePhoneNumber(?string $phone, ?string $defaultCountry = null): ?string
    {
        return \App\Helpers\PhoneHelper::normalize($phone, $defaultCountry);
    }
}

