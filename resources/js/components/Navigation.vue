<template>
  <nav class="navbar" role="navigation" aria-label="main navigation">
    <div class="navbar-brand">
      <router-link to="/" class="navbar-item">
        <img src="https://bulma.io/images/bulma-logo.png" width="112" height="28" alt="Logo">
      </router-link>

      <!-- Hamburger menu button -->
      <a
        role="button"
        class="navbar-burger --jb-navbar-menu-toggle mobile-aside-button"
        aria-label="menu"
        aria-expanded="false"
        data-target="navbar-menu">
        <span class="icon">
          <i class="mdi mdi-dots-vertical"></i>
        </span>
      </a>
    </div>

    <div
      id="navbar-menu"
      class="navbar-menu">
      <div class="navbar-start">
        <!-- Regular menu items -->
        <router-link to="/suppliers" class="navbar-item">
          <span class="icon"><i class="mdi mdi-account-multiple"></i></span>
          <span>Suppliers</span>
        </router-link>

        <router-link to="/groups" class="navbar-item">
          <span class="icon"><i class="mdi mdi-folder"></i></span>
          <span>Groups</span>
        </router-link>

        <router-link to="/units" class="navbar-item">
          <span class="icon"><i class="mdi mdi-scale"></i></span>
          <span>Units</span>
        </router-link>

        <!-- Dropdown menu -->
        <div class="navbar-item dropdown has-dropdown">
          <a class="navbar-link">
            <span class="icon"><i class="mdi mdi-dots-horizontal"></i></span>
            <span>More</span>
            <span class="icon">
              <i class="mdi mdi-plus"></i>
            </span>
          </a>

          <div class="navbar-dropdown">
            <router-link to="/brands" class="navbar-item">
              <span class="icon"><i class="mdi mdi-tag"></i></span>
              <span>Brands</span>
            </router-link>
            <router-link to="/items" class="navbar-item">
              <span class="icon"><i class="mdi mdi-package-variant"></i></span>
              <span>Items</span>
            </router-link>
            <router-link to="/recipes" class="navbar-item">
              <span class="icon"><i class="mdi mdi-book-open"></i></span>
              <span>Recipes</span>
            </router-link>
          </div>
        </div>
      </div>

      <div class="navbar-end">
        <div class="navbar-item">
          <div class="buttons">
            <router-link to="/profile" class="button is-primary">
              <strong>Profile</strong>
            </router-link>
            <a class="button is-light" @click="logout">
              Log out
            </a>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
// Let the theme's main.js handle all navigation functionality
const logout = () => {
  // Add your logout logic here
  console.log('Logout clicked')
}
</script>

<style scoped>
/* Let the theme handle all dropdown functionality */
</style>
