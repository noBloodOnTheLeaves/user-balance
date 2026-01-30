<?php

declare(strict_types=1);

namespace App\Enums\Finance;

enum UserBalanceTransactionType: string
{
    case CREDIT = 'credit';
    case DEBIT = 'debit';

    public function label(): string
    {
        return match ($this) {
            self::CREDIT => 'Начисление',
            self::DEBIT => 'Списание',
        };
    }
}
