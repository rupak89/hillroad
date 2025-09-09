<script setup>

import { onMounted } from 'vue';


</script>

<template>
    <div>

        <section class="is-hero-bar">
            <div class="flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0">
                <h1 class="title">
                Dashboard
                </h1>

            </div>
        </section>

        <section class="section main-section">
            <div class="grid gap-6 grid-cols-1 md:grid-cols-3 mb-6">
                <router-link to="/recipes">
                    <div class="card">
                        <div class="card-content">
                        <div class="flex items-center justify-between">
                            <div class="widget-label">
                                <h1>Recipe</h1>
                                <!-- <h3>Recipe</h3>

                                <h1>
                                    512
                                </h1> -->
                            </div>
                            <span class="icon widget-icon text-green-500"><i class="mdi mdi-chef-hat mdi-48px"></i></span>
                        </div>
                        </div>
                    </div>
                </router-link>

                <router-link to="/menus">
                    <div class="card">
                        <div class="card-content">
                        <div class="flex items-center justify-between">
                            <div class="widget-label">

                                <h1>Menu</h1>
                            </div>
                            <span class="icon widget-icon text-red-500"><i class="mdi mdi-food-fork-drink mdi-48px"></i></span>
                        </div>
                        </div>
                    </div>
                </router-link>

                <router-link to="/items">
                    <div class="card">
                        <div class="card-content">
                        <div class="flex items-center justify-between">
                            <div class="widget-label">
                            <h1>
                                Items
                                </h1>
                            </div>
                            <span class="icon widget-icon text-blue-500"><i class="mdi mdi-shaker-outline mdi-48px"></i></span>
                        </div>
                        </div>
                    </div>
                </router-link>



                <router-link to="/suppliers">
                    <div class="card">
                        <div class="card-content">
                        <div class="flex items-center justify-between">
                            <div class="widget-label">

                                <h1>Supplier</h1>
                            </div>
                            <span class="icon widget-icon text-red-500"><i class="mdi mdi-domain mdi-48px"></i></span>
                        </div>
                        </div>
                    </div>
                </router-link>

                <router-link to="/groups">
                    <div class="card">
                        <div class="card-content">
                            <div class="flex items-center justify-between">
                                <div class="widget-label">
                                    <h1>Groups</h1>
                                </div>
                                <span class="icon widget-icon text-purple-500"><i class="mdi mdi-account-group mdi-48px"></i></span>
                            </div>
                        </div>
                    </div>
                </router-link>

                <router-link to="/addunit">
                    <div class="card">
                        <div class="card-content">
                            <div class="flex items-center justify-between">
                                <div class="widget-label">
                                    <h1>Stock Units</h1>
                                </div>
                                <span class="icon widget-icon text-orange-500"><i class="mdi mdi-tape-measure mdi-48px"></i></span>
                            </div>
                        </div>
                    </div>
                </router-link>

                <router-link to="/brands">
                    <div class="card">
                        <div class="card-content">
                            <div class="flex items-center justify-between">
                                <div class="widget-label">
                                    <h1>Brands</h1>
                                </div>
                                <span class="icon widget-icon text-teal-500"><i class="mdi mdi-tag mdi-48px"></i></span>
                            </div>
                        </div>
                    </div>
                </router-link>

            </div>



        </section>


    </div>
</template>
