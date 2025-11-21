<x-mail::message>
    # Booking Confirmation

    Dear {{ $booking->customer->first_name }} {{ $booking->customer->last_name }},

    Thank you for your booking at **{{ $booking->property->name }}**.

    **Booking Details:**
    - **Check-in:** {{ $booking->start_date->format('M d, Y') }}
    - **Check-out:** {{ $booking->end_date->format('M d, Y') }}
    - **Total Price:** ${{ number_format($booking->total_price, 2) }}

    We have received your request and it is currently **{{ $booking->status }}**. We will notify you once it is confirmed.

    <x-mail::button :url="config('app.url')">
        View Booking
    </x-mail::button>

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>