<script setup>
import axios from 'axios'
import { ref } from 'vue'
import useUserStore from '@/stores/user';

const userStore = useUserStore();
const error = ref('')
const loading = ref(false)

const logout = async () => {
  error.value = ''
  loading.value = true
  try {
    await axios.get('/sanctum/csrf-cookie')
    await axios.post('/logout')
    window.location.href = '/login'
  } catch (e) {
    if (e.response && e.response.data && e.response.data.message) {
      error.value = e.response.data.message
    } else {
      error.value = 'Failed to logout. Please try again.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
    <nav id="navbar-main" class="navbar is-fixed-top">
        <div class="navbar-brand">
            <a class="navbar-item mobile-aside-button">
            <span class="icon"><i class="mdi mdi-forwardburger mdi-24px"></i></span>
            </a>

        </div>
        <div class="navbar-brand is-right">
            <a class="navbar-item --jb-navbar-menu-toggle"
               data-target="navbar-menu">
            <span class="icon">
              <i class="mdi mdi-dots-vertical mdi-24px"></i>
            </span>
            </a>
        </div>
        <div class="navbar-menu" id="navbar-menu">
            <div class="navbar-end">

                <div class="navbar-item dropdown has-divider has-user-avatar">
                    <a class="navbar-link">
                    <div class="user-avatar">
                        <img src="https://avatars.dicebear.com/v2/initials/john-doe.svg" alt="John Doe" class="rounded-full">
                    </div>
                    <div class="is-user-name"><span>{{ userStore.user.name }}</span></div>
                    <span class="icon"><i class="mdi mdi-chevron-down"></i></span>
                    </a>
                    <div class="navbar-dropdown">
                    <!-- <router-link to="/profile" class="navbar-item">
                        <span class="icon"><i class="mdi mdi-account"></i></span>
                        <span>My Profile</span>
                    </router-link> -->
                    <!-- <a class="navbar-item">
                        <span class="icon"><i class="mdi mdi-settings"></i></span>
                        <span>Settings</span>
                    </a>
                    <a class="navbar-item">
                        <span class="icon"><i class="mdi mdi-email"></i></span>
                        <span>Messages</span>
                    </a> -->
                    <hr class="navbar-divider">
                    <a class="navbar-item" @click.prevent="logout" href="#">
                        <span class="icon"><i class="mdi mdi-logout"></i></span>
                        <span>Log Out</span>
                    </a>
                    </div>
                </div>

                <a title="Log out" @click.prevent="logout" href="#" class="navbar-item desktop-icon-only">
                    <span class="icon"><i class="mdi mdi-logout"></i></span>
                    <span>Log out</span>
                </a>
            </div>
        </div>
        </nav>

</template>
