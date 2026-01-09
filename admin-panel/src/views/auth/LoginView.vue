<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import errorLogger from '@/services/error-logger'

const router = useRouter()

const authStore = ref()
const login = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)

onMounted(async () => {
  const { useAuthStore } = await import('@/stores/auth')
  authStore.value = useAuthStore()
})

async function handleSubmit() {
  if (!login.value || !password.value) {
    error.value = 'Please fill in all fields'
    return
  }

  if (!authStore.value) {
    error.value = 'Authentication service not ready'
    return
  }

  loading.value = true
  error.value = ''

  try {
    await authStore.value.login({
      login: login.value,
      password: password.value
    })
    router.push('/')
  } catch (err: any) {
    if (err.response?.data?.message?.includes('Rate limiter') || 
        err.response?.data?.exception === 'Illuminate\\Routing\\Exceptions\\MissingRateLimiterException') {
      error.value = 'The server is currently experiencing configuration issues. Please try again later or contact the administrator.'
    } else {
      const errorMessage = err instanceof Error ? err.message : 'Login failed'
      error.value = errorMessage
    }
    
    if (err instanceof Error) {
      errorLogger.logError(err)
    } else {
      errorLogger.logError(new Error('Login form submission error'))
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="login-container min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900 px-3 py-4 sm:p-6">
    <div class="login-form-wrapper w-full max-w-[400px] bg-white dark:bg-gray-800 rounded-xl shadow-md sm:shadow-lg p-5 sm:p-8">
      <div class="mb-6">
        <h2 class="text-center text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
          Admin Panel Login
        </h2>
      </div>
      
      <form class="space-y-5" @submit.prevent="handleSubmit">
        <div class="space-y-4">
          <div>
            <label for="login" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Username or Email</label>
            <input
              id="login"
              v-model="login"
              name="login"
              type="text"
              required
              class="appearance-none block w-full px-3 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:focus:ring-indigo-400 dark:focus:border-indigo-400 text-base bg-white dark:bg-gray-800 transition-all duration-200"
              placeholder="Enter your username or email"
              :disabled="loading"
              autocomplete="username"
            />
          </div>
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Password</label>
            <input
              id="password"
              v-model="password"
              name="password"
              type="password"
              required
              class="appearance-none block w-full px-3 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:focus:ring-indigo-400 dark:focus:border-indigo-400 text-base bg-white dark:bg-gray-800 transition-all duration-200"
              placeholder="Enter your password"
              :disabled="loading"
              autocomplete="current-password"
            />
          </div>
        </div>

        <div v-if="error" class="rounded-lg bg-red-50 dark:bg-red-900/50 p-3">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-red-400 dark:text-red-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                Login Error
              </h3>
              <div class="mt-1 text-sm text-red-700 dark:text-red-300">
                {{ error }}
              </div>
            </div>
          </div>
        </div>

        <div class="mt-6">
          <button
            type="submit"
            :disabled="loading"
            class="group relative w-full flex justify-center py-2.5 px-4 border border-transparent text-base font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
          >
            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
              <svg
                class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
                aria-hidden="true"
              >
                <path
                  fill-rule="evenodd"
                  d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                  clip-rule="evenodd"
                />
              </svg>
            </span>
            <span class="ml-2">{{ loading ? 'Signing in...' : 'Sign in' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<style>
:root {
  --color-primary: 99, 102, 241;
  --color-primary-dark: 79, 70, 229;
}

/* Fix for mobile Safari */
@supports (-webkit-touch-callout: none) {
  .min-h-screen {
    min-height: -webkit-fill-available;
  }
}

/* Base styles */
.bg-primary {
  background-color: rgb(var(--color-primary));
}

.bg-primary-dark {
  background-color: rgb(var(--color-primary-dark));
}

.text-primary {
  color: rgb(var(--color-primary));
}

.text-primary-700 {
  color: rgb(var(--color-primary-dark));
}

.bg-primary-50 {
  background-color: rgba(var(--color-primary), 0.1);
}

.bg-primary-900\/50 {
  background-color: rgba(var(--color-primary-dark), 0.2);
}

.text-primary-300 {
  color: rgba(var(--color-primary), 0.8);
}

.focus\:ring-primary:focus {
  --tw-ring-color: rgb(var(--color-primary));
}

.hover\:bg-primary-dark:hover {
  background-color: rgb(var(--color-primary-dark));
}

/* Touch device optimizations */
@media (hover: none) {
  input, 
  button {
    font-size: 16px;
    line-height: 1.4;
  }
}

/* Prevent zoom on iOS */
@media screen and (max-width: 640px) {
  input[type="text"],
  input[type="password"] {
    font-size: 16px;
  }
}

/* Ensure the login container takes up appropriate vertical space */
.login-container {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  width: 100%;
}

/* Make the form look better on all screen sizes */
.login-form-wrapper {
  margin: 0 auto;
  width: 100%;
}

/* Responsive adjustments */
@media screen and (max-height: 600px) {
  .login-container {
    align-items: flex-start;
    padding-top: 1rem;
    padding-bottom: 1rem;
  }
}

/* Large screen optimizations */
@media screen and (min-width: 1024px) {
  .login-form-wrapper {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  
  .login-form-wrapper:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  }
  
  button:hover:not(:disabled) {
    transform: translateY(-1px);
  }
}
</style>