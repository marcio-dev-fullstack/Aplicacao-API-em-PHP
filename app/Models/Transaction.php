<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'payer_account_id',
        'payee_account_id',
        'amount',
        'type',
        'status',
        'idempotency_key',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    public function payerAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'payer_account_id');
    }

    public function payeeAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'payee_account_id');
    }
}
