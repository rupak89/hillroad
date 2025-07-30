<script setup>
import axios from 'axios';
import { onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Pagination from '../../components/Pagination.vue';
import { useFlashMessage } from '@/composables/useFlashMessage.js';

const route = useRoute();
const router = useRouter();
const { success, error } = useFlashMessage();
const recipes = ref([]);
const deletingRecipeIds = ref(new Set());

const pagination = ref({
  current_page: 1,
  per_page: 10,
  total: 0,
  last_page: 1,
  from: 0,
  to: 0
});
const isLoading = ref(false);

onMounted(() => {
  fetchRecipesList();
  checkForSuccessMessage();
});

// Watch for route changes to handle success messages
watch(() => route.query, () => {
  checkForSuccessMessage();
});

const checkForSuccessMessage = () => {
  if (route.query.success && route.query.recipe) {
    const action = route.query.success === 'created' ? 'created' : 'updated';
    success(
      `Recipe ${action === 'created' ? 'Created' : 'Updated'}`,
      `"${route.query.recipe}" has been ${action} successfully!`
    );

    // Clear the query parameters
    router.replace({ path: '/recipes' });
  }
};

const fetchRecipesList = async (page = 1) => {
  isLoading.value = true;
  try {
    const response = await axios.get(`/api/recipes?page=${page}&per_page=${pagination.value.per_page}`);

    recipes.value = response.data.recipes.data;
    pagination.value = {
      current_page: response.data.recipes.current_page,
      per_page: response.data.recipes.per_page,
      total: response.data.recipes.total,
      last_page: response.data.recipes.last_page,
      from: response.data.recipes.from,
      to: response.data.recipes.to
    };

  } catch (fetchError) {
    console.error('Error fetching recipes:', fetchError);
    error('Loading Error', 'Error loading recipes. Please refresh the page.');
  } finally {
    isLoading.value = false;
  }
};

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page && page !== pagination.value.current_page) {
    fetchRecipesList(page);
  }
};

const changePage = (page) => {
  goToPage(page);
};

const changePerPage = (newPerPage) => {
  pagination.value.per_page = newPerPage;
  fetchRecipesList(1); // Reset to first page when changing per page
};

const deleteRecipe = async (recipeId, recipeName) => {
  if (!confirm(`Are you sure you want to delete recipe "${recipeName}"? This action cannot be undone.`)) {
    return;
  }

  // Add this recipe ID to the deleting set
  deletingRecipeIds.value.add(recipeId);

  try {
    const response = await axios.delete(`/api/recipes/${recipeId}`);

    if (response.data) {
      // Remove the recipe from the local array
      recipes.value = recipes.value.filter(recipe => recipe.id !== recipeId);

      // Update pagination info
      pagination.value.total = Math.max(0, pagination.value.total - 1);
      if (recipes.value.length === 0 && pagination.value.current_page > 1) {
        // If current page is empty and not the first page, go to previous page
        goToPage(pagination.value.current_page - 1);
      }

      // Show success flash message
      success('Recipe Deleted', `"${recipeName}" has been deleted successfully.`);
    }
  } catch (deleteError) {
    console.error('Error deleting recipe:', deleteError);

    if (deleteError.response && deleteError.response.status === 404) {
      error('Recipe Not Found', 'Recipe not found or already deleted.');
      // Refresh the list in case it was deleted by someone else
      fetchRecipesList();
    } else {
      error('Delete Failed', 'Error deleting recipe. Please try again.');
    }
  } finally {
    // Remove this recipe ID from the deleting set
    deletingRecipeIds.value.delete(recipeId);
  }
};

const isRecipeDeleting = (recipeId) => {
  return deletingRecipeIds.value.has(recipeId);
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};
</script>

