<?php

namespace App\Services;

use App\Models\Wallet;

class WalletService
{
    public function addAmount(Wallet $wallet, $amount)
    {
        $wallet->increment('balance', $amount);
    }

    public function subAmount(Wallet $wallet, $amount)
    {
        $wallet->decrement('balance', $amount);
    }
}
