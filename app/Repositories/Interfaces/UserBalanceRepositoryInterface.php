<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Models\User;
use App\Models\UserBalance;

interface UserBalanceRepositoryInterface
{
    public function getByUserId(int $userId): UserBalance;

    public function updateAmount(int $userId, float $amount): UserBalance;

    //public function getBalanceWithLastOperations(User $user): UserBalance;
}
