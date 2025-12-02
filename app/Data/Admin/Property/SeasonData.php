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
        public bool $is_fixed_range,
        public int $priority,
        public int $min_stay,
        public ?array $check_in_days,
        public bool $is_default,
        public MoneyData $price_amount,
    ) {}

    public static function fromModel(Season $season): self
    {
        return new self(
            id: $season->id,
            name: $season->name,
            start_date: $season->start_date?->format('Y-m-d'),
            end_date: $season->end_date?->format('Y-m-d'),
            is_fixed_range: $season->is_fixed_range,
            priority: $season->priority,
            min_stay: $season->min_stay,
            check_in_days: $season->check_in_days,
            is_default: $season->is_default,
            price_amount: MoneyData::fromModel($season->price_amount),
        );
    }
}
