<?php

namespace Tests\Unit;

use App\Models\Configuration\Season;
use App\Rules\Booking\FullSeasonBookingRule;
use App\Rules\Booking\MinPersonsRule;
use Carbon\Carbon;
use Tests\TestCase;

class SeasonBookingRulesTest extends TestCase
{
    public function test_min_persons_rule()
    {
        $season = new Season([
            'min_persons' => 3,
        ]);
        $season->id = 1; // Mock ID

        $rule = new MinPersonsRule();

        // Should fail with 2 persons
        try {
            $rule->validate(now(), now()->addDay(), $season, 2);
            $this->fail('Should have thrown exception');
        } catch (\Exception $e) {
            $this->assertEquals('Minimum number of persons for this period is 3.', $e->getMessage());
        }

        // Should pass with 3 persons
        $rule->validate(now(), now()->addDay(), $season, 3);

        // Should pass with 4 persons
        $rule->validate(now(), now()->addDay(), $season, 4);
    }

    public function test_full_season_booking_rule()
    {
        $season = new Season([
            'is_full_season_booking_only' => true,
            'start_date' => '2025-06-01',
            'end_date' => '2025-08-31',
        ]);
        // Set attributes that are cast
        $season->start_date = \Illuminate\Support\Facades\Date::parse('2025-06-01');
        $season->end_date = \Illuminate\Support\Facades\Date::parse('2025-08-31');

        $rule = new FullSeasonBookingRule();

        // Scenario 1: Booking matches exactly
        $start = \Illuminate\Support\Facades\Date::parse('2025-06-01');
        $end = \Illuminate\Support\Facades\Date::parse('2025-08-31');
        $rule->validate($start, $end, $season);

        // Scenario 2: Booking is shorter
        try {
            $start = \Illuminate\Support\Facades\Date::parse('2025-06-01');
            $end = \Illuminate\Support\Facades\Date::parse('2025-06-10');
            $rule->validate($start, $end, $season);
            $this->fail('Should have thrown exception for shorter booking');
        } catch (\Exception $e) {
            $this->assertStringContainsString('This season can only be booked as a whole', $e->getMessage());
        }

        // Scenario 3: Booking starts later
        try {
            $start = \Illuminate\Support\Facades\Date::parse('2025-06-02');
            $end = \Illuminate\Support\Facades\Date::parse('2025-09-01'); // Same duration but shifted
            $rule->validate($start, $end, $season);
            $this->fail('Should have thrown exception for wrong start date');
        } catch (\Exception $e) {
            $this->assertStringContainsString('Booking must start on the first day of the season', $e->getMessage());
        }
    }

    public function test_season_recurring_matching()
    {
        $season = new Season([
            'is_recurring' => true,
        ]);
        $season->start_date = \Illuminate\Support\Facades\Date::parse('2020-06-01'); // Defined in 2020
        $season->end_date = \Illuminate\Support\Facades\Date::parse('2020-08-31');

        // Check match in 2025
        $date = \Illuminate\Support\Facades\Date::parse('2025-07-15');
        $this->assertTrue($season->matchesDate($date), 'Should match recurring season in future year');

        $date = \Illuminate\Support\Facades\Date::parse('2025-01-15');
        $this->assertFalse($season->matchesDate($date), 'Should not match date outside season in future year');
        
        // Check cross-year season (e.g. Dec 20 to Jan 10)
        $season->start_date = \Illuminate\Support\Facades\Date::parse('2020-12-20');
        $season->end_date = \Illuminate\Support\Facades\Date::parse('2021-01-10');
        
        $date = \Illuminate\Support\Facades\Date::parse('2025-12-25');
        $this->assertTrue($season->matchesDate($date), 'Should match recurring cross-year season in December');
        
        $date = \Illuminate\Support\Facades\Date::parse('2026-01-05');
        $this->assertTrue($season->matchesDate($date), 'Should match recurring cross-year season in January');
        
        $date = \Illuminate\Support\Facades\Date::parse('2025-11-01');
        $this->assertFalse($season->matchesDate($date), 'Should not match before season start');
    }
}
