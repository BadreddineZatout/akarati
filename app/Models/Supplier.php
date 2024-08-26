<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Supplier extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function name(): Attribute
    {
        return Attribute::make(
            get: fn () => "$this->first_name $this->last_name"
        );
    }

    public function invoices(): MorphMany
    {
        return $this->morphMany(Invoice::class, 'invoicable');
    }
}
