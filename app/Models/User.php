<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, InteractsWithMedia, Notifiable;

    protected $attributes = [
        'status' => 'active',
    ];

    protected $fillable = [
        'center_id',
        'name',
        'phone',
        'email',
        'password',
        'fcm_token',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')->singleFile();
    }

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }

    public function therapistProfile(): HasOne
    {
        return $this->hasOne(TherapistProfile::class);
    }

    public function patientProfile(): HasOne
    {
        return $this->hasOne(PatientProfile::class, 'user_id');
    }

    public function assignedPatients(): HasMany
    {
        return $this->hasMany(PatientProfile::class, 'therapist_id');
    }

    public function treatmentPlans(): HasMany
    {
        return $this->hasMany(TreatmentPlan::class, 'patient_id');
    }

    public function nutritionPlans(): HasMany
    {
        return $this->hasMany(NutritionPlan::class, 'patient_id');
    }

    public function assignedExercises(): HasMany
    {
        return $this->hasMany(PatientExercise::class, 'patient_id');
    }

    public function exerciseLogs(): HasMany
    {
        return $this->hasMany(PatientExerciseLog::class, 'patient_id');
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'sender_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'receiver_id');
    }
}
