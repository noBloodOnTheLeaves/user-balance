<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\ProcessUserBalanceOperation;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class UserBalanceTransactionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'balance:operate
        {login : Логин пользователя}
        {type : credit|debit}
        {amount : Сумма операции}
        {description : Описание операции}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Проведение операции по балансу пользователя';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $data = [
            'login' => $this->argument('login'),
            'type' => $this->argument('type'),
            'amount' => $this->argument('amount'),
            'description' => $this->argument('description'),
        ];

        $validator = Validator::make($data, [
            'login' => ['required', 'exists:users,login'],
            'type' => ['required', 'in:credit,debit'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::where('login', $data['login'])->firstOrFail();

        ProcessUserBalanceOperation::dispatch(
            userId: $user->id,
            amount: (float) $data['amount'],
            type: $data['type'],
            description: $data['description']
        );

        $this->info('Операция поставлена в очередь');

        return self::SUCCESS;
    }
}
