<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promotion extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function fullname(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->block->project->name.'-'.$this->block->name.'-'.$this->name,
        );
    }

    public function promotion_type(): BelongsTo
    {
        return $this->belongsTo(PromotionType::class, 'promotion_type_id');
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class, 'block_id');
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_promotions');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function supplier_invoices(): HasMany
    {
        return $this->hasMany(Invoice::class)->has('supplier');
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Invoice::class)->whereNull('supplier_id');
    }
}
