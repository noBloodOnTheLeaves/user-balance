<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\BalanceService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class ProcessUserBalanceOperation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly int $userId,
        private readonly float $amount,
        private readonly string $type,
        private readonly string $description
    ) {}

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle(BalanceService $balanceService): void
    {
        $user = User::query()->findOrFail($this->userId);

        $balanceService->changeBalance(
            user: $user,
            amount: $this->amount,
            type: $this->type,
            description: $this->description
        );

        Cache::forget("user:{$user->id}:balance");
    }
}
