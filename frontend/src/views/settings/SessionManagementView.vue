<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark">
    <!-- Modern Header -->
    <header class="sticky top-0 z-20 bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-lg border-b border-border-light dark:border-border-dark shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20 py-6">
          <!-- Left Side: Navigation and Title -->
          <div class="flex items-center gap-4">
            <router-link 
              to="/settings" 
              class="p-2 rounded-lg bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/20 dark:border-gray-700/50 text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary hover:bg-white/70 dark:hover:bg-gray-800/70 transition-all duration-200 hover:scale-105 shadow-sm hover:shadow-md"
            >
              <i class="ri-arrow-left-line text-lg"></i>
            </router-link>
            
            <div class="flex flex-col">
              <h1 class="text-2xl font-bold text-text-light-primary dark:text-text-dark-primary leading-tight">
                {{ t('session_management') }}
              </h1>
              <p class="text-text-light-secondary dark:text-text-dark-secondary text-sm mt-0.5">
                Manage your active sessions and devices
              </p>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Info Notice -->
      <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-2xl p-6 mb-8">
        <div class="flex items-start gap-4">
          <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="ri-information-line text-blue-600 dark:text-blue-400 text-xl"></i>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-2">Session Management</h3>
            <p class="text-blue-700 dark:text-blue-300 leading-relaxed">
              View and manage all devices where you're currently logged in. If you see any suspicious activity, end those sessions immediately.
            </p>
          </div>
        </div>
      </div>

      <!-- Sessions List -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Active Sessions</h2>
          <button
            @click="openCreateModal"
            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 dark:bg-blue-500 text-white rounded-lg font-semibold hover:bg-blue-600/90 dark:hover:bg-blue-500/90 transition-all duration-200"
          >
            <i class="ri-add-line"></i>
            Create Session
          </button>
        </div>
        
        <div v-if="loading" class="p-6 text-center">
          <i class="ri-loader-4-line animate-spin text-4xl text-blue-600 dark:text-blue-400"></i>
          <p class="text-gray-500 dark:text-gray-400 mt-4">Loading sessions...</p>
        </div>
        
        <div v-else-if="sessions.length > 0" class="p-6 space-y-4">
          <div v-for="session in sessions" :key="session.id" class="border border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-3">
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                  <span class="text-base font-semibold text-gray-900 dark:text-white">{{ session.name }}</span>
                  <span 
                    class="px-3 py-1 rounded-full text-xs font-semibold flex items-center gap-1"
                    :class="{
                      'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400': getSessionStatus(session).color === 'green',
                      'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400': getSessionStatus(session).color === 'gray',
                      'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400': getSessionStatus(session).color === 'red'
                    }"
                  >
                    <i :class="getSessionStatus(session).icon" class="text-sm"></i>
                    {{ getSessionStatus(session).label }}
                  </span>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  Created: {{ new Date(session.created_at).toLocaleDateString() }}
                </p>
                <p v-if="session.expires_at" class="text-sm text-gray-500 dark:text-gray-400">
                  Expires: {{ new Date(session.expires_at).toLocaleDateString() }}
                </p>
                <div v-if="session.used_at" class="mt-2 p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                  <p class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-1">
                    ✓ Used on {{ new Date(session.used_at).toLocaleDateString() }} at {{ new Date(session.used_at).toLocaleTimeString() }}
                  </p>
                  <div v-if="session.device_name || session.browser" class="flex flex-wrap gap-2 text-xs text-blue-700 dark:text-blue-300">
                    <span v-if="session.device_name">
                      <i class="ri-smartphone-line"></i> {{ session.device_name }}
                    </span>
                    <span v-if="session.platform">
                      <i class="ri-computer-line"></i> {{ session.platform }}
                    </span>
                    <span v-if="session.browser">
                      <i class="ri-global-line"></i> {{ session.browser }}
                    </span>
                    <span v-if="session.ip_address">
                      <i class="ri-map-pin-line"></i> {{ session.ip_address }}
                    </span>
                  </div>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <button
                  @click="copyLink(session.id)"
                  class="p-2 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                  title="Copy link"
                >
                  <i class="ri-file-copy-line text-lg"></i>
                </button>
                <button
                  @click="deleteSession(session.id)"
                  class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                  title="Delete session"
                >
                  <i class="ri-delete-bin-line text-lg"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
        
        <div v-else class="p-6">
          <div class="text-center py-12">
            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
              <i class="ri-computer-line text-2xl text-gray-400 dark:text-gray-500"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No Sessions Yet</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-6">Create a session link to share account access securely</p>
            <button
              @click="openCreateModal"
              class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 dark:bg-blue-500 text-white rounded-lg font-semibold hover:bg-blue-600/90 dark:hover:bg-blue-500/90 transition-all duration-200"
            >
              <i class="ri-add-line"></i>
              Create First Session
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Session Modal -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div 
          class="absolute inset-0 bg-black/50 backdrop-blur-sm"
          @click="showCreateModal = false"
        ></div>

        <!-- Modal -->
        <Transition
          enter-active-class="transition duration-200 ease-out"
          enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100"
          leave-active-class="transition duration-150 ease-in"
          leave-from-class="opacity-100 scale-100"
          leave-to-class="opacity-0 scale-95"
        >
          <div 
            v-if="showCreateModal"
            class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6"
          >
            <!-- Close button -->
            <button 
              class="absolute right-4 top-4 p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
              @click="showCreateModal = false"
            >
              <i class="ri-close-line text-xl text-gray-500 dark:text-gray-400"></i>
            </button>

            <!-- Content -->
            <div class="space-y-4">
              <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Create Management Session</h2>
              
              <p class="text-gray-600 dark:text-gray-400">
                Give this session a name to help you identify it later.
              </p>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Session Name
                </label>
                <input
                  v-model="sessionName"
                  type="text"
                  placeholder="e.g., Team Member Access"
                  class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-gray-900 dark:text-white"
                  @keyup.enter="createSession"
                />
              </div>

              <!-- Actions -->
              <div class="flex gap-3 pt-4">
                <button
                  @click="showCreateModal = false"
                  class="flex-1 py-2 px-4 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium transition-colors"
                >
                  Cancel
                </button>
                <button
                  @click="createSession"
                  :disabled="creating || !sessionName.trim()"
                  class="flex-1 py-2 px-4 bg-blue-600 dark:bg-blue-500 hover:bg-blue-700 dark:hover:bg-blue-600 text-white rounded-lg font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <i v-if="creating" class="ri-loader-4-line animate-spin mr-2"></i>
                  {{ creating ? 'Creating...' : 'Create Session' }}
                </button>
              </div>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import axiosInstance from '@/axios'
