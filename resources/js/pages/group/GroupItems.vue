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
const group = ref(null);
const deletingItemIds = ref(new Set());
const searchQuery = ref('');
const searchTimeout = ref(null);

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
  fetchGroupItems();
});

const fetchGroupItems = async (page = 1, search = '') => {
  isLoading.value = true;
  try {
    const groupId = route.params.id;
    const response = await axios.get(`/api/groups/${groupId}/items?page=${page}&per_page=${pagination.value.per_page}&search=${search}`);

    items.value = response.data.items.data;
    group.value = response.data.group;
    pagination.value = {
      current_page: response.data.items.current_page,
      per_page: response.data.items.per_page,
      total: response.data.items.total,
      last_page: response.data.items.last_page,
      from: response.data.items.from,
      to: response.data.items.to
    };

  } catch (fetchError) {
    console.error('Error fetching group items:', fetchError);
    if (fetchError.response && fetchError.response.status === 404) {
      error('Group Not Found', 'The group you are looking for does not exist.');
      router.push('/groups');
    } else {
      error('Loading Error', 'Error loading group items. Please refresh the page.');
    }
  } finally {
    isLoading.value = false;
  }
};

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page && page !== pagination.value.current_page) {
    fetchGroupItems(page, searchQuery.value);
  }
};

const changePage = (page) => {
  goToPage(page);
};

const changePerPage = (newPerPage) => {
  pagination.value.per_page = newPerPage;
  fetchGroupItems(1, searchQuery.value); // Reset to first page when changing per page
};

// Watch for search query changes
watch(searchQuery, (newQuery) => {
  // Clear the existing timeout
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value);
  }

  // Set a new timeout for 500ms
  searchTimeout.value = setTimeout(() => {
    fetchGroupItems(1, newQuery);
  }, 500);
});

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
      fetchGroupItems(pagination.value.current_page, searchQuery.value);
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

const formatPrice = (price) => {
  if (!price) return 'N/A';
  return `$${parseFloat(price).toFixed(2)}`;
};
</script>

<template>
  <section class="section main-section">

    <router-link to="/groups" class="button is-small">
      <span class="icon"><i class="mdi mdi-arrow-left"></i></span>
      <span class="sm:hidden lg:inline">Back to Groups</span>
    </router-link>

    <section class="is-hero-bar">
      <div class="flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0">
        <h1 class="title">
          <span v-if="group">Items in "{{ group.name }}"</span>
          <span v-else>Group Items</span>
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
          <span v-if="group">{{ group.name }} Items</span>
          <span v-else>Group Items</span>
        </p>
        <a href="#" class="card-header-icon" @click.prevent="fetchGroupItems(pagination.current_page, searchQuery)">
          <span class="icon"><i class="mdi mdi-reload"></i></span>
        </a>
      </header>

      <div class="card-content">
        <!-- Search Bar -->
        <div class="field">
          <div class="control has-icons-left">
            <input
              class="input"
              type="text"
              placeholder="Search items..."
              v-model="searchQuery"
            >
            <span class="icon is-small is-left">
              <i class="mdi mdi-magnify"></i>
            </span>
          </div>
        </div>

        <div v-if="isLoading" class="has-text-centered py-6">
          <div class="button is-loading is-static">Loading items...</div>
        </div>
        <div v-else-if="items.length === 0" class="has-text-centered py-6">
          <span class="icon large"><i class="mdi mdi-package-variant-off mdi-48px"></i></span>
          <p class="mt-4" v-if="searchQuery">No items found matching "{{ searchQuery }}" in this group.</p>
          <p class="mt-4" v-else-if="group">No items found in "{{ group.name }}". Click "Add Item" to create your first item.</p>
          <p class="mt-4" v-else>No items found in this group.</p>
        </div>
        <div v-else>
          <!-- Desktop Table -->
          <table class="hidden lg:block">
            <thead>
              <tr>
                <th class="image-cell"></th>
                <th>Item Name</th>
                <th>Supplier</th>
                <th>Brand</th>
                <th>Latest Price</th>
                <th>Units</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in items" :key="index">
                <td class="image-cell">
                  <span class="icon"><i class="mdi mdi-package-variant"></i></span>
                </td>
                <td data-label="Name">{{ item.item_name }}</td>
                <td data-label="Supplier">{{ item.supplier?.supplier_name || 'N/A' }}</td>
                <td data-label="Brand">{{ item.default_brand?.name || 'N/A' }}</td>
                <td data-label="Latest Price">{{ formatPrice(item.latest_price) }}</td>
                <td data-label="Units">
                  <div class="text-sm">
                    <div>Ordering: {{ item.ordering_unit?.name || 'N/A' }}</div>
                    <div>Counting: {{ item.counting_unit?.name || 'N/A' }}</div>
                  </div>
                </td>
                <td class="actions-cell">
                  <div class="buttons right nowrap">
                    <router-link
                      :to="`/items/edit/${item.id}`"
                      class="button small green"
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

          <!-- Mobile Cards -->
          <div class="lg:hidden">
            <div class="card mb-3" v-for="(item, index) in items" :key="index">
              <div class="card-content">
                <div class="flex justify-between items-start">
                  <router-link
                    :to="`/items/edit/${item.id}`"
                    class="item-card flex-1"
                    :class="{ 'is-disabled': isItemDeleting(item.id) }">
                    <div class="font-semibold">{{ item.item_name }}</div>
                    <div class="text-sm text-gray-600 mt-1">
                      <div>Supplier: {{ item.supplier?.supplier_name || 'N/A' }}</div>
                      <div>Brand: {{ item.default_brand?.name || 'N/A' }}</div>
                      <div>Price: {{ formatPrice(item.latest_price) }}</div>
                      <div>Units: {{ item.ordering_unit?.name || 'N/A' }} / {{ item.counting_unit?.name || 'N/A' }}</div>
                    </div>
                  </router-link>
                  <button
                    class="button small red ml-3"
                    :class="{ 'is-loading': isItemDeleting(item.id) }"
                    :disabled="isItemDeleting(item.id)"
                    @click="deleteItem(item.id, item.item_name)"
                    type="button">
                    <span class="icon"><i class="mdi mdi-trash-can"></i></span>
                  </button>
                </div>
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

<style scoped>
/* Custom item card styles */
.item-card {
  color: inherit;
  text-decoration: none;
}

.item-card:hover {
  background-color: #f5f5f5;
  border-radius: 0.375rem;
}

.item-card.is-disabled {
  opacity: 0.6;
  pointer-events: none;
}
</style>
