<template>
  <section class="section main-section">

    <router-link to="/" class="button is-small">
      <span class="icon"><i class="mdi mdi-arrow-left"></i></span>
      <span class="sm:hidden lg:inline">Dashboard</span>
    </router-link>

    <section class="is-hero-bar">
      <div class="flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0">
        <h1 class="title">
          Menu Management
        </h1>
        <router-link to="/menus/add" class="button light">
          <button class="button light">Add Menu</button>
        </router-link>
      </div>
    </section>

    <div class="card has-table">
      <header class="card-header">
        <p class="card-header-title">
          <span class="icon"><i class="mdi mdi-food"></i></span>
          Menus
        </p>
        <a href="#" class="card-header-icon" @click.prevent="fetchMenusList">
          <span class="icon"><i class="mdi mdi-reload"></i></span>
        </a>
      </header>

      <div class="card-content">
        <div v-if="isLoading" class="has-text-centered py-6">
          <div class="button is-loading is-static">Loading menus...</div>
        </div>
        <div v-else-if="menus.length === 0" class="has-text-centered py-6">
          <span class="icon large"><i class="mdi mdi-food-off mdi-48px"></i></span>
          <p class="mt-4">No menus found. Click "Add Menu" to create your first menu.</p>
        </div>
        <div v-else>
          <table class="hidden lg:block">
            <thead>
              <tr>
                <th class="image-cell"></th>
                <th>Menu Name</th>
                <th>Head Count</th>
                <th>Segments</th>
                <th>Total Cost</th>
                <th>Cost per Person</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(menu, index) in menus" :key="index">
                <td class="image-cell">
                  <span class="icon"><i class="mdi mdi-food"></i></span>
                </td>
                <td data-label="Menu Name">
                  <div>
                    <strong>{{ menu.name }}</strong>
                    <div v-if="menu.description" class="text-sm text-gray-600">
                      {{ menu.description }}
                    </div>
                  </div>
                </td>
                <td data-label="Head Count">{{ menu.target_head_count }} guests</td>
                <td data-label="Segments">{{ menu.segments_count || menu.segments?.length || 0 }} segments</td>
                <td data-label="Total Cost">
                  <div v-if="menu.total_cost">
                    <strong>${{ parseFloat(menu.total_cost).toFixed(2) }}</strong>
                  </div>
                  <div v-else class="has-text-grey">
                    N/A
                  </div>
                </td>
                <td data-label="Cost per Person">
                  <div v-if="menu.cost_per_person">
                    <strong>${{ parseFloat(menu.cost_per_person).toFixed(2) }}</strong>
                  </div>
                  <div v-else class="has-text-grey">
                    N/A
                  </div>
                </td>
                <td class="actions-cell">
                  <div class="buttons right nowrap">
                    <router-link
                      :to="`/menus/edit/${menu.id}`"
                      class="button small blue"
                      :class="{ 'is-disabled': isMenuDeleting(menu.id) }"
                      :disabled="isMenuDeleting(menu.id)">
                      <span class="icon"><i class="mdi mdi-pencil"></i></span>
                    </router-link>
                    <router-link
                      :to="`/menus/${menu.id}/print`"
                      class="button small purple"
                      :class="{ 'is-disabled': isMenuDeleting(menu.id) }"
                      :disabled="isMenuDeleting(menu.id)"
                      title="Print Customer Menu">
                      <span class="icon"><i class="mdi mdi-printer"></i></span>
                    </router-link>
                    <button
                      class="button small green"
                      :disabled="isMenuDeleting(menu.id)"
                      @click="duplicateMenu(menu.id, menu.name)"
                      type="button"
                      title="Duplicate Menu">
                      <span class="icon"><i class="mdi mdi-content-copy"></i></span>
                    </button>
                    <button
                      class="button small orange"
                      :disabled="isMenuDeleting(menu.id)"
                      @click="viewCostBreakdown(menu.id)"
                      type="button"
                      title="Cost Breakdown">
                      <span class="icon"><i class="mdi mdi-calculator"></i></span>
                    </button>
                    <button
                      class="button small red"
                      :class="{ 'is-loading': isMenuDeleting(menu.id) }"
                      :disabled="isMenuDeleting(menu.id)"
                      @click="deleteMenu(menu.id, menu.name)"
                      type="button">
                      <span class="icon"><i class="mdi mdi-trash-can"></i></span>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <div class="lg:hidden">
            <div class="card mb-3 flex justify-between" v-for="(menu, index) in menus" :key="index">
              <router-link
                :to="`/menus/edit/${menu.id}`"
                class="menu-card space-x-2 p-4 w-full">
                <div>
                  <span class="icon"><i class="mdi mdi-food"></i></span>
                  <strong>{{ menu.name }}</strong>
                </div>
                <div v-if="menu.description" class="text-sm text-gray-600">
                  {{ menu.description }}
                </div>
                <div class="flex justify-between mt-2">
                  <div>
                    <span class="icon"><i class="mdi mdi-account-group"></i></span>
                    {{ menu.target_head_count }} guests
                  </div>
                  <div v-if="menu.cost_per_person">
                    <span class="icon"><i class="mdi mdi-currency-usd"></i></span>
                    ${{ parseFloat(menu.cost_per_person).toFixed(2) }}/person
                  </div>
                </div>
              </router-link>

              <div class="buttons right nowrap flex-col">
                <button
                  class="button small green mb-1"
                  :disabled="isMenuDeleting(menu.id)"
                  @click="duplicateMenu(menu.id, menu.name)"
                  type="button">
                  <span class="icon"><i class="mdi mdi-content-copy"></i></span>
                </button>
                <button
                  class="button small orange mb-1"
                  :disabled="isMenuDeleting(menu.id)"
                  @click="viewCostBreakdown(menu.id)"
                  type="button">
                  <span class="icon"><i class="mdi mdi-calculator"></i></span>
                </button>
                <button
                  class="button small red"
                  :class="{ 'is-loading': isMenuDeleting(menu.id) }"
                  :disabled="isMenuDeleting(menu.id)"
                  @click="deleteMenu(menu.id, menu.name)"
                  type="button">
                  <span class="icon"><i class="mdi mdi-trash-can"></i></span>
                </button>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <Pagination
            :pagination="pagination"
            item-name="menus"
            @go-to-page="goToPage"
            @change-per-page="changePerPage"
          />
        </div>
      </div>
    </div>

    <!-- Cost Breakdown Modal -->
    <div v-if="showCostModal" class="modal is-active">
      <div class="modal-background" @click="closeCostModal"></div>
      <div class="modal-card">
        <header class="modal-card-head">
          <p class="modal-card-title">Cost Breakdown</p>
          <button class="delete" @click="closeCostModal"></button>
        </header>
        <section class="modal-card-body">
          <div v-if="costBreakdown">
            <h4 class="subtitle">{{ costBreakdown.menu_name }}</h4>
            <p class="mb-4">Target: {{ costBreakdown.target_head_count }} guests</p>

            <div v-for="segment in costBreakdown.segments" :key="segment.id" class="mb-4">
              <h5 class="title is-5">{{ segment.name }}</h5>
              <div v-for="item in segment.items" :key="item.id" class="box">
                <div class="level">
                  <div class="level-left">
                    <div>
                      <strong>{{ item.recipe_name }}</strong>
                      <br>
                      <small>{{ item.formatted_quantity }}</small>
                      <div v-if="item.notes" class="text-sm text-gray-600">
                        {{ item.notes }}
                      </div>
                    </div>
                  </div>
                  <div class="level-right">
                    <div class="has-text-right">
                      <div>${{ parseFloat(item.cost_per_unit).toFixed(2) }} per unit</div>
                      <strong>${{ parseFloat(item.total_cost).toFixed(2) }}</strong>
                    </div>
                  </div>
                </div>
              </div>
              <div class="has-text-right">
                <strong>Segment Total: ${{ parseFloat(segment.total_cost).toFixed(2) }}</strong>
              </div>
            </div>

            <div class="box has-background-light">
                            <div class="level">
                <div class="level-left">
                  Total Cost:
                </div>
                <div class="level-right">
                  <strong>${{ parseFloat(costBreakdown.totals.total_cost).toFixed(2) }}</strong>
                </div>
              </div>
              <div class="level">
                <div class="level-left">
                  <strong>Cost per Person:</strong>
                </div>
                <div class="level-right">
                  <strong class="has-text-primary">${{ parseFloat(costBreakdown.totals.cost_per_person).toFixed(2) }}</strong>
                </div>
              </div>
            </div>
          </div>
        </section>
        <footer class="modal-card-foot">
          <button class="button" @click="closeCostModal">Close</button>
        </footer>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import { useFlashMessage } from '@/composables/useFlashMessage.js'
