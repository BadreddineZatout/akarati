<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $guarded = [];

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
