<?php

declare(strict_types=1);


namespace App\Repositories\Eloquent;

use App\Models\UserBalance;
use App\Repositories\Interfaces\UserBalanceRepositoryInterface;

class UserBalanceRepository implements UserBalanceRepositoryInterface
{
    public function getByUserId(int $userId): UserBalance
    {
        return UserBalance::where('user_id', $userId)->lockForUpdate()->firstOrFail();
    }

    public function updateAmount(int $userId, float $amount): UserBalance
    {
        $balance = $this->getByUserId($userId);
        $balance->update(['amount' => $amount]);

        return $balance;
    }
}
