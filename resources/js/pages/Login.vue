<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import useUserStore from '@/stores/user'

axios.defaults.withCredentials = true

const router = useRouter()
const userStore = useUserStore()
// Importing necessary modules and components

const login = ref('');
const password = ref('');
const remember = ref(true);
const error = ref('');
const loading = ref(false);




onMounted(() => {
  // Check if user is already authenticated
  //checkAuthAndRedirect();

})

const submitLogin = async () => {
  error.value = ''
  loading.value = true

  try {
    await userStore.login({
      email: login.value,
      password: password.value,
      remember: remember.value
    })
    // Only redirect if login is successful (no error thrown)
    router.push('/')
  } catch (e) {

    if (e.response && e.response.data && e.response.data.message) {
      error.value = e.response.data.message
    } else {
      error.value = 'Login failed. Please try again.'
    }

  } finally {
    loading.value = false
  }
}


</script>

<template>
  <div class="form-screen">
    <section class="section main-section ">
    <div class="card">
      <header class="card-header">
        <p class="card-header-title">
          <span class="icon"><i class="mdi mdi-lock"></i></span>
          Login
        </p>
      </header>
      <div class="card-content">
        <form @submit.prevent="submitLogin">
          <div class="field spaced">
            <label class="label">Login</label>
            <div class="control icons-left">
              <input v-model="login" class="input" type="text" name="login" placeholder="user@example.com" autocomplete="username">
              <span class="icon is-small left"><i class="mdi mdi-account"></i></span>
            </div>
            <p class="help">
              Please enter your login
            </p>
          </div>

          <div class="field spaced">
            <label class="label">Password</label>
            <p class="control icons-left">
              <input v-model="password" class="input" type="password" name="password" placeholder="Password" autocomplete="current-password">
              <span class="icon is-small left"><i class="mdi mdi-asterisk"></i></span>
            </p>
            <p class="help">
              Please enter your password
            </p>
          </div>

          <div class="field spaced">
            <div class="control">
              <label class="checkbox"><input type="checkbox" v-model="remember" name="remember" value="1">
                <span class="check"></span>
                <span class="control-label">Remember</span>
              </label>
            </div>
          </div>

          <div v-if="error" class="notification is-danger">{{ error }}</div>

          <hr>

          <div class="field grouped">
            <div class="control">
              <button type="submit" class="button blue" :disabled="loading">
                <span v-if="loading" class="loader"></span>
                <span v-else>Login</span>
              </button>
            </div>

          </div>

        </form>
      </div>
    </div>
  </section>
  </div>
</template>



<style scoped>
.loader {
  border: 2px solid #f3f3f3;
  border-top: 2px solid #3498db;
  border-radius: 50%;
  width: 14px;
  height: 14px;
  animation: spin 1s linear infinite;
  display: inline-block;
  margin-right: 5px;
}
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
