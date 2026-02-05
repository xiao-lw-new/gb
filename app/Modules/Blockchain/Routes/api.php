<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Blockchain\Http\Controllers\TransactionController;

Route::prefix('api/blockchain')->group(function () {
    Route::post('submit-tx', [TransactionController::class, 'submit']);
});

