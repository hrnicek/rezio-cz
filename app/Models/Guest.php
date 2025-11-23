<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Guest extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'first_name',
        'last_name',
        'is_adult',
        'gender',
        'nationality',
        'document_type',
        'document_number',
        'birth_date',
        'birth_place',
        'address',
        'signature',
    ];

    protected $casts = [
        'is_adult' => 'boolean',
        'birth_date' => 'date',
        'address' => 'array',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
