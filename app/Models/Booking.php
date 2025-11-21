<?php

namespace App\Models;

use Spatie\ModelStates\HasStates;
use Spatie\Activitylog\LogOptions;
use App\States\Booking\BookingState;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Booking extends Model
{
    /** @use HasFactory<\Database\Factories\BookingFactory> */
    use HasFactory, SoftDeletes, HasStates, LogsActivity;

    public const ALLOWED_STATUSES = ['pending', 'confirmed', 'cancelled', 'paid', 'blocked'];

    protected static function booted(): void
    {
        static::deleted(function (Booking $booking) {
            $booking->cleaningTask()->delete();
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'code',
        'property_id',
        'user_id',
        'customer_id',
        'season_id',
        'start_date',
        'end_date',
        'date_start',
        'date_end',
        'total_price',
        'status',
        'notes',
        'reminders_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'date_start' => 'datetime',
            'date_end' => 'datetime',
            'total_price' => 'decimal:2',
            'status' => BookingState::class,
            'reminders_sent_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function bookingPayment(): HasOne
    {
        return $this->hasOne(BookingPayment::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'booking_services')
            ->withPivot(['quantity', 'price_total'])
            ->withTimestamps();
    }

    public function cleaningTask(): HasOne
    {
        return $this->hasOne(CleaningTask::class);
    }
}
