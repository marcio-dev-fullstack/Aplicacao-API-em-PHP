<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccountResource;
use App\Http\Resources\TransactionResource;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AccountController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $accounts = $request->user()->accounts;

        return response()->json([
            'status' => 'success',
            'data' => AccountResource::collection($accounts)
        ]);
    }

    public function balance(Account $account): JsonResponse
    {
        Gate::authorize('view', $account);

        return response()->json([
            'status' => 'success',
            'data' => new AccountResource($account)
        ]);
    }

    public function statement(Account $account, Request $request): JsonResponse
    {
        Gate::authorize('view', $account);

        $transactions = Transaction::where('payer_account_id', $account->id)
            ->orWhere('payee_account_id', $account->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => TransactionResource::collection($transactions),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'total' => $transactions->total(),
            ]
        ]);
    }
}
