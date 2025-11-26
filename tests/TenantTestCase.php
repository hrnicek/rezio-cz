<?php

namespace Tests;

use App\Models\Central\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TenantTestCase extends TestCase
{
    protected $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->initializeTenancy();
    }

    protected function initializeTenancy()
    {
        $this->tenant = Tenant::create([
            'id' => 'test_' . str()->random(10),
        ]);

        $this->tenant->domains()->create([
            'domain' => $this->tenant->id . '.localhost',
        ]);

        tenancy()->initialize($this->tenant);

        \Illuminate\Support\Facades\URL::forceRootUrl('http://' . $this->tenant->domains->first()->domain);
    }

    protected function tearDown(): void
    {
        if ($this->tenant) {
            $this->tenant->delete();
        }

        parent::tearDown();
    }
}
