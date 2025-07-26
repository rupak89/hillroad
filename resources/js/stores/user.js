import axios from "axios";
// Axios configuration is now centralized in bootstrap.js

import { defineStore } from "pinia";

const useUserStore = defineStore("user", {
    state:()=> ({
        user: null,
        isAuthenticated: false,
    }),

    actions: {
        async fetchUser() {
            try {
                await axios.get('/sanctum/csrf-cookie');
                const { data } = await axios.get('/api/user');

                this.user = data;
                this.isAuthenticated = !!data;
            } catch (error) {
                console.error("Error fetching user:", error);
                this.user = null;
                this.isAuthenticated = false;

                throw error;
            }
        },
        async login(credentials) {

            await axios.get('/sanctum/csrf-cookie');

            const response = await axios.post('/login', credentials);

            // On success, update the state directly from the login response.
            this.user = response.data.user;
            this.isAuthenticated = true;
        },
    },

});

export default useUserStore;
