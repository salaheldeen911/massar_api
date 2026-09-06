<?php

namespace App\Traits;

use App\Models\Center;
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
                    $builder->where($builder->getQuery()->from . '.center_id', $centerId);
                }
            }
        });

        static::creating(function ($model) {
            if (empty($model->center_id) && ! isLandlord()) {
                $centerId = currentCenterId();
                if ($centerId !== null) {
                    $model->center_id = $centerId;
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
