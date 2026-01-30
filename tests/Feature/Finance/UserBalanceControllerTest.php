<?php

namespace Tests\Feature\Finance;

use App\Models\User;
use App\Models\UserBalance;
use App\Models\UserBalanceTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class UserBalanceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get('/finance/balance');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_receives_balance_and_recent_transactions(): void
    {
        $user = User::factory()->create();
        UserBalance::create([
            'user_id' => $user->id,
            'amount' => 125.50,
        ]);

        $baseTime = now()->subDays(6);
        for ($i = 1; $i <= 6; $i++) {
            UserBalanceTransaction::create([
                'user_id' => $user->id,
                'amount' => 10 * $i,
                'type' => 'credit',
                'description' => "Transaction {$i}",
                'balance_after' => 100 + $i,
                'created_at' => $baseTime->copy()->addDays($i),
            ]);
        }

        Cache::flush();

        $response = $this->actingAs($user)->getJson('/finance/balance');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'balance',
                'transactions',
            ],
        ]);
        $response->assertJsonPath('data.balance', '125.50');

        $transactions = $response->json('data.transactions');
        $this->assertCount(5, $transactions);
        $this->assertSame('Transaction 6', $transactions[0]['description']);
        $this->assertSame('Transaction 2', $transactions[4]['description']);
    }
}
