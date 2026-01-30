<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserBalanceTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'amount' => (float) $this->amount,
            'description' => $this->description,
            'balance_after' => (float) $this->balance_after,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
