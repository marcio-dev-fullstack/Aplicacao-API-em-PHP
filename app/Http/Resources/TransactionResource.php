<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payer_account_id' => $this->payer_account_id,
            'payee_account_id' => $this->payee_account_id,
            'amount_cents' => $this->amount,
            'amount_formatted' => 'R$ ' . number_format($this->amount / 100, 2, ',', '.'),
            'type' => $this->type,
            'status' => $this->status,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
