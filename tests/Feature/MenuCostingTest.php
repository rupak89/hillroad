<?php

namespace Tests\Feature;

use App\Enums\UnitType as UnitTypeEnum;
use App\Models\Item;
use App\Models\Menu;
use App\Models\MenuSegment;
use App\Models\MenuSegmentItem;
use App\Models\Recipe;
use App\Models\Unit;
use App\Models\UnitType;
use App\Models\User;
use App\Services\MenuService;
use App\Services\RecipeCostService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MenuCostingTest extends TestCase
{
    use RefreshDatabase;

    public function test_recipe_cost_is_divided_by_servings(): void
    {
        [$recipe] = $this->makeRecipeWithIngredient();

        $cost = app(RecipeCostService::class)->calculateRecipeCost($recipe);

        $this->assertSame(4, $cost['servings']);
        $this->assertEqualsWithDelta(6.00, $cost['total_cost'], 0.001);
        $this->assertEqualsWithDelta(1.50, $cost['cost_per_serving'], 0.001);
        $this->assertTrue($cost['can_calculate_full_cost']);
    }

    public function test_menu_cost_fields_are_per_guest_and_per_event(): void
    {
        [$recipe, $user] = $this->makeRecipeWithIngredient();
        $menu = $this->makeMenu($user, $recipe);

        $this->assertEqualsWithDelta(1.50, $menu->cost_per_guest, 0.001);
        $this->assertEqualsWithDelta(1.50, $menu->cost_per_person, 0.001);
        $this->assertEqualsWithDelta(15.00, $menu->total_event_cost, 0.001);
        $this->assertEqualsWithDelta(1.80, $menu->selling_price_per_guest, 0.001);
        $this->assertEqualsWithDelta(18.00, $menu->total_selling_price, 0.001);
    }

    public function test_menu_index_exposes_explicit_cost_fields(): void
    {
        [$recipe, $user] = $this->makeRecipeWithIngredient();
        $menu = $this->makeMenu($user, $recipe);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/menus');

        $response->assertOk()
            ->assertJsonPath('menus.data.0.id', $menu->id)
            ->assertJsonPath('menus.data.0.cost_per_guest', 1.5)
            ->assertJsonPath('menus.data.0.cost_per_person', 1.5)
            ->assertJsonPath('menus.data.0.total_event_cost', 15)
            ->assertJsonPath('menus.data.0.selling_price_per_guest', 1.8)
            ->assertJsonPath('menus.data.0.total_selling_price', 18)
            ->assertJsonPath('menus.data.0.segments_count', 1);
    }

    public function test_cost_breakdown_totals_do_not_return_nan_values(): void
    {
        [$recipe, $user] = $this->makeRecipeWithIngredient();
        $menu = $this->makeMenu($user, $recipe);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/menus/{$menu->id}/cost-breakdown");

        $response->assertOk()
            ->assertJsonPath('breakdown.totals.cost_per_guest', 1.5)
            ->assertJsonPath('breakdown.totals.cost_per_person', 1.5)
            ->assertJsonPath('breakdown.totals.total_event_cost', 15)
            ->assertJsonPath('breakdown.totals.total_cost', 15)
            ->assertJsonPath('breakdown.totals.total_selling_price', 18)
            ->assertJsonPath('breakdown.segments.0.items.0.cost_per_unit', 1.5)
            ->assertJsonPath('breakdown.segments.0.items.0.total_cost', 1.5);
    }

    public function test_cost_calculation_does_not_produce_an_n_plus_one_query_explosion(): void
    {
        [$recipe, $user] = $this->makeRecipeWithIngredient();
        $menu = $this->makeMenu($user, $recipe);

        app(RecipeCostService::class)->forget();

        \Illuminate\Support\Facades\DB::enableQueryLog();
        app(MenuService::class)->getCostBreakdown($menu);
        $queryCount = count(\Illuminate\Support\Facades\DB::getQueryLog());
        \Illuminate\Support\Facades\DB::disableQueryLog();

        $this->assertLessThan(50, $queryCount, "Cost breakdown used {$queryCount} queries.");
    }

    /**
     * @return array{0: Recipe, 1: User}
     */
    private function makeRecipeWithIngredient(): array
    {
        $unitType = UnitType::create([
            'label' => 'Weight-'.Str::random(8),
            'physical_type' => UnitTypeEnum::Mass->value,
            'base_unit' => 'kilogram',
        ]);

        $kilogram = Unit::create([
            'name' => 'kilogram-'.Str::random(8),
            'ratio' => 1,
            'unit_type_id' => $unitType->id,
        ]);

        $gram = Unit::create([
            'name' => 'gram-'.Str::random(8),
            'ratio' => 0.001,
            'unit_type_id' => $unitType->id,
        ]);

        $item = Item::create([
            'item_name' => 'Flour-'.Str::random(8),
            'ordering_unit_id' => $kilogram->id,
            'counting_unit_id' => $gram->id,
            'latest_price' => 12.00,
        ]);

        $recipe = Recipe::create([
            'recipe_name' => 'Sauce-'.Str::random(8),
            'servings' => 4,
        ]);

        $recipe->items()->attach($item->id, [
            'unit_id' => $gram->id,
            'quantity' => 500,
        ]);

        $user = User::factory()->create();

        return [$recipe->fresh(), $user];
    }

    private function makeMenu(User $user, Recipe $recipe): Menu
    {
        $menu = Menu::create([
            'name' => 'Menu-'.Str::random(8),
            'target_head_count' => 10,
            'markup_percentage' => 20,
            'user_id' => $user->id,
        ]);

        $segment = MenuSegment::create([
            'menu_id' => $menu->id,
            'name' => 'Main',
            'sort_order' => 1,
        ]);

        MenuSegmentItem::create([
            'menu_segment_id' => $segment->id,
            'recipe_id' => $recipe->id,
            'quantity' => 1,
            'sort_order' => 1,
        ]);

        return $menu->fresh();
    }
}
