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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Booking\Folio, \App\Models\Finance\Payment>
     */
    public function folio(): BelongsTo
    {
        return $this->belongsTo(Folio::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Booking\Booking, \App\Models\Finance\Payment>
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\Finance\Invoice, \App\Models\Finance\Payment>
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }
}
