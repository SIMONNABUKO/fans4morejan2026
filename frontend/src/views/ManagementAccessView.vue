<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark flex items-center justify-center p-4">
    <div class="max-w-md w-full text-center">
      <div v-if="processing" class="space-y-4">
        <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
          <i class="ri-loader-4-line animate-spin text-blue-600 dark:text-blue-400 text-2xl"></i>
        </div>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Logging you in...</h2>
        <p class="text-gray-600 dark:text-gray-400">Setting up your session</p>
      </div>
      
      <div v-else-if="error" class="space-y-4">
        <div class="w-16 h-16 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
          <i class="ri-error-warning-line text-red-600 dark:text-red-400 text-2xl"></i>
        </div>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Access Failed</h2>
        <p class="text-gray-600 dark:text-gray-400">{{ error }}</p>
        <button
          @click="$router.push('/login')"
          class="mt-4 px-6 py-3 bg-blue-600 dark:bg-blue-500 text-white rounded-lg font-semibold hover:bg-blue-700 dark:hover:bg-blue-600 transition-colors"
        >
          Go to Login
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axiosInstance from '@/axios'
import { useAuthStore } from '@/stores/authStore'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const processing = ref(true)
const error = ref(null)

onMounted(async () => {
  const token = route.params.token
  
  if (!token) {
    error.value = 'Invalid session link'
    processing.value = false
    return
  }

  try {
    // Call the claim endpoint
    const response = await axiosInstance.post(`/management-access/${token}`)
    
    if (response.data.success) {
      // Store the authentication token using auth store method
      authStore.setToken(response.data.token)
      console.log('✅ Token stored in localStorage:', !!localStorage.getItem('auth_token'))
      
      // Set age verification to bypass age check
      localStorage.setItem('age-verified', 'true')
      console.log('✅ Age verified set:', localStorage.getItem('age-verified'))
      
      // Test if the token works by calling /me
      console.log('✅ Testing token with /me endpoint...')
      try {
        const meResponse = await axiosInstance.get('/me')
        console.log('✅ /me endpoint successful:', meResponse.data)
        authStore.user = meResponse.data
      } catch (meError) {
        console.error('❌ /me endpoint failed:', meError)
        // If /me fails, try using the user from the claim response
        if (response.data.user) {
          console.log('✅ Using user from claim response instead')
          authStore.user = response.data.user
        }
      }
      
      console.log('✅ About to redirect to home page')
      // Redirect to home page (same as normal login)
      // MainLayout will handle checking auth
      router.push('/')
    } else {
      error.value = response.data.message || 'Failed to claim session'
      processing.value = false
    }
  } catch (err) {
    console.error('Error claiming session:', err)
    error.value = err.response?.data?.message || 'This session link has expired or is invalid'
    processing.value = false
  }
})
</script>

