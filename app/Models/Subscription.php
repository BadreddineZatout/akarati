<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends \Laravelcm\Subscriptions\Models\Subscription
{
    use HasFactory;

    protected $guarded = [];
}
