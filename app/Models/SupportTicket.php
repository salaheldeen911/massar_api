<?php

namespace App\Models;

use App\Traits\BelongsToCenter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicket extends Model
{
    use BelongsToCenter, HasFactory;

    protected $attributes = [
        'status' => 'open',
    ];

    protected $fillable = [
        'center_id',
        'user_id',
        'subject',
        'message',
        'reply',
        'replied_by',
        'replied_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'replied_at' => 'datetime',
        ];
    }

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function repliedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replied_by');
    }
}
