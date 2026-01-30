<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class UserCreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create
        {name : Имя пользователя}
        {login : Логин пользователя}
        {email : Email пользователя}
        {password : Пароль}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создание пользователя через CLI';

    /**
     * Execute the console command.
     */
    public function handle(CreatesNewUsers $userCreator)
    {
        $input = [
            'name' => $this->argument('name'),
            'login' => $this->argument('login'),
            'email' => $this->argument('email'),
            'password' => $this->argument('password'),
            'password_confirmation' => $this->argument('password'),
        ];

        try {
            $user = $userCreator->create($input);
        } catch (ValidationException $e) {
            foreach ($e->errors() as $messages) {
                foreach ($messages as $message) {
                    $this->error($message);
                }
            }

            return self::FAILURE;
        }

        $user->balance()->create(['amount' => 0]);

        $this->info("Пользователь {$user->email} создан");

        return self::SUCCESS;
    }
}
