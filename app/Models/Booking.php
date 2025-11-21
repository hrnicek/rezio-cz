<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'user_id',
        'start_date',
        'end_date',
        'guest_info',
        'total_price',
        'status',
        'notes',
        'reminders_sent_at',
    ];

    public const ALLOWED_STATUSES = ['pending', 'confirmed', 'cancelled', 'paid', 'blocked'];

    protected $casts = [
        'guest_info' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'total_price' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'status' => 'string',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cleaningTask(): HasOne
    {
        return $this->hasOne(CleaningTask::class);
    }
}
