<?php

namespace Tests\Feature;

use App\Models\Central\Tenant;
use App\Models\Communication\EmailTemplate;
use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EmailTemplateTest extends TestCase
{
    // use RefreshDatabase; // Can be tricky with tenancy, let's manage manually or rely on tenant creation

    protected $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a tenant for testing
        // We use a random ID to avoid conflicts
        $this->tenant = Tenant::create([
            'id' => 'test_' . uniqid(),
        ]);

        // Initialize tenancy
        tenancy()->initialize($this->tenant);

        // Run migrations for the tenant (if not auto-run)
        // Usually tenancy package handles this if configured, but let's be safe or check config.
        // Assuming standard setup where creation triggers DB creation & migration.
        // If not, we might need Artisan::call('tenants:migrate');
        
        // No seeding needed for virtual defaults
    }

    protected function tearDown(): void
    {
        // Cleanup tenant (and its DB)
        if ($this->tenant) {
            $this->tenant->delete();
        }
        
        parent::tearDown();
    }

    public function test_default_templates_are_virtual()
    {
        // Ensure DB is empty of templates
        $this->assertDatabaseCount('email_templates', 0);

        // Fetch a default template
        $template = EmailTemplate::findForProperty('RESERVATION_CONFIRMED', null);
        
        $this->assertNotNull($template);
        $this->assertNull($template->id); // It's virtual, so no ID
        $this->assertNull($template->property_id);
        $this->assertEquals('AFTER_CREATION', $template->trigger_reference);
    }

    public function test_fallback_logic()
    {
        // 1. Create a property
        $property = Property::create([
            'name' => 'Test Property',
            'slug' => 'test-property',
        ]);

        // 2. Fetch template for property (should return virtual default)
        $found = EmailTemplate::findForProperty('RESERVATION_CONFIRMED', $property->id);
        $this->assertNotNull($found);
        $this->assertNull($found->id); // Virtual
        $this->assertNull($found->property_id); // Default data has no property_id

        // 3. Create override for this property
        $override = EmailTemplate::create([
            'property_id' => $property->id,
            'type' => 'RESERVATION_CONFIRMED',
            'subject' => 'Custom Subject',
            'title' => 'Custom Title',
            'content' => 'Custom Content',
            'trigger_reference' => 'AFTER_CREATION',
            'trigger_offset_days' => 0,
        ]);

        // 4. Fetch again (should return override from DB)
        $foundOverride = EmailTemplate::findForProperty('RESERVATION_CONFIRMED', $property->id);
        $this->assertEquals($override->id, $foundOverride->id);
        $this->assertEquals('Custom Subject', $foundOverride->subject);

        // 5. Fetch for another property (should still return virtual default)
        $otherPropertyId = $property->id + 1;
        $foundDefault = EmailTemplate::findForProperty('RESERVATION_CONFIRMED', $otherPropertyId);
        $this->assertNull($foundDefault->id);
    }
}
