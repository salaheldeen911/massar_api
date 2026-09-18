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
            $fields = ['name', 'email', 'phone', 'facebook', 'whatsapp', 'instagram', 'linkedin'];
            $updateData = [];

            foreach ($fields as $field) {
                if (array_key_exists($field, $data)) {
                    $updateData[$field] = $data[$field];
                }
            }

            if (! empty($updateData)) {
                $center->update($updateData);
            }

            if (isset($data['logo']) && $data['logo'] instanceof UploadedFile) {
                $center->addMedia($data['logo'])->toMediaCollection('logo');
            }

            return $center->fresh('media');
        });
    }
}
