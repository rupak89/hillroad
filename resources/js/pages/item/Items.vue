<script setup>
import axios from 'axios';
import { onMounted, ref } from 'vue';

const items = ref([]);

onMounted(() => {
  fetchItemList();
});


const fetchItemList =  async () => {
  axios.get('/api/items')
    .then(response => {
      console.log('Items fetched successfully:', response.data);
      items.value = response.data.items;

    })
    .catch(error => {
      console.error('Items fetching units:', error);
    });
};


</script>

<template>


  <section class="section main-section">

    <section class="is-hero-bar">
        <div class="flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0">
            <h1 class="title">
            Items
            </h1>
            <router-link to="items/add" class="button light">
                Add Items
            </router-link>
        </div>
    </section>


    <div class="card has-table">
      <header class="card-header">
        <p class="card-header-title">
          <span class="icon"><i class="mdi mdi-account-multiple"></i></span>
          Items
        </p>
        <a href="#" class="card-header-icon">
          <span class="icon"><i class="mdi mdi-reload"></i></span>
        </a>
      </header>

      <div class="card-content">
        <table>
  <thead>
    <tr>
      <th class="checkbox-cell">
        <label class="checkbox">
          <input type="checkbox">
          <span class="check"></span>
        </label>
      </th>
      <th>Item Name</th>
      <th>Supplier</th>
      <th>Latest Price</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    <tr v-for="(item, index) in items" :key="index">
      <td class="checkbox-cell">
        <label class="checkbox">
          <input type="checkbox">
          <span class="check"></span>
        </label>
      </td>
      <td data-label="Item Name">{{ item.item_name }}</td>
      <td data-label="Supplier">{{ item.supplier_name }}</td>
      <td data-label="Latest Price">{{ item.latest_price }}</td>

      <td class="actions-cell">
        <div class="buttons right nowrap">
          <button class="button small blue --jb-modal" data-target="view-item-modal" type="button">
            <span class="icon"><i class="mdi mdi-eye"></i></span>
          </button>
          <button class="button small red --jb-modal" data-target="delete-item-modal" type="button">
            <span class="icon"><i class="mdi mdi-trash-can"></i></span>
          </button>
        </div>
      </td>
    </tr>

    <!-- Add more rows here dynamically -->

  </tbody>
</table>

        <div class="table-pagination">
          <div class="flex items-center justify-between">
            <div class="buttons">
              <button type="button" class="button active">1</button>
              <button type="button" class="button">2</button>
              <button type="button" class="button">3</button>
            </div>
            <small>Page 1 of 3</small>
          </div>
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
