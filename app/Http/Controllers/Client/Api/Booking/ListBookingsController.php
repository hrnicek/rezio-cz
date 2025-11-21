<?php

namespace App\Http\Controllers\Client\Api\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListBookingsController extends Controller
{
    public function __invoke(Request $request, string $token): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', 'string'],
            'with_services' => ['nullable', 'boolean'],
        ]);

        $property = Property::where('widget_token', $token)->firstOrFail();

        $query = Booking::query()
            ->where('property_id', $property->id)
            ->where('status', '!=', 'cancelled');

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (!empty($validated['start_date']) && !empty($validated['end_date'])) {
            $start = \Carbon\Carbon::parse($validated['start_date'])->startOfDay();
            $end = \Carbon\Carbon::parse($validated['end_date'])->startOfDay();
            $query->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($qq) use ($start, $end) {
                        $qq->where('start_date', '<', $start)
                            ->where('end_date', '>', $end);
                    });
            });
        }

        if (!empty($validated['with_services'])) {
            $query->with(['services' => function ($q) {
                $q->select('services.id', 'services.name');
            }]);
        }

        $bookings = $query->latest()->get(['id', 'start_date', 'end_date', 'status', 'total_price']);

        return response()->json([
            'bookings' => $bookings->map(function ($b) use ($validated) {
                $base = [
                    'id' => $b->id,
                    'start_date' => $b->start_date?->toDateString(),
                    'end_date' => $b->end_date?->toDateString(),
                    'status' => $b->status,
                    'total_price' => $b->total_price,
                ];

                if (!empty($validated['with_services'])) {
                    $base['services'] = ($b->services ?? collect())->map(function ($s) {
                        return [
                            'id' => $s->id,
                            'name' => $s->name,
                            'quantity' => (int) ($s->pivot->quantity ?? 0),
                            'price_total' => (float) ($s->pivot->price_total ?? 0),
                        ];
                    })->values();
                }

                return $base;
            }),
        ]);
    }
}
