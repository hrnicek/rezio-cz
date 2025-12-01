<?php

namespace App\Enums;

enum InvoiceType: string
{
    case Proforma = 'proforma';
    case Invoice = 'invoice';
    case PaymentConfirmation = 'payment_confirmation';
    case CreditNote = 'credit_note';

    public function label(): string
    {
        return match ($this) {
            self::Proforma => 'Zálohová faktura',
            self::Invoice => 'Faktura',
            self::PaymentConfirmation => 'Potvrzení platby',
            self::CreditNote => 'Dobropis',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Proforma => 'blue',
            self::Invoice => 'green',
            self::PaymentConfirmation => 'indigo',
            self::CreditNote => 'red',
        };
    }
}
