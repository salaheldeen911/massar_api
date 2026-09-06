<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NutritionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'therapist_id',
        'breakfast',
        'lunch',
        'dinner',
        'snacks',
        'supplements',
        'foods_to_avoid',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function therapist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'therapist_id');
    }
}
