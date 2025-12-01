<?php

namespace App\Models\Finance;

use App\Models\Booking\Booking;
use App\Models\Booking\Folio;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'booking_payments';

    protected $fillable = [
        'booking_id', 'folio_id',
        'amount', 'currency',
        'status', 'payment_method', 'gateway', 'transaction_id',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'paid_at' => 'datetime',
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
}
