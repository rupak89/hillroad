<script setup>
import Navbar from '@/components/parts/Navbar.vue';
import AsideMenu from '@/components/parts/Asidemenu.vue';
import Footer from '@/components/parts/Footer.vue';
import FlashMessageContainer from '@/components/FlashMessageContainer.vue';
import { RouterView } from 'vue-router';
</script>

<template>
    <Navbar></Navbar>
    <AsideMenu></AsideMenu>
    <section class="section main-section ">
        <RouterView></RouterView>
    </section>

    <Footer></Footer>

    <!-- Flash Messages -->
    <FlashMessageContainer />
</template>


