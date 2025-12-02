<?php

namespace App\Support;

use Akaunting\Money\Money as BaseMoney;

class Money extends BaseMoney
{
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'formatted' => $this->format(),
        ]);
    }
}
