<?php

namespace Tests\Feature;

use App\DTOs\Menu\CreateMenuDTO;
use App\DTOs\Menu\UpdateMenuDTO;
use App\Models\Recipe;
use App\Models\User;
use App\Services\MenuService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_menu_rolls_back_when_a_later_item_fails(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::create(['recipe_name' => 'Valid recipe', 'servings' => 1]);

        $dto = CreateMenuDTO::fromRequest([
            'name' => 'Broken menu',
            'target_head_count' => 10,
            'markup_percentage' => 0,
            'segments' => [
                [
                    'name' => 'Starter',
                    'sort_order' => 1,
                    'items' => [
                        ['recipe_id' => $recipe->id, 'quantity' => 1],
                    ],
                ],
                [
                    'name' => 'Main',
                    'sort_order' => 2,
                    'items' => [
                        ['recipe_id' => 999999, 'quantity' => 1],
                    ],
                ],
            ],
        ], $user->id);

        try {
            app(MenuService::class)->createMenu($dto);
            $this->fail('Expected the invalid recipe to fail the menu insert.');
        } catch (\Throwable) {
            // Expected.
        }

        $this->assertDatabaseCount('menus', 0);
        $this->assertDatabaseCount('menu_segments', 0);
        $this->assertDatabaseCount('menu_segment_items', 0);
    }

    public function test_updating_a_menu_rolls_back_when_a_later_item_fails(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::create(['recipe_name' => 'Valid recipe', 'servings' => 1]);

        $menu = app(MenuService::class)->createMenu(CreateMenuDTO::fromRequest([
            'name' => 'Original name',
            'target_head_count' => 10,
            'markup_percentage' => 0,
            'segments' => [
                [
                    'name' => 'Main',
                    'sort_order' => 1,
                    'items' => [
                        ['recipe_id' => $recipe->id, 'quantity' => 2, 'notes' => 'Original note'],
                    ],
                ],
            ],
        ], $user->id));

        $segment = $menu->segments->first();
        $item = $segment->items->first();

        $dto = UpdateMenuDTO::fromRequest([
            'name' => 'Renamed menu',
            'target_head_count' => 20,
            'markup_percentage' => 10,
            'segments' => [
                [
                    'id' => $segment->id,
                    'name' => 'Main',
                    'sort_order' => 1,
                    'items' => [
                        [
                            'id' => $item->id,
                            'recipe_id' => $recipe->id,
                            'quantity' => 3,
                            'notes' => 'Updated note',
                        ],
                        [
                            'recipe_id' => 999999,
                            'quantity' => 1,
                        ],
                    ],
                ],
            ],
        ]);

        try {
            app(MenuService::class)->updateMenu($menu, $dto);
            $this->fail('Expected the invalid recipe to fail the menu update.');
        } catch (\Throwable) {
            // Expected.
        }

        $menu->refresh();
        $this->assertSame('Original name', $menu->name);
        $this->assertSame(10, $menu->target_head_count);
        $this->assertDatabaseCount('menu_segments', 1);
        $this->assertDatabaseCount('menu_segment_items', 1);

        $item->refresh();
        $this->assertSame('Original note', $item->notes);
        $this->assertEqualsWithDelta(2, (float) $item->quantity, 0.001);
    }
}
