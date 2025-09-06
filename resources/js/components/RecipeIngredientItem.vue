<template>
  <div class="ingredient-row mb-3">
    <div class="ingredient-item">
      <div class="control">
        <Multiselect
          v-model="ingredient.item_id"
          :options="items"
          label="item_name"
          value-prop="id"
          placeholder="Search and select item..."
          :searchable="true"
          :clear-on-select="false"
          :close-on-select="true"
          :can-deselect="true"
          :classes="{
            container: errors[`ingredients.${index}.item_id`] ? 'multiselect-error' : '',
            containerActive: 'is-active'
          }"
          @change="(value) => handleItemChange(value)"
        />
      </div>
      <p v-if="errors[`ingredients.${index}.item_id`]" class="help is-danger is-size-7">
        {{ errors[`ingredients.${index}.item_id`][0] }}
      </p>
    </div>
    <div class="ingredient-unit">
      <div class="control">
        <Multiselect
          v-model="ingredient.unit_id"
          :options="availableUnits"
          label="name"
          value-prop="id"
          placeholder="Search and select unit..."
          :searchable="true"
          :clear-on-select="false"
          :close-on-select="true"
          :can-deselect="true"
          :classes="{
            container: errors[`ingredients.${index}.unit_id`] ? 'multiselect-error' : '',
            containerActive: 'is-active'
          }"
        />
      </div>
      <p v-if="errors[`ingredients.${index}.unit_id`]" class="help is-danger is-size-7">
        {{ errors[`ingredients.${index}.unit_id`][0] }}
      </p>
    </div>
    <div class="ingredient-quantity">
      <div class="control">
        <input
          class="input"
          :class="{ 'is-danger': errors[`ingredients.${index}.quantity`] }"
          type="number"
          step="0.01"
          placeholder="Quantity"
          v-model="ingredient.quantity">
      </div>
      <p v-if="errors[`ingredients.${index}.quantity`]" class="help is-danger is-size-7">
        {{ errors[`ingredients.${index}.quantity`][0] }}
      </p>
    </div>
    <div class="ingredient-remove">
      <button
        v-if="canRemove"
        class="button is-small is-danger"
        type="button"
        @click="$emit('remove')">
        <span class="icon"><i class="mdi mdi-close"></i></span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import Multiselect from '@vueform/multiselect';

const props = defineProps({
  ingredient: {
    type: Object,
    required: true
  },
  index: {
    type: Number,
    required: true
  },
  items: {
    type: Array,
    required: true
  },
  units: {
    type: Array,
    required: true
  },
  errors: {
    type: Object,
    default: () => ({})
  },
  canRemove: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['remove', 'itemChange']);

const availableUnits = computed(() => {
  if (!props.ingredient.item_id) {
    return props.units;
  }

  // Find the selected item
  const selectedItem = props.items.find(item => item.id == props.ingredient.item_id);
  if (!selectedItem || !selectedItem.counting_unit_id) {
    return props.units;
  }

  // Find the counting unit to get its unit_type_id
  const countingUnit = props.units.find(unit => unit.id == selectedItem.counting_unit_id);
  if (!countingUnit) {
    return props.units;
  }

  // Filter units to only show those with the same unit_type_id
  return props.units.filter(unit => unit.unit_type_id === countingUnit.unit_type_id);
});

const handleItemChange = (itemId) => {
  // Find the selected item
  const selectedItem = props.items.find(item => item.id == itemId);

  if (selectedItem && selectedItem.counting_unit_id) {
    // Auto-select the counting unit for this ingredient
    props.ingredient.unit_id = selectedItem.counting_unit_id;
  } else {
    // Clear unit if no counting unit is set
    props.ingredient.unit_id = '';
  }

  emit('itemChange', itemId);
};
</script>

<style scoped>
.ingredient-row {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr auto;
  gap: 1rem;
  align-items: start;
}

.ingredient-item,
.ingredient-unit,
.ingredient-quantity,
.ingredient-remove {
  display: flex;
  flex-direction: column;
}

.ingredient-remove {
  align-items: center;
  justify-content: flex-start;
  padding-top: 0.375rem; /* Align with input height */
}

/* Responsive design for smaller screens */
@media (max-width: 768px) {
  .ingredient-row {
    grid-template-columns: 1fr;
    gap: 0.5rem;
  }

  .ingredient-remove {
    justify-self: end;
    padding-top: 0;
  }
}

/* Multiselect styling to match Bulma theme */
:deep(.multiselect) {
  min-height: 2.5rem;
  border-radius: 4px;
  border: 1px solid #dbdbdb;
  background: white;
}

:deep(.multiselect.is-active) {
  border-color: #3273dc;
  box-shadow: 0 0 0 0.125em rgba(50, 115, 220, 0.25);
}

:deep(.multiselect-error) {
  border-color: #ff3860 !important;
}

:deep(.multiselect-error.is-active) {
  box-shadow: 0 0 0 0.125em rgba(255, 56, 96, 0.25) !important;
}

:deep(.multiselect-wrapper) {
  position: relative;
}

:deep(.multiselect-input) {
  border: none;
  outline: none;
  background: transparent;
  padding: 0.5rem 0.75rem;
  font-size: 1rem;
}

:deep(.multiselect-single-label) {
  padding: 0.5rem 0.75rem;
  font-size: 1rem;
  line-height: 1.5;
}

:deep(.multiselect-placeholder) {
  padding: 0.5rem 0.75rem;
  color: #b5b5b5;
  font-size: 1rem;
}

:deep(.multiselect-dropdown) {
  border: 1px solid #dbdbdb;
  border-top: none;
  border-radius: 0 0 4px 4px;
  max-height: 200px;
  background: white;
}

:deep(.multiselect-option) {
  padding: 0.5rem 0.75rem;
  font-size: 1rem;
  cursor: pointer;
}

:deep(.multiselect-option:hover) {
  background: #f5f5f5;
}

:deep(.multiselect-option.is-selected) {
  background: #3273dc;
  color: white;
}

:deep(.multiselect-option.is-highlighted) {
  background: #f5f5f5;
}
</style>
