<?php

namespace App\Http\Requests\Traits;

trait NormalizesPhone
{
    /**
     * Prepare the data for validation by normalizing phone numbers to international format.
     */
    protected function prepareForValidation(): void
    {
        $country = $this->input('country');
        $merges = [];

        if ($this->has('phone') && is_string($this->input('phone'))) {
            $merges['phone'] = normalizePhoneNumber($this->input('phone'), $country);
        }

        if ($this->has('admin_phone') && is_string($this->input('admin_phone'))) {
            $merges['admin_phone'] = normalizePhoneNumber($this->input('admin_phone'), $country);
        }

        if ($this->has('whatsapp') && is_string($this->input('whatsapp'))) {
            $merges['whatsapp'] = normalizePhoneNumber($this->input('whatsapp'), $country);
        }

        if (! empty($merges)) {
            $this->merge($merges);
        }
    }
}
