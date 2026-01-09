<template>
<div class="min-h-screen bg-background-light dark:bg-background-dark">
  <!-- Modern Header -->
  <header class="sticky top-0 z-20 bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-lg border-b border-border-light dark:border-border-dark shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-20 py-6">
        <!-- Left Side: Navigation and Title -->
        <div class="flex items-center gap-4">
          <div class="flex flex-col">
            <h1 class="text-2xl font-bold text-text-light-primary dark:text-text-dark-primary leading-tight">
              Messages
            </h1>
            <p class="text-text-light-secondary dark:text-text-dark-secondary text-sm mt-0.5">
              Connect with your subscribers and fans
            </p>
          </div>
        </div>
        
        <!-- Right Side: Quick Stats -->
        <div class="hidden md:flex items-center gap-6">
          <div class="text-right">
            <div class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Conversations</div>
            <div class="text-lg font-bold text-primary-light dark:text-primary-dark">{{ messagesStore.conversations.length }}</div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- Search and Navigation Section -->
  <div class="sticky top-20 z-10 bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-lg border-b border-border-light dark:border-border-dark shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
      <!-- Enhanced Search -->
      <div class="relative mb-4">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
          <i class="ri-search-line text-text-light-tertiary dark:text-text-dark-tertiary text-lg"></i>
        </div>
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="t('search_conversations')"
          class="w-full pl-12 pr-4 py-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-text-light-primary dark:text-text-dark-primary placeholder:text-text-light-tertiary dark:placeholder:text-text-dark-tertiary focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:border-transparent transition-all duration-200"
          @input="handleSearch"
        />
      </div>

      <!-- Dynamic Navigation -->
      <MessagesNavigation 
        @refresh="refreshConversations" 
        @filter-change="handleFilterChange"
      />
    </div>
  </div>

  <!-- Main Content -->
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-16">
      <div class="flex flex-col items-center gap-4">
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-primary-light dark:border-primary-dark border-t-transparent"></div>
        <p class="text-text-light-secondary dark:text-text-dark-secondary">{{ t('loading_conversations') }}</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="flex justify-center items-center py-16">
      <div class="text-center">
        <div class="w-16 h-16 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
          <i class="ri-error-warning-line text-2xl text-red-600 dark:text-red-400"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Error Loading Messages</h3>
        <p class="text-red-500 dark:text-red-400">{{ error }}</p>
      </div>
    </div>

    <!-- Messages List -->
    <div v-else class="space-y-4">
      <!-- Empty State -->
      <div v-if="messagesStore.conversations.length === 0 && !searchResults.length" class="flex flex-col items-center justify-center py-16 text-center">
        <div class="w-20 h-20 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-6">
          <i class="ri-message-3-line text-3xl text-gray-400 dark:text-gray-500"></i>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">{{ t('no_conversations') }}</h3>
        <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md">Start a conversation to see your messages here</p>
        <button 
          @click="openNewMessageModal"
          class="inline-flex items-center gap-2 px-6 py-3 bg-primary-light dark:bg-primary-dark text-white rounded-lg font-semibold hover:bg-primary-light/90 dark:hover:bg-primary-dark/90 transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl"
        >
          <i class="ri-add-line"></i>
          Start New Conversation
        </button>
      </div>

      <!-- Search Results -->
      <div v-if="searchResults.length" class="space-y-3">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Search Results</h3>
        <div class="grid gap-3">
          <div 
            v-for="user in searchResults" 
            :key="user.id" 
            @click="openChat(user)" 
            class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 flex items-center gap-4 cursor-pointer hover:shadow-lg transition-all duration-200 hover:scale-[1.02] group"
          >
            <div class="relative">
              <img 
                :src="user.avatar || '/default-avatar.png'" 
                :alt="user.name" 
                class="w-12 h-12 rounded-full object-cover ring-2 ring-gray-100 dark:ring-gray-700 group-hover:ring-primary-light dark:group-hover:ring-primary-dark transition-all duration-200"
              >
              <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white dark:border-gray-800"></span>
            </div>
            <div class="flex-1">
              <button 
                @click.stop="$router.push(`/${user.username}/posts`)"
                class="font-semibold text-gray-900 dark:text-white group-hover:text-primary-light dark:group-hover:text-primary-dark transition-colors duration-200 cursor-pointer block w-full text-left"
              >
                {{ user.name }}
              </button>
              <button 
                @click.stop="$router.push(`/${user.username}/posts`)"
                class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary-light dark:hover:text-primary-dark transition-colors cursor-pointer block w-full text-left"
              >
                @{{ user.username }}
              </button>
            </div>
            <i class="ri-arrow-right-s-line text-gray-400 dark:text-gray-500 group-hover:text-primary-light dark:group-hover:text-primary-dark transition-colors duration-200"></i>
          </div>
        </div>
      </div>

      <!-- Conversations List -->
      <div v-else-if="filteredConversations.length > 0" class="space-y-3">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Conversations</h3>
        <div class="grid gap-3">
          <MessagePreview
            v-for="conversation in filteredConversations"
            :key="conversation.id"
            :conversation="conversation"
            @click="openChat(conversation.user)"
            @user-blocked="handleUserBlocked"
            @user-added-to-list="handleUserAddedToList"
          />
        </div>
      </div>

      <!-- No Results State -->
      <div v-else-if="searchQuery && !searchResults.length" class="flex flex-col items-center justify-center py-16 text-center">
        <div class="w-20 h-20 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-6">
          <i class="ri-search-line text-3xl text-gray-400 dark:text-gray-500"></i>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No Results Found</h3>
        <p class="text-gray-500 dark:text-gray-400 mb-6">Try searching with a different term</p>
        <button 
          @click="searchQuery = ''"
          class="inline-flex items-center gap-2 px-6 py-3 bg-gray-600 dark:bg-gray-500 text-white rounded-lg font-semibold hover:bg-gray-600/90 dark:hover:bg-gray-500/90 transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl"
        >
          <i class="ri-refresh-line"></i>
          Clear Search
        </button>
      </div>
    </div>
  </div>

  <!-- Floating Action Button for New Message -->
  <button
    @click="openNewMessageModal"
    class="fixed bottom-6 right-6 w-16 h-16 bg-gradient-to-r from-primary-light to-primary-dark hover:from-primary-dark hover:to-primary-light text-white rounded-full shadow-xl hover:shadow-2xl transition-all duration-300 flex items-center justify-center z-30 group hover:scale-110"
  >
    <i class="ri-add-line text-2xl group-hover:rotate-90 transition-transform duration-300"></i>
  </button>

  <!-- New Message Modal for recipient editing -->
  <NewMessageModal
    :is-open="isNewMessageModalOpen"
    @close="closeNewMessageModal"
  />
