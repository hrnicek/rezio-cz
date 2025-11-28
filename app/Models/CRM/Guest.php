<?php

namespace App\Models\CRM;

use App\Models\Booking\Booking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Guest extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'guests';

    protected $fillable = [
        'booking_id',
        'first_name', 'last_name', 'is_adult',
        'gender', 'nationality', 'document_type', 'document_number',
        'birth_date', 'birth_place', 'address', 'signature'
    ];

    protected $casts = [
        'is_adult' => 'boolean',
        'birth_date' => 'date',
        'address' => 'array', // JSON cast
    ];

    // --- RELATIONS ---

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}