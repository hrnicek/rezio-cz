<?php

namespace App\Services\EmailTemplates;

class DefaultTemplates
{
    public static function all(): array
    {
        return [
            'RESERVATION_CONFIRMED' => [
                'type' => 'RESERVATION_CONFIRMED',
                'trigger_reference' => 'AFTER_CREATION',
                'trigger_offset_days' => 0,
                'subject' => 'Rezervace ubytování ##objekt## byla úspěšně přijata!',
                'title' => 'Vaše rezervace úspěšně dorazila!',
                'content' => "Dobrý den, ##jmeno## ##prijmeni##,\nděkujeme, že jste si pro své další dobrodružství vybrali naše ubytování ##objekt##! S radostí potvrzujeme přijetí Vaší rezervace na ##od## - ##do##.\nV příloze naleznete ubytovací smlouvu a platební údaje.",
            ],
            'PAYMENT_REMINDER' => [
                'type' => 'PAYMENT_REMINDER',
                'trigger_reference' => 'BEFORE_DUE_DATE',
                'trigger_offset_days' => 1,
                'subject' => '##objekt## - platba za rezervaci čeká na vyřízení',
                'title' => 'Platba čeká na dokončení',
                'content' => "Čas běží a ##ucel## čeká na zaplacení!\nBlíží se termín splatnosti úhrady ##castka## za Vaši rezervaci ##objekt##.\nPomocí QR kódu nebo tlačítka platbu vyřídíte za pár vteřin.",
            ],
            'PAYMENT_RECEIVED' => [
                'type' => 'PAYMENT_RECEIVED',
                'trigger_reference' => 'ON_PAYMENT',
                'trigger_offset_days' => 0,
                'subject' => '##objekt## - potvrzení o přijetí Vaší platby',
                'title' => 'Platba přijata! Vaše dobrodružství může začít!',
                'content' => "Děkujeme ##jmeno## ##prijmeni##, ##ucel## byla zaplacena.\nJste zase o krok blíž k plánovanému dobrodružství!",
            ],
            'INVOICE_SEND' => [
                'type' => 'INVOICE_SEND',
                'trigger_reference' => 'MANUAL',
                'trigger_offset_days' => 0,
                'subject' => '##objekt## - věříme, že jste si pobyt užili.',
                'title' => 'Děkujeme za váš pobyt.',
                'content' => "Dobrý den,\nděkujeme, že jste u nás byli ubytováni. V příloze vám zasíláme fakturu za váš pobyt.\nBudeme se těšit, až vás u nás opět přivítáme.",
            ],
            'PAYMENT_OVERDUE' => [
                'type' => 'PAYMENT_OVERDUE',
                'trigger_reference' => 'AFTER_DUE_DATE',
                'trigger_offset_days' => 1,
                'subject' => '##objekt## - ajaj - rezervace po splatnosti',
                'title' => 'Ještě jste neprovedli platbu?',
                'content' => "Vážená/ý ##jmeno## ##prijmeni##,\nrádi bychom Vás informovali, že ##ucel## je již po splatnosti.\nProsíme o dokončení platby přes přiložený QR kód.",
            ],
            'RESERVATION_CANCELLED_AUTO' => [
                'type' => 'RESERVATION_CANCELLED_AUTO',
                'trigger_reference' => 'AFTER_DUE_DATE',
                'trigger_offset_days' => 2,
                'subject' => '##objekt## - automatické storno rezervace',
                'title' => 'Je nám líto, ale neuvidíme se.',
                'content' => "Dobrý den,\nna základě neuhrazení platby byla Vaše rezervace automaticky stornována.\nPokud máte stále zájem, zkuste rezervaci obnovit v systému.",
            ],
            'RESERVATION_CANCELLED_MANUAL' => [
                'type' => 'RESERVATION_CANCELLED_MANUAL',
                'trigger_reference' => 'MANUAL',
                'trigger_offset_days' => 0,
                'subject' => '##objekt## - zrušení rezervace',
                'title' => 'Vaše rezervace byla stornována',
                'content' => "Je nám líto, ale neuvidíme se. Rezervaci ##objekt## jsme na základě žádosti zrušili.",
            ],
            'BEFORE_ARRIVAL_INFO' => [
                'type' => 'BEFORE_ARRIVAL_INFO',
                'trigger_reference' => 'BEFORE_ARRIVAL',
                'trigger_offset_days' => 3,
                'subject' => 'Už se těšíte? Máme pro Vás důležité informace!',
                'title' => 'Dobrodružství je už na dosah!',
                'content' => "S radostí očekáváme Váš příjezd do ##objekt##!\nZde jsou instrukce k příjezdu a Váš přístupový kód:\n##pin_kod##\nTěšíme se na Vás!",
            ],
        ];
    }

    public static function get(string $type): ?array
    {
        return self::all()[$type] ?? null;
    }
}
