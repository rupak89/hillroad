<template>
  <section class="section main-section">
    <router-link to="/recipes" class="button is-small">
      <span class="icon"><i class="mdi mdi-arrow-left"></i></span>
      <span class="sm:hidden lg:inline">Back</span>
    </router-link>

    <div class="card mb-6">
      <header class="card-header">
        <p class="card-header-title">
          <span class="icon"><i class="mdi mdi-ballot"></i></span>
          {{ isEditMode ? 'Edit Recipe' : 'Add Recipe' }}
        </p>
      </header>
      <div class="card-content">
        <div v-if="isLoading" class="has-text-centered py-6">
          <div class="button is-loading is-static">Loading recipe data...</div>
        </div>
        <form v-else @submit.prevent="submitForm">

          <!-- Recipe Name -->
          <div class="field">
            <label class="label">Recipe Name</label>
            <div class="control">
              <input
                class="input"
                :class="{ 'is-danger': errors.recipe_name }"
                type="text"
                placeholder="e.g. Spaghetti Bolognese"
                v-model="recipe.recipe_name"
                >
            </div>
            <p v-if="errors.recipe_name" class="help is-danger">{{ errors.recipe_name[0] }}</p>
          </div>

          <!-- Servings -->
          <div class="field">
            <label class="label">Servings</label>
            <div class="control">
              <input
                class="input"
                :class="{ 'is-danger': errors.servings }"
                type="number"
                min="1"
                step="1"
                placeholder="e.g. 4"
                v-model.number="recipe.servings">
            </div>
            <p v-if="errors.servings" class="help is-danger">{{ errors.servings[0] }}</p>
            <p class="help">Number of servings this recipe makes</p>
          </div>

          <!-- Instructions -->
          <div class="field">
            <label class="label">Instructions</label>
            <div class="control">
              <textarea
                class="textarea"
                :class="{ 'is-danger': errors.instruction }"
                placeholder="Describe steps here..."
                v-model="recipe.instruction">
              </textarea>
            </div>
            <p v-if="errors.instruction" class="help is-danger">{{ errors.instruction[0] }}</p>
          </div>

          <!-- Thumbnail Upload (optional) -->
          <div class="field hidden">
            <label class="label">Thumbnail (URL)</label>
            <div class="control">
              <input
                class="input"
                :class="{ 'is-danger': errors.thumbnail }"
                type="text"
                placeholder="https://example.com/image.jpg"
                v-model="recipe.thumbnail">
            </div>
            <p v-if="errors.thumbnail" class="help is-danger">{{ errors.thumbnail[0] }}</p>
          </div>

          <hr>

          <!-- Ingredients (Items) -->
          <div class="field">
            <label class="label">Ingredients</label>
            <RecipeIngredientItem
              v-for="(ingredient, index) in recipe.ingredients"
              :key="ingredient.id"
              :ingredient="ingredient"
              :index="index"
              :items="items"
              :units="availableUnits"
              :errors="errors"
              :can-remove="recipe.ingredients.length > 1"
              @remove="removeIngredient(index)"
            />
            <button class="button is-small add-button" type="button" @click="addIngredient">
              <span class="icon"><i class="mdi mdi-plus"></i></span>
              <span>Add Ingredient</span>
            </button>
          </div>

          <hr>

          <!-- Sub-Recipes -->
          <div class="field">
            <label class="label">Sub-Recipes</label>
            <RecipeSubRecipeItem
              v-for="(sub, index) in recipe.sub_recipes"
              :key="sub.id"
              :sub-recipe="sub"
              :index="index"
              :recipes="availableRecipes"
              :errors="errors"
              :can-remove="recipe.sub_recipes.length > 1"
              @remove="removeSubRecipe(index)"
            />
            <button class="button is-small add-button" type="button" @click="addSubRecipe">
              <span class="icon"><i class="mdi mdi-plus"></i></span>
              <span>Add Sub-Recipe</span>
            </button>
          </div>

          <hr>

          <!-- Recipe Cost Calculator (only show in edit mode) -->
          <div v-if="isEditMode && recipeId" class="field">
            <RecipeCostCalculator :recipe-id="recipeId" />
          </div>

          <hr>

          <!-- Submit Buttons -->
          <div class="field grouped">
            <div class="control">
              <button
                type="submit"
                class="button green"
                :class="{ 'is-loading': isSubmitting }"
                :disabled="isSubmitting">
                {{ isEditMode ? 'Update Recipe' : 'Add Recipe' }}
              </button>
            </div>
            <div class="control">
              <router-link to="/recipes" class="button">
                Cancel
              </router-link>
            </div>
          </div>

        </form>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import RecipeCostCalculator from '@/components/RecipeCostCalculator.vue';
import RecipeIngredientItem from '@/components/RecipeIngredientItem.vue';
import RecipeSubRecipeItem from '@/components/RecipeSubRecipeItem.vue';

// Composables
import { useRecipeForm } from '@/composables/useRecipeForm.js';
import { useRecipeData } from '@/composables/useRecipeData.js';

// Form management
const {
  recipe,
  errors,
  isSubmitting,
  recipeId,
  isEditMode,
  submitForm
} = useRecipeForm();

// Data loading
const {
  items,
  recipes,
  isLoading,
  itemStore,
  loadData
} = useRecipeData();

// Generate unique ID for form items
const generateId = () => Math.random().toString(36).substr(2, 9);

// Available units computed
const availableUnits = computed(() => itemStore.units || []);

// Available recipes (filtered to exclude current recipe in edit mode)
const availableRecipes = computed(() => {
  if (isEditMode.value && recipeId.value) {
    return recipes.value.filter(recipe => recipe.id != recipeId.value);
  }
  return recipes.value;
});

// Ingredient management
const addIngredient = () => {
  recipe.value.ingredients.push({ id: generateId(), item_id: '', unit_id: '', quantity: '' });
};

const removeIngredient = (index) => {
  if (recipe.value.ingredients.length > 1) {
    recipe.value.ingredients.splice(index, 1);
  }
};

// Sub-recipe management
const addSubRecipe = () => {
  recipe.value.sub_recipes.push({ id: generateId(), child_recipe_id: '', quantity: '' });
};

const removeSubRecipe = (index) => {
  if (recipe.value.sub_recipes.length > 1) {
    recipe.value.sub_recipes.splice(index, 1);
  }
};

// Initialize data on component mount
onMounted(async () => {
  try {
    await loadData(recipe, recipeId);
  } catch (error) {
    console.error('Failed to load component data:', error);
  }
});
</script>

<style scoped>
.add-button {
  background-color: #abf6ab !important; /* Light green */
  border-color: #90ee90 !important;
  color: #2c3e50 !important; /* Dark text for better contrast */
}

.add-button:hover {
  background-color: #7dd87d !important; /* Slightly darker green on hover */
  border-color: #7dd87d !important;
}
</style>
