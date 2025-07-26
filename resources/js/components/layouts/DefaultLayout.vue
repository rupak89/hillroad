<script setup>
import Navbar from '@/components/parts/Navbar.vue';
import AsideMenu from '@/components/parts/AsideMenu.vue';
import Footer from '@/components/parts/Footer.vue';
import { RouterView } from 'vue-router';
</script>

<template>
    <Navbar></Navbar>
    <AsideMenu></AsideMenu>
    <section class="section main-section ">
        <RouterView></RouterView>
    </section>

    <Footer></Footer>

</template>


