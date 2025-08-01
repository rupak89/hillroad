<script setup>
import { defineProps, defineEmits, watch, ref, computed } from 'vue';
import Multiselect from '@vueform/multiselect';

const props = defineProps({
  unitTypeList: {
    type: Array,
    required: true
  },
  values: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['update', 'delete']);

const unitValues = ref({ ...props.values });

// Computed property to handle null values for multiselect
const unitTypeId = computed({
  get() {
    return unitValues.value.unit_type_id === null || unitValues.value.unit_type_id === 'null'
      ? ''
      : unitValues.value.unit_type_id;
  },
  set(value) {
    unitValues.value.unit_type_id = value === '' ? null : value;
  }
});

const confirmAndDelete = () => {
  if (unitValues.value.id && confirm('Are you sure you want to delete this unit?')) {
    emit('delete', unitValues.value.id);
  }
};

// Keep local unitValues in sync with props.values
watch(
  () => props.values,
  (newVal) => {
    unitValues.value = { ...newVal };
  },
  { deep: true, immediate: true }
);

// Emit update when any field changes
watch(
  unitValues,
  (newVal) => {
    emit('update', { ...newVal });
  },
  { deep: true }
);
</script>

<template>
  <div class="md:columns-3">

    <div class="field">
      <label class="label">Unit Type</label>
      <div class="control">
        <Multiselect
          v-model="unitTypeId"
          :options="unitTypeList"
          placeholder="Select unit type"
          :searchable="true"
          :clearable="false"
          :close-on-select="true"
          label="label"
          value-prop="id"
          class="multiselect"
        />
      </div>
    </div>
    <div class="field">
      <label class="label">Ratio</label>
      <div class="control ">
        <input
          class="input"
          type="number"
          step="any"
          placeholder="e.g. 1.5"
          v-model.number="unitValues.ratio"
        >
      </div>
    </div>
    <div class="field">
      <label class="label">Unit Name</label>
      <div class="control flex gap-2 items-center">
        <input
          class="input"
          type="text"
          placeholder="e.g. bidfood"
          v-model="unitValues.name"
        >
        <button class="button small red" type="button" @click="confirmAndDelete">
          <span class="icon">
            <i class="mdi mdi-trash-can"></i>
          </span>
        </button>
      </div>
      <p class="help hidden">
        This field is required
      </p>
    </div>
  </div>
</template>
