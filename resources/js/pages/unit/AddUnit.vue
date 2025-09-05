<script setup>

import Unit from '@/components/Unit.vue';
import axios from 'axios';
import { onMounted, onServerPrefetch } from 'vue';
import { ref } from 'vue';

var unittypes  = ref([]);
var unitList = ref([]);

onMounted(() => {
  fetchUnitTypes();
  fetchUnitList();

});

const fetchUnitTypes =  async () => {
  axios.get('/api/unittypes')
    .then(response => {
      console.log('Units fetched successfully:', response.data);
      unittypes.value = response.data.unittypes;
    })
    .catch(error => {
      console.error('Error fetching units:', error);
    });
};

const fetchUnitList =  async () => {
  axios.get('/api/units')
    .then(response => {
      console.log('Units fetched successfully:', response.data);
      unitList.value = response.data.units;

      // Add an empty unit to the list
      unitList.value.push({
        id: 0,
        name: '',
        unit_type_id: null,
        ratio: null
      });

    })
    .catch(error => {
      console.error('Error fetching units:', error);
    });
};

const updateUnit = async (updatedUnit, index) => {
  if (updatedUnit.id !== 0) {
    try {
      await axios.put(`/api/units/${updatedUnit.id}`, updatedUnit);
      // Update the local unitList with the new data using index
      if (typeof index === 'number' && index >= 0) {
        console.log('Updating unit at index:', index, 'with data:', updatedUnit);
        //unitList.value[index] = { ...updatedUnit };
      }
    } catch (error) {
      console.error('Error updating unit:', error);
    }
  }
  else {
    try {
      const response = await axios.post('/api/units', updatedUnit);
      // Add the new unit to the local unitList
      unitList.value[index] = response.data.unit;
    } catch (error) {
      console.error('Error adding unit:', error);
    }
  }
};

const deleteUnit = async (unitId) => {
  try {
    await axios.delete(`/api/units/${unitId}`);
    // Remove the unit from the local unitList
    unitList.value = unitList.value.filter(unit => unit.id !== unitId);
  } catch (error) {
    console.error('Error deleting unit:', error);
  }
};

</script>

<template>
  <section class="section main-section">

    <router-link to="/" class="button is-small">
      <span class="icon"><i class="mdi mdi-arrow-left"></i></span>
      <span class="sm:hidden lg:inline">Dashboard</span>
    </router-link>

    <div class="card mb-6">
      <header class="card-header">
        <p class="card-header-title">
          <span class="icon"><i class="mdi mdi-ballot"></i></span>
          Stock Units
        </p>
      </header>
      <div class="card-content">
        <form method="get">
          <div v-for="(unit, index) in unitList" :key="index">
            <Unit
              v-if="unittypes.length > 0"
              :unitTypeList="unittypes"
              :values="unit"
              @update="(updated) => updateUnit(updated, index)"
              @delete="(id) => deleteUnit(id)"
            />
          </div>
          <hr>
          <div class="field grouped">
            <div class="control">
              <button type="submit" class="button green">
                +
              </button>
            </div>

          </div>
        </form>
      </div>
    </div>
  </section>


</template>


