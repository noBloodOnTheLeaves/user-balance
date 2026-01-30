<?php

namespace Tests\Feature\Finance;

use App\Models\User;
use App\Models\UserBalanceTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserBalanceTransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get('/finance/transactions');

        $response->assertRedirect('/login');
    }

    public function test_transactions_can_be_searched_and_sorted(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        UserBalanceTransaction::create([
            'user_id' => $user->id,
            'amount' => 50,
            'type' => 'debit',
            'description' => 'Coffee',
            'balance_after' => 950,
            'created_at' => now()->subDays(3),
        ]);
        UserBalanceTransaction::create([
            'user_id' => $user->id,
            'amount' => 200,
            'type' => 'credit',
            'description' => 'Salary',
            'balance_after' => 1150,
            'created_at' => now()->subDays(2),
        ]);
        UserBalanceTransaction::create([
            'user_id' => $user->id,
            'amount' => 10,
            'type' => 'debit',
            'description' => 'Fee',
            'balance_after' => 940,
            'created_at' => now()->subDay(),
        ]);
        UserBalanceTransaction::create([
            'user_id' => $otherUser->id,
            'amount' => 5,
            'type' => 'debit',
            'description' => 'Fee',
            'balance_after' => 995,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->getJson('/finance/transactions?search=e&sort=amount&direction=asc');

        $response->assertOk();
        $response->assertJsonPath('meta.total', 2);
        $response->assertJsonPath('data.0.description', 'Fee');
        $response->assertJsonPath('data.1.description', 'Coffee');
    }
}
