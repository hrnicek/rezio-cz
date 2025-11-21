<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendBookingReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders to guests 3 days before check-in';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $bookings = \App\Models\Booking::where('status', 'confirmed')
            ->whereDate('start_date', '=', now()->addDays(3)->toDateString())
            ->whereNull('reminders_sent_at')
            ->with('property')
            ->get();

        $this->info("Found {$bookings->count()} bookings to remind.");

        foreach ($bookings as $booking) {
            \Illuminate\Support\Facades\Mail::to($booking->guest_email)
                ->send(new \App\Mail\BookingReminder($booking));

            $booking->update(['reminders_sent_at' => now()]);

            $this->info("Sent reminder to {$booking->guest_email} for booking #{$booking->id}");
        }
    }
}
