<x-mail::message>
    # {{ $subject ?? '' }}

    {!! nl2br(e($body)) !!}

    <x-mail::button :url="config('app.url')">
        View Booking
    </x-mail::button>

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>