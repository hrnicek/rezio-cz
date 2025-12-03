<?php

namespace App\Http\Resources\Tenant\Widgets\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WidgetReservationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'success' => true,
            'booking_id' => $this->id,
            'booking_code' => $this->code,
            'total_price' => $this->total_price_amount,
            'currency' => $this->currency,
        ];
    }
}
