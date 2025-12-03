<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Widget extends Model
{
    use HasUuids;

    protected $fillable = [
        'uuid',
        'is_active',
        'allowed_domains',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'allowed_domains' => 'array',
    ];
}