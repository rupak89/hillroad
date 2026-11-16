<?php

namespace Tests\Feature;

use App\Enums\UnitType as UnitTypeEnum;
use App\Models\Item;
use App\Models\Recipe;
use App\Models\Unit;
use App\Models\UnitType;
use App\Models\User;
use App\Services\RecipeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RecipeUpdateSafetyTest extends TestCase
{
    use RefreshDatabase;

    private RecipeService $recipeService;

    private Unit $unit;

    private Item $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->recipeService = app(RecipeService::class);

        $unitType = UnitType::create([
            'label' => 'Mass-'.Str::random(8),
            'physical_type' => UnitTypeEnum::Mass->value,
            'base_unit' => 'kilogram',
        ]);

        $this->unit = Unit::create([
            'name' => 'kilogram-'.Str::random(8),
            'ratio' => 1,
            'unit_type_id' => $unitType->id,
        ]);

        $this->item = Item::create([
            'item_name' => 'Sugar-'.Str::random(8),
            'ordering_unit_id' => $this->unit->id,
            'counting_unit_id' => $this->unit->id,
            'latest_price' => 5,
        ]);
    }

    public function test_updating_only_the_name_preserves_ingredients_and_sub_recipes(): void
    {
        $recipe = Recipe::create(['recipe_name' => 'Parent', 'servings' => 2]);
        $subRecipe = Recipe::create(['recipe_name' => 'Child', 'servings' => 1]);

        $recipe->items()->attach($this->item->id, [
            'unit_id' => $this->unit->id,
            'quantity' => 250,
        ]);
        $recipe->subRecipes()->attach($subRecipe->id, ['quantity' => 1]);

        $updated = $this->recipeService->updateRecipe($recipe->id, [
            'recipe_name' => 'Renamed parent',
        ]);

        $this->assertSame('Renamed parent', $updated->recipe_name);
        $this->assertCount(1, $updated->items);
        $this->assertCount(1, $updated->subRecipes);
        $this->assertDatabaseCount('recipe_items', 1);
        $this->assertDatabaseCount('recipe_recipes', 1);
    }

    public function test_ingredients_can_be_cleared_explicitly(): void
    {
        $recipe = Recipe::create(['recipe_name' => 'Parent', 'servings' => 1]);
        $recipe->items()->attach($this->item->id, [
            'unit_id' => $this->unit->id,
            'quantity' => 250,
        ]);

        $this->recipeService->updateRecipe($recipe->id, [
            'ingredients' => [],
            'sub_recipes' => [],
        ]);

        $this->assertCount(0, $recipe->fresh()->items);
        $this->assertCount(0, $recipe->fresh()->subRecipes);
    }

    public function test_optional_fields_can_be_cleared_with_null(): void
    {
        $recipe = Recipe::create([
            'recipe_name' => 'Parent',
            'instruction' => 'Mix it',
            'thumbnail' => 'photo.jpg',
            'servings' => 1,
        ]);

        $updated = $this->recipeService->updateRecipe($recipe->id, [
            'instruction' => null,
            'thumbnail' => null,
        ]);

        $this->assertNull($updated->instruction);
        $this->assertNull($updated->thumbnail);
    }

    public function test_servings_are_persisted_on_create_and_update(): void
    {
        $created = $this->recipeService->createRecipe([
            'recipe_name' => 'Bread',
            'servings' => 8,
        ]);

        $this->assertSame(8, $created->servings);

        $updated = $this->recipeService->updateRecipe($created->id, [
            'servings' => 6,
        ]);

        $this->assertSame(6, $updated->servings);
        $this->assertDatabaseHas('recipes', ['id' => $created->id, 'servings' => 6]);
    }

    public function test_api_partial_update_does_not_wipe_ingredients(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::create(['recipe_name' => 'Parent', 'servings' => 1]);
        $recipe->items()->attach($this->item->id, [
            'unit_id' => $this->unit->id,
            'quantity' => 250,
        ]);

        Sanctum::actingAs($user);

        $this->putJson("/api/recipes/{$recipe->id}", [
            'recipe_name' => 'Renamed through API',
        ])->assertOk();

        $this->assertCount(1, $recipe->fresh()->items);
    }

    public function test_api_create_persists_servings(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/recipes', [
            'recipe_name' => 'Bread',
            'servings' => 8,
        ]);

        $response->assertCreated()
            ->assertJsonPath('recipe.servings', 8);

        $this->assertDatabaseHas('recipes', [
            'recipe_name' => 'Bread',
            'servings' => 8,
        ]);
    }
}
