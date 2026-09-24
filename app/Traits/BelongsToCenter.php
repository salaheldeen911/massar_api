<?php

namespace App\Traits;

use App\Models\Center;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToCenter
{
    /**
     * Boot the trait to apply global center scoping and auto-assignment.
     */
    protected static function bootBelongsToCenter(): void
    {
        static::addGlobalScope('center_scope', function (Builder $builder) {
            if (! isLandlord()) {
                $centerId = currentCenterId();
                if ($centerId !== null) {
                    $table = $builder->getQuery()->from;
                    $builder->where(function ($query) use ($table, $centerId) {
                        $query->where($table . '.center_id', $centerId)
                              ->orWhereNull($table . '.center_id');
                    });
                }
            }
        });

        static::creating(function ($model) {
            if ($model instanceof User) {
                return;
            }

            if (empty($model->center_id)) {
                $centerId = currentCenterId();
                if ($centerId !== null) {
                    $model->center_id = $centerId;
                } else {
                    if (! empty($model->patient_id)) {
                        $model->center_id = User::where('id', $model->patient_id)->value('center_id');
                    } elseif (! empty($model->user_id)) {
                        $model->center_id = User::where('id', $model->user_id)->value('center_id');
                    }
                }
            }
        });
    }

    /**
     * Get the Center that owns the model.
     */
    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }
}