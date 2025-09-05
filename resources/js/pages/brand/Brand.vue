<script setup>
import axios from 'axios';
import { onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Pagination from '../../components/Pagination.vue';
import { useFlashMessage } from '@/composables/useFlashMessage.js';

const route = useRoute();
const router = useRouter();
const { success, error } = useFlashMessage();
const brands = ref([]);
const deletingBrandIds = ref(new Set());

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
  fetchBrandsList();
  checkForSuccessMessage();
});

// Watch for route changes to handle success messages
watch(() => route.query, () => {
  checkForSuccessMessage();
});

const checkForSuccessMessage = () => {
  if (route.query.success && route.query.brand) {
    const action = route.query.success === 'created' ? 'created' : 'updated';
    success(
      `Brand ${action === 'created' ? 'Created' : 'Updated'}`,
      `"${route.query.brand}" has been ${action} successfully!`
    );

    // Clear the query parameters
    router.replace({ path: '/brands' });
  }
};

const fetchBrandsList = async (page = 1) => {
  isLoading.value = true;
  try {
    const response = await axios.get(`/api/brands?page=${page}&per_page=${pagination.value.per_page}`);

      brands.value = response.data.brands.data;
      pagination.value = {
        current_page: response.data.brands.current_page,
        per_page: response.data.brands.per_page,
        total: response.data.brands.total,
        last_page: response.data.brands.last_page,
        from: response.data.brands.from,
        to: response.data.brands.to
      };

  } catch (error) {
    console.error('Error fetching brands:', error);
    error('Loading Error', 'Error loading brands. Please refresh the page.');
  } finally {
    isLoading.value = false;
  }
};

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page && page !== pagination.value.current_page) {
    fetchBrandsList(page);
  }
};

const changePage = (page) => {
  goToPage(page);
};

const changePerPage = (newPerPage) => {
  pagination.value.per_page = newPerPage;
  fetchBrandsList(1); // Reset to first page when changing per page
};

const deleteBrand = async (brandId, brandName) => {
  if (!confirm(`Are you sure you want to delete brand "${brandName}"? This action cannot be undone.`)) {
    return;
  }

  // Add this brand ID to the deleting set
  deletingBrandIds.value.add(brandId);

  try {
    const response = await axios.delete(`/api/brands/${brandId}`);

    if (response.data) {
      // Remove the brand from the local array
      brands.value = brands.value.filter(brand => brand.id !== brandId);

      // Update pagination info
      pagination.value.total = Math.max(0, pagination.value.total - 1);
      if (brands.value.length === 0 && pagination.value.current_page > 1) {
        // If current page is empty and not the first page, go to previous page
        goToPage(pagination.value.current_page - 1);
      }

      // Show success flash message
      success('Brand Deleted', `"${brandName}" has been deleted successfully.`);
    }
  } catch (deleteError) {
    console.error('Error deleting brand:', deleteError);

    if (deleteError.response && deleteError.response.status === 404) {
      error('Brand Not Found', 'Brand not found or already deleted.');
      // Refresh the list in case it was deleted by someone else
      fetchBrandsList();
    } else {
      error('Delete Failed', 'Error deleting brand. Please try again.');
    }
  } finally {
    // Remove this brand ID from the deleting set
    deletingBrandIds.value.delete(brandId);
  }
};

const isBrandDeleting = (brandId) => {
  return deletingBrandIds.value.has(brandId);
};
</script>

<template>
  <section class="section main-section">

    <section class="is-hero-bar">
        <div class="flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0">
            <h1 class="title">
            Brands
            </h1>
            <router-link to="/brands/add" class="button light">
                <button class="button light">Add Brand</button>
            </router-link>
        </div>
    </section>

    <div class="card has-table">
      <header class="card-header">
        <p class="card-header-title">
          <span class="icon"><i class="mdi mdi-tag-multiple"></i></span>
          Brands
        </p>
        <a href="#" class="card-header-icon" @click.prevent="fetchBrandsList">
          <span class="icon"><i class="mdi mdi-reload"></i></span>
        </a>
      </header>

      <div class="card-content">
        <div v-if="isLoading" class="has-text-centered py-6">
          <div class="button is-loading is-static">Loading brands...</div>
        </div>
        <div v-else-if="brands.length === 0" class="has-text-centered py-6">
          <span class="icon large"><i class="mdi mdi-tag-off mdi-48px"></i></span>
          <p class="mt-4">No brands found. Click "Add Brand" to create your first brand.</p>
        </div>
        <div v-else>
          <table class="hidden lg:block ">
          <thead>
          <tr >
            <th class="image-cell"></th>
            <th>Name</th>
            <th>Items Count</th>
            <th></th>
          </tr>
          </thead>
          <tbody>
          <tr v-for="(brand, index) in brands" :key="index">
            <td class="image-cell">
              <span class="icon"><i class="mdi mdi-tag"></i></span>
            </td>
            <td data-label="Name">{{ brand.name }}</td>
            <td data-label="Items">{{ brand.items_count || 0 }}</td>

            <td class="actions-cell">
              <div class="buttons right nowrap">
                <router-link
                  :to="`/brands/edit/${brand.id}`"
                  class="button small green"
                  :class="{ 'is-disabled': isBrandDeleting(brand.id) }"
                  :disabled="isBrandDeleting(brand.id)">
                  <span class="icon"><i class="mdi mdi-pencil"></i></span>
                </router-link>
                <button
                  class="button small red"
                  :class="{ 'is-loading': isBrandDeleting(brand.id) }"
                  :disabled="isBrandDeleting(brand.id)"
                  @click="deleteBrand(brand.id, brand.name)"
                  type="button">
                  <span class="icon"><i class="mdi mdi-trash-can"></i></span>
                </button>
              </div>
            </td>
          </tr>
          </tbody>
        </table>

        <div class="lg:hidden" >
            <div class="card mb-3 flex justify-between" v-for="(brand, index) in brands" :key="index">
                <router-link
                    :to="`/brands/edit/${brand.id}`"
                    class="brand-card space-x-2 p-4 w-full"
                    >
                        <div>
                            <span class="icon"><i class="mdi mdi-tag"></i></span>
                            {{ brand.name }}
                        </div>
                        <div class="text-sm text-gray-600">
                            <span class="icon"><i class="mdi mdi-package-variant"></i></span>
                            {{ brand.items_count || 0 }} items
                        </div>
                </router-link>

                <div class="buttons right nowrap">
                    <button
                    class="button small red "
                    :class="{ 'is-loading': isBrandDeleting(brand.id) }"
                    :disabled="isBrandDeleting(brand.id)"
                    @click="deleteBrand(brand.id, brand.name)"
                    type="button">
                    <span class="icon py-10 px-5"><i class="mdi mdi-trash-can"></i></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <Pagination
          :pagination="pagination"
          item-name="brands"
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
/* Custom brand card styles */
.brand-card {
  background-color: #fef3c7;
  border-left: 4px solid #f59e0b;
  border-radius: 0.375rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.brand-card:hover {
  background-color: #fde68a;
}
</style>
