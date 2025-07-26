<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/user-test', function (Request $request) {
    return response()->json([
        'session' => session()->all(),
        'user' => $request->user(),
        'cookies' => request()->cookies->all(),
    ]);
})->middleware('auth:sanctum');

Route::get('/session-test-api', function (Request $request) {
    return session()->all();
})->middleware('auth:sanctum');

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::apiResource('brands', App\Http\Controllers\Api\BrandController::class);

    Route::apiResource('items', App\Http\Controllers\Api\ItemController::class);

    Route::apiResource('suppliers', App\Http\Controllers\Api\SupplierController::class);

    Route::apiResource('groups', App\Http\Controllers\Api\GroupController::class);

    Route::apiResource('units', App\Http\Controllers\Api\UnitController::class);

    Route::get('/unittypes', [App\Http\Controllers\Api\UnitTypeController::class, 'index'])
        ->name('api.unittype.index');
});



