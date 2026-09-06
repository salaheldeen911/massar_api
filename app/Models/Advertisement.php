<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Advertisement extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $attributes = [
        'status' => 'active',
    ];

    protected $fillable = [
        'title',
        'description',
        'start_at',
        'expire_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'date',
            'expire_at' => 'date',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('banner')->singleFile();
    }
}
