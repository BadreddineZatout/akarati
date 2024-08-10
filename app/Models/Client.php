<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class, 'client_promotions');
    }

    public function profits(): HasMany
    {
        return $this->hasMany(Profit::class);
    }
}
