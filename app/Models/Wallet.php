<?php

namespace App\Models;

use App\Enums\TransactionStatusEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    protected function pendingTransactionsCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->transactions()->where('status', TransactionStatusEnum::PENDING->value)->count(),
        );
    }

    public function hasEnoughBalance($amount): bool
    {
        return $this->balance >= $amount;
    }
}
