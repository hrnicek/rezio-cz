<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\CleaningTask;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateCleaningTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleaning:generate-tasks {--days=3 : Number of days in advance to generate tasks}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates cleaning tasks for upcoming check-outs.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $checkOutDate = Carbon::today()->addDays($days);

        $bookings = Booking::where('end_date', $checkOutDate)
            ->whereDoesntHave('cleaningTask')
            ->get();

        if ($bookings->isEmpty()) {
            $this->info('No new cleaning tasks to generate for '.$checkOutDate->toDateString().'.');
            return;
        }

        foreach ($bookings as $booking) {
            CleaningTask::create([
                'booking_id' => $booking->id,
                'property_id' => $booking->property_id,
                'due_date' => $booking->end_date,
                'notes' => 'Cleaning required for booking #'.$booking->id.' at property #'.$booking->property_id.'.',
            ]);
            $this->info('Generated cleaning task for booking #'.$booking->id.'.');
        }

        $this->info('Cleaning tasks generated successfully.');
    }
}
