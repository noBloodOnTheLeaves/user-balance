<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Finance\UserBalanceTransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBalanceTransaction extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'amount',
        'type',
        'description',
        'balance_after',
        'created_at',
    ];

    protected $casts = [
        'type' => UserBalanceTransactionType::class,
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
