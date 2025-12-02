<?php

namespace App\Models\Central;

use Stancl\Tenancy\ResourceSyncing\TenantPivot;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\MaintenanceMode;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Database\Contracts\TenantWithDatabase;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains, MaintenanceMode;

    // public function users()
    // {
    //     return $this->belongsToMany(CentralUser::class, 'tenant_users', 'tenant_id', 'global_user_id', 'id', 'global_id')
    //         ->using(TenantPivot::class);
    // }

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'data',
        ];
    }
}
