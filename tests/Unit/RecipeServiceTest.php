<?php

namespace Tests\Unit;

use App\Services\RecipeService;
use App\Models\Recipe;
use App\Models\Item;
use App\DTOs\CreateRecipeDTO;
use App\DTOs\UpdateRecipeDTO;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;
use Mockery;

class RecipeServiceTest extends TestCase
{
    use RefreshDatabase;

    private RecipeService $recipeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->recipeService = new RecipeService();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_get_paginated_recipes()
    {
        // Arrange
        Recipe::factory()->count(15)->create();

        // Act
        $result = $this->recipeService->getRecipes(10);

        // Assert
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(15, $result->total());
        $this->assertCount(10, $result->items());
    }

    /** @test */
    public function it_can_create_recipe_without_ingredients_and_sub_recipes()
    {
        // Arrange
        $data = [
            'recipe_name' => 'Test Recipe',
            'instruction' => 'Test instructions',
            'thumbnail' => 'test.jpg'
        ];

        // Act
        $recipe = $this->recipeService->createRecipe($data);

        // Assert
        $this->assertInstanceOf(Recipe::class, $recipe);
        $this->assertEquals('Test Recipe', $recipe->recipe_name);
        $this->assertEquals('Test instructions', $recipe->instruction);
        $this->assertEquals('test.jpg', $recipe->thumbnail);
        $this->assertDatabaseHas('recipes', [
            'recipe_name' => 'Test Recipe',
            'instruction' => 'Test instructions',
            'thumbnail' => 'test.jpg'
        ]);
    }

    /** @test */
    public function it_can_create_recipe_with_ingredients()
    {
        // Arrange
        $item1 = Item::factory()->create();
        $item2 = Item::factory()->create();

        $data = [
            'recipe_name' => 'Recipe with Ingredients',
            'instruction' => 'Test instructions',
            'ingredients' => [
                [
                    'item_id' => $item1->id,
                    'unit_id' => 1,
                    'quantity' => 2.5
                ],
                [
                    'item_id' => $item2->id,
                    'unit_id' => 2,
                    'quantity' => 1.0
                ]
            ]
        ];

        // Act
        $recipe = $this->recipeService->createRecipe($data);

        // Assert
        $this->assertCount(2, $recipe->items);
        $this->assertEquals(2.5, $recipe->items->first()->pivot->quantity);
        $this->assertEquals(1, $recipe->items->first()->pivot->unit_id);
    }

    /** @test */
    public function it_can_create_recipe_with_sub_recipes()
    {
        // Arrange
        $subRecipe1 = Recipe::factory()->create();
        $subRecipe2 = Recipe::factory()->create();

        $data = [
            'recipe_name' => 'Recipe with Sub-recipes',
            'instruction' => 'Test instructions',
            'sub_recipes' => [
                [
                    'child_recipe_id' => $subRecipe1->id,
                    'quantity' => 1.0
                ],
                [
                    'child_recipe_id' => $subRecipe2->id,
                    'quantity' => 2.0
                ]
            ]
        ];

        // Act
        $recipe = $this->recipeService->createRecipe($data);

        // Assert
        $this->assertCount(2, $recipe->subRecipes);
        $this->assertEquals(1.0, $recipe->subRecipes->first()->pivot->quantity);
    }

    /** @test */
    public function it_prevents_self_reference_in_sub_recipes()
    {
        // Arrange
        $recipe = Recipe::factory()->create();

        $data = [
            'recipe_name' => 'Self-referencing Recipe',
            'instruction' => 'Test instructions',
            'sub_recipes' => [
                [
                    'child_recipe_id' => $recipe->id,
                    'quantity' => 1.0
                ]
            ]
        ];

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);