</div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useMessagesStore } from '@/stores/messagesStore'
import { useMessagesFilterStore } from '@/stores/messagesFilterStore'
import { useListStore } from '@/stores/listStore'
import MessagePreview from '@/components/messages/MessagePreview.vue'
import MessagesNavigation from '@/components/messages/MessagesNavigation.vue'
import NewMessageModal from '@/components/messages/NewMessageModal.vue'
import { useI18n } from 'vue-i18n'
import axiosInstance from '@/axios'

const { t } = useI18n()
const router = useRouter()
const messagesStore = useMessagesStore()
const messagesFilterStore = useMessagesFilterStore()
const listStore = useListStore()

const searchQuery = ref('')
const searchResults = ref([])
const isNewMessageModalOpen = ref(false)

const loading = computed(() => messagesStore.loading)
const error = computed(() => messagesStore.error)

const filteredConversations = computed(() => {
  // If we have a specific filter active, return the filtered results from API
  if (messagesFilterStore.activeFilter !== 'all') {
    return messagesStore.filteredConversations || []
  }

  // For 'all' filter, use regular conversations with search filtering
  let conversations = messagesStore.conversations || []

  // Apply search query filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    conversations = conversations.filter(conv =>
      conv.user.username.toLowerCase().includes(query) ||
      conv.user.handle.toLowerCase().includes(query)
    )
  }

  return conversations
})

const openChat = (user) => {
  if (user && user.id) {
    router.push({ name: 'single-message', params: { id: user.id } })
  } else {
    console.error('Invalid user object:', user)
  }
}

