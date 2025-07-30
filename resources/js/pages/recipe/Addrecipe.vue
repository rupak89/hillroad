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
            <div v-for="(ingredient, index) in recipe.ingredients" :key="'ingredient-' + index" class="ingredient-row mb-3">
              <div class="ingredient-item">
                <div class="control">
                  <div class="select is-fullwidth">
                    <select
                      v-model="ingredient.item_id"
                      :class="{ 'is-danger': errors[`ingredients.${index}.item_id`] }">
                      <option disabled value="">Select Item</option>
                      <option v-for="item in items" :key="item.id" :value="item.id">{{ item.item_name }}</option>
                    </select>
                  </div>
                </div>
                <p v-if="errors[`ingredients.${index}.item_id`]" class="help is-danger is-size-7">
                  {{ errors[`ingredients.${index}.item_id`][0] }}
                </p>
              </div>
              <div class="ingredient-unit">
                <div class="control">
                  <div class="select is-fullwidth">
                    <select
                      v-model="ingredient.unit_id"
                      :class="{ 'is-danger': errors[`ingredients.${index}.unit_id`] }">
                      <option disabled value="">Select Unit</option>
                      <option v-for="unit in availableUnits" :key="unit.id" :value="unit.id">
                        {{ unit.name }}
                      </option>
                    </select>
                  </div>
                </div>
                <p v-if="errors[`ingredients.${index}.unit_id`]" class="help is-danger is-size-7">
                  {{ errors[`ingredients.${index}.unit_id`][0] }}
                </p>
              </div>
              <div class="ingredient-quantity">
                <div class="control">
                  <input
                    class="input"
                    :class="{ 'is-danger': errors[`ingredients.${index}.quantity`] }"
                    type="number"
                    step="0.01"
                    placeholder="Quantity"
                    v-model="ingredient.quantity">
                </div>
                <p v-if="errors[`ingredients.${index}.quantity`]" class="help is-danger is-size-7">
                  {{ errors[`ingredients.${index}.quantity`][0] }}
                </p>
              </div>
              <div class="ingredient-remove">
                <button
                  v-if="recipe.ingredients.length > 1"
                  class="button is-small is-danger"
                  type="button"
                  @click="removeIngredient(index)">
                  <span class="icon"><i class="mdi mdi-close"></i></span>
                </button>
              </div>
            </div>
            <button class="button is-small add-button" type="button" @click="addIngredient">
              <span class="icon"><i class="mdi mdi-plus"></i></span>
              <span>Add Ingredient</span>
            </button>
          </div>

          <hr>

          <!-- Sub-Recipes -->
          <div class="field">
            <label class="label">Sub-Recipes</label>
            <div v-for="(sub, index) in recipe.sub_recipes" :key="'sub-' + index" class="sub-recipe-row mb-3">
              <div class="sub-recipe-item">
                <div class="control">
                  <div class="select is-fullwidth">
                    <select
                      v-model="sub.child_recipe_id"
                      :class="{ 'is-danger': errors[`sub_recipes.${index}.child_recipe_id`] }">
                      <option disabled value="">Select Sub-Recipe</option>
                      <option v-for="r in availableRecipes" :key="r.id" :value="r.id">{{ r.recipe_name }}</option>
                    </select>
                  </div>
                </div>
                <p v-if="errors[`sub_recipes.${index}.child_recipe_id`]" class="help is-danger is-size-7">
                  {{ errors[`sub_recipes.${index}.child_recipe_id`][0] }}
                </p>
              </div>
              <div class="sub-recipe-quantity">
                <div class="control">
                  <input
                    class="input"
                    :class="{ 'is-danger': errors[`sub_recipes.${index}.quantity`] }"
                    type="number"
                    step="0.01"
                    placeholder="Quantity"
                    v-model="sub.quantity">
                </div>
                <p v-if="errors[`sub_recipes.${index}.quantity`]" class="help is-danger is-size-7">
                  {{ errors[`sub_recipes.${index}.quantity`][0] }}
                </p>
              </div>
              <div class="sub-recipe-remove">
                <button
                  v-if="recipe.sub_recipes.length > 1"
                  class="button is-small is-danger"
                  type="button"
                  @click="removeSubRecipe(index)">
                  <span class="icon"><i class="mdi mdi-close"></i></span>
                </button>
              </div>
            </div>
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

<script>
import axios from 'axios';
import { useFlashMessage } from '@/composables/useFlashMessage.js';
import useItemStore from '@/stores/item.js';
import RecipeCostCalculator from '@/components/RecipeCostCalculator.vue';