import Pagination from '../../components/Pagination.vue'

const route = useRoute()
const router = useRouter()
const { success, error } = useFlashMessage()

// Data
const menus = ref([])
const deletingMenuIds = ref(new Set())
const pagination = reactive({
  current_page: 1,
  per_page: 10,
  total: 0,
  last_page: 1,
  from: 0,
  to: 0
})
const isLoading = ref(false)
const showCostModal = ref(false)
const costBreakdown = ref(null)

// Methods
const checkForSuccessMessage = () => {
  if (route.query.success && route.query.menu) {
    const action = route.query.success === 'created' ? 'created' : 'updated'
    success(
      `Menu ${action === 'created' ? 'Created' : 'Updated'}`,
      `"${route.query.menu}" has been ${action} successfully!`
    )

    // Clear the query parameters
    router.replace({ path: '/menus' })
  }
}

const fetchMenusList = async (page = 1) => {
  isLoading.value = true
  try {
    const response = await axios.get(`/api/menus?page=${page}&per_page=${pagination.per_page}`)

    menus.value = response.data.menus.data
    pagination.current_page = response.data.menus.current_page
    pagination.per_page = response.data.menus.per_page
    pagination.total = response.data.menus.total
    pagination.last_page = response.data.menus.last_page
    pagination.from = response.data.menus.from
    pagination.to = response.data.menus.to

  } catch (fetchError) {
    console.error('Error fetching menus:', fetchError)
    error('Loading Error', 'Error loading menus. Please refresh the page.')
  } finally {
    isLoading.value = false
  }
}

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.last_page && page !== pagination.current_page) {
    fetchMenusList(page)
  }
}

