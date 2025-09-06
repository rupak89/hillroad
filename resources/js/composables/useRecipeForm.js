import { ref, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useFlashMessage } from '@/composables/useFlashMessage.js';
import axios from 'axios';

// Generate unique ID for form items
const generateId = () => Math.random().toString(36).substr(2, 9);

export function useRecipeForm() {
  const router = useRouter();
  const route = useRoute();
  const { success, error } = useFlashMessage();

  const recipe = ref({
    recipe_name: '',
    instruction: '',
    servings: 1,
    thumbnail: '',
    ingredients: [
      { id: generateId(), item_id: '', unit_id: '', quantity: '' }
    ],
    sub_recipes: [
      { id: generateId(), child_recipe_id: '', quantity: '' }
    ]
  });

  const errors = ref({});
  const isSubmitting = ref(false);
  const recipeId = ref(null);

  const isEditMode = computed(() => route.params.id !== undefined);

  const cleanFormData = () => {
    // Remove empty ingredients and strip temporary IDs
    recipe.value.ingredients = recipe.value.ingredients
      .filter(ingredient => ingredient.item_id && ingredient.unit_id && ingredient.quantity)
      .map(({ id, ...ingredient }) => ingredient);

    // Remove empty sub-recipes and strip temporary IDs
    recipe.value.sub_recipes = recipe.value.sub_recipes
      .filter(subRecipe => subRecipe.child_recipe_id && subRecipe.quantity)
      .map(({ id, ...subRecipe }) => subRecipe);
  };

  const validateRecipe = () => {
    // Check if there's at least one ingredient or one sub-recipe
    const hasIngredients = recipe.value.ingredients.some(ingredient =>
      ingredient.item_id && ingredient.unit_id && ingredient.quantity
    );

    const hasSubRecipes = recipe.value.sub_recipes.some(subRecipe =>
      subRecipe.child_recipe_id && subRecipe.quantity
    );

    if (!hasIngredients && !hasSubRecipes) {
      return {
        isValid: false,
        message: 'A recipe must have at least one ingredient or one sub-recipe.'
      };
    }

    return { isValid: true };
  };

  const submitForm = async () => {
    errors.value = {};
    isSubmitting.value = true;

    try {
      // Validate the recipe first
      const validation = validateRecipe();
      if (!validation.isValid) {
        error('Validation Error', validation.message);
        return;
      }

      // Clean the form data before submitting
      cleanFormData();

      let response;
      if (isEditMode.value) {
        response = await axios.put(`/api/recipes/${route.params.id}`, recipe.value);
      } else {
        response = await axios.post('/api/recipes', recipe.value);
      }

      const action = isEditMode.value ? 'updated' : 'created';
      const recipeName = response.data.recipe.recipe_name;

      success(
        `Recipe ${action === 'created' ? 'Created' : 'Updated'}`,
        `"${recipeName}" has been ${action} successfully!`
      );

      // Redirect to recipes list with success message
      router.push({
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
        errors.value = submitError.response.data.errors || {};
      } else {
        // Other errors
        error(
          'Submit Failed',
          `Error ${isEditMode.value ? 'updating' : 'creating'} recipe. Please try again.`
        );
      }
    } finally {
      isSubmitting.value = false;
    }
  };

  return {
    recipe,
    errors,
    isSubmitting,
    recipeId,
    isEditMode,
    submitForm
  };
}
