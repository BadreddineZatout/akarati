<?php

namespace App\Models;

use App\Enums\ProfitStateEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientPromotion extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'state' => ProfitStateEnum::class,
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }
}
