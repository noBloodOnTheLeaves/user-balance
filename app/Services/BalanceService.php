<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Finance\UserBalanceTransactionType;
use App\Models\User;
use App\Repositories\Interfaces\UserBalanceRepositoryInterface;
use DomainException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

readonly class BalanceService
{
    public function __construct(
        private UserBalanceRepositoryInterface $balanceRepository
    ) {}

    /**
     * @throws \Throwable
     */
    public function changeBalance(
        User $user,
        float $amount,
        string $type,
        string $description
    ): void {
        DB::transaction(function () use ($user, $amount, $type, $description) {

            $balance = $this->balanceRepository->getByUserId($user->id);

            $newAmount = $type === UserBalanceTransactionType::CREDIT->value
                ? $balance->amount + $amount
                : $balance->amount - $amount;

            if ($newAmount < 0) {
                throw new DomainException('Недостаточно средств');
            }

            $this->balanceRepository->updateAmount($user->id, $newAmount);

            $user->transactions()->create([
                'amount' => $amount,
                'type' => $type,
                'description' => $description,
                'balance_after' => $newAmount,
            ]);
        });
    }

    public function getBalanceWithLastTransactions(User $user): array
    {
        return Cache::remember(
            key: "user:{$user->id}:balance",
            ttl: now()->addSeconds(10),
            callback: fn () => [
                'balance' => $user->balance->amount,
                'transactions' => $user->transactions()
                    ->latest()
                    ->limit(5)
                    ->get(),
            ]
        );
    }
}
