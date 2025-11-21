<x-mail::message>
    # New Booking Alert

    You have a new booking for **{{ $booking->property->name }}**.

    **Guest Details:**
    - **Name:** {{ $booking->guest_info['name'] }}
    - **Email:** {{ $booking->guest_info['email'] }}
    - **Phone:** {{ $booking->guest_info['phone'] ?? 'N/A' }}

    **Booking Details:**
    - **Check-in:** {{ $booking->start_date->format('M d, Y') }}
    - **Check-out:** {{ $booking->end_date->format('M d, Y') }}
    - **Total Price:** ${{ number_format($booking->total_price, 2) }}
    - **Status:** {{ $booking->status }}

    <x-mail::button :url="route('bookings.index')">
        Manage Bookings
    </x-mail::button>

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>