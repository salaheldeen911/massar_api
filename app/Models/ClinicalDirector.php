<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ClinicalDirector extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $attributes = [
        'sort_order' => 0,
    ];

    protected $fillable = [
        'name',
        'title',
        'bio',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photo')->singleFile();
    }
}
