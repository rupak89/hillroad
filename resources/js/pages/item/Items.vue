<script setup>
import axios from 'axios';
import { onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Pagination from '../../components/Pagination.vue';
import { useFlashMessage } from '@/composables/useFlashMessage.js';

const route = useRoute();
const router = useRouter();
const { success, error } = useFlashMessage();
const items = ref([]);
const deletingItemIds = ref(new Set());

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
  fetchItemsList();
  checkForSuccessMessage();
});

// Watch for route changes to handle success messages
watch(() => route.query, () => {
  checkForSuccessMessage();
});

const checkForSuccessMessage = () => {
  if (route.query.success && route.query.item) {
    const action = route.query.success === 'created' ? 'created' : 'updated';
    success(
      `Item ${action === 'created' ? 'Created' : 'Updated'}`,
      `"${route.query.item}" has been ${action} successfully!`
    );

    // Clear the query parameters
    router.replace({ path: '/items' });
  }
};

const fetchItemsList = async (page = 1) => {
  isLoading.value = true;
  try {
    const response = await axios.get(`/api/items?page=${page}&per_page=${pagination.value.per_page}`);

    items.value = response.data.items.data;
    pagination.value = {
      current_page: response.data.items.current_page,
      per_page: response.data.items.per_page,
      total: response.data.items.total,
      last_page: response.data.items.last_page,
      from: response.data.items.from,
      to: response.data.items.to
    };

  } catch (error) {
    console.error('Error fetching items:', error);
    error('Loading Error', 'Error loading items. Please refresh the page.');
  } finally {
    isLoading.value = false;
  }
};

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page && page !== pagination.value.current_page) {
    fetchItemsList(page);
  }
};

const changePage = (page) => {
  goToPage(page);
};

const changePerPage = (newPerPage) => {
  pagination.value.per_page = newPerPage;
  fetchItemsList(1); // Reset to first page when changing per page
};

const deleteItem = async (itemId, itemName) => {
  if (!confirm(`Are you sure you want to delete item "${itemName}"? This action cannot be undone.`)) {
    return;
  }

  // Add this item ID to the deleting set
  deletingItemIds.value.add(itemId);

  try {
    const response = await axios.delete(`/api/items/${itemId}`);

    if (response.data) {
      // Remove the item from the local array
      items.value = items.value.filter(item => item.id !== itemId);

      // Update pagination info
      pagination.value.total = Math.max(0, pagination.value.total - 1);
      if (items.value.length === 0 && pagination.value.current_page > 1) {
        // If current page is empty and not the first page, go to previous page
        goToPage(pagination.value.current_page - 1);
      }

      // Show success flash message
      success('Item Deleted', `"${itemName}" has been deleted successfully.`);
    }
  } catch (deleteError) {
    console.error('Error deleting item:', deleteError);

    if (deleteError.response && deleteError.response.status === 404) {
      error('Item Not Found', 'Item not found or already deleted.');
      // Refresh the list in case it was deleted by someone else
      fetchItemsList();
    } else {
      error('Delete Failed', 'Error deleting item. Please try again.');
    }
  } finally {
    // Remove this item ID from the deleting set
    deletingItemIds.value.delete(itemId);
  }
};

const isItemDeleting = (itemId) => {
  return deletingItemIds.value.has(itemId);
};
</script>

<template>
  <section class="section main-section">
    <section class="is-hero-bar">
      <div class="flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0">
        <h1 class="title">
          Items
        </h1>
        <router-link to="/items/add" class="button light">
          <button class="button light">Add Item</button>
        </router-link>
      </div>
    </section>

    <div class="card has-table">
      <header class="card-header">
        <p class="card-header-title">
          <span class="icon"><i class="mdi mdi-package-variant"></i></span>
          Items
        </p>
        <a href="#" class="card-header-icon" @click.prevent="fetchItemsList">
          <span class="icon"><i class="mdi mdi-reload"></i></span>
        </a>
      </header>

      <div class="card-content">
        <div v-if="isLoading" class="has-text-centered py-6">
          <div class="button is-loading is-static">Loading items...</div>
        </div>
        <div v-else-if="items.length === 0" class="has-text-centered py-6">
          <span class="icon large"><i class="mdi mdi-package-off mdi-48px"></i></span>
          <p class="mt-4">No items found. Click "Add Item" to create your first item.</p>
        </div>
        <div v-else>
          <table class="hidden lg:block">
            <thead>
              <tr>
                <th class="image-cell"></th>
                <th>Item Name</th>
                <th>Latest Price</th>
                <th>Supplier</th>
                <th>Brand</th>

                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in items" :key="index">
                <td class="image-cell">
                  <span class="icon"><i class="mdi mdi-package-variant"></i></span>
                </td>
                <td data-label="Item Name">{{ item.item_name }} - {{ item.ordering_unit.name }} </td>
                <td data-label="Latest Price">
                  <div v-if="item.latest_price && item.ordering_unit">
                    <strong>${{ item.latest_price }}</strong>

                  </div>
                  <div v-else class="has-text-grey">
                    N/A
                  </div>
                </td>
                <td data-label="Supplier">{{ item.supplier ? item.supplier.supplier_name : 'N/A' }}</td>
                <td data-label="Brand">{{ item.default_brand ? item.default_brand.name : 'N/A' }}</td>


                <td class="actions-cell">
                  <div class="buttons right nowrap">
                    <router-link
                      :to="`/items/edit/${item.id}`"
                      class="button small blue"
                      :class="{ 'is-disabled': isItemDeleting(item.id) }"
                      :disabled="isItemDeleting(item.id)">
                      <span class="icon"><i class="mdi mdi-pencil"></i></span>
                    </router-link>
                    <button
                      class="button small red"
                      :class="{ 'is-loading': isItemDeleting(item.id) }"
                      :disabled="isItemDeleting(item.id)"
                      @click="deleteItem(item.id, item.item_name)"
                      type="button">
                      <span class="icon"><i class="mdi mdi-trash-can"></i></span>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <div class="lg:hidden">
            <div class="card mb-3 flex justify-between" v-for="(item, index) in items" :key="index">
              <router-link
                :to="`/items/edit/${item.id}`"
                class="item-card space-x-2 p-4 w-full"
                >
                <div>
                  <span class="icon"><i class="mdi mdi-package-variant"></i></span>
                  {{ item.item_name }} - {{ item.ordering_unit.name }}
                </div>
                <div v-if="item.latest_price">
                  <span class="icon"><i class="mdi mdi-currency-usd"></i></span>
                  ${{ item.latest_price }}
                </div>
                <div>
                  <span class="icon"><i class="mdi mdi-domain"></i></span>
                  {{ item.supplier ? item.supplier.supplier_name : 'No Supplier' }}
                </div>

              </router-link>

              <div class="buttons right nowrap">
                <button
                  class="button small red"
                  :class="{ 'is-loading': isItemDeleting(item.id) }"
                  :disabled="isItemDeleting(item.id)"
                  @click="deleteItem(item.id, item.item_name)"
                  type="button">
                  <span class="icon py-10 px-5"><i class="mdi mdi-trash-can"></i></span>
                </button>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <Pagination
            :pagination="pagination"
            item-name="items"
            @go-to-page="goToPage"
            @change-per-page="changePerPage"
          />
        </div>
      </div>
    </div>
  </section>


</template>

<style>
/* Custom item card styles */
.item-card {
  background-color: #dbeafe;
  border-left: 4px solid #f59e0b;
  border-radius: 0.375rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.item-card:hover {
  background-color: #dbeafe;
}
</style>