export default {
  components: {
    RecipeCostCalculator
  },
  setup() {
    const { success, error } = useFlashMessage();
    const itemStore = useItemStore();
    return { success, error, itemStore };
  },
  data() {
    return {
      recipe: {
        recipe_name: '',
        instruction: '',
        thumbnail: '',
        ingredients: [
          { item_id: '', unit_id: '', quantity: '' }
        ],
        sub_recipes: [
          { child_recipe_id: '', quantity: '' }
        ]
      },
      items: [],
      recipes: [],
      errors: {},
      isSubmitting: false,
      isLoading: true,
      recipeId: null,
    };
  },
  computed: {
    isEditMode() {
      return this.$route.params.id !== undefined;
    },
    availableRecipes() {
      // Filter out the current recipe from sub-recipes dropdown
      if (this.isEditMode && this.recipeId) {
        return this.recipes.filter(recipe => recipe.id != this.recipeId);
      }
      return this.recipes;
    },
    availableUnits() {
      return this.itemStore.units || [];
    }
  },
  methods: {
    async fetchDropdownData() {
      try {
        const excludeId = this.isEditMode ? this.$route.params.id : null;
        const url = excludeId ? `/api/recipes-dropdown-data/${excludeId}` : '/api/recipes-dropdown-data';
        const response = await axios.get(url);

        this.items = response.data.items;
        this.recipes = response.data.recipes;
      } catch (fetchError) {
        console.error('Error fetching dropdown data:', fetchError);
        this.error('Loading Error', 'Error loading form data. Please refresh the page.');
      }
    },

    async fetchRecipe() {
      if (!this.isEditMode) return;

      try {
        const response = await axios.get(`/api/recipes/${this.$route.params.id}`);
        const recipeData = response.data.recipe;

        // Debug logging
        console.log('Recipe data received:', recipeData);
        console.log('Sub-recipes data:', recipeData.sub_recipes || recipeData.subRecipes);

        this.recipeId = recipeData.id;
        this.recipe.recipe_name = recipeData.recipe_name;
        this.recipe.instruction = recipeData.instruction || '';
        this.recipe.thumbnail = recipeData.thumbnail || '';

        // Populate ingredients
        if (recipeData.items && recipeData.items.length > 0) {
          this.recipe.ingredients = recipeData.items.map(item => ({
            item_id: item.id,
            unit_id: item.pivot.unit_id,
            quantity: item.pivot.quantity
          }));
        } else {
          this.recipe.ingredients = [{ item_id: '', unit_id: '', quantity: '' }];
        }

        // Populate sub-recipes
        if (recipeData.sub_recipes && recipeData.sub_recipes.length > 0) {
          this.recipe.sub_recipes = recipeData.sub_recipes.map(subRecipe => ({
            child_recipe_id: subRecipe.id,
            quantity: subRecipe.pivot.quantity
          }));
        } else if (recipeData.subRecipes && recipeData.subRecipes.length > 0) {
          this.recipe.sub_recipes = recipeData.subRecipes.map(subRecipe => ({
            child_recipe_id: subRecipe.id,
            quantity: subRecipe.pivot.quantity
          }));
        } else {
          this.recipe.sub_recipes = [{ child_recipe_id: '', quantity: '' }];
        }

      } catch (fetchError) {
        console.error('Error fetching recipe:', fetchError);
        this.error('Loading Error', 'Error loading recipe data.');
        this.$router.push('/recipes');
      }
    },

    addIngredient() {
      this.recipe.ingredients.push({ item_id: '', unit_id: '', quantity: '' });
    },

    removeIngredient(index) {
      if (this.recipe.ingredients.length > 1) {
        this.recipe.ingredients.splice(index, 1);
      }
    },

    addSubRecipe() {
      this.recipe.sub_recipes.push({ child_recipe_id: '', quantity: '' });
    },

    removeSubRecipe(index) {
      if (this.recipe.sub_recipes.length > 1) {
        this.recipe.sub_recipes.splice(index, 1);
      }
    },

    resetForm() {
      this.recipe = {
        recipe_name: '',
        instruction: '',
        thumbnail: '',
        ingredients: [{ item_id: '', unit_id: '', quantity: '' }],
        sub_recipes: [{ child_recipe_id: '', quantity: '' }]
      };
      this.errors = {};
    },

    cleanFormData() {
      // Remove empty ingredients
      this.recipe.ingredients = this.recipe.ingredients.filter(ingredient =>
        ingredient.item_id && ingredient.unit_id && ingredient.quantity
      );

      // Remove empty sub-recipes
      this.recipe.sub_recipes = this.recipe.sub_recipes.filter(subRecipe =>
        subRecipe.child_recipe_id && subRecipe.quantity
      );
    },

    validateRecipe() {
      // Check if there's at least one ingredient or one sub-recipe
      const hasIngredients = this.recipe.ingredients.some(ingredient =>
        ingredient.item_id && ingredient.unit_id && ingredient.quantity
      );

      const hasSubRecipes = this.recipe.sub_recipes.some(subRecipe =>
        subRecipe.child_recipe_id && subRecipe.quantity
      );

      if (!hasIngredients && !hasSubRecipes) {
        return {
          isValid: false,
          message: 'A recipe must have at least one ingredient or one sub-recipe.'
        };
      }

      return { isValid: true };
    },

    async submitForm() {
      this.errors = {};
      this.isSubmitting = true;

      try {
        // Validate the recipe first
        const validation = this.validateRecipe();
        if (!validation.isValid) {
          this.error('Validation Error', validation.message);
          return;
        }

        // Clean the form data before submitting
        this.cleanFormData();

        let response;
        if (this.isEditMode) {
          response = await axios.put(`/api/recipes/${this.$route.params.id}`, this.recipe);
        } else {
          response = await axios.post('/api/recipes', this.recipe);
        }

        const action = this.isEditMode ? 'updated' : 'created';
        const recipeName = response.data.recipe.recipe_name;

        this.success(
          `Recipe ${action === 'created' ? 'Created' : 'Updated'}`,
          `"${recipeName}" has been ${action} successfully!`
        );

        // Redirect to recipes list with success message
        this.$router.push({
          path: '/recipes',
          query: {
            success: action,
            recipe: recipeName
          }
        });

      } catch (submitError) {
        console.error('Error submitting form:', submitError);

        if (submitError.response && submitError.response.status === 422) {
          // Validation errors
          this.errors = submitError.response.data.errors || {};
        } else {
          // Other errors
          this.error(
            'Submit Failed',
            `Error ${this.isEditMode ? 'updating' : 'creating'} recipe. Please try again.`
          );
        }
      } finally {
        this.isSubmitting = false;
      }
    }
  },

  async mounted() {
    this.isLoading = true;
    try {
      // Fetch units from store and dropdown data in parallel
      await Promise.all([
        this.itemStore.fetchUnits(),
        this.fetchDropdownData()
      ]);

      if (this.isEditMode) {
        await this.fetchRecipe();
      }
    } catch (error) {
      console.error('Error loading data:', error);
      this.error('Loading Error', 'Error loading form data. Please refresh the page.');
    } finally {
      this.isLoading = false;
    }
  }
};
</script>

