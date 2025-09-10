<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Menu\CreateMenuDTO;
use App\DTOs\Menu\UpdateMenuDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Menu\StoreMenuRequest;
use App\Http\Requests\Menu\UpdateMenuRequest;
use App\Models\Menu;
use App\Services\MenuService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    public function __construct(
        private readonly MenuService $menuService
    ) {}

    /**
     * Check if the user owns the menu
     */
    private function checkOwnership(Menu $menu, Request $request): ?JsonResponse
    {
        if ($menu->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        return null;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 10);
        $menus = $this->menuService->getMenusForUser($request->user(), $perPage);

        return response()->json([
            'message' => 'List of menus',
            'menus' => $menus,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMenuRequest $request): JsonResponse
    {
        $dto = CreateMenuDTO::fromRequest($request->validated(), $request->user()->id);
        $menu = $this->menuService->createMenu($dto);

        return response()->json([
            'message' => 'Menu created successfully',
            'menu' => $menu,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Menu $menu): JsonResponse
    {
        if ($error = $this->checkOwnership($menu, $request)) {
            return $error;
        }

        $menu = $this->menuService->getMenuWithRelations($menu);

        return response()->json([
            'message' => 'Menu details',
            'menu' => $menu,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMenuRequest $request, Menu $menu): JsonResponse
    {
        if ($error = $this->checkOwnership($menu, $request)) {
            return $error;
        }

        $dto = UpdateMenuDTO::fromRequest($request->validated());
        $menu = $this->menuService->updateMenu($menu, $dto);

        return response()->json([
            'message' => 'Menu updated successfully',
            'menu' => $menu,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Menu $menu): JsonResponse
    {
        if ($error = $this->checkOwnership($menu, $request)) {
            return $error;
        }

        $menu->delete();

        return response()->json([
            'message' => 'Menu deleted successfully',
        ]);
    }

    /**
     * Duplicate a menu
     */
    public function duplicate(Request $request, Menu $menu): JsonResponse
    {
        if ($error = $this->checkOwnership($menu, $request)) {
            return $error;
        }

        $duplicatedMenu = $this->menuService->duplicateMenu($menu);

        return response()->json([
            'message' => 'Menu duplicated successfully',
            'menu' => $duplicatedMenu,
        ], 201);
    }

    /**
     * Get menu cost breakdown
     */
    public function costBreakdown(Request $request, Menu $menu): JsonResponse
    {
        if ($error = $this->checkOwnership($menu, $request)) {
            return $error;
        }

        $breakdown = $this->menuService->getCostBreakdown($menu);

        return response()->json([
            'message' => 'Menu cost breakdown',
            'breakdown' => $breakdown,
        ]);
    }

    /**
     * Get customer-friendly printable menu
     */
    public function printableMenu(Request $request, Menu $menu): JsonResponse
    {
        if ($error = $this->checkOwnership($menu, $request)) {
            return $error;
        }

        $printableMenu = $this->menuService->getPrintableMenu($menu);

        return response()->json([
            'message' => 'Customer printable menu',
            'menu' => $printableMenu,
        ]);
    }
}
