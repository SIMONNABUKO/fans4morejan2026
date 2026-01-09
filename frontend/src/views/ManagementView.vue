<template>
<div class="min-h-screen bg-background-light dark:bg-background-dark">
  <!-- Modern Header -->
  <header class="sticky top-0 z-20 bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-lg border-b border-border-light dark:border-border-dark shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-20 py-6">
        <!-- Left Side: Navigation and Title -->
        <div class="flex items-center gap-4">
          <router-link 
            to="/dashboard" 
            class="p-2 rounded-lg bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/20 dark:border-gray-700/50 text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary hover:bg-white/70 dark:hover:bg-gray-800/70 transition-all duration-200 hover:scale-105 shadow-sm hover:shadow-md"
          >
            <i class="ri-arrow-left-line text-lg"></i>
          </router-link>
          
          <div class="flex flex-col">
            <h1 class="text-2xl font-bold text-text-light-primary dark:text-text-dark-primary leading-tight">
              Management
            </h1>
            <p class="text-text-light-secondary dark:text-text-dark-secondary text-sm mt-0.5">
              Manage sessions and account moderators
            </p>
          </div>
        </div>
        
        <!-- Right Side: Quick Stats -->
        <div class="hidden md:flex items-center gap-6">
          <div class="text-right">
            <div class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Sessions</div>
            <div class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ sessions.length }}</div>
          </div>
          <div class="text-right">
            <div class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Moderators</div>
            <div class="text-lg font-bold text-green-600 dark:text-green-400">{{ moderators.length }}</div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="space-y-8">
      <!-- Management Sessions Section -->
      <section class="space-y-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
              <i class="ri-shield-keyhole-line text-blue-600 dark:text-blue-400 text-lg"></i>
            </div>
            <h2 class="text-xl font-semibold text-text-light-primary dark:text-text-dark-primary">Management Sessions</h2>
          </div>
          <button 
            @click="showCreateSessionModal = true"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 dark:bg-blue-500 text-white rounded-lg font-semibold hover:bg-blue-600/90 dark:hover:bg-blue-500/90 transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-blue-600 dark:focus:ring-blue-500"
          >
            <div class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center">
              <i class="ri-add-line text-sm"></i>
            </div>
            <span class="text-sm">Create Session</span>
          </button>
        </div>

        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-6">
          <div class="flex items-start gap-3">
            <div class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
              <i class="ri-information-line text-white text-sm"></i>
            </div>
            <div>
              <h3 class="text-blue-900 dark:text-blue-100 font-semibold mb-2">How Management Sessions Work</h3>
              <p class="text-blue-800 dark:text-blue-200 text-sm leading-relaxed mb-3">
                Management Sessions allow you to share a link with someone you trust to allow them to log into your account without sharing your login information.
              </p>
              <p class="text-blue-800 dark:text-blue-200 text-sm leading-relaxed">
                To share access, click on the copy icon of one of your unclaimed sessions and send the link to your trusted party. Links can only be used once. Deleting a session will also revoke access.
              </p>
            </div>
          </div>
        </div>
        
        <!-- Sessions List -->
        <div v-if="sessions.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <SessionCard 
            v-for="session in sessions" 
            :key="session.id" 
            :session="session"
            @copy="copySession"
            @delete="deleteSession"
          />
        </div>

        <div v-else class="text-center py-12">
          <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="ri-shield-keyhole-line text-2xl text-gray-400 dark:text-gray-500"></i>
          </div>
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Management Sessions</h3>
          <p class="text-gray-500 dark:text-gray-400 mb-6">Create your first management session to share account access securely</p>
          <button 
            @click="showCreateSessionModal = true"
            class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 dark:bg-blue-500 text-white rounded-lg font-semibold hover:bg-blue-600/90 dark:hover:bg-blue-500/90 transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl"
          >
            <i class="ri-add-line"></i>
            Create First Session
          </button>
        </div>
      </section>

      <!-- Account Moderators Section -->
      <section class="space-y-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
              <i class="ri-user-settings-line text-green-600 dark:text-green-400 text-lg"></i>
            </div>
            <h2 class="text-xl font-semibold text-text-light-primary dark:text-text-dark-primary">Account Moderators</h2>
          </div>
          <button 
            @click="showAddModeratorModal = true"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 dark:bg-green-500 text-white rounded-lg font-semibold hover:bg-green-600/90 dark:hover:bg-green-500/90 transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-green-600 dark:focus:ring-green-500"
          >
            <div class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center">
              <i class="ri-user-add-line text-sm"></i>
            </div>
            <span class="text-sm">Add Moderator</span>
          </button>
        </div>

        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-6">
          <div class="flex items-start gap-3">
            <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
              <i class="ri-information-line text-white text-sm"></i>
            </div>
            <div>
              <h3 class="text-green-900 dark:text-green-100 font-semibold mb-2">Moderator Permissions</h3>
              <p class="text-green-800 dark:text-green-200 text-sm leading-relaxed">
                Add, Remove and Edit your Account Moderators. Your Account Moderators can moderate your Posts or Stream Chat depending on the Permissions you assign to them.
              </p>
            </div>
          </div>
        </div>

        <!-- Moderators List -->
        <div v-if="moderators.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <ModeratorCard 
            v-for="moderator in moderators" 
            :key="moderator.id" 
            :moderator="moderator"
            @edit="editModerator"
            @remove="removeModerator"
          />
        </div>

        <div v-else class="text-center py-12">
          <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="ri-user-settings-line text-2xl text-gray-400 dark:text-gray-500"></i>
          </div>
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Account Moderators</h3>
          <p class="text-gray-500 dark:text-gray-400 mb-6">Add moderators to help manage your content and community</p>
          <button 
            @click="showAddModeratorModal = true"
            class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 dark:bg-green-500 text-white rounded-lg font-semibold hover:bg-green-600/90 dark:hover:bg-green-500/90 transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl"
          >
            <i class="ri-user-add-line"></i>
            Add First Moderator
          </button>
        </div>
      </section>
    </div>
  </div>

  <!-- Modals -->
  <CreateSessionModal
    v-if="showCreateSessionModal"
    @close="showCreateSessionModal = false"
    @create="createSession"
  />

  <AddModeratorModal
    v-if="showAddModeratorModal"
    @close="showAddModeratorModal = false"
    @add="addModerator"
  />
