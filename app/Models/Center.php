<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Multitenancy\Models\Tenant;

class Center extends Tenant implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = 'centers';

    protected $attributes = [
        'country' => 'Egypt',
        'status' => 'pending',
        'subscription_status' => 'trialing',
    ];

    protected $fillable = [
        'name',
        'phone',
        'specialty',
        'country',
        'city',
        'status',
        'trial_starts_at',
        'trial_ends_at',
        'subscription_status',
        'facebook',
        'whatsapp',
        'instagram',
        'linkedin',
    ];

    protected function casts(): array
    {
        return [
            'trial_starts_at' => 'datetime',
            'trial_ends_at' => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function patientProfiles(): HasMany
    {
        return $this->hasMany(PatientProfile::class);
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(Exercise::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }
}
