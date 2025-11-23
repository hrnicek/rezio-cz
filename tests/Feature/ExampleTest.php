<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TenantTestCase;

class ExampleTest extends TenantTestCase
{

    public function test_returns_a_successful_response()
    {
        $response = $this->get(route('welcome'));

        $response->assertStatus(200);
    }
}
