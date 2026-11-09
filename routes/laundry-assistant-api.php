<?php

use App\Http\Controllers\BixbyLaundryController;
use Illuminate\Support\Facades\Route;

Route::post('/laundry-assistant/bixby', BixbyLaundryController::class)
    ->middleware('throttle:laundry-bixby')->name('assistant.bixby');
