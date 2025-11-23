<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use App\Models\Tenant;
use Stancl\Tenancy\Facades\Tenancy;

abstract class TestCase extends BaseTestCase
{
    // protected ?Tenant $testTenant = null;

    // public function createApplication()
    // {
    //     return parent::createApplication();
    // }
}
