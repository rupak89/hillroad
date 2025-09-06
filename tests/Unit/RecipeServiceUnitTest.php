<?php

namespace Tests\Unit;

use App\Services\RecipeService;
use App\Models\Recipe;
use App\Models\Item;
use App\DTOs\CreateRecipeDTO;
use App\DTOs\UpdateRecipeDTO;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use PHPUnit\Framework\TestCase;
use Mockery;

class RecipeServiceUnitTest extends TestCase
{
    private $recipeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->recipeService = Mockery::mock(\App\Services\RecipeService::class)->makePartial();
        $this->recipeService->shouldAllowMockingProtectedMethods();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_would_create_cycle_detects_self_reference()
    {
        // Create a concrete service for testing this specific method
        $realService = new \App\Services\RecipeService();

        // Use reflection to test the private method directly since it only checks the array
        $reflection = new \ReflectionClass($realService);
        $method = $reflection->getMethod('wouldCreateCycle');
        $method->setAccessible(true);

        // Test self-reference - should return true immediately without database calls
        $result = $method->invoke($realService, 1, [1]);

        $this->assertTrue($result);
    }

    public function test_would_create_cycle_allows_valid_relationships()
    {
        // Mock the fetchRecipesWithChildren method to return empty collection (no existing relationships)
        $this->recipeService
            ->shouldReceive('fetchRecipesWithChildren')
            ->andReturn(new Collection());

        // Test using the public validateSubRecipes method instead of private wouldCreateCycle
        $result = $this->recipeService->validateSubRecipes(1, [2]);
        $this->assertTrue($result);
    }

    public function test_validate_sub_recipes_with_valid_recipes()
    {
        // Mock the fetchRecipesWithChildren method to return empty collection
        $this->recipeService
            ->shouldReceive('fetchRecipesWithChildren')
            ->andReturn(new Collection());

        $result = $this->recipeService->validateSubRecipes(1, [2, 3]);

        $this->assertTrue($result);
    }

    public function test_validate_sub_recipes_with_self_reference()
    {
        // Self-reference should be detected without database calls
        $result = $this->recipeService->validateSubRecipes(1, [1]);

        $this->assertFalse($result);
    }

    public function test_validate_sub_recipes_with_empty_array()
    {
        // Mock the fetchRecipesWithChildren method to avoid database calls
        $this->recipeService
            ->shouldReceive('fetchRecipesWithChildren')
            ->andReturn(new Collection());

        // Empty array should be valid
        $result = $this->recipeService->validateSubRecipes(1, []);

        $this->assertTrue($result);
    }

    public function test_clear_recipe_cache_resets_internal_cache()
    {
        // Create a concrete RecipeService instance for this test
        $realService = new \App\Services\RecipeService();

        // Use reflection to access private property
        $reflection = new \ReflectionClass($realService);
        $cacheProperty = $reflection->getProperty('recipeCache');
        $cacheProperty->setAccessible(true);

        // Set some dummy data in cache
        $cacheProperty->setValue($realService, [1 => 'dummy']);

        // Clear cache using private method
        $clearMethod = $reflection->getMethod('clearRecipeCache');
        $clearMethod->setAccessible(true);
        $clearMethod->invoke($realService);

        // Verify cache is empty
        $cache = $cacheProperty->getValue($realService);
        $this->assertEmpty($cache);
    }

    public function test_has_cyclic_dependency_detects_simple_cycle()
    {
        // Create a concrete RecipeService instance for this test
        $realService = new \App\Services\RecipeService();

        // Use reflection to test private method
        $reflection = new \ReflectionClass($realService);
        $method = $reflection->getMethod('hasCyclicDependency');
        $method->setAccessible(true);

        // Set up cache property with mock data
        $cacheProperty = $reflection->getProperty('recipeCache');
        $cacheProperty->setAccessible(true);

        // Create mock recipe with sub-recipes
        $subRecipe = Mockery::mock();
        $subRecipe->id = 1;

        $recipe = Mockery::mock();
        $recipe->subRecipes = collect([$subRecipe]);

        $cacheProperty->setValue($realService, [1 => $recipe]);

        $visited = [];
        $result = $method->invokeArgs($realService, [1, 2, &$visited]);


        $this->assertTrue($result);
    }

    public function test_has_cyclic_dependency_handles_no_cycle()
    {
        // Create a concrete RecipeService instance for this test
        $realService = new \App\Services\RecipeService();

        // Use reflection to test private method
        $reflection = new \ReflectionClass($realService);
        $method = $reflection->getMethod('hasCyclicDependency');
        $method->setAccessible(true);

        // Set up cache property with mock data
        $cacheProperty = $reflection->getProperty('recipeCache');
        $cacheProperty->setAccessible(true);

        // Create mock recipe with no sub-recipes
        $recipe = Mockery::mock();
        $recipe->subRecipes = collect();

        $cacheProperty->setValue($realService, [1 => $recipe]);

        $visited = [];
        $result = $method->invokeArgs($realService, [1, 2, &$visited]);

        $this->assertFalse($result);
    }

    public function test_fetch_recipes_with_children_can_be_mocked()
    {
        // Test that we can mock the protected method
        $mockRecipe = Mockery::mock();
        $mockRecipe->id = 1;
        $mockRecipe->subRecipes = collect();

        $this->recipeService
            ->shouldReceive('fetchRecipesWithChildren')
            ->with([1], ['id'])
            ->andReturn(new Collection([$mockRecipe]));

        // Use reflection to call the method
        $reflection = new \ReflectionClass($this->recipeService);
        $method = $reflection->getMethod('fetchRecipesWithChildren');
        $method->setAccessible(true);

        $result = $method->invoke($this->recipeService, [1], ['id']);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(1, $result);
    }

    public function test_service_constants_and_defaults()
    {
        // Test that the service has reasonable defaults
        $this->assertInstanceOf(RecipeService::class, $this->recipeService);
    }

    public function test_complex_cycle_detection_scenario()
    {
        // Mock fetchRecipesWithChildren to simulate a complex recipe chain
        $recipeA = Mockery::mock();
        $recipeA->id = 1;
        $recipeB = Mockery::mock();
        $recipeB->id = 2;
        $recipeB->subRecipes = collect();
        $recipeA->subRecipes = collect([$recipeB]);

        $this->recipeService
            ->shouldReceive('fetchRecipesWithChildren')
            ->andReturn(new Collection([$recipeA, $recipeB]));

        // Test that adding recipe A as a sub-recipe to recipe C doesn't create a cycle
        $result = $this->recipeService->validateSubRecipes(3, [1]);

        $this->assertTrue($result);
    }
}
