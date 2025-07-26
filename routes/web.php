<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/login', [AuthController::class, 'login'])
    ->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::get('/session-test', function (Request $request) {
    session(['test' => 'value']);
    return session()->all();
});

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');


