<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public Booking $booking)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $template = $this->booking->property->emailTemplates()
            ->where('type', 'booking_confirmation')
            ->where('is_active', true)
            ->first();

        return new Envelope(
            subject: $template ? $this->replacePlaceholders($template->subject) : 'Booking Confirmation - ' . $this->booking->property->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $template = $this->booking->property->emailTemplates()
            ->where('type', 'booking_confirmation')
            ->where('is_active', true)
            ->first();

        if ($template) {
            return new Content(
                markdown: 'emails.bookings.dynamic',
                with: [
                    'body' => $this->replacePlaceholders($template->body),
                ],
            );
        }

        return new Content(
            markdown: 'emails.bookings.confirmation',
        );
    }

    protected function replacePlaceholders(string $content): string
    {
        $placeholders = [
            '{{ customer_name }}' => $this->booking->customer ? $this->booking->customer->first_name . ' ' . $this->booking->customer->last_name : 'Guest',
            '{{ booking_code }}' => $this->booking->code,
            '{{ property_name }}' => $this->booking->property->name,
        ];

        return str_replace(array_keys($placeholders), array_values($placeholders), $content);
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
