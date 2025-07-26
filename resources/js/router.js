import { createRouter, createWebHistory } from 'vue-router';
import Home from '@/pages/Home.vue';
import Notfound from '@/pages/Notfound.vue';
import AddItem from '@/pages/item/AddItem.vue';
import Items from './pages/item/Items.vue';
import AddSupplier from '@/pages/supplier/AddSupplier.vue';
import AddUnit from '@/pages/unit/AddUnit.vue';
import Supplier from './pages/supplier/Supplier.vue';
import Recipes from './pages/recipe/recipes.vue';
import Addrecipe from './pages/recipe/Addrecipe.vue';
import Login from './pages/Login.vue';
import DefaultLayout from './components/layouts/DefaultLayout.vue';
import GuestLayout from './components/layouts/GuestLayout.vue';
import useUserStore from './stores/user';



const routes = [
    {
        path:"/",
        component: DefaultLayout,
        children: [
            { path: '/', component: Home },
            { path: '/recipes', component: Recipes },
            { path: '/recipes/add', component: Addrecipe },
            { path: '/items', component: Items },
            { path: '/items/add', component: AddItem , name: 'additem'},
            { path: '/suppliers', component: Supplier },
            { path: '/suppliers/add', component: AddSupplier },
            { path: '/suppliers/edit/:id', component: AddSupplier },
            { path: '/addunit', component: AddUnit },
            { path: '/404', component: Notfound },
        ],
        // beforeEnter: (to, from, next) => {
        //     try{
        //         const useUserStore = useUserStore();
        //         useUserStore.fetchUser();
        //         next();
        //     }
        //     catch (error) {
        //         console.error('Failed to fetch user data:', error);
        //         //next('/login');
        //     }
        // }
    },
    {
        path: '/login',
        component: GuestLayout,
        children: [
            { path: '/login', component: Login },
        ],
        beforeEnter: (to, from, next) => {
            try{
                const userStore = useUserStore();

                if(userStore.isAuthenticated) {
                    console.log('User is authenticated, redirecting to home');
                    //next('/');
                }
            }
            catch (error) {
                console.error('Failed to check authentication:', error);
            }
            next();
        }
    }



];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
