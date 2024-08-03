<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $guarded = [];

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
