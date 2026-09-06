<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreatmentPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'therapist_id',
        'manual_therapy',
        'manual_therapy_date',
        'electrotherapy',
        'electrotherapy_date',
        'medications',
        'goals',
    ];

    protected function casts(): array
    {
        return [
            'manual_therapy_date' => 'date',
            'electrotherapy_date' => 'date',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function therapist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'therapist_id');
    }
}
