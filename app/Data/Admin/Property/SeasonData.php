<?php

namespace App\Data\Admin\Property;

use App\Data\Shared\MoneyData;
use App\Models\Configuration\Season;
use Spatie\LaravelData\Data;

class SeasonData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $start_date,
        public ?string $end_date,
        public int $priority,
        public int $min_stay,
        public int $min_persons,
        public bool $is_default,
        public MoneyData $price_amount,
        public bool $is_full_season_booking_only,
        public bool $is_recurring,
    ) {}

    public static function fromModel(Season $season): self
    {
        return new self(
            id: $season->id,
            name: $season->name,
            start_date: $season->start_date?->format('Y-m-d'),
            end_date: $season->end_date?->format('Y-m-d'),
            priority: $season->priority,
            min_stay: $season->min_stay,
            min_persons: $season->min_persons,
            is_default: $season->is_default,
            price_amount: MoneyData::fromModel($season->price_amount),
            is_full_season_booking_only: $season->is_full_season_booking_only,
            is_recurring: $season->is_recurring,
        );
    }
}
