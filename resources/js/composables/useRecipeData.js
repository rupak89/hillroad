import { ref } from 'vue';
import axios from 'axios';
import { useRoute } from 'vue-router';
import { useFlashMessage } from '@/composables/useFlashMessage.js';
import useItemStore from '@/stores/item.js';

// Generate unique ID for form items
const generateId = () => Math.random().toString(36).substr(2, 9);

export function useRecipeData() {
  const route = useRoute();
  const { error } = useFlashMessage();
  const itemStore = useItemStore();

  const items = ref([]);
  const recipes = ref([]);
  const isLoading = ref(true);

  const fetchDropdownData = async () => {
    try {
      const excludeId = route.params.id || null;
      const url = excludeId ? `/api/recipes-dropdown-data/${excludeId}` : '/api/recipes-dropdown-data';
      const response = await axios.get(url);

      items.value = response.data.items;
      recipes.value = response.data.recipes;
    } catch (fetchError) {
      console.error('Error fetching dropdown data:', fetchError);
      error('Loading Error', 'Error loading form data. Please refresh the page.');
    }
  };

  const fetchRecipe = async (recipe, recipeId) => {
    if (!route.params.id) return;

    try {
      const response = await axios.get(`/api/recipes/${route.params.id}`);
      const recipeData = response.data.recipe;

      console.log('Recipe data received:', recipeData);
      console.log('Sub-recipes data:', recipeData.sub_recipes || recipeData.subRecipes);

      recipeId.value = recipeData.id;
      recipe.value.recipe_name = recipeData.recipe_name;
      recipe.value.instruction = recipeData.instruction || '';
      recipe.value.servings = recipeData.servings || 1;
      recipe.value.thumbnail = recipeData.thumbnail || '';

      // Populate ingredients
      if (recipeData.items && recipeData.items.length > 0) {
        recipe.value.ingredients = recipeData.items.map(item => ({
          id: generateId(),
          item_id: item.id,
          unit_id: item.pivot.unit_id,
          quantity: item.pivot.quantity
        }));
      } else {
        recipe.value.ingredients = [{ id: generateId(), item_id: '', unit_id: '', quantity: '' }];
      }

      // Populate sub-recipes
      if (recipeData.sub_recipes && recipeData.sub_recipes.length > 0) {
        recipe.value.sub_recipes = recipeData.sub_recipes.map(subRecipe => ({
          id: generateId(),
          child_recipe_id: subRecipe.id,
          quantity: subRecipe.pivot.quantity
        }));
      } else if (recipeData.subRecipes && recipeData.subRecipes.length > 0) {
        recipe.value.sub_recipes = recipeData.subRecipes.map(subRecipe => ({
          id: generateId(),
          child_recipe_id: subRecipe.id,
          quantity: subRecipe.pivot.quantity
        }));
      } else {
        recipe.value.sub_recipes = [{ id: generateId(), child_recipe_id: '', quantity: '' }];
      }

    } catch (fetchError) {
      console.error('Error fetching recipe:', fetchError);
      error('Loading Error', 'Error loading recipe data.');
      throw fetchError;
    }
  };

  const loadData = async (recipe, recipeId) => {
    isLoading.value = true;
    try {
      // Fetch units from store and dropdown data in parallel
      await Promise.all([
        itemStore.fetchUnits(),
        fetchDropdownData()
      ]);

      if (route.params.id) {
        await fetchRecipe(recipe, recipeId);
      }
    } catch (loadError) {
      console.error('Error loading data:', loadError);
      error('Loading Error', 'Error loading form data. Please refresh the page.');
      throw loadError;
    } finally {
      isLoading.value = false;
    }
  };

  return {
    items,
    recipes,
    isLoading,
    itemStore,
    loadData
  };
}
