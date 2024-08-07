<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Block extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function project(): BelongsTo
    {
     return $this->belongsTo(Project::class);
    }
    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class);
    }
}
