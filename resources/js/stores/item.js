import axios from "axios";
import { defineStore } from "pinia";

const useItemStore = defineStore("item", {
    state: () => ({
        // Reference data
        units: [],
        suppliers: [],
        brands: [],
        groups: [],

        // Loading states
        loading: {
            units: false,
            suppliers: false,
            brands: false,
            groups: false,
            items: false
        },

        // Cache control
        lastFetched: {
            units: null,
            suppliers: null,
            brands: null,
            groups: null
        },

        // Items data
        items: [],
        currentItem: null,

        // Errors
        errors: {}
    }),

    getters: {
        isLoading: (state) => (type) => state.loading[type],

        isStale: (state) => (type, maxAge = 5 * 60 * 1000) => { // 5 minutes default
            const lastFetch = state.lastFetched[type]
            return !lastFetch || (Date.now() - lastFetch) > maxAge
        },

        isLoadingAny: (state) => {
            return Object.values(state.loading).some(loading => loading)
        }
    },

    actions: {
        async fetchUnits(force = false) {
            if (!force && this.units.length > 0 && !this.isStale('units')) {
                return this.units
            }

            this.loading.units = true
            try {
                await axios.get('/sanctum/csrf-cookie');
                const response = await axios.get('/api/units');
                this.units = response.data.units?.data || response.data.units || [];
                this.lastFetched.units = Date.now();
                return this.units;
            } catch (error) {
                console.error('Error fetching units:', error);
                this.errors.units = error.response?.data?.message || 'Failed to load units';
                throw error;
            } finally {
                this.loading.units = false;
            }
        },

        async fetchSuppliers(force = false) {
            if (!force && this.suppliers.length > 0 && !this.isStale('suppliers')) {
                return this.suppliers
            }

            this.loading.suppliers = true
            try {
                await axios.get('/sanctum/csrf-cookie');
                const response = await axios.get('/api/suppliers?per_page=100'); // Get more for dropdowns
                this.suppliers = response.data.suppliers?.data || response.data.suppliers || [];
                this.lastFetched.suppliers = Date.now();
                return this.suppliers;
            } catch (error) {
                console.error('Error fetching suppliers:', error);
                this.errors.suppliers = error.response?.data?.message || 'Failed to load suppliers';
                throw error;
            } finally {
                this.loading.suppliers = false;
            }
        },

        async fetchBrands(force = false) {
            if (!force && this.brands.length > 0 && !this.isStale('brands')) {
                return this.brands
            }

            this.loading.brands = true
            try {
                await axios.get('/sanctum/csrf-cookie');
                const response = await axios.get('/api/brands?per_page=100');
                this.brands = response.data.brands?.data || response.data.brands || [];
                this.lastFetched.brands = Date.now();
                return this.brands;
            } catch (error) {
                console.error('Error fetching brands:', error);
                this.errors.brands = error.response?.data?.message || 'Failed to load brands';
                throw error;
            } finally {
                this.loading.brands = false;
            }
        },

        async fetchGroups(force = false) {
            if (!force && this.groups.length > 0 && !this.isStale('groups')) {
                return this.groups
            }

            this.loading.groups = true
            try {
                await axios.get('/sanctum/csrf-cookie');
                const response = await axios.get('/api/groups?per_page=100');
                this.groups = response.data.groups?.data || response.data.groups || [];
                this.lastFetched.groups = Date.now();
                return this.groups;
            } catch (error) {
                console.error('Error fetching groups:', error);
                this.errors.groups = error.response?.data?.message || 'Failed to load groups';
                throw error;
            } finally {
                this.loading.groups = false;
            }
        },

        // Fetch all reference data at once
        async fetchAllReferenceData(force = false) {
            try {
                await Promise.all([
                    this.fetchUnits(force),
                    this.fetchSuppliers(force),
                    this.fetchBrands(force),
                    this.fetchGroups(force)
                ]);
            } catch (error) {
                console.error('Error fetching reference data:', error);
                throw error;
            }
        },

        // Methods to add new items when created elsewhere
        addUnit(unit) {
            if (!this.units.find(u => u.id === unit.id)) {
                this.units.push(unit);
            }
        },

        addSupplier(supplier) {
            if (!this.suppliers.find(s => s.id === supplier.id)) {
                this.suppliers.push(supplier);
            }
        },

        addBrand(brand) {
            if (!this.brands.find(b => b.id === brand.id)) {
                this.brands.push(brand);
            }
        },

        addGroup(group) {
            if (!this.groups.find(g => g.id === group.id)) {
                this.groups.push(group);
            }
        },

        // Update methods
        updateSupplier(updatedSupplier) {
            const index = this.suppliers.findIndex(s => s.id === updatedSupplier.id);
            if (index !== -1) {
                this.suppliers[index] = updatedSupplier;
            }
        },

        updateBrand(updatedBrand) {
            const index = this.brands.findIndex(b => b.id === updatedBrand.id);
            if (index !== -1) {
                this.brands[index] = updatedBrand;
            }
        },

        updateGroup(updatedGroup) {
            const index = this.groups.findIndex(g => g.id === updatedGroup.id);
            if (index !== -1) {
                this.groups[index] = updatedGroup;
            }
        },

        // Clear cache
        clearReferenceCache() {
            this.units = [];
            this.suppliers = [];
            this.brands = [];
            this.groups = [];
            this.lastFetched = {
                units: null,
                suppliers: null,
                brands: null,
                groups: null
            };
        },

        // Clear errors
        clearErrors() {
            this.errors = {};
        }
    },
});

export default useItemStore;
