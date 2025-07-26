<template>
  <div v-if="pagination.last_page > 1 || pagination.total > minItemsForPagination" class="table-pagination">
    <div class="flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0">
      <!-- Pagination Info & Per Page Selector -->
      <div class="flex flex-col sm:flex-row items-center space-y-2 sm:space-y-0 sm:space-x-4">
        <div class="text-sm text-gray-600">
          Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} {{ itemName }}
        </div>
        <div class="flex items-center space-x-2">
          <label class="text-sm text-gray-600">Per page:</label>
          <select
            class="input is-small"
            style="width: auto; min-width: 60px;"
            :value="pagination.per_page"
            @change="$emit('change-per-page', parseInt($event.target.value))">
            <option v-for="option in perPageOptions" :key="option" :value="option">
              {{ option }}
            </option>
          </select>
        </div>
      </div>

      <!-- Pagination Controls -->
      <div v-if="pagination.last_page > 1" class="flex items-center space-x-1">
        <!-- Previous Button -->
        <button
          type="button"
          class="button is-small"
          :class="{ 'is-disabled': pagination.current_page === 1 }"
          :disabled="pagination.current_page === 1"
          @click="$emit('go-to-page', pagination.current_page - 1)">
          <span class="icon is-small">
            <i class="mdi mdi-chevron-left"></i>
          </span>
        </button>

        <!-- First Page -->
        <button
          v-if="getVisiblePageNumbers()[0] > 1"
          type="button"
          class="button is-small"
          @click="$emit('go-to-page', 1)">
          1
        </button>

        <!-- Dots if needed -->
        <span v-if="getVisiblePageNumbers()[0] > 2" class="px-2">...</span>

        <!-- Visible Page Numbers -->
        <button
          v-for="page in getVisiblePageNumbers()"
          :key="page"
          type="button"
          class="button is-small"
          :class="{ 'blue': page === pagination.current_page }"
          @click="$emit('go-to-page', page)">
          {{ page }}
        </button>

        <!-- Dots if needed -->
        <span v-if="getVisiblePageNumbers()[getVisiblePageNumbers().length - 1] < pagination.last_page - 1" class="px-2">...</span>

        <!-- Last Page -->
        <button
          v-if="getVisiblePageNumbers()[getVisiblePageNumbers().length - 1] < pagination.last_page"
          type="button"
          class="button is-small"
          @click="$emit('go-to-page', pagination.last_page)">
          {{ pagination.last_page }}
        </button>

        <!-- Next Button -->
        <button
          type="button"
          class="button is-small"
          :class="{ 'is-disabled': pagination.current_page === pagination.last_page }"
          :disabled="pagination.current_page === pagination.last_page"
          @click="$emit('go-to-page', pagination.current_page + 1)">
          <span class="icon is-small">
            <i class="mdi mdi-chevron-right"></i>
          </span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  pagination: {
    type: Object,
    required: true,
    validator: (value) => {
      return value.hasOwnProperty('current_page') &&
             value.hasOwnProperty('per_page') &&
             value.hasOwnProperty('total') &&
             value.hasOwnProperty('last_page') &&
             value.hasOwnProperty('from') &&
             value.hasOwnProperty('to');
    }
  },
  itemName: {
    type: String,
    default: 'items'
  },
  perPageOptions: {
    type: Array,
    default: () => [5, 10, 25, 50]
  },
  minItemsForPagination: {
    type: Number,
    default: 10
  },
  delta: {
    type: Number,
    default: 2
  }
});

const emit = defineEmits(['go-to-page', 'change-per-page']);

const getVisiblePageNumbers = () => {
  const current = props.pagination.current_page;
  const last = props.pagination.last_page;
  const delta = props.delta; // Number of pages to show on each side of current page

  let start = Math.max(1, current - delta);
  let end = Math.min(last, current + delta);

  // Adjust if we're near the beginning or end
  if (current <= delta) {
    end = Math.min(last, 2 * delta + 1);
  }
  if (current >= last - delta) {
    start = Math.max(1, last - 2 * delta);
  }

  const pages = [];
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  return pages;
};
</script>
