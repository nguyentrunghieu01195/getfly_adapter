<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VietQRController;

Route::prefix('vqr')->group(function(){
    Route::post('api/token_generate', [VietQRController::class, 'tokenGenerateAction']);
    Route::post('bank/api/transaction-sync', [VietQRController::class, 'transactionSyncAction']);
});
