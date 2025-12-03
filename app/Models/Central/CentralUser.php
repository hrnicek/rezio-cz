<?php

namespace App\Models\Central;

use App\Models\User as TenantUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Concerns\CentralConnection;
use Stancl\Tenancy\ResourceSyncing\ResourceSyncing;
use Stancl\Tenancy\ResourceSyncing\SyncMaster;

class CentralUser extends Model implements SyncMaster
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    use CentralConnection, ResourceSyncing;

    public $table = 'users';

    protected $fillable = [
        'global_id',
        'name',
        'email',
        'password',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->global_id)) {
                $model->global_id = (string) Str::uuid();
            }
        });
    }

    /**
     * Class name of the tenant resource model.
     *
     * In our example, the tenant resource is TenantUser.
     */
    public function getTenantModelName(): string
    {
        return TenantUser::class;
    }

    /**
     * Class name of the related central resource model.
     *
     * Since the class we're in is actually the central
     * resource, we can just return static::class.
     */
    public function getCentralModelName(): string
    {
        return static::class;
    }

    /**
     * List of all attributes to keep in sync with the other resource
     * (meaning the tenant model described in the other tab above).
     *
     * When this resource gets updated, the other resource's columns
     * with the same names will automatically be updated too.
     */
    public function getSyncedAttributeNames(): array
    {
        return [
            'global_id',
            'name',
            'password',
            'email',
        ];
    }
}
