<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientExerciseLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_exercise_id',
        'patient_id',
        'completed_sets',
        'completed_repeats',
        'duration_spent',
        'is_completed',
        'logged_at',
    ];

    protected function casts(): array
    {
        return [
            'completed_sets' => 'integer',
            'completed_repeats' => 'integer',
            'duration_spent' => 'integer',
            'is_completed' => 'boolean',
            'logged_at' => 'date',
        ];
    }

    public function patientExercise(): BelongsTo
    {
        return $this->belongsTo(PatientExercise::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
