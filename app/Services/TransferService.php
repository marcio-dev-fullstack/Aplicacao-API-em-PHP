<?php

namespace App\Services;

use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\InvalidTransactionException;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransferService
{
    public function execute(string $payerAccountId, string $payeeAccountId, int $amount, ?string $idempotencyKey = null): Transaction
    {
        if ($payerAccountId === $payeeAccountId) {
            throw new InvalidTransactionException('A conta de origem e destino não podem ser iguais.');
        }

        if ($amount <= 0) {
            throw new InvalidTransactionException('O valor da transferência deve ser maior que zero.');
        }

        if ($idempotencyKey) {
            $existingTx = Transaction::where('idempotency_key', $idempotencyKey)->first();
            if ($existingTx) {
                return $existingTx;
            }
        }

        return DB::transaction(function () use ($payerAccountId, $payeeAccountId, $amount, $idempotencyKey) {
            $firstId = min($payerAccountId, $payeeAccountId);
            $secondId = max($payerAccountId, $payeeAccountId);

            $accounts = Account::whereIn('id', [$firstId, $secondId])
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $payer = $accounts->get($payerAccountId);
            $payee = $accounts->get($payeeAccountId);

            if (!$payer || !$payee) {
                throw new InvalidTransactionException('Uma ou ambas as contas informadas não existem.');
            }

            if (!$payer->is_active || !$payee->is_active) {
                throw new InvalidTransactionException('Uma das contas bancárias está inativa.');
            }

            if ($payer->balance < $amount) {
                throw new InsufficientBalanceException("Saldo insuficiente. Saldo disponível: R$ " . number_format($payer->balance / 100, 2, ',', '.'));
            }

            $payer->decrement('balance', $amount);
            $payee->increment('balance', $amount);

            $transaction = Transaction::create([
                'payer_account_id' => $payer->id,
                'payee_account_id' => $payee->id,
                'amount' => $amount,
                'type' => 'transfer',
                'status' => 'completed',
                'idempotency_key' => $idempotencyKey,
            ]);

            Log::info("Transferência realizada com sucesso: Transaction ID {$transaction->id}, Valor: centavos {$amount}");

            return $transaction;
        });
    }

    public function deposit(string $account_id, int $amount): Transaction
    {
        if ($amount <= 0) {
            throw new InvalidTransactionException('O valor do depósito deve ser positivo.');
        }

        return DB::transaction(function () use ($account_id, $amount) {
            $account = Account::where('id', $account_id)->lockForUpdate()->firstOrFail();
            $account->increment('balance', $amount);

            return Transaction::create([
                'payer_account_id' => null,
                'payee_account_id' => $account->id,
                'amount' => $amount,
                'type' => 'deposit',
                'status' => 'completed',
            ]);
        });
    }
}
