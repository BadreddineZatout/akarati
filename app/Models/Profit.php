<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profit extends Model
{
    protected $guarded = [];

    protected $casts = [
        'paid_at' => 'date',
    ];

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function paidTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_to');
    }
}
