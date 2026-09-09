<?php

namespace App\Services\Business;

use App\Models\Center;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CenterDetailsService
{
    public function getCenterDetails(): Center
    {
        $center = currentCenter();
        if (! $center) {
            throw ValidationException::withMessages([
                'center' => ['Active center context not found.'],
            ]);
        }

        return $center->load('media');
    }

    public function updateCenterDetails(array $data): Center
    {
        $center = $this->getCenterDetails();

        return DB::transaction(function () use ($center, $data) {
            $updateData = array_filter([
                'name' => $data['name'] ?? null,
                'email' => array_key_exists('email', $data) ? $data['email'] : null,
                'phone' => $data['phone'] ?? null,
                'facebook' => array_key_exists('facebook', $data) ? $data['facebook'] : null,
                'whatsapp' => array_key_exists('whatsapp', $data) ? $data['whatsapp'] : null,
                'instagram' => array_key_exists('instagram', $data) ? $data['instagram'] : null,
                'linkedin' => array_key_exists('linkedin', $data) ? $data['linkedin'] : null,
            ], fn ($val) => $val !== null);

            $center->update($updateData);

            if (isset($data['logo']) && $data['logo'] instanceof UploadedFile) {
                $center->addMedia($data['logo'])->toMediaCollection('logo');
            }

            return $center->fresh('media');
        });
    }
}
