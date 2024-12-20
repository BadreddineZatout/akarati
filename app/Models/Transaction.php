<?php

namespace App\Models;

use App\Enums\TransactionStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $guarded = [];

    protected $casts = [
        'status' => TransactionStatusEnum::class,
    ];

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by', 'id');
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}
