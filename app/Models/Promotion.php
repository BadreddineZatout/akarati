<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Promotion extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function promotion_type(): BelongsTo
    {
        return $this->belongsTo(PromotionType::class,'promotion_type_id');
    }
    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class,'block_id');
    }
}
