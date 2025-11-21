<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\CleaningTask;
use App\Models\Property;
use App\Models\User; // Import User model
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class GenerateCleaningTasksTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that cleaning tasks are generated for upcoming check-outs.
     */
    public function test_cleaning_tasks_are_generated_for_upcoming_check_outs(): void
    {
        $user = User::factory()->create(); // Create user
        $property = Property::factory()->create(['user_id' => $user->id]); // Link property to user
        $booking1 = Booking::factory()->create(['property_id' => $property->id, 'user_id' => $user->id, 'end_date' => today()->addDays(3)]);
        $booking1->update(["status" => 'confirmed']); // Set status
        $booking2 = Booking::factory()->create(['property_id' => $property->id, 'user_id' => $user->id, 'end_date' => today()->addDays(3)]);
        $booking2->update(["status" => 'confirmed']); // Set status

        Artisan::call('cleaning:generate-tasks', ['--days' => 3]);

        $this->assertCount(2, CleaningTask::all());
    }

    /**
     * Test that cleaning tasks are not duplicated.
     */
    public function test_cleaning_tasks_are_not_duplicated(): void
    {
        $user = User::factory()->create(); // Create user
        $property = Property::factory()->create(['user_id' => $user->id]); // Link property to user
        $booking = Booking::factory()->create(['property_id' => $property->id, 'user_id' => $user->id, 'end_date' => today()->addDays(3)]);
        $booking->update(["status" => 'confirmed']); // Set status

        Artisan::call('cleaning:generate-tasks', ['--days' => 3]);
        Artisan::call('cleaning:generate-tasks', ['--days' => 3]);

        $this->assertCount(1, CleaningTask::all());
        $this->assertDatabaseHas('cleaning_tasks', [
            'booking_id' => $booking->id,
        ]);
    }

    /**
     * Test that no cleaning tasks are generated if no eligible bookings exist.
     */
    public function test_no_cleaning_tasks_are_generated_if_no_eligible_bookings_exist(): void
    {
        $user = User::factory()->create(); // Create user
        $property = Property::factory()->create(['user_id' => $user->id]); // Link property to user
        $booking = Booking::factory()->create([
            'property_id' => $property->id,
            'user_id' => $user->id,
            'end_date' => today()->addDays(10), // Too far in the future
        ]);
        $booking->update(["status" => 'confirmed']); // Set status

        Artisan::call('cleaning:generate-tasks', ['--days' => 3]);

        $this->assertCount(0, CleaningTask::all());
    }

    /**
     * Test that cleaning tasks are not generated for bookings with existing cleaning tasks.
     */
    public function test_cleaning_tasks_are_not_generated_for_bookings_with_existing_cleaning_tasks(): void
    {
        $user = User::factory()->create(); // Create user
        $property = Property::factory()->create(['user_id' => $user->id]); // Link property to user
        $booking = Booking::factory()->create([
            'property_id' => $property->id,
            'user_id' => $user->id,
            'end_date' => today()->addDays(3),
        ]);
        $booking->update(["status" => 'confirmed']); // Set status
        CleaningTask::factory()->for($booking)->for($property)->create();

        Artisan::call('cleaning:generate-tasks', ['--days' => 3]);

        $this->assertCount(1, CleaningTask::all());
    }
}
