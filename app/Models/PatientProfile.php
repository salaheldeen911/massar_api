<?php

namespace App\Models;

use App\Traits\BelongsToCenter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PatientProfile extends Model implements HasMedia
{
    use BelongsToCenter, HasFactory, InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'center_id',
        'therapist_id',
        'diagnosis_id',
        'birth_date',
        'current_week',
        'patient_history',
        'chief_complain',
        'diagnosis',
        'special_tests_notes',
        'objective_findings',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'current_week' => 'integer',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('special_tests')->singleFile();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function therapist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'therapist_id');
    }

    public function diagnosisModel(): BelongsTo
    {
        return $this->belongsTo(Diagnosis::class, 'diagnosis_id');
    }
}
