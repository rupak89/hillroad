import axios from 'axios';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;

// Use the same origin as the page by default (the Vite dev server proxies
// /api and /sanctum to Laravel). Set VITE_API_URL only when the API lives on
// a different origin, and add that origin to CORS_ALLOWED_ORIGINS.
if (import.meta.env.VITE_API_URL) {
    axios.defaults.baseURL = import.meta.env.VITE_API_URL;
}
