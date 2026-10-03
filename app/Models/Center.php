<?php

namespace App\Models;

use App\Enums\CenterStatus;
use App\Enums\CenterSubscriptionStatus;
use App\Enums\CenterType;
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
        'type' => 'institution',
        'country' => 'Egypt',
        'therapists_count' => 1,
        'branches_count' => 1,
        'terms_accepted' => true,
        'status' => 'pending',
        'subscription_status' => 'trialing',
        'primary_color' => '#012053',
        'secondary_color' => '#F05023',
    ];

    protected $fillable = [
        'name',
        'type',
        'phone',
        'email',
        'specialty',
        'country',
        'city',
        'therapists_count',
        'branches_count',
        'referral_source',
        'terms_accepted',
        'status',
        'rejection_reason',
        'trial_starts_at',
        'trial_ends_at',
        'subscription_status',
        'facebook',
        'whatsapp',
        'instagram',
        'linkedin',
        'primary_color',
        'secondary_color',
    ];

    protected function casts(): array
    {
        return [
            'therapists_count' => 'integer',
            'branches_count' => 'integer',
            'terms_accepted' => 'boolean',
            'trial_starts_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'type' => CenterType::class,
            'status' => CenterStatus::class,
            'subscription_status' => CenterSubscriptionStatus::class,
        ];
    }

    public function isIndividual(): bool
    {
        return $this->type === CenterType::INDIVIDUAL;
    }

    public function isInstitution(): bool
    {
        return $this->type === CenterType::INSTITUTION;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile();
        $this->addMediaCollection('license_document')->singleFile();
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
