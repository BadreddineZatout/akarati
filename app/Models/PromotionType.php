<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromotionType extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class);
    }
}
