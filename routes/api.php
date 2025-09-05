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

    // Menu Management Routes
    Route::apiResource('menus', App\Http\Controllers\Api\MenuController::class);
    Route::post('/menus/{menu}/duplicate', [App\Http\Controllers\Api\MenuController::class, 'duplicate'])
        ->name('api.menus.duplicate');
    Route::get('/menus/{menu}/cost-breakdown', [App\Http\Controllers\Api\MenuController::class, 'costBreakdown'])
        ->name('api.menus.cost-breakdown');
    Route::get('/menus/{menu}/printable', [App\Http\Controllers\Api\MenuController::class, 'printableMenu'])
        ->name('api.menus.printable');

    // Menu Segment Routes
    Route::post('/menu-segments', [App\Http\Controllers\Api\MenuSegmentController::class, 'store'])
        ->name('api.menu-segments.store');
    Route::put('/menu-segments/{segment}', [App\Http\Controllers\Api\MenuSegmentController::class, 'update'])
        ->name('api.menu-segments.update');
    Route::delete('/menu-segments/{segment}', [App\Http\Controllers\Api\MenuSegmentController::class, 'destroy'])
        ->name('api.menu-segments.destroy');
    Route::post('/menu-segments/reorder', [App\Http\Controllers\Api\MenuSegmentController::class, 'reorderSegments'])
        ->name('api.menu-segments.reorder');

    // Menu Segment Item Routes
    Route::post('/menu-segments/{segment}/items', [App\Http\Controllers\Api\MenuSegmentController::class, 'addItem'])
        ->name('api.menu-segments.add-item');
    Route::put('/menu-segment-items/{item}', [App\Http\Controllers\Api\MenuSegmentController::class, 'updateItem'])
        ->name('api.menu-segment-items.update');
    Route::delete('/menu-segment-items/{item}', [App\Http\Controllers\Api\MenuSegmentController::class, 'removeItem'])
        ->name('api.menu-segment-items.destroy');
    Route::post('/menu-segments/{segment}/items/reorder', [App\Http\Controllers\Api\MenuSegmentController::class, 'reorderItems'])
        ->name('api.menu-segments.reorder-items');
});



