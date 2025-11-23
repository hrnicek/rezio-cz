<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\CleaningTask;
use App\Models\Property;
use App\Models\User; // Import User model
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TenantTestCase;

class CleaningTaskTest extends TenantTestCase
{
    use WithFaker;

    /**
     * Test that a cleaning task can be created.
     */
    public function test_cleaning_task_can_be_created(): void
    {
        $user = User::factory()->create(); //Create user
        $property = Property::factory()->create(); // Link property to user
        $booking = Booking::factory()->create(['property_id' => $property->id, 'user_id' => $user->id]); // Link booking to property and user
        $booking->update(["status" => 'confirmed']); // Set a status for the booking

        $dueDate = now()->addDays(5)->toDateString();

        $task = CleaningTask::factory()->for($booking)->for($property)->create([
            'due_date' => $dueDate,
            'notes' => 'Test cleaning notes.',
        ]);


        $this->assertDatabaseHas('cleaning_tasks', [
            'id' => $task->id,
            'booking_id' => $booking->id,
            'property_id' => $property->id,
            'notes' => 'Test cleaning notes.',
            'completed_at' => null,
        ]);

        // Check due date separately since SQLite stores it with time
        $this->assertEquals($dueDate, $task->due_date->toDateString());

        $this->assertTrue($task->booking->is($booking));
        $this->assertTrue($task->property->is($property));
    }

    /**
     * Test the completed state of a cleaning task.
     */
    public function test_cleaning_task_can_be_marked_as_completed(): void
    {
        $user = User::factory()->create(); // Create user
        $property = Property::factory()->create(); // Link property to user
        $booking = Booking::factory()->create(['property_id' => $property->id, 'user_id' => $user->id]); // Link booking to property and user
        $booking->update(["status" => 'confirmed']); // Set a status for the booking

        $task = CleaningTask::factory()->for($booking)->for($property)->completed()->create(); // Ensure task is linked

        $this->assertNotNull($task->completed_at);
        $this->assertDatabaseHas('cleaning_tasks', [
            'id' => $task->id,
            'completed_at' => $task->completed_at,
        ]);
    }

    /**
     * Test that a cleaning task is deleted when its booking is deleted.
     */
    public function test_cleaning_task_is_deleted_when_booking_is_deleted(): void
    {
        $user = User::factory()->create(); // Create user
        $property = Property::factory()->create(); // Link property to user
        $booking = Booking::factory()->create(['property_id' => $property->id, 'user_id' => $user->id]); // Link booking to property and user
        $booking->update(["status" => 'confirmed']); // Set a status for the booking

        $task = CleaningTask::factory()->for($booking)->for($property)->create(); // Ensure task is linked

        $this->assertDatabaseHas('cleaning_tasks', ['id' => $task->id]);

        $booking->delete();

        $this->assertDatabaseMissing('cleaning_tasks', ['id' => $task->id]);
    }
}
