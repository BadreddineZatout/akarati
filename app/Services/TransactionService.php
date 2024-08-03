<?php

namespace App\Services;

use App\Enums\TransactionStatusEnum;
use App\Models\Transaction;
use Filament\Notifications\Notification;

class TransactionService
{
    public function addTransaction($wallet_id, $amount)
    {
        Transaction::create([
            'issued_by' => auth()->id(),
            'amount' => $amount,
            'wallet_id' => $wallet_id,
            'status' => TransactionStatusEnum::PENDING->value,
        ]);

        return Notification::make()
            ->title('Transaction Created successfully')
            ->success()
            ->send();
    }
}
