# Upcoming Stay Reminder

Dear {{ $booking->guest_name }},

We are looking forward to welcoming you to **{{ $booking->property->name }}** on **{{ $booking->start_date->format('F j, Y') }}**.

Here are your booking details:
- **Check-in:** {{ $booking->start_date->format('F j, Y') }}
- **Check-out:** {{ $booking->end_date->format('F j, Y') }}
- **Guests:** {{ $booking->guests ?? 'N/A' }}

If you have any questions, please reply to this email.

Safe travels!

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>