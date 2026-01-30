<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\UserBalanceTransactionIndexRequest;
use App\Http\Resources\UserBalanceTransactionResource;
use Illuminate\Http\Request;

class UserBalanceTransactionController extends Controller
{
    public function index(UserBalanceTransactionIndexRequest $request)
    {
        $query = $request->user()
            ->transactions()
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(
                    'description',
                    'like',
                    "%{$request->search}%"
                );
            })
            ->orderBy(
                $request->get('sort', 'created_at'),
                $request->get('direction', 'desc')
            );

        return UserBalanceTransactionResource::collection(
            $query->paginate(5)
        );
    }
}
