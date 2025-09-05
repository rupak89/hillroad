<script setup>
import axios from 'axios';
import { onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Pagination from '../../components/Pagination.vue';
import { useFlashMessage } from '@/composables/useFlashMessage.js';

const route = useRoute();
const router = useRouter();
const { success, error } = useFlashMessage();
const groups = ref([]);
const deletingGroupIds = ref(new Set());

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
  fetchGroupsList();
  checkForSuccessMessage();
});

// Watch for route changes to handle success messages
watch(() => route.query, () => {
  checkForSuccessMessage();
});

const checkForSuccessMessage = () => {
  if (route.query.success && route.query.group) {
    const action = route.query.success === 'created' ? 'created' : 'updated';
    success(
      `Group ${action === 'created' ? 'Created' : 'Updated'}`,
      `"${route.query.group}" has been ${action} successfully!`
    );

    // Clear the query parameters
    router.replace({ path: '/groups' });
  }
};

const fetchGroupsList = async (page = 1) => {
  isLoading.value = true;
  try {
    const response = await axios.get(`/api/groups?page=${page}&per_page=${pagination.value.per_page}`);

      groups.value = response.data.groups.data;
      pagination.value = {
        current_page: response.data.groups.current_page,
        per_page: response.data.groups.per_page,
        total: response.data.groups.total,
        last_page: response.data.groups.last_page,
        from: response.data.groups.from,
        to: response.data.groups.to
      };

  } catch (error) {
    console.error('Error fetching groups:', error);
    error('Loading Error', 'Error loading groups. Please refresh the page.');
  } finally {
    isLoading.value = false;
  }
};

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page && page !== pagination.value.current_page) {
    fetchGroupsList(page);
  }
};

const changePage = (page) => {
  goToPage(page);
};

const changePerPage = (newPerPage) => {
  pagination.value.per_page = newPerPage;
  fetchGroupsList(1); // Reset to first page when changing per page
};

const deleteGroup = async (groupId, groupName) => {
  if (!confirm(`Are you sure you want to delete group "${groupName}"? This action cannot be undone.`)) {
    return;
  }

  // Add this group ID to the deleting set
  deletingGroupIds.value.add(groupId);

  try {
    const response = await axios.delete(`/api/groups/${groupId}`);

    if (response.data) {
      // Remove the group from the local array
      groups.value = groups.value.filter(group => group.id !== groupId);

      // Update pagination info
      pagination.value.total = Math.max(0, pagination.value.total - 1);
      if (groups.value.length === 0 && pagination.value.current_page > 1) {
        // If current page is empty and not the first page, go to previous page
        goToPage(pagination.value.current_page - 1);
      }

      // Show success flash message
      success('Group Deleted', `"${groupName}" has been deleted successfully.`);
    }
  } catch (deleteError) {
    console.error('Error deleting group:', deleteError);

    if (deleteError.response && deleteError.response.status === 404) {
      error('Group Not Found', 'Group not found or already deleted.');
      // Refresh the list in case it was deleted by someone else
      fetchGroupsList();
    } else {
      error('Delete Failed', 'Error deleting group. Please try again.');
    }
  } finally {
    // Remove this group ID from the deleting set
    deletingGroupIds.value.delete(groupId);
  }
};

const isGroupDeleting = (groupId) => {
  return deletingGroupIds.value.has(groupId);
};
</script>

<template>
  <section class="section main-section">

    <router-link to="/" class="button is-small">
      <span class="icon"><i class="mdi mdi-arrow-left"></i></span>
      <span class="sm:hidden lg:inline">Dashboard</span>
    </router-link>

    <section class="is-hero-bar">
        <div class="flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0">
            <h1 class="title">
            Groups
            </h1>
            <router-link to="/groups/add" class="button light">
                <button class="button light">Add Group</button>
            </router-link>
        </div>
    </section>

    <div class="card has-table">
      <header class="card-header">
        <p class="card-header-title">
          <span class="icon"><i class="mdi mdi-folder-multiple"></i></span>
          Groups
        </p>
        <a href="#" class="card-header-icon" @click.prevent="fetchGroupsList">
          <span class="icon"><i class="mdi mdi-reload"></i></span>
        </a>
      </header>

      <div class="card-content">
        <div v-if="isLoading" class="has-text-centered py-6">
          <div class="button is-loading is-static">Loading groups...</div>
        </div>
        <div v-else-if="groups.length === 0" class="has-text-centered py-6">
          <span class="icon large"><i class="mdi mdi-folder-off mdi-48px"></i></span>
          <p class="mt-4">No groups found. Click "Add Group" to create your first group.</p>
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
          <tr v-for="(group, index) in groups" :key="index">
            <td class="image-cell">
              <span class="icon"><i class="mdi mdi-folder"></i></span>
            </td>
            <td data-label="Name">{{ group.name }}</td>
            <td data-label="Items">{{ group.items_count || 0 }}</td>

            <td class="actions-cell">
              <div class="buttons right nowrap">
                <router-link
                  :to="`/groups/edit/${group.id}`"
                  class="button small green"
                  :class="{ 'is-disabled': isGroupDeleting(group.id) }"
                  :disabled="isGroupDeleting(group.id)">
                  <span class="icon"><i class="mdi mdi-pencil"></i></span>
                </router-link>
                <button
                  class="button small red"
                  :class="{ 'is-loading': isGroupDeleting(group.id) }"
                  :disabled="isGroupDeleting(group.id)"
                  @click="deleteGroup(group.id, group.name)"
                  type="button">
                  <span class="icon"><i class="mdi mdi-trash-can"></i></span>
                </button>
              </div>
            </td>
          </tr>
          </tbody>
        </table>

        <div class="lg:hidden" >
            <div class="card mb-3 flex justify-between" v-for="(group, index) in groups" :key="index">
                <router-link
                    :to="`/groups/edit/${group.id}`"
                    class="group-card space-x-2 p-4 w-full"
                    >
                        <div>
                            <span class="icon"><i class="mdi mdi-folder"></i></span>
                            {{ group.name }}
                        </div>
                        <div class="text-sm text-gray-600">
                            <span class="icon"><i class="mdi mdi-package-variant"></i></span>
                            {{ group.items_count || 0 }} items
                        </div>
                </router-link>

                <div class="buttons right nowrap">
                    <button
                    class="button small red "
                    :class="{ 'is-loading': isGroupDeleting(group.id) }"
                    :disabled="isGroupDeleting(group.id)"
                    @click="deleteGroup(group.id, group.name)"
                    type="button">
                    <span class="icon py-10 px-5"><i class="mdi mdi-trash-can"></i></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <Pagination
          :pagination="pagination"
          item-name="groups"
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
/* Custom group card styles */
.group-card {
  background-color: #d1fae5;
  border-left: 4px solid #10b981;
  border-radius: 0.375rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.group-card:hover {
  background-color: #a7f3d0;
}
</style>
