<?php

namespace Tests\Feature\Tenant;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TenantTestCase;

class ReportTest extends TenantTestCase
{
    use RefreshDatabase;

    public function test_reports_page_can_be_rendered()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('admin.reports.index'));

        $response->assertOk();
    }

    public function test_reports_can_be_exported()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('admin.reports.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $response->assertHeader('content-disposition', 'attachment; filename=report.csv');
    }
}
