<script setup>
import useUserStore from '@/stores/user';

import { onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const userStore = useUserStore();

onMounted(() => {
    checkAuthAndRedirect();
});

const checkAuthAndRedirect = () => {

    userStore.fetchUser().then(() => {
        if(!userStore.isAuthenticated) {

            router.push('/login')
        }

    }).catch(() => {
        // If fetching user fails, stay on login page
        console.log('Failed to fetch user, staying on login page');
        router.push('/login')

    });

}

const isAuthenticated = computed(() => {
  return userStore.isAuthenticated;
})

</script>
<template>

        <RouterView></RouterView>

</template>

