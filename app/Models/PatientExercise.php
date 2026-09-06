<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PatientExercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'exercise_id',
        'assigned_by',
        'sets',
        'repeats',
        'duration',
        'notes',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sets' => 'integer',
            'repeats' => 'integer',
            'duration' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PatientExerciseLog::class);
    }
}
