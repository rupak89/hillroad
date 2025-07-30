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

    Route::apiResource('recipes', App\Http\Controllers\Api\RecipeController::class);
    Route::get('/recipes-dropdown-data/{excludeRecipeId?}', [App\Http\Controllers\Api\RecipeController::class, 'getDropdownData'])
        ->name('api.recipes.dropdown-data');

    // Recipe cost calculation routes
    Route::get('/recipes/{id}/cost', [App\Http\Controllers\Api\RecipeController::class, 'calculateCost'])
        ->name('api.recipes.cost');
    Route::post('/recipes/{id}/cost-per-serving', [App\Http\Controllers\Api\RecipeController::class, 'calculateCostPerServing'])
        ->name('api.recipes.cost-per-serving');
    Route::post('/recipes/calculate-multiple-costs', [App\Http\Controllers\Api\RecipeController::class, 'calculateMultipleCosts'])
        ->name('api.recipes.multiple-costs');

    Route::get('/unittypes', [App\Http\Controllers\Api\UnitTypeController::class, 'index'])
        ->name('api.unittype.index');
});