const refreshConversations = async () => {
  if (messagesFilterStore.activeFilter === 'all') {
    // For 'all' filter, use regular conversations
    await messagesStore.fetchRecentConversations()
  } else {
    // For list filters, fetch filtered conversations from API
    await fetchFilteredConversations(messagesFilterStore.activeFilter)
  }
  console.log('Conversations after refresh:', messagesStore.conversations)
}

const fetchFilteredConversations = async (filterId) => {
  console.log('🎯 Fetching filtered conversations for filter:', filterId)
  
  try {
    messagesStore.loading = true
    
    // Call the backend API to get filtered conversations
    const response = await axiosInstance.get('/user/message-filters/filtered-conversations', {
      params: { filter_id: filterId }
    })
    
    console.log('🎯 Filtered conversations response:', response.data)
    
    // Store the filtered conversations in messagesStore
    messagesStore.filteredConversations = response.data.conversations || []
    
    console.log('🎯 Stored filtered conversations:', messagesStore.filteredConversations.length)
  } catch (error) {
    console.error('Error fetching filtered conversations:', error)
    messagesStore.error = 'Failed to load filtered conversations'
  } finally {
    messagesStore.loading = false
  }
}

const handleSearch = async () => {
  if (searchQuery.value.length >= 2) {
    searchResults.value = await messagesStore.searchUsers(searchQuery.value)
  } else {
    searchResults.value = []
  }
}

const handleFilterChange = async (filterId) => {
  console.log('🎯 Messages filter changed to:', filterId)
  
  // If it's a list filter, make sure we have the list data with members and fetch filtered conversations
  if (filterId.startsWith('list_')) {
    const listId = parseInt(filterId.replace('list_', ''))
    await listStore.fetchListMembers(listId)
    
    // Fetch filtered conversations from the backend
    await fetchFilteredConversations(filterId)
  } else if (filterId === 'all') {
    // For 'all' filter, fetch regular conversations
    await messagesStore.fetchRecentConversations()
  }
}

const handleUserBlocked = async (userId) => {
  console.log('🎯 User blocked:', userId)
  // Refresh conversations to remove the blocked user
  await refreshConversations()
}

const handleUserAddedToList = async (listData) => {
  console.log('🎯 User added to list:', listData)
  // Refresh conversations to include the new list member
  await refreshConversations()
}

// New Message Modal functions
const openNewMessageModal = () => {
  isNewMessageModalOpen.value = true
}

const closeNewMessageModal = () => {
  isNewMessageModalOpen.value = false
}

// Function to open conversation with a specific user by username
const openConversationWithUser = async (username) => {
  try {
    // First, search for the user
    const users = await messagesStore.searchUsers(username)
    const targetUser = users.find(user => user.username === username || user.name === username)
    
    if (targetUser) {
      // User found, open the conversation
      openChat(targetUser)
    } else {
      // User not found, show error
      console.error('User not found:', username)
      // You could show a toast notification here
    }
  } catch (error) {
    console.error('Error opening conversation with user:', error)
  }
}

watch(searchQuery, (newValue) => {
  if (newValue === '') {
    searchResults.value = []
  }
})

onMounted(async () => {
  // Initialize message filters and conversations
  await Promise.all([
    messagesFilterStore.fetchUserFilterPreferences(),
    refreshConversations()
  ])
  
  // Check if we should auto-open the new message modal for recipient editing
  const route = router.currentRoute.value
  if (route.query.openModal === 'true' && route.query.mode === 'mass') {
    console.log('Auto-opening NewMessageModal for recipient editing')
    openNewMessageModal()
    
    // Clean up query parameters to avoid reopening on refresh
    router.replace({ 
      name: 'messages',
      query: {} 
    })
  }
  
  // Check if we should open a conversation with a specific user
  if (route.query.user) {
    console.log('Opening conversation with user:', route.query.user)
    await openConversationWithUser(route.query.user)
    
    // Clean up query parameters to avoid reopening on refresh
    router.replace({ 
      name: 'messages',
      query: {} 
    })
  }
})
</script>

