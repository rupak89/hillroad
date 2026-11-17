<?php

namespace Tests\Feature;

use App\Enums\UnitType as UnitTypeEnum;
use App\Models\GstSetting;
use App\Models\Item;
use App\Models\Menu;
use App\Models\MenuSegment;
use App\Models\MenuSegmentItem;
use App\Models\Recipe;
use App\Models\Unit;
use App\Models\UnitType;
use App\Models\User;
use App\Services\GstService;
use App\Services\RecipeCostService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GstSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_gst_settings_default_to_the_new_zealand_rate(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/settings/gst');

        $response->assertOk()
            ->assertJsonPath('settings.gst_registered', true)
            ->assertJsonPath('settings.prices_include_gst', false);

        $this->assertEqualsWithDelta(15, $response->json('settings.gst_rate'), 0.001);
    }

    public function test_gst_settings_require_authentication(): void
    {
        $this->getJson('/api/settings/gst')->assertUnauthorized();
        $this->putJson('/api/settings/gst', [])->assertUnauthorized();
    }

    public function test_gst_settings_can_be_updated(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->putJson('/api/settings/gst', [
            'gst_registered' => true,
            'gst_rate' => 15,
            'prices_include_gst' => true,
        ]);

        $response->assertOk()
            ->assertJsonPath('settings.gst_registered', true)
            ->assertJsonPath('settings.prices_include_gst', true);

        $this->assertDatabaseHas('gst_settings', [
            'gst_registered' => true,
            'gst_rate' => 15,
            'prices_include_gst' => true,
        ]);

        $this->getJson('/api/settings/gst')
            ->assertOk()
            ->assertJsonPath('settings.prices_include_gst', true);
    }

    public function test_gst_rate_must_be_a_percentage(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->putJson('/api/settings/gst', [
            'gst_registered' => true,
            'gst_rate' => 150,
            'prices_include_gst' => false,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['gst_rate']);
    }

    public function test_recipe_costs_include_gst(): void
    {
        $recipe = $this->makeRecipe();

        $cost = app(RecipeCostService::class)->calculateRecipeCost($recipe);

        $this->assertEqualsWithDelta(6.00, $cost['total_cost'], 0.001);
        $this->assertEqualsWithDelta(0.90, $cost['total_gst'], 0.001);
        $this->assertEqualsWithDelta(6.90, $cost['total_cost_incl_gst'], 0.001);
        $this->assertEqualsWithDelta(1.73, $cost['cost_per_serving_incl_gst'], 0.001);
        $this->assertEqualsWithDelta(15, $cost['gst_rate'], 0.001);
        $this->assertTrue($cost['gst_registered']);
        $this->assertFalse($cost['prices_include_gst']);
    }

    public function test_recipe_costs_back_out_gst_when_item_prices_are_inclusive(): void
    {
        $this->enableGstInclusivePrices();

        $recipe = $this->makeRecipe();
        $cost = app(RecipeCostService::class)->calculateRecipeCost($recipe);

        // $12.00 GST-inclusive per kg = $10.4348 excl. GST per kg.
        $this->assertEqualsWithDelta(5.22, $cost['total_cost'], 0.001);
        $this->assertEqualsWithDelta(0.78, $cost['total_gst'], 0.001);
        $this->assertEqualsWithDelta(6.00, $cost['total_cost_incl_gst'], 0.001);
        $this->assertEqualsWithDelta(10.43, $cost['item_costs'][0]['unit_cost'], 0.001);
        $this->assertEqualsWithDelta(12.00, $cost['item_costs'][0]['unit_cost_incl_gst'], 0.001);
    }

    public function test_no_gst_is_applied_when_the_business_is_not_registered(): void
    {
        $this->setGstSettings([
            'gst_registered' => false,
            'gst_rate' => 15,
            'prices_include_gst' => false,
        ]);

        $recipe = $this->makeRecipe();
        $cost = app(RecipeCostService::class)->calculateRecipeCost($recipe);

        $this->assertEqualsWithDelta(6.00, $cost['total_cost'], 0.001);
        $this->assertEqualsWithDelta(0.00, $cost['total_gst'], 0.001);
        $this->assertEqualsWithDelta(6.00, $cost['total_cost_incl_gst'], 0.001);
        $this->assertEqualsWithDelta(0, $cost['gst_rate'], 0.001);
        $this->assertFalse($cost['gst_registered']);
    }

    public function test_menu_api_exposes_gst_inclusive_and_exclusive_values(): void
    {
        [$recipe, $user] = $this->makeRecipeAndUser();
        $menu = $this->makeMenu($user, $recipe);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/menus/{$menu->id}");

        $response->assertOk()
            ->assertJsonPath('menu.gst.registered', true)
            ->assertJsonPath('menu.gst.prices_include_gst', false);

        $this->assertEqualsWithDelta(15, $response->json('menu.gst.rate'), 0.001);
        $this->assertEqualsWithDelta(1.50, $response->json('menu.cost_per_guest'), 0.001);
        $this->assertEqualsWithDelta(0.23, $response->json('menu.cost_gst_per_guest'), 0.001);
        $this->assertEqualsWithDelta(1.73, $response->json('menu.cost_per_guest_incl_gst'), 0.001);
        $this->assertEqualsWithDelta(1.80, $response->json('menu.selling_price_per_guest'), 0.001);
        $this->assertEqualsWithDelta(0.27, $response->json('menu.selling_gst_per_guest'), 0.001);
        $this->assertEqualsWithDelta(2.07, $response->json('menu.selling_price_per_guest_incl_gst'), 0.001);
        $this->assertEqualsWithDelta(18.00, $response->json('menu.total_selling_price'), 0.001);
        $this->assertEqualsWithDelta(2.70, $response->json('menu.total_selling_gst'), 0.001);
        $this->assertEqualsWithDelta(20.70, $response->json('menu.total_selling_price_incl_gst'), 0.001);
    }

    public function test_cost_breakdown_exposes_gst_totals(): void
    {
        [$recipe, $user] = $this->makeRecipeAndUser();
        $menu = $this->makeMenu($user, $recipe);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/menus/{$menu->id}/cost-breakdown");

        $response->assertOk()
            ->assertJsonPath('breakdown.gst.registered', true)
            ->assertJsonPath('breakdown.gst.rate', 15);

        $this->assertEqualsWithDelta(2.25, $response->json('breakdown.totals.total_cost_gst'), 0.001);
        $this->assertEqualsWithDelta(17.25, $response->json('breakdown.totals.total_event_cost_incl_gst'), 0.001);
        $this->assertEqualsWithDelta(2.70, $response->json('breakdown.totals.total_selling_gst'), 0.001);
        $this->assertEqualsWithDelta(20.70, $response->json('breakdown.totals.total_selling_price_incl_gst'), 0.001);
        $this->assertEqualsWithDelta(1.73, $response->json('breakdown.segments.0.items.0.total_cost_incl_gst'), 0.001);
    }

    public function test_printable_menu_quotes_gst_inclusive_prices(): void
    {
        [$recipe, $user] = $this->makeRecipeAndUser();
        $menu = $this->makeMenu($user, $recipe);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/menus/{$menu->id}/printable");

        $response->assertOk()
            ->assertJsonPath('menu.pricing.gst.registered', true)
            ->assertJsonPath('menu.pricing.gst.note', 'All prices include GST at 15%.');

        $this->assertEqualsWithDelta(2.07, $response->json('menu.pricing.price_per_person'), 0.001);
        $this->assertEqualsWithDelta(1.80, $response->json('menu.pricing.price_per_person_excl_gst'), 0.001);
        $this->assertEqualsWithDelta(20.70, $response->json('menu.pricing.total_price'), 0.001);
        $this->assertEqualsWithDelta(2.70, $response->json('menu.pricing.gst.amount_total'), 0.001);
        $this->assertEqualsWithDelta(2.07, $response->json('menu.segments.0.items.0.price_per_person'), 0.001);
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    private function setGstSettings(array $settings): GstSetting
    {
        $record = GstSetting::current();
        $record->update($settings);

        // The service caches the row for the duration of a request.
        app(GstService::class)->forget();

        return $record->fresh();
    }

    private function enableGstInclusivePrices(): void
    {
        $this->setGstSettings([
            'gst_registered' => true,
            'gst_rate' => 15,
            'prices_include_gst' => true,
        ]);
    }

    /**
     * @return array{0: Recipe, 1: User}
     */
    private function makeRecipeAndUser(): array
    {
        return [$this->makeRecipe(), User::factory()->create()];
    }

    private function makeRecipe(): Recipe
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

        return $recipe->fresh();
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
