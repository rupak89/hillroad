<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MenuAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_view_their_own_menu(): void
    {
        $owner = User::factory()->create();
        $menu = $this->makeMenu($owner);

        Sanctum::actingAs($owner);

        $this->getJson("/api/menus/{$menu->id}")->assertOk();
    }

    public function test_a_user_cannot_view_another_users_menu(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $menu = $this->makeMenu($owner);

        Sanctum::actingAs($intruder);

        $this->getJson("/api/menus/{$menu->id}")->assertForbidden();
        $this->getJson("/api/menus/{$menu->id}/cost-breakdown")->assertForbidden();
        $this->getJson("/api/menus/{$menu->id}/printable")->assertForbidden();
    }

    public function test_a_user_cannot_update_or_delete_another_users_menu(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $menu = $this->makeMenu($owner);
        $recipe = Recipe::create(['recipe_name' => 'Test', 'servings' => 1]);

        Sanctum::actingAs($intruder);

        $this->putJson("/api/menus/{$menu->id}", [
            'name' => 'Hijacked',
            'target_head_count' => 1,
            'markup_percentage' => 0,
            'segments' => [
                [
                    'name' => 'Main',
                    'sort_order' => 1,
                    'items' => [
                        ['recipe_id' => $recipe->id, 'quantity' => 1],
                    ],
                ],
            ],
        ])->assertForbidden();

        $this->deleteJson("/api/menus/{$menu->id}")->assertForbidden();

        $this->assertDatabaseHas('menus', ['id' => $menu->id, 'name' => $menu->name]);
    }

    public function test_menu_index_only_returns_the_users_menus(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $ownMenu = $this->makeMenu($owner);
        $this->makeMenu($other);

        Sanctum::actingAs($owner);

        $this->getJson('/api/menus')
            ->assertOk()
            ->assertJsonCount(1, 'menus.data')
            ->assertJsonPath('menus.data.0.id', $ownMenu->id);
    }

    public function test_a_user_cannot_add_a_segment_to_another_users_menu(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $menu = $this->makeMenu($owner);

        Sanctum::actingAs($intruder);

        $this->postJson('/api/menu-segments', [
            'menu_id' => $menu->id,
            'name' => 'Injected',
        ])->assertForbidden();

        $this->assertDatabaseCount('menu_segments', 0);

        Sanctum::actingAs($owner);

        $this->postJson('/api/menu-segments', [
            'menu_id' => $menu->id,
            'name' => 'Starter',
        ])->assertCreated();

        $this->assertDatabaseHas('menu_segments', [
            'menu_id' => $menu->id,
            'name' => 'Starter',
        ]);
    }

    private function makeMenu(User $user): Menu
    {
        return Menu::create([
            'name' => 'Menu-'.$user->id,
            'target_head_count' => 10,
            'markup_percentage' => 0,
            'user_id' => $user->id,
        ]);
    }
}
