<?php

namespace App\Models;

use App\Traits\BelongsToCenter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NutritionPlan extends Model
{
    use BelongsToCenter, HasFactory;

    protected $fillable = [
        'center_id',
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
