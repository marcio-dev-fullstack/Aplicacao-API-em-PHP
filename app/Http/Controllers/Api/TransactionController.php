<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransferRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Account;
use App\Models\Transaction;
use App\Services\TransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TransactionController extends Controller
{
    public function __construct(
        protected TransferService $transferService
    ) {}

    public function transfer(TransferRequest $request): JsonResponse
    {
        $payerAccount = Account::findOrFail($request->payer_account_id);
        Gate::authorize('transferFrom', $payerAccount);

        $idempotencyKey = $request->header('X-Idempotency-Key');

        $transaction = $this->transferService->execute(
            payerAccountId: $request->payer_account_id,
            payeeAccountId: $request->payee_account_id,
            amount: (int) $request->amount,
            idempotencyKey: $idempotencyKey
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Transferência realizada com sucesso.',
            'data' => new TransactionResource($transaction)
        ], 201);
    }

    public function deposit(Request $request): JsonResponse
    {
        $request->validate([
            'account_id' => ['required', 'uuid', 'exists:accounts,id'],
            'amount' => ['required', 'integer', 'gt:0'],
        ]);

        $account = Account::findOrFail($request->account_id);
        Gate::authorize('view', $account);

        $transaction = $this->transferService->deposit($account->id, (int) $request->amount);

        return response()->json([
            'status' => 'success',
            'message' => 'Depósito realizado com sucesso.',
            'data' => new TransactionResource($transaction)
        ]);
    }

    public function show(Transaction $transaction, Request $request): JsonResponse
    {
        $userAccountIds = $request->user()->accounts()->pluck('id')->toArray();
        if (!in_array($transaction->payer_account_id, $userAccountIds) && !in_array($transaction->payee_account_id, $userAccountIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso não autorizado a esta transação.'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => new TransactionResource($transaction)
        ]);
    }
}