const changePerPage = (newPerPage) => {
  pagination.per_page = newPerPage
  fetchMenusList(1)
}

const deleteMenu = async (menuId, menuName) => {
  if (!confirm(`Are you sure you want to delete menu "${menuName}"? This action cannot be undone.`)) {
    return
  }

  deletingMenuIds.value.add(menuId)

  try {
    await axios.delete(`/api/menus/${menuId}`)

    menus.value = menus.value.filter(menu => menu.id !== menuId)
    pagination.total = Math.max(0, pagination.total - 1)

    if (menus.value.length === 0 && pagination.current_page > 1) {
      goToPage(pagination.current_page - 1)
    }

    success('Menu Deleted', `"${menuName}" has been deleted successfully.`)
  } catch (deleteError) {
    console.error('Error deleting menu:', deleteError)
    error('Delete Failed', 'Error deleting menu. Please try again.')
  } finally {
    deletingMenuIds.value.delete(menuId)
  }
}

const duplicateMenu = async (menuId, menuName) => {
  try {
    const response = await axios.post(`/api/menus/${menuId}/duplicate`)

    success('Menu Duplicated', `"${menuName}" has been duplicated successfully.`)
    fetchMenusList() // Refresh the list
  } catch (duplicateError) {
    console.error('Error duplicating menu:', duplicateError)
    error('Duplicate Failed', 'Error duplicating menu. Please try again.')
  }
}

const viewCostBreakdown = async (menuId) => {
  try {
    const response = await axios.get(`/api/menus/${menuId}/cost-breakdown`)
    costBreakdown.value = response.data.breakdown
    showCostModal.value = true
  } catch (costError) {
    console.error('Error fetching cost breakdown:', costError)
    error('Cost Calculation Failed', 'Error calculating menu costs.')
  }
}

const closeCostModal = () => {
  showCostModal.value = false
  costBreakdown.value = null
}

const isMenuDeleting = (menuId) => {
  return deletingMenuIds.value.has(menuId)
}

// Component lifecycle
onMounted(() => {
  fetchMenusList()
  checkForSuccessMessage()
})
</script>

<style scoped>
.menu-card {
  background-color: #f0f9ff;
  border-left: 4px solid #0ea5e9;
  border-radius: 0.375rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.menu-card:hover {
  background-color: #e0f2fe;
}

.modal-card {
  max-width: 800px;
  width: 90vw;
}
</style>