        $this->recipeService->updateRecipe($recipe->id, $data);
    }

    /** @test */
    public function it_can_get_recipe_by_id()
    {
        // Arrange
        $recipe = Recipe::factory()->create([
            'recipe_name' => 'Test Recipe',
            'instruction' => 'Test instructions'
        ]);

        // Act
        $result = $this->recipeService->getRecipe($recipe->id);

        // Assert
        $this->assertInstanceOf(Recipe::class, $result);
        $this->assertEquals($recipe->id, $result->id);
        $this->assertEquals('Test Recipe', $result->recipe_name);
    }

    /** @test */
    public function it_throws_exception_when_recipe_not_found()
    {
        // Act & Assert
        $this->expectException(ModelNotFoundException::class);

        $this->recipeService->getRecipe(999);
    }

    /** @test */
    public function it_can_update_recipe_basic_fields()
    {
        // Arrange
        $recipe = Recipe::factory()->create([
            'recipe_name' => 'Original Recipe',
            'instruction' => 'Original instructions'
        ]);

        $data = [
            'recipe_name' => 'Updated Recipe',
            'instruction' => 'Updated instructions',
            'thumbnail' => 'updated.jpg'
        ];

        // Act
        $updatedRecipe = $this->recipeService->updateRecipe($recipe->id, $data);

        // Assert
        $this->assertEquals('Updated Recipe', $updatedRecipe->recipe_name);
        $this->assertEquals('Updated instructions', $updatedRecipe->instruction);
        $this->assertEquals('updated.jpg', $updatedRecipe->thumbnail);
        $this->assertDatabaseHas('recipes', [
            'id' => $recipe->id,
            'recipe_name' => 'Updated Recipe',
            'instruction' => 'Updated instructions',
            'thumbnail' => 'updated.jpg'
        ]);
    }

    /** @test */
    public function it_can_update_recipe_ingredients()
    {
        // Arrange
        $recipe = Recipe::factory()->create();
        $oldItem = Item::factory()->create();
        $newItem = Item::factory()->create();

        // Add initial ingredient
        $recipe->items()->attach($oldItem->id, ['quantity' => 1.0, 'unit_id' => 1]);

        $data = [
            'ingredients' => [
                [
                    'item_id' => $newItem->id,
                    'unit_id' => 2,
                    'quantity' => 3.0
                ]
            ]
        ];

        // Act
        $updatedRecipe = $this->recipeService->updateRecipe($recipe->id, $data);

        // Assert
        $this->assertCount(1, $updatedRecipe->items);
        $this->assertEquals($newItem->id, $updatedRecipe->items->first()->id);
        $this->assertEquals(3.0, $updatedRecipe->items->first()->pivot->quantity);
        $this->assertEquals(2, $updatedRecipe->items->first()->pivot->unit_id);
    }

    /** @test */
    public function it_throws_exception_when_updating_non_existent_recipe()
    {
        // Arrange
        $data = ['recipe_name' => 'Updated Recipe'];

        // Act & Assert
        $this->expectException(ModelNotFoundException::class);

        $this->recipeService->updateRecipe(999, $data);
    }

    /** @test */
    public function it_can_delete_recipe()
    {
        // Arrange
        $recipe = Recipe::factory()->create();

        // Act
        $result = $this->recipeService->deleteRecipe($recipe->id);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseMissing('recipes', ['id' => $recipe->id]);
    }

    /** @test */
    public function it_throws_exception_when_deleting_non_existent_recipe()
    {
        // Act & Assert
        $this->expectException(ModelNotFoundException::class);

        $this->recipeService->deleteRecipe(999);
    }

    /** @test */
    public function it_can_get_recipes_for_dropdown()
    {
        // Arrange
        Recipe::factory()->count(5)->create();

        // Act
        $recipes = $this->recipeService->getRecipesForDropdown();

        // Assert
        $this->assertCount(5, $recipes);
        $this->assertInstanceOf(Collection::class, $recipes);
    }

    /** @test */
    public function it_can_get_recipes_for_dropdown_excluding_specific_recipe()
    {
        // Arrange
        $excludedRecipe = Recipe::factory()->create();
        Recipe::factory()->count(4)->create();

        // Act
        $recipes = $this->recipeService->getRecipesForDropdown($excludedRecipe->id);

        // Assert
        $this->assertCount(4, $recipes);
        $this->assertFalse($recipes->contains('id', $excludedRecipe->id));
    }

    /** @test */
    public function it_can_validate_sub_recipes_without_cycles()
    {
        // Arrange
        $recipe = Recipe::factory()->create();
        $subRecipe = Recipe::factory()->create();

        // Act
        $result = $this->recipeService->validateSubRecipes($recipe->id, [$subRecipe->id]);

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function it_can_validate_sub_recipes_with_self_reference()
    {
        // Arrange
        $recipe = Recipe::factory()->create();

        // Act
        $result = $this->recipeService->validateSubRecipes($recipe->id, [$recipe->id]);

        // Assert
        $this->assertFalse($result);
    }



    /** @test */
    public function it_handles_empty_sub_recipes_gracefully()
    {
        // Arrange
        $data = [
            'recipe_name' => 'Recipe with Empty Sub-recipes',
            'instruction' => 'Test instructions',
            'sub_recipes' => [

            ]
        ];

        // Act
        $recipe = $this->recipeService->createRecipe($data);

        // Assert
        $this->assertCount(0, $recipe->subRecipes);
    }

    /** @test */
    public function it_validates_required_recipe_name()
    {
        // Arrange
        $data = [
            'instruction' => 'Test instructions'
            // Missing recipe_name
        ];

        // Act & Assert
        $this->expectException(\Exception::class);

        $this->recipeService->createRecipe($data);
    }

    /** @test */
    public function it_can_handle_null_optional_fields()
    {
        // Arrange
        $data = [
            'recipe_name' => 'Minimal Recipe',
            'instruction' => null,
            'thumbnail' => null
        ];

        // Act
        $recipe = $this->recipeService->createRecipe($data);

        // Assert
        $this->assertEquals('Minimal Recipe', $recipe->recipe_name);
        $this->assertNull($recipe->instruction);
        $this->assertNull($recipe->thumbnail);
    }

    /** @test */
    public function it_logs_errors_when_operations_fail()
    {
        // Arrange
        Log::spy();

        // Act & Assert - Try to get non-existent recipe
        try {
            $this->recipeService->getRecipe(999);
        } catch (ModelNotFoundException $e) {
            // Expected exception
        }

        Log::shouldHaveReceived('error')
            ->once()
            ->with('Recipe not found: 999');
    }
}
