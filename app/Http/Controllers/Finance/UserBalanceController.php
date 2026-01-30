<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserBalanceResource;
use App\Services\BalanceService;
use Illuminate\Http\Request;

class UserBalanceController extends Controller
{
    public function show(
        Request $request,
        BalanceService $service
    ): UserBalanceResource {
        return new UserBalanceResource(
            $service->getBalanceWithLastTransactions($request->user())
        );
    }
}
