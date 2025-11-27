<?php

namespace App\Enums;

enum ServicePriceType: string
{
    case PerPerson = 'per_person';
    case PerNight = 'per_night';
    case PerDay = 'per_day'; // Legacy support
    case PerStay = 'per_stay';
    case Fixed = 'fixed';
    case Flat = 'flat'; // Legacy support
    case PerHour = 'per_hour';

    public function label(): string
    {
        return match ($this) {
            self::PerPerson => 'Za osobu',
            self::PerNight, self::PerDay => 'Za noc',
            self::PerStay => 'Za pobyt',
            self::Fixed, self::Flat => 'Fixní',
            self::PerHour => 'Za hodinu',
        };
    }
}
