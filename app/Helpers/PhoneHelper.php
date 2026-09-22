<?php

namespace App\Helpers;

use libphonenumber\PhoneNumberType;
use Propaganistas\LaravelPhone\PhoneNumber;

class PhoneHelper
{
    /**
     * Candidate country codes for regional auto-detection (prioritized for Massar API).
     */
    protected static array $candidateCountries = [
        'EG', 'SA', 'AE', 'KW', 'QA', 'OM', 'BH', 'JO', 'LB', 'IQ', 'YE', 'SY', 'SD', 'LY', 'TN', 'DZ', 'MA',
    ];

    /**
     * Normalize a phone number string to E.164 standard format.
     * Automatically deduces the country for local numbers without requiring '+' or country code.
     */
    public static function normalize(?string $phone, ?string $defaultCountry = null): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return $phone;
        }

        $cleaned = trim($phone);

        // Convert leading double zero (00) to plus (+)
        if (str_starts_with($cleaned, '00')) {
            $cleaned = '+' . substr($cleaned, 2);
        }

        // If the number already has an international prefix (+), parse directly
        if (str_starts_with($cleaned, '+')) {
            try {
                $instance = new PhoneNumber($cleaned);
                if ($instance->isValid()) {
                    return $instance->formatE164();
                }
            } catch (\Throwable) {
                // Ignore parsing errors and fall through
            }

            return $cleaned;
        }

        // If an explicit country context is provided (e.g. center country), try it first
        if ($defaultCountry) {
            try {
                $instance = new PhoneNumber($cleaned, $defaultCountry);
                if ($instance->isValid()) {
                    return $instance->formatE164();
                }
            } catch (\Throwable) {
                // Fall through to candidates
            }
        }

        // First pass: Match mobile numbers across candidate countries
        foreach (self::$candidateCountries as $country) {
            try {
                $instance = new PhoneNumber($cleaned, $country);
                if ($instance->isValid() && $instance->isOfType(PhoneNumberType::MOBILE)) {
                    return $instance->formatE164();
                }
            } catch (\Throwable) {
                continue;
            }
        }

        // Second pass: Match any valid phone number (e.g. landline) across candidate countries
        foreach (self::$candidateCountries as $country) {
            try {
                $instance = new PhoneNumber($cleaned, $country);
                if ($instance->isValid()) {
                    return $instance->formatE164();
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return $cleaned;
    }
}