<style scoped>
.ingredient-row {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr auto;
  gap: 1rem;
  align-items: start;
}

.ingredient-item,
.ingredient-unit,
.ingredient-quantity,
.ingredient-remove {
  display: flex;
  flex-direction: column;
}

.ingredient-remove {
  align-items: center;
  justify-content: flex-start;
  padding-top: 0.375rem; /* Align with input height */
}

.sub-recipe-row {
  display: grid;
  grid-template-columns: 2fr 1fr auto;
  gap: 1rem;
  align-items: start;
}

.sub-recipe-item,
.sub-recipe-quantity,
.sub-recipe-remove {
  display: flex;
  flex-direction: column;
}

.sub-recipe-remove {
  align-items: center;
  justify-content: flex-start;
  padding-top: 0.375rem; /* Align with input height */
}

.add-button {
  background-color: #abf6ab !important; /* Light green */
  border-color: #90ee90 !important;
  color: #2c3e50 !important; /* Dark text for better contrast */
}

.add-button:hover {
  background-color: #7dd87d !important; /* Slightly darker green on hover */
  border-color: #7dd87d !important;
}

/* Responsive design for smaller screens */
@media (max-width: 768px) {
  .ingredient-row {
    grid-template-columns: 1fr;
    gap: 0.5rem;
  }

  .ingredient-remove {
    justify-self: end;
    padding-top: 0;
  }

  .sub-recipe-row {
    grid-template-columns: 1fr;
    gap: 0.5rem;
  }

  .sub-recipe-remove {
    justify-self: end;
    padding-top: 0;
  }
}
</style>
