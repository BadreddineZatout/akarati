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

    public function acceptTransaction(Transaction $transaction)
    {
        $transaction->update([
            'status' => TransactionStatusEnum::ACCEPTED->value,
        ]);

        $transaction->wallet->update([
            'balance' => $transaction->wallet->balance + $transaction->amount,
        ]);

        return Notification::make()
            ->title('Transaction Accepted successfully')
            ->success()
            ->send();
    }

    public function refuseTransaction(Transaction $transaction)
    {
        $transaction->update([
            'status' => TransactionStatusEnum::REFUSED->value,
        ]);

        return Notification::make()
            ->title('Transaction Refused')
            ->danger()
            ->send();
    }
}
