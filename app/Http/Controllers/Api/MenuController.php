<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Menu\CreateMenuDTO;
use App\DTOs\Menu\UpdateMenuDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Menu\StoreMenuRequest;
use App\Http\Requests\Menu\UpdateMenuRequest;
use App\Http\Resources\MenuResource;
use App\Models\Menu;
use App\Services\MenuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function __construct(
        private readonly MenuService $menuService
    ) {}

    /**
     * Display a listing of the user's menus.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $menus = $this->menuService->getMenusForUser(
            $request->user(),
            (int) ($validated['per_page'] ?? 10)
        );

        // Keep the paginator shape while serialising each menu explicitly.
        $menus->through(fn (Menu $menu) => (new MenuResource($menu))->resolve());

        return response()->json([
            'message' => 'List of menus',
            'menus' => $menus,
        ]);
    }

    /**
     * Store a newly created menu.
     */
    public function store(StoreMenuRequest $request): JsonResponse
    {
        $dto = CreateMenuDTO::fromRequest($request->validated(), $request->user()->id);
        $menu = $this->menuService->createMenu($dto);

        return response()->json([
            'message' => 'Menu created successfully',
            'menu' => new MenuResource($menu),
        ], 201);
    }

    /**
     * Display the specified menu.
     */
    public function show(Menu $menu): JsonResponse
    {
        $this->authorize('view', $menu);

        $menu = $this->menuService->getMenuWithRelations($menu);

        return response()->json([
            'message' => 'Menu details',
            'menu' => new MenuResource($menu),
        ]);
    }

    /**
     * Update the specified menu.
     */
    public function update(UpdateMenuRequest $request, Menu $menu): JsonResponse
    {
        $this->authorize('update', $menu);

        $dto = UpdateMenuDTO::fromRequest($request->validated());
        $menu = $this->menuService->updateMenu($menu, $dto);

        return response()->json([
            'message' => 'Menu updated successfully',
            'menu' => new MenuResource($menu),
        ]);
    }

    /**
     * Remove the specified menu.
     */
    public function destroy(Menu $menu): JsonResponse
    {
        $this->authorize('delete', $menu);

        $menu->delete();

        return response()->json([
            'message' => 'Menu deleted successfully',
        ]);
    }

    /**
     * Duplicate a menu.
     */
    public function duplicate(Menu $menu): JsonResponse
    {
        $this->authorize('duplicate', $menu);

        $duplicatedMenu = $this->menuService->duplicateMenu($menu);

        return response()->json([
            'message' => 'Menu duplicated successfully',
            'menu' => new MenuResource($duplicatedMenu),
        ], 201);
    }

    /**
     * Get menu cost breakdown.
     */
    public function costBreakdown(Menu $menu): JsonResponse
    {
        $this->authorize('viewCostBreakdown', $menu);

        $breakdown = $this->menuService->getCostBreakdown($menu);

        return response()->json([
            'message' => 'Menu cost breakdown',
            'breakdown' => $breakdown,
        ]);
    }

    /**
     * Get customer-friendly printable menu.
     */
    public function printableMenu(Menu $menu): JsonResponse
    {
        $this->authorize('print', $menu);

        $printableMenu = $this->menuService->getPrintableMenu($menu);

        return response()->json([
            'message' => 'Customer printable menu',
            'menu' => $printableMenu,
        ]);
    }
}
