<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function fullname(): Attribute
    {
        return Attribute::make(
            get: fn () => "$this->first_name $this->last_name",
        );
    }

    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class, 'client_promotions');
    }

    public function profits(): HasMany
    {
        return $this->hasMany(Profit::class);
    }
}
