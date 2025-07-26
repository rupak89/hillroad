import axios from "axios";
// Axios configuration is now centralized in bootstrap.js

import { defineStore } from "pinia";

const useItemStore = defineStore("item", {
    state:()=> ({
        units: {},
        suppliers: {},
        brands: {},
    }),

    actions: {
        async fetchUnits() {
            try {
                await axios.get('/sanctum/csrf-cookie');
                var response = await axios.get('/api/units');
                this.units = response.data.units;

            } catch (error) {


                throw error;
            }
        },
        async fetchSuppliers() {
            try {
                await axios.get('/sanctum/csrf-cookie');
                var response = await axios.get('/api/suppliers');
                console.log(response.data.suppliers.data);
                this.suppliers = response.data.suppliers.data;

            } catch (error) {


                throw error;
            }
        },

        async fetchBrands() {
            try {
                await axios.get('/sanctum/csrf-cookie');
                var response = await axios.get('/api/brands');
                this.brands = response.data.brands;

            } catch (error) {


                throw error;
            }
        },

        async fetchGroups() {
            try {
                await axios.get('/sanctum/csrf-cookie');
                var response = await axios.get('/api/groups');
                this.groups = response.data.groups.data;

            } catch (error) {


                throw error;
            }
        },
    },

});

export default useItemStore;
