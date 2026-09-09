<?php

namespace App\Models;

use App\Traits\BelongsToCenter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Exercise extends Model implements HasMedia
{
    use BelongsToCenter, HasFactory, InteractsWithMedia;

    protected $fillable = [
        'center_id',
        'therapist_id',
        'title',
        'default_sets',
        'default_repeats',
        'default_duration',
        'therapist_notes',
    ];

    protected function casts(): array
    {
        return [
            'default_sets' => 'integer',
            'default_repeats' => 'integer',
            'default_duration' => 'integer',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('video')->singleFile();
    }

    public function therapist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'therapist_id');
    }

    public function patientExercises(): HasMany
    {
        return $this->hasMany(PatientExercise::class);
    }
}
