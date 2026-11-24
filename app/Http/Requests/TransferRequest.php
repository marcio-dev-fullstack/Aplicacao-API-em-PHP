<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payer_account_id' => ['required', 'uuid', 'exists:accounts,id'],
            'payee_account_id' => ['required', 'uuid', 'exists:accounts,id', 'different:payer_account_id'],
            'amount' => ['required', 'integer', 'gt:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.gt' => 'O valor da transferência deve ser maior que zero (em centavos).',
            'payee_account_id.different' => 'A conta de destino deve ser diferente da conta de origem.',
        ];
    }
}