</div>
</template>

<script setup>
import { ref } from 'vue'
import SessionCard from '@/components/management/SessionCard.vue'
import ModeratorCard from '@/components/management/ModeratorCard.vue'
import CreateSessionModal from '@/components/management/CreateSessionModal.vue'
import AddModeratorModal from '@/components/management/AddModeratorModal.vue'

// State
const sessions = ref([])
const moderators = ref([])
const showCreateSessionModal = ref(false)
const showAddModeratorModal = ref(false)

// Session handlers
const createSession = (sessionData) => {
  sessions.value.push({
    id: Date.now(),
    ...sessionData,
    status: 'unclaimed',
    createdAt: new Date().toISOString()
  })
  showCreateSessionModal.value = false
}

const copySession = (sessionId) => {
  const session = sessions.value.find(s => s.id === sessionId)
  if (session) {
    // In a real app, this would be an API-generated link
    const link = `https://example.com/management-access/${sessionId}`
    navigator.clipboard.writeText(link)
    // Update session status
    session.status = 'copied'
  }
}

const deleteSession = (sessionId) => {
  sessions.value = sessions.value.filter(s => s.id !== sessionId)
}

// Moderator handlers
const addModerator = (moderatorData) => {
  moderators.value.push({
    id: Date.now(),
    ...moderatorData,
    addedAt: new Date().toISOString()
  })
  showAddModeratorModal.value = false
}

const editModerator = (moderatorId) => {
  // Handle moderator editing
}

const removeModerator = (moderatorId) => {
  moderators.value = moderators.value.filter(m => m.id !== moderatorId)
}
</script>