import { useToast } from 'vue-toastification'

const { t } = useI18n()
const router = useRouter()
const toast = useToast()

const sessions = ref([])
const loading = ref(false)
const creating = ref(false)
const showCreateModal = ref(false)
const sessionName = ref('')

const fetchSessions = async () => {
  loading.value = true
  try {
    const response = await axiosInstance.get('/management-sessions')
    if (response.data.success) {
      sessions.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching sessions:', error)
    toast.error('Failed to load sessions')
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  showCreateModal.value = true
}

const createSession = async () => {
  if (!sessionName.value.trim()) {
    toast.error('Please enter a session name')
    return
  }
  
  creating.value = true
  try {
    const response = await axiosInstance.post('/management-sessions', {
      name: sessionName.value
    })
    
    if (response.data.success) {
      sessions.value.unshift(response.data.data)
      toast.success('Session created successfully!')
      
      // Copy link to clipboard
      await navigator.clipboard.writeText(response.data.link)
      toast.info('Session link copied to clipboard!')
      
      // Reset form and close modal
      sessionName.value = ''
      showCreateModal.value = false
    }
  } catch (error) {
    console.error('Error creating session:', error)
    toast.error(error.response?.data?.message || 'Failed to create session')
  } finally {
    creating.value = false
  }
}

const copyLink = async (sessionId) => {
  const session = sessions.value.find(s => s.id === sessionId)
  if (session) {
    // Use the link from the API response or construct it
    const link = session.link || `${window.location.origin}/management-access/${session.token}`
    await navigator.clipboard.writeText(link)
    toast.success('Link copied to clipboard!')
  }
}

const deleteSession = async (sessionId) => {
  if (!confirm('Are you sure you want to delete this session? This will log out anyone using this link.')) {
    return
  }
  
  try {
    const response = await axiosInstance.delete(`/management-sessions/${sessionId}`)
    
    if (response.data.success) {
      sessions.value = sessions.value.filter(s => s.id !== sessionId)
      toast.success('Session deleted successfully')
    }
  } catch (error) {
    console.error('Error deleting session:', error)
    toast.error('Failed to delete session')
  }
}

const getSessionStatus = (session) => {
  if (session.used_at) {
    return { label: 'Used', color: 'gray', icon: 'ri-checkbox-circle-fill' }
  }
  if (session.expires_at && new Date(session.expires_at) < new Date()) {
    return { label: 'Expired', color: 'red', icon: 'ri-error-warning-fill' }
  }
  return { label: 'Active', color: 'green', icon: 'ri-checkbox-blank-circle-fill' }
}

onMounted(() => {
  fetchSessions()
})
</script>

<style scoped>
/* Add your styles here */
</style>
