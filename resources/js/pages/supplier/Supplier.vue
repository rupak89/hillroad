<script setup>
import axios from 'axios';
import { onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Pagination from '../../components/Pagination.vue';
import { useFlashMessage } from '@/composables/useFlashMessage.js';

const route = useRoute();
const router = useRouter();
const { success, error } = useFlashMessage();
const suppliers = ref([]);
const deletingSupplierIds = ref(new Set());

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
  fetchSuppliersList();
  checkForSuccessMessage();
});

// Watch for route changes to handle success messages
watch(() => route.query, () => {
  checkForSuccessMessage();
});

const checkForSuccessMessage = () => {
  if (route.query.success && route.query.supplier) {
    const action = route.query.success === 'created' ? 'created' : 'updated';
    success(
      `Supplier ${action === 'created' ? 'Created' : 'Updated'}`,
      `"${route.query.supplier}" has been ${action} successfully!`
    );

    // Clear the query parameters
    router.replace({ path: '/suppliers' });
  }
};

const fetchSuppliersList = async (page = 1) => {
  isLoading.value = true;
  try {
    const response = await axios.get(`/api/suppliers?page=${page}&per_page=${pagination.value.per_page}`);

      suppliers.value = response.data.suppliers.data;
      pagination.value = {
        current_page: response.data.suppliers.current_page,
        per_page: response.data.suppliers.per_page,
        total: response.data.suppliers.total,
        last_page: response.data.suppliers.last_page,
        from: response.data.suppliers.from,
        to: response.data.suppliers.to
      };

  } catch (error) {
    console.error('Error fetching suppliers:', error);
    error('Loading Error', 'Error loading suppliers. Please refresh the page.');
  } finally {
    isLoading.value = false;
  }
};

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page && page !== pagination.value.current_page) {
    fetchSuppliersList(page);
  }
};

const changePage = (page) => {
  goToPage(page);
};

const changePerPage = (newPerPage) => {
  pagination.value.per_page = newPerPage;
  fetchSuppliersList(1); // Reset to first page when changing per page
};

const deleteSupplier = async (supplierId, supplierName) => {
  if (!confirm(`Are you sure you want to delete supplier "${supplierName}"? This action cannot be undone.`)) {
    return;
  }

  // Add this supplier ID to the deleting set
  deletingSupplierIds.value.add(supplierId);

  try {
    const response = await axios.delete(`/api/suppliers/${supplierId}`);

    if (response.data) {
      // Remove the supplier from the local array
      suppliers.value = suppliers.value.filter(supplier => supplier.id !== supplierId);

      // Update pagination info
      pagination.value.total = Math.max(0, pagination.value.total - 1);
      if (suppliers.value.length === 0 && pagination.value.current_page > 1) {
        // If current page is empty and not the first page, go to previous page
        goToPage(pagination.value.current_page - 1);
      }

      // Show success flash message
      success('Supplier Deleted', `"${supplierName}" has been deleted successfully.`);
    }
  } catch (deleteError) {
    console.error('Error deleting supplier:', deleteError);

    if (deleteError.response && deleteError.response.status === 404) {
      error('Supplier Not Found', 'Supplier not found or already deleted.');
      // Refresh the list in case it was deleted by someone else
      fetchSuppliersList();
    } else {
      error('Delete Failed', 'Error deleting supplier. Please try again.');
    }
  } finally {
    // Remove this supplier ID from the deleting set
    deletingSupplierIds.value.delete(supplierId);
  }
};

const isSupplierDeleting = (supplierId) => {
  return deletingSupplierIds.value.has(supplierId);
};
</script>

<template>


  <section class="section main-section">

    <section class="is-hero-bar">
        <div class="flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0">
            <h1 class="title">
            Suppliers
            </h1>
            <router-link to="/suppliers/add" class="button light">
                <button class="button light">Add Supplier</button>
            </router-link>
        </div>
    </section>


    <div class="card has-table">
      <header class="card-header">
        <p class="card-header-title">
          <span class="icon"><i class="mdi mdi-account-multiple"></i></span>
          Suppliers
        </p>
        <a href="#" class="card-header-icon" @click.prevent="fetchSuppliersList">
          <span class="icon"><i class="mdi mdi-reload"></i></span>
        </a>
      </header>

      <div class="card-content">
        <div v-if="isLoading" class="has-text-centered py-6">
          <div class="button is-loading is-static">Loading suppliers...</div>
        </div>
        <div v-else-if="suppliers.length === 0" class="has-text-centered py-6">
          <span class="icon large"><i class="mdi mdi-account-off mdi-48px"></i></span>
          <p class="mt-4">No suppliers found. Click "Add Supplier" to create your first supplier.</p>
        </div>
        <div v-else>
          <table class="hidden lg:block ">
          <thead>
          <tr >

            <th class="image-cell"></th>
            <th>Name</th>
            <th>Contact</th>
            <th>Phone</th>
            <th></th>
          </tr>
          </thead>
          <tbody>
          <tr v-for="(supplier, index) in suppliers" :key="index">

            <td class="image-cell">
              <span class="icon"><i class="mdi mdi-domain"></i></span>
            </td>
            <td data-label="Name">{{ supplier.supplier_name }}</td>
            <td data-label="Company">{{ supplier.contact_name }}</td>
            <td data-label="City">{{ supplier.phone }}</td>

            <td class="actions-cell">
              <div class="buttons right nowrap">
                <router-link
                  :to="`/suppliers/edit/${supplier.id}`"
                  class="button small blue"
                  :class="{ 'is-disabled': isSupplierDeleting(supplier.id) }"
                  :disabled="isSupplierDeleting(supplier.id)">
                  <span class="icon"><i class="mdi mdi-pencil"></i></span>

                </router-link>
                <button
                  class="button small red"
                  :class="{ 'is-loading': isSupplierDeleting(supplier.id) }"
                  :disabled="isSupplierDeleting(supplier.id)"
                  @click="deleteSupplier(supplier.id, supplier.supplier_name)"
                  type="button">
                  <span class="icon"><i class="mdi mdi-trash-can"></i></span>
                </button>
              </div>
            </td>
          </tr>

          </tbody>
        </table>

        <div class="lg:hidden" >
            <div class="card   mb-3 flex justify-between" v-for="(supplier, index) in suppliers" :key="index">


                <router-link
                    :to="`/suppliers/edit/${supplier.id}`"
                    class="supplier-card space-x-2 p-4  w-full"
                    >
                        <div>
                            <span class="icon"><i class="mdi mdi-domain"></i></span>
                            {{ supplier.supplier_name }}

                        </div>
                        <div>
                            <span class="icon"><i class="mdi mdi-phone"></i></span>
                            {{ supplier.phone }}
                        </div>

                </router-link>


                <div class="buttons right nowrap">

                    <button
                    class="button small red "
                    :class="{ 'is-loading': isSupplierDeleting(supplier.id) }"
                    :disabled="isSupplierDeleting(supplier.id)"
                    @click="deleteSupplier(supplier.id, supplier.supplier_name)"
                    type="button">
                    <span class="icon py-10 px-5"><i class="mdi mdi-trash-can"></i></span>
                    </button>
                </div>

            </div>
        </div>




        <!-- Pagination -->
        <Pagination
          :pagination="pagination"
          item-name="suppliers"
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
/* Custom supplier card styles */
.supplier-card {
  background-color: #dbeafe;
  border-left: 4px solid #3b82f6;
  border-radius: 0.375rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.supplier-card:hover {
  background-color: #bfdbfe;
}
</style>
