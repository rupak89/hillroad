<?php

use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\GstSettingController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\MenuSegmentController;
use App\Http\Controllers\Api\RecipeController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\UnitTypeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::apiResource('brands', BrandController::class);

    Route::apiResource('items', ItemController::class);

    Route::apiResource('suppliers', SupplierController::class);

    Route::apiResource('groups', GroupController::class);

    // Group items route
    Route::get('/groups/{group}/items', [GroupController::class, 'items'])
        ->name('api.groups.items');

    Route::apiResource('units', UnitController::class);

    Route::apiResource('recipes', RecipeController::class);
    Route::get('/recipes-dropdown-data/{excludeRecipeId?}', [RecipeController::class, 'getDropdownData'])
        ->name('api.recipes.dropdown-data');

    // Recipe cost calculation routes
    Route::get('/recipes/{id}/cost', [RecipeController::class, 'calculateCost'])
        ->name('api.recipes.cost');
    Route::post('/recipes/{id}/cost-for-quantity', [RecipeController::class, 'calculateCostBasedOnQuantity'])
        ->name('api.recipes.cost-for-quantity');
    Route::post('/recipes/calculate-multiple-costs', [RecipeController::class, 'calculateMultipleCosts'])
        ->name('api.recipes.multiple-costs');

    Route::get('/unittypes', [UnitTypeController::class, 'index'])
        ->name('api.unittype.index');

    // Business-wide GST settings.
    Route::get('/settings/gst', [GstSettingController::class, 'show'])
        ->name('api.settings.gst.show');
    Route::put('/settings/gst', [GstSettingController::class, 'update'])
        ->name('api.settings.gst.update');

    // Menu Management Routes
    Route::apiResource('menus', MenuController::class);
    Route::post('/menus/{menu}/duplicate', [MenuController::class, 'duplicate'])
        ->name('api.menus.duplicate');
    Route::get('/menus/{menu}/cost-breakdown', [MenuController::class, 'costBreakdown'])
        ->name('api.menus.cost-breakdown');
    Route::get('/menus/{menu}/printable', [MenuController::class, 'printableMenu'])
        ->name('api.menus.printable');

    // Menu Segment Routes
    Route::post('/menu-segments', [MenuSegmentController::class, 'store'])
        ->name('api.menu-segments.store');
    Route::put('/menu-segments/{segment}', [MenuSegmentController::class, 'update'])
        ->name('api.menu-segments.update');
    Route::delete('/menu-segments/{segment}', [MenuSegmentController::class, 'destroy'])
        ->name('api.menu-segments.destroy');
    Route::post('/menu-segments/reorder', [MenuSegmentController::class, 'reorderSegments'])
        ->name('api.menu-segments.reorder');

    // Menu Segment Item Routes
    Route::post('/menu-segments/{segment}/items', [MenuSegmentController::class, 'addItem'])
        ->name('api.menu-segments.add-item');
    Route::put('/menu-segment-items/{item}', [MenuSegmentController::class, 'updateItem'])
        ->name('api.menu-segment-items.update');
    Route::delete('/menu-segment-items/{item}', [MenuSegmentController::class, 'removeItem'])
        ->name('api.menu-segment-items.destroy');
    Route::post('/menu-segments/{segment}/items/reorder', [MenuSegmentController::class, 'reorderItems'])
        ->name('api.menu-segments.reorder-items');
});

// Unknown API routes must return JSON instead of falling through to the SPA
// catch-all in routes/web.php.
Route::any('/{any}', fn () => response()->json(['message' => 'Not found'], 404))
    ->where('any', '.*');
