<script setup>

import { onMounted, ref } from 'vue';
import axios from 'axios';
import useItemStore from '@/stores/item.js';

const itemStore = useItemStore();

var name = ref('');
var orderingUnit = ref({})
var countingUnit
var price
var group
var defaultSupplier
var defaultBrand


onMounted(() => {
    itemStore.fetchUnits();
    itemStore.fetchSuppliers();
    itemStore.fetchBrands();
    itemStore.fetchGroups();
})



</script>

<template>

  <section class="section main-section">

    <section class="is-hero-bar">
        <div class="flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0">
            <router-link to="" class="button light">
                 Back
            </router-link>
        </div>
    </section>

    <div class="card mb-6">
      <header class="card-header">
        <p class="card-header-title">
          <span class="icon"><i class="mdi mdi-ballot"></i></span>
          Add Item
        </p>
      </header>
      <div class="card-content">
        <form method="get">
            <div class="field">
                <label class="label">Item Name</label>

                <div class="control">
                <input class="input" type="text" placeholder="e.g. Tortilla">
                </div>
                <p class="help">
                This field is required
                </p>
            </div>

          <div class="columns-2">

            <div class="field">
                    <label class="label">Ordering Unit</label>
                    <div class="control">
                    <div class="select">
                        <select>
                        <option value="">Not Set</option>
                        <option v-for="(unit, index) in itemStore.units"
                            :key="index"
                            :value="unit.id"
                        >
                        {{ unit.name }}
                        </option>
                        </select>
                    </div>
                    </div>
            </div>

            <div class="field">
                    <label class="label">Default Supplier</label>
                    <div class="control">

                    <div class="select">
                        <select>
                        <option>Not Set</option>
                        <option v-for="(supplier, index) in itemStore.suppliers"
                            :key="index"
                            :value="supplier.id"
                        >
                        {{ supplier.supplier_name }}
                        </option>
                        </select>
                    </div>
                    </div>
            </div>

          </div>

          <div class="columns-2">

            <div class="field">
                    <label class="label">Counting Unit</label>
                    <div class="control">
                    <div class="select">
                        <select>
                        <option value="">Not Set</option>
                        <option v-for="(unit, index) in itemStore.units"
                            :key="index"
                            :value="unit.id"
                        >
                        {{ unit.name }}
                        </option>
                        </select>
                    </div>
                    </div>
            </div>

            <div class="field">
                    <label class="label">Default Brand</label>
                    <div class="control">
                    <div class="select">
                        <select>
                        <option>Not Set</option>
                        <option v-for="(brand, index) in itemStore.brands"
                            :key="index"
                            :value="brand.id"
                        >
                        {{ brand.name }}
                        </option>
                        </select>
                    </div>
                    </div>
            </div>

          </div>

          <div class="columns-2">

            <div class="field">
                    <label class="label">Latest Price</label>
                    <div class="control">
                        <input class="input" type="text" placeholder="e.g. 1.99">
                    </div>
            </div>

            <div class="field">
                    <label class="label">Group</label>
                    <div class="control">
                    <div class="select">
                        <select>
                        <option>Not Set</option>
                        <option v-for="(group, index) in itemStore.groups"
                            :key="index"
                            :value="group.id"
                        >
                        {{ group.name }}
                        </option>
                        </select>
                    </div>
                    </div>
            </div>

          </div>

          <hr>

          <div class="field grouped">
            <div class="control">
              <button type="submit" class="button green">
                Add item
              </button>
            </div>
            <div class="control">
              <button type="reset" class="button red">
                Reset
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

  </section>



</template>
