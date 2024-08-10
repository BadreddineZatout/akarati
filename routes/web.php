<?php

use App\Http\Controllers\PromotionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('promotions/{promotion}', [PromotionController::class, 'destroy'])->name('promotions.destroy');

});
