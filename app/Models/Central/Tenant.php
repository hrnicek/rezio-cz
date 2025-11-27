<?php

namespace App\Models\Central;

use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    /**
     * Custom columns that won't be stored in the data JSON column
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'plan',           // e.g., 'free', 'pro', 'enterprise'
            'trial_ends_at',  // Trial expiration date
        ];
    }

    /**
     * Casts for custom columns
     */
    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
        ];
    }
}
