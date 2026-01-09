<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark">
    <!-- Mobile View -->
    <div class="lg:hidden">
      <!-- Mobile Header -->
      <header class="bg-surface-light dark:bg-surface-dark shadow-sm">
        <div class="px-4 py-4 flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-br from-primary-light to-primary-dark rounded-xl flex items-center justify-center">
              <img src="/fslogo.png" alt="Fans4More" class="w-6 h-6 object-contain" />
            </div>
            <div>
              <h1 class="text-xl font-bold text-text-light-primary dark:text-text-dark-primary">Fans4More</h1>
              <p class="text-xs text-text-light-secondary dark:text-text-dark-secondary">Privacy Policy</p>
            </div>
          </div>
          <div class="flex items-center space-x-3">
            <!-- Theme Toggle -->
            <button 
              @click="toggleTheme"
              class="p-2 text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary hover:bg-glassmorphism-light-tertiary dark:hover:bg-glassmorphism-dark-tertiary rounded-xl transition-all duration-200"
            >
              <i :class="theme === 'dark' ? 'ri-sun-line' : 'ri-moon-line'" class="text-xl"></i>
            </button>
            <router-link 
              to="/auth"
              class="text-sm font-medium text-primary-light dark:text-primary-dark hover:text-gradient-blue-to dark:hover:text-gradient-blue-from"
            >
              Sign In
            </router-link>
          </div>
        </div>
      </header>

      <!-- Mobile Content -->
      <section class="px-4 py-8">
        <div class="max-w-sm mx-auto">
          <h2 class="text-2xl font-bold text-text-light-primary dark:text-text-dark-primary mb-3 text-center">
            Privacy Policy
          </h2>
          <p class="text-text-light-secondary dark:text-text-dark-secondary mb-6 text-center">
            Last updated: {{ lastUpdated }}
          </p>
          
          <!-- Loading State -->
          <div v-if="loading" class="text-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-3 border-primary-light border-t-transparent mx-auto mb-3"></div>
            <span class="text-text-light-secondary dark:text-text-dark-secondary">Loading privacy policy...</span>
          </div>

          <!-- Error State -->
          <div v-else-if="error" class="text-center py-8">
            <div class="w-16 h-16 bg-error-light/10 dark:bg-error-dark/20 rounded-full flex items-center justify-center mx-auto mb-4">
              <i class="ri-error-warning-line text-error-light dark:text-error-dark text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-text-light-primary dark:text-text-dark-primary mb-2">Error Loading Privacy Policy</h3>
            <p class="text-text-light-secondary dark:text-text-dark-secondary mb-4">{{ error }}</p>
            <button @click="fetchPrivacyPolicy" class="px-4 py-2 bg-primary-light text-background-light rounded-lg hover:bg-primary-dark transition-colors">
              Try Again
            </button>
          </div>

          <!-- Content -->
          <div v-else-if="privacyPolicy" class="bg-surface-light dark:bg-surface-dark rounded-2xl shadow-lg border border-border-light dark:border-border-dark p-6">
            <div class="prose prose-sm max-w-none text-text-light-primary dark:text-text-dark-primary" v-html="privacyPolicy.content"></div>
          </div>
        </div>
      </section>
    </div>

    <!-- Desktop View -->
    <div class="hidden lg:flex min-h-screen">
      <!-- Left Side - Content -->
      <div class="flex-1 flex flex-col">
        <!-- Desktop Header -->
        <header class="bg-surface-light dark:bg-surface-dark border-b border-border-light dark:border-border-dark">
          <div class="px-8 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="w-12 h-12 bg-gradient-to-br from-primary-light to-primary-dark rounded-2xl flex items-center justify-center">
                <img src="/fslogo.png" alt="Fans4More" class="w-8 h-8 object-contain" />
              </div>
              <div>
                <h1 class="text-2xl font-bold text-text-light-primary dark:text-text-dark-primary">Fans4More</h1>
                <p class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Privacy Policy</p>
              </div>
            </div>
            
            <div class="flex items-center space-x-4">
              <!-- Theme Toggle -->
              <button 
                @click="toggleTheme"
                class="p-2 text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary hover:bg-glassmorphism-light-tertiary dark:hover:bg-glassmorphism-dark-tertiary rounded-lg transition-all duration-200"
              >
                <i :class="theme === 'dark' ? 'ri-sun-line' : 'ri-moon-line'" class="text-xl"></i>
              </button>
              <router-link 
                to="/auth"
                class="text-sm text-primary-light dark:text-primary-dark hover:text-gradient-blue-to dark:hover:text-gradient-blue-from"
              >
                Sign In
              </router-link>
            </div>
          </div>
        </header>

        <!-- Desktop Content -->
        <div class="flex-1 flex items-center justify-center px-8 py-12">
          <div class="max-w-4xl w-full">
            <!-- Loading State -->
            <div v-if="loading" class="text-center py-12">
              <div class="animate-spin rounded-full h-12 w-12 border-4 border-primary-light border-t-transparent mx-auto mb-4"></div>
              <span class="text-text-light-secondary dark:text-text-dark-secondary">Loading privacy policy...</span>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="text-center py-12">
              <div class="w-20 h-20 bg-error-light/10 dark:bg-error-dark/20 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="ri-error-warning-line text-error-light dark:text-error-dark text-3xl"></i>
              </div>
              <h3 class="text-xl font-semibold text-text-light-primary dark:text-text-dark-primary mb-3">Error Loading Privacy Policy</h3>
              <p class="text-text-light-secondary dark:text-text-dark-secondary mb-6">{{ error }}</p>
              <button @click="fetchPrivacyPolicy" class="px-6 py-3 bg-primary-light text-background-light rounded-lg hover:bg-primary-dark transition-colors">
                Try Again
              </button>
            </div>

            <!-- Content -->
            <div v-else-if="privacyPolicy" class="bg-surface-light dark:bg-surface-dark rounded-3xl shadow-2xl border border-border-light dark:border-border-dark overflow-hidden">
              <!-- Header with gradient -->
              <div class="bg-gradient-to-r from-primary-light to-primary-dark px-8 py-6 text-background-light">
                <div class="flex justify-between items-start">
                  <div>
                    <h2 class="text-2xl font-bold">Privacy Policy</h2>
                    <p class="text-background-light/80 mt-1">Last updated: {{ lastUpdated }}</p>
                  </div>
                  <div class="flex items-center gap-3">
                    <div class="px-3 py-1 bg-background-light/20 rounded-full">
                      <span class="text-xs font-medium">Version {{ privacyPolicy.version }}</span>
                    </div>
                    <div v-if="privacyPolicy.effective_date" class="px-3 py-1 bg-background-light/20 rounded-full">
                      <span class="text-xs font-medium">Effective: {{ formatDate(privacyPolicy.effective_date) }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Content -->
              <div class="p-8">
                <div class="prose prose-lg max-w-none text-text-light-primary dark:text-text-dark-primary" v-html="privacyPolicy.content"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Side - Auth Panel -->
      <div class="w-96 bg-surface-light dark:bg-surface-dark border-l border-border-light dark:border-border-dark flex flex-col">
        <div class="flex-1 flex items-center justify-center p-8">
          <div class="w-full max-w-sm text-center">
            <div class="mb-8">
              <h3 class="text-2xl font-bold text-text-light-primary dark:text-text-dark-primary mb-2">
                Join Fans4More
              </h3>
              <p class="text-text-light-secondary dark:text-text-dark-secondary">
                Start your journey today
              </p>
            </div>

            <!-- Primary Actions -->
            <div class="space-y-4 mb-8">
              <router-link
                to="/auth"
                class="w-full py-4 bg-gradient-to-r from-primary-light to-primary-dark text-background-light rounded-2xl font-semibold hover:shadow-lg hover:shadow-primary-light/25 transition-all duration-200 flex items-center justify-center space-x-2"
              >
                <i class="ri-user-add-line"></i>
                <span>Create Account</span>
              </router-link>
              <router-link
                to="/auth"
                class="w-full py-4 bg-glassmorphism-light-secondary dark:bg-glassmorphism-dark-secondary text-text-light-primary dark:text-text-dark-primary rounded-2xl font-semibold hover:bg-glassmorphism-light-primary dark:hover:bg-glassmorphism-dark-primary transition-colors flex items-center justify-center space-x-2"
              >
                <i class="ri-login-box-line"></i>
                <span>Sign In</span>
              </router-link>
            </div>

            <!-- Divider -->
            <div class="relative mb-8">
              <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-border-light dark:border-border-dark"></div>
              </div>
              <div class="relative flex justify-center text-sm">
                <span class="px-4 bg-surface-light dark:bg-surface-dark text-text-light-secondary dark:text-text-dark-secondary">
                  Or continue with
                </span>
              </div>
            </div>

            <!-- Social Login -->
            <div class="space-y-3 mb-8">
              <button class="w-full py-3 bg-surface-light dark:bg-glassmorphism-dark-secondary border border-border-light dark:border-border-dark text-text-light-primary dark:text-text-dark-primary rounded-xl flex items-center justify-center space-x-3 hover:bg-glassmorphism-light-secondary dark:hover:bg-glassmorphism-dark-primary transition-colors">
                <i class="ri-google-fill text-xl text-error-light"></i>
                <span>Continue with Google</span>
              </button>
              <button class="w-full py-3 bg-info-light text-background-light rounded-xl flex items-center justify-center space-x-3 hover:bg-info-dark transition-colors">
                <i class="ri-twitter-fill text-xl"></i>
                <span>Continue with Twitter</span>
              </button>
              <button class="w-full py-3 bg-ui-highlight text-background-light rounded-xl flex items-center justify-center space-x-3 hover:bg-gradient-purple-to transition-colors">
                <i class="ri-twitch-fill text-xl"></i>
                <span>Continue with Twitch</span>
              </button>
            </div>

            <!-- Terms -->
            <p class="text-xs text-center text-text-light-secondary dark:text-text-dark-secondary leading-relaxed">
              By joining, you agree to our 
              <router-link to="/terms" class="text-primary-light dark:text-primary-dark hover:underline">Terms</router-link> and 
              <router-link to="/privacy" class="text-primary-light dark:text-primary-dark hover:underline">Privacy Policy</router-link>.
              <br>You must be 18+ to join.
            </p>
          </div>
        </div>

        <!-- Footer -->
        <Footer />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axiosInstance from '@/axios'
import { useTheme } from '@/composables/useTheme'
import Footer from '@/components/Footer.vue'

const router = useRouter()
const { theme, setTheme } = useTheme()
const loading = ref(true)
const error = ref(null)
const privacyPolicy = ref(null)
const lastUpdated = ref('')

const fetchPrivacyPolicy = async () => {
  try {
    loading.value = true
    error.value = null
    
    const response = await axiosInstance.get('/privacy/latest')
    privacyPolicy.value = response.data.privacy_policy
    
    if (privacyPolicy.value.updated_at) {
      lastUpdated.value = formatDate(privacyPolicy.value.updated_at)
    }
  } catch (err) {
    console.error('Error fetching privacy policy:', err)
    error.value = err.response?.data?.message || 'Failed to load privacy policy'
  } finally {
    loading.value = false
  }
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const toggleTheme = () => {
  setTheme(theme.value === 'dark' ? 'light' : 'dark')
}

onMounted(() => {
  fetchPrivacyPolicy()
})
</script>

<style scoped>
/* Custom styles for the privacy policy content */
.prose {
  @apply text-text-light-primary dark:text-text-dark-primary;
}

.prose h1 {
  @apply text-3xl font-bold text-text-light-primary dark:text-text-dark-primary mb-6;
}

.prose h2 {
  @apply text-2xl font-semibold text-text-light-primary dark:text-text-dark-primary mb-4 mt-8;
}

.prose h3 {
  @apply text-xl font-medium text-text-light-primary dark:text-text-dark-primary mb-3 mt-6;
}

.prose p {
  @apply mb-4 text-text-light-secondary dark:text-text-dark-secondary leading-relaxed;
}

.prose ul {
  @apply list-disc list-inside mb-4 text-text-light-secondary dark:text-text-dark-secondary space-y-2;
}

.prose li {
  @apply text-text-light-secondary dark:text-text-dark-secondary;
}

.prose strong {
  @apply font-semibold text-text-light-primary dark:text-text-dark-primary;
}

.prose a {
  @apply text-primary-light dark:text-primary-dark hover:text-primary-dark dark:hover:text-primary-light underline;
}

.prose blockquote {
  @apply border-l-4 border-border-light dark:border-border-dark pl-4 italic text-text-light-secondary dark:text-text-dark-secondary;
}

.prose code {
  @apply bg-glassmorphism-light-secondary dark:bg-glassmorphism-dark-secondary px-2 py-1 rounded text-sm font-mono;
}

.prose pre {
  @apply bg-glassmorphism-light-secondary dark:bg-glassmorphism-dark-secondary p-4 rounded-lg overflow-x-auto;
}

.prose table {
  @apply w-full border-collapse border border-border-light dark:border-border-dark;
}

.prose th {
  @apply bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark px-4 py-2 text-left font-semibold;
}

.prose td {
  @apply border border-border-light dark:border-border-dark px-4 py-2;
}
</style> 