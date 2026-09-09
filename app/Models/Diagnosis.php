<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Diagnosis extends Model
{
    use HasFactory;

    protected $table = 'diagnoses';

    protected $fillable = [
        'name',
        'code',
        'category_letter',
    ];

    public function patientProfiles(): HasMany
    {
        return $this->hasMany(PatientProfile::class, 'diagnosis_id');
    }
}
