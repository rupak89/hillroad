<template>
  <div class="sub-recipe-row mb-3">
    <div class="sub-recipe-item">
      <div class="control">
        <Multiselect
          v-model="subRecipe.child_recipe_id"
          :options="recipes"
          label="recipe_name"
          value-prop="id"
          placeholder="Search and select sub-recipe..."
          :searchable="true"
          :clear-on-select="false"
          :close-on-select="true"
          :can-deselect="true"
          :classes="{
            container: errors[`sub_recipes.${index}.child_recipe_id`] ? 'multiselect-error' : '',
            containerActive: 'is-active'
          }"
        />
      </div>
      <p v-if="errors[`sub_recipes.${index}.child_recipe_id`]" class="help is-danger is-size-7">
        {{ errors[`sub_recipes.${index}.child_recipe_id`][0] }}
      </p>
    </div>
    <div class="sub-recipe-quantity">
      <div class="control">
        <input
          class="input"
          :class="{ 'is-danger': errors[`sub_recipes.${index}.quantity`] }"
          type="number"
          step="0.01"
          placeholder="Quantity"
          v-model="subRecipe.quantity">
      </div>
      <p v-if="errors[`sub_recipes.${index}.quantity`]" class="help is-danger is-size-7">
        {{ errors[`sub_recipes.${index}.quantity`][0] }}
      </p>
    </div>
    <div class="sub-recipe-remove">
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
import Multiselect from '@vueform/multiselect';

defineProps({
  subRecipe: {
    type: Object,
    required: true
  },
  index: {
    type: Number,
    required: true
  },
  recipes: {
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

defineEmits(['remove']);
</script>

<style scoped>
.sub-recipe-row {
  display: grid;
  grid-template-columns: 2fr 1fr auto;
  gap: 1rem;
  align-items: start;
}

.sub-recipe-item,
.sub-recipe-quantity,
.sub-recipe-remove {
  display: flex;
  flex-direction: column;
}

.sub-recipe-remove {
  align-items: center;
  justify-content: flex-start;
  padding-top: 0.375rem; /* Align with input height */
}

/* Responsive design for smaller screens */
@media (max-width: 768px) {
  .sub-recipe-row {
    grid-template-columns: 1fr;
    gap: 0.5rem;
  }

  .sub-recipe-remove {
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
