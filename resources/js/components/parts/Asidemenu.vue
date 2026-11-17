<script setup>
// Served from public/ so the logo keeps a stable URL in dev and in builds.
const logoUrl = '/images/hill-road-catering-logo-white.webp';
</script>

<style scoped>
.aside-brand {
    display: block;
    width: 100%;
    max-width: 13.5rem;
    line-height: 0;
}

.aside-logo {
    display: block;
    width: 100%;
    height: auto;
}
</style>

<template>

    <aside class="aside is-placed-left is-expanded">
    <div class="aside-tools">
        <router-link to="/" class="aside-brand" aria-label="Hill Road Catering — dashboard">
        <img
            :src="logoUrl"
            alt="Hill Road Catering"
            class="aside-logo"
            width="2538"
            height="965"
        >
        </router-link>
    </div>
    <div class="menu is-menu-main">
        <p class="menu-label">General</p>
        <ul class="menu-list">
        <!-- <li class="--set-active-index-html">
            <a href="index.html">
            <span class="icon"><i class="mdi mdi-desktop-mac"></i></span>
            <span class="menu-item-label">Dashboard</span>
            </a>
        </li> -->
        <li class="--set-active-tables-html">
            <router-link to="/recipes">
            <span class="icon"><i class="mdi mdi-chef-hat"></i></span>
            <span class="menu-item-label ">Recipe</span>
            </router-link>
        </li>
        <li class="--set-active-tables-html">
            <router-link to="/items">
            <span class="icon"><i class="mdi mdi-shaker-outline"></i></span>
            <span class="menu-item-label">Item</span>
            </router-link>
        </li>
        <li class="--set-active-tables-html">
            <router-link to="/suppliers">
            <span class="icon"><i class="mdi mdi-domain"></i></span>
            <span class="menu-item-label">Supplier</span>
            </router-link>
        </li>
        <li class="--set-active-tables-html">
            <router-link to="/addunit">
            <span class="icon"><i class="mdi mdi-tape-measure"></i></span>
            <span class="menu-item-label">Stock Unit</span>
            </router-link>
        </li>
        <li class="--set-active-tables-html">
            <router-link to="/menus">
            <span class="icon"><i class="mdi mdi-food-fork-drink"></i></span>
            <span class="menu-item-label">Menu</span>
            </router-link>
        </li>
        <li class="--set-active-tables-html">
            <router-link to="/groups">
            <span class="icon"><i class="mdi mdi-account-group"></i></span>
            <span class="menu-item-label">Group</span>
            </router-link>
        </li>
        <li class="--set-active-tables-html">
            <router-link to="/brands">
            <span class="icon"><i class="mdi mdi-tag-outline"></i></span>
            <span class="menu-item-label">Brand</span>
            </router-link>
        </li>
        </ul>
        <p class="menu-label">Settings</p>
        <ul class="menu-list">
        <li class="--set-active-tables-html">
            <router-link to="/settings/gst">
            <span class="icon"><i class="mdi mdi-percent"></i></span>
            <span class="menu-item-label">GST Settings</span>
            </router-link>
        </li>
        </ul>
        <p class="menu-label hidden">Examples</p>
        <ul class="menu-list hidden">
        <!-- <li class="--set-active-tables-html">
            <a href="tables.html">
            <span class="icon"><i class="mdi mdi-table"></i></span>
            <span class="menu-item-label">Tables</span>
            </a>
        </li>
        <li class="--set-active-forms-html">
            <a href="forms.html">
            <span class="icon"><i class="mdi mdi-square-edit-outline"></i></span>
            <span class="menu-item-label">Forms</span>
            </a>
        </li>
        <li class="--set-active-profile-html">
            <a href="profile.html">
            <span class="icon"><i class="mdi mdi-account-circle"></i></span>
            <span class="menu-item-label">Profile</span>
            </a>
        </li>
        <li>
            <a href="login.html">
            <span class="icon"><i class="mdi mdi-lock"></i></span>
            <span class="menu-item-label">Login</span>
            </a>
        </li> -->
        <!-- <li>
            <a class="dropdown">
            <span class="icon"><i class="mdi mdi-view-list"></i></span>
            <span class="menu-item-label">Submenus</span>
            <span class="icon"><i class="mdi mdi-plus"></i></span>
            </a>
            <ul>
            <li>
                <router-link to="/sub-item-one">
                <span>Sub-item One</span>
                </router-link>
            </li>
            <li>
                <router-link to="/sub-item-two">
                <span>Sub-item Two</span>
                </router-link>
            </li>
            </ul>
        </li> -->
        </ul>
        <!-- <p class="menu-label">About</p>
        <ul class="menu-list">
        <li>
            <a href="https://justboil.me/tailwind-admin-templates/free-dashboard/" class="has-icon">
            <span class="icon"><i class="mdi mdi-help-circle"></i></span>
            <span class="menu-item-label">About</span>
            </a>
        </li>
        <li>
            <a href="https://github.com/justboil/admin-one-tailwind" class="has-icon">
            <span class="icon"><i class="mdi mdi-github-circle"></i></span>
            <span class="menu-item-label">GitHub</span>
            </a>
        </li>
        </ul> -->
    </div>
    </aside>
</template>
