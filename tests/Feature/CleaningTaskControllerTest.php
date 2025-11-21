<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\CleaningTask;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class CleaningTaskControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that an authenticated user can view the cleaning tasks index page.
     */
    public function test_authenticated_user_can_view_cleaning_tasks_index(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('admin.cleaning-tasks.index'));

        $response->assertOk();
        $response->assertInertia(fn($page) => $page->component('Admin/CleaningTasks/Index'));
    }

    /**
     * Test that cleaning tasks are displayed on the index page.
     */
    public function test_cleaning_tasks_are_displayed_on_index_page(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);
        $booking = Booking::factory()->create(['property_id' => $property->id, 'user_id' => $user->id]);
        $booking->update(["status" => 'confirmed']); // Set a status for the booking
        $task = CleaningTask::factory()->create(['booking_id' => $booking->id, 'property_id' => $property->id]);

        $response = $this->actingAs($user)->get(route('admin.cleaning-tasks.index'));

        $response->assertOk();
        $response->assertInertia(
            fn($page) => $page
                ->component('Admin/CleaningTasks/Index')
                ->has('cleaningTasks.data', 1)
                ->where('cleaningTasks.data.0.id', $task->id)
        );
    }

    /**
     * Test that an authenticated user can mark a cleaning task as complete.
     */
    public function test_authenticated_user_can_mark_cleaning_task_as_complete(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);
        $booking = Booking::factory()->create(['property_id' => $property->id, 'user_id' => $user->id]);
        $booking->update(["status" => 'confirmed']); // Set a status for the booking
        $task = CleaningTask::factory()->create(['booking_id' => $booking->id, 'property_id' => $property->id, 'completed_at' => null]);

        $response = $this->actingAs($user)
            ->post(route('admin.cleaning-tasks.complete', $task));

        $response->assertRedirect(route('admin.cleaning-tasks.index'));
        $this->assertNotNull($task->fresh()->completed_at);
        $this->assertDatabaseHas('cleaning_tasks', [
            'id' => $task->id,
        ]);
    }

    /**
     * Test that an unauthenticated user cannot view the cleaning tasks index page.
     */
    public function test_unauthenticated_user_cannot_view_cleaning_tasks_index(): void
    {
        $response = $this->get(route('admin.cleaning-tasks.index'));
        $response->assertRedirect('/login');
    }

    /**
     * Test that an unauthenticated user cannot mark a cleaning task as complete.
     */
    public function test_unauthenticated_user_cannot_mark_cleaning_task_as_complete(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);
        $booking = Booking::factory()->create(['property_id' => $property->id, 'user_id' => $user->id]);
        $booking->update(["status" => 'confirmed']); // Set a status for the booking
        $task = CleaningTask::factory()->create(['booking_id' => $booking->id, 'property_id' => $property->id, 'completed_at' => null]);

        $response = $this->post(route('admin.cleaning-tasks.complete', $task));
        $response->assertRedirect('/login');
        $this->assertNull($task->fresh()->completed_at);
    }
}
