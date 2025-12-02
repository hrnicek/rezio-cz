<?php

namespace App\Models\Finance;

use App\Enums\PaymentMethod;
use App\Models\Booking\Booking;
use App\Models\Booking\Folio;
use App\States\Payment\PaymentState;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\ModelStates\HasStates;

class Payment extends Model
{
    use HasFactory, HasStates, HasUuids;

    protected $table = 'booking_payments';

    protected $fillable = [
        'booking_id', 'folio_id',
        'amount', 'currency',
        'status', 'payment_method', 'gateway', 'transaction_id',
        'paid_at', 'notes',
    ];

    protected $casts = [
        'amount' => \App\Casts\MoneyCast::class,
        'paid_at' => 'datetime',
        'status' => PaymentState::class,
        'payment_method' => PaymentMethod::class,
    ];

    protected $dispatchesEvents = [
        'created' => \App\Events\Payment\PaymentCreated::class,
        'updated' => \App\Events\Payment\PaymentUpdated::class,
        'deleted' => \App\Events\Payment\PaymentDeleted::class,
    ];

    // --- RELATIONS ---

    public function folio(): BelongsTo
    {
        return $this->belongsTo(Folio::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }
}