<template>
  <section class="section main-section">

    <section class="is-hero-bar">
        <div class="flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0">
            <h1 class="title">
            Recipes
            </h1>
            <router-link to="/recipes/add" class="button light">
                <button class="button light">Add Recipe</button>
            </router-link>
        </div>
    </section>

    <div class="card has-table">
      <header class="card-header">
        <p class="card-header-title">
          <span class="icon"><i class="mdi mdi-chef-hat"></i></span>
          Recipes
        </p>
        <a href="#" class="card-header-icon" @click.prevent="fetchRecipesList">
          <span class="icon"><i class="mdi mdi-reload"></i></span>
        </a>
      </header>

      <div class="card-content">
        <div v-if="isLoading" class="has-text-centered py-6">
          <div class="button is-loading is-static">Loading recipes...</div>
        </div>
        <div v-else-if="recipes.length === 0" class="has-text-centered py-6">
          <span class="icon large"><i class="mdi mdi-chef-hat mdi-48px"></i></span>
          <p class="mt-4">No recipes found. Click "Add Recipe" to create your first recipe.</p>
        </div>
        <div v-else>
          <table class="hidden lg:block">
          <thead>
          <tr>
            <th class="image-cell"></th>
            <th>Recipe Name</th>
            <th>Instructions</th>
            <th>Created At</th>
            <th></th>
          </tr>
          </thead>
          <tbody>
          <tr v-for="(recipe, index) in recipes" :key="index">
            <td class="image-cell">
              <span class="icon"><i class="mdi mdi-chef-hat"></i></span>
            </td>
            <td data-label="Name">{{ recipe.recipe_name }}</td>
            <td data-label="Instructions">
              <span v-if="recipe.instruction">
                {{ recipe.instruction.length > 50 ? recipe.instruction.substring(0, 50) + '...' : recipe.instruction }}
              </span>
              <span v-else class="text-gray-400">No instructions</span>
            </td>
            <td data-label="Created">
              <small class="text-gray-500">{{ formatDate(recipe.created_at) }}</small>
            </td>

            <td class="actions-cell">
              <div class="buttons right nowrap">
                <router-link
                  :to="`/recipes/edit/${recipe.id}`"
                  class="button small blue"
                  :class="{ 'is-disabled': isRecipeDeleting(recipe.id) }"
                  :disabled="isRecipeDeleting(recipe.id)">
                  <span class="icon"><i class="mdi mdi-pencil"></i></span>
                </router-link>
                <button
                  class="button small red"
                  :class="{ 'is-loading': isRecipeDeleting(recipe.id) }"
                  :disabled="isRecipeDeleting(recipe.id)"
                  @click="deleteRecipe(recipe.id, recipe.recipe_name)"
                  type="button">
                  <span class="icon"><i class="mdi mdi-trash-can"></i></span>
                </button>
              </div>
            </td>
          </tr>

          </tbody>
        </table>

        <div class="lg:hidden" >
            <div class="card mb-3 flex justify-between" v-for="(recipe, index) in recipes" :key="index">

                <router-link
                    :to="`/recipes/edit/${recipe.id}`"
                    class="recipe-card space-x-2 p-4 w-full"
                    >
                        <div>
                            <span class="icon"><i class="mdi mdi-chef-hat"></i></span>
                            {{ recipe.recipe_name }}
                        </div>
                        <div v-if="recipe.instruction" class="text-sm text-gray-600">
                            {{ recipe.instruction.length > 30 ? recipe.instruction.substring(0, 30) + '...' : recipe.instruction }}
                        </div>
                        <div class="text-xs text-gray-400">
                            {{ formatDate(recipe.created_at) }}
                        </div>
                </router-link>

                <div class="buttons right nowrap">
                    <button
                    class="button small red"
                    :class="{ 'is-loading': isRecipeDeleting(recipe.id) }"
                    :disabled="isRecipeDeleting(recipe.id)"
                    @click="deleteRecipe(recipe.id, recipe.recipe_name)"
                    type="button">
                    <span class="icon py-10 px-5"><i class="mdi mdi-trash-can"></i></span>
                    </button>
                </div>

            </div>
        </div>

        <!-- Pagination -->
        <Pagination
          :pagination="pagination"
          item-name="recipes"
          @go-to-page="goToPage"
          @change-per-page="changePerPage"
        />
        </div>
      </div>
    </div>
  </section>

  <section class="section main-section">
    <div class="card empty hidden">
      <div class="card-content">
        <div>
          <span class="icon large"><i class="mdi mdi-emoticon-sad mdi-48px"></i></span>
        </div>
        <p>Nothing's here…</p>
      </div>
    </div>
  </section>
</template>

<style>
/* Custom recipe card styles */
.recipe-card {
  background-color: #fef3c7;
  border-left: 4px solid #f59e0b;
  border-radius: 0.375rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.recipe-card:hover {
  background-color: #fde68a;
}
</style>
