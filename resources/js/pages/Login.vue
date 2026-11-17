<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import useUserStore from '@/stores/user'

axios.defaults.withCredentials = true

const router = useRouter()
const userStore = useUserStore()
// Importing necessary modules and components

// Served from public/ so the logo keeps a stable URL in dev and in builds.
const logoUrl = '/images/hill-road-catering-logo-white.webp'

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
    <div class="login-brand">
      <img :src="logoUrl" alt="Hill Road Catering">
    </div>
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
.form-screen {
  position: fixed;
  inset: 0;
  z-index: 45;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem 1.5rem;
  overflow-y: auto;
  background:
    radial-gradient(120% 100% at 50% -10%, rgba(255, 255, 255, 0.1), transparent 55%),
    linear-gradient(150deg, #3a4633 0%, #303b2b 100%);
}

.login-brand {
  width: min(250px, 65vw);
  margin-bottom: 1.6rem;
}

.login-brand img {
  display: block;
  width: 100%;
  height: auto;
}

.form-screen .main-section {
  display: block;
  width: 100%;
  max-width: 430px;
  height: auto;
  padding: 0;
}

.form-screen .card {
  width: 100%;
  overflow: hidden;
  border-color: rgba(255, 255, 255, 0.14);
  border-radius: 6px;
  box-shadow: 0 35px 70px -30px rgba(15, 20, 13, 0.65);
}

.loader {
  border: 2px solid #e6ddcb;
  border-top: 2px solid #3a4633;
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

@media (max-height: 720px) {
  .form-screen {
    justify-content: flex-start;
  }
}

@media (max-width: 560px) {
  .form-screen {
    padding: 1.5rem 1rem;
  }

  .login-brand {
    width: 190px;
    margin-bottom: 1.25rem;
  }
}
</style>
