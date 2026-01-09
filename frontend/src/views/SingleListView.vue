<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark">
    <!-- Modern Header -->
    <header class="sticky top-0 z-20 bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-lg border-b border-border-light dark:border-border-dark shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 py-3">
          <!-- Left Side: Navigation and Title -->
          <div class="flex items-center gap-4">
            <button 
              @click="goBack"
              class="p-2 rounded-lg bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/20 dark:border-gray-700/50 text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary hover:bg-white/70 dark:hover:bg-gray-800/70 transition-all duration-200 hover:scale-105 shadow-sm hover:shadow-md"
            >
              <i class="ri-arrow-left-line text-lg"></i>
            </button>
            
            <div class="flex flex-col">
              <h1 class="text-2xl font-bold text-text-light-primary dark:text-text-dark-primary leading-tight">
                {{ list.name }}
              </h1>
              <p class="text-text-light-secondary dark:text-text-dark-secondary text-sm mt-0.5">
                {{ members.length }} {{ members.length === 1 ? 'Member' : 'Members' }}
              </p>
            </div>
          </div>
          
          <!-- Right Side: Add Member Button -->
          <button 
            v-if="canAddMembers"
            @click="openAddMemberModal"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-light dark:bg-primary-dark text-white rounded-lg font-semibold hover:bg-primary-light/90 dark:hover:bg-primary-dark/90 transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark"
          >
            <div class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center">
              <i class="ri-user-add-line text-sm"></i>
            </div>
            <span class="text-sm">Add Member</span>
          </button>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-flex items-center gap-3">
          <div class="w-6 h-6 border-2 border-primary-light dark:border-primary-dark border-t-transparent rounded-full animate-spin"></div>
          <span class="text-text-light-secondary dark:text-text-dark-secondary">Loading members...</span>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="text-center py-12">
        <div class="max-w-md mx-auto">
          <div class="w-16 h-16 bg-red-100 dark:bg-red-900/20 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="ri-error-warning-line text-2xl text-red-500"></i>
          </div>
          <h3 class="text-lg font-semibold text-text-light-primary dark:text-text-dark-primary mb-2">
            Error Loading Members
          </h3>
          <p class="text-text-light-secondary dark:text-text-dark-secondary mb-4">
            {{ error }}
          </p>
          <button 
            @click="fetchListData"
            class="px-4 py-2 bg-primary-light dark:bg-primary-dark text-white rounded-full hover:bg-primary-light/90 dark:hover:bg-primary-dark/90 transition-colors"
          >
            Try Again
          </button>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="members.length === 0" class="text-center py-16">
        <div class="max-w-md mx-auto">
          <div class="w-24 h-24 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="ri-user-line text-4xl text-gray-400 dark:text-gray-500"></i>
          </div>
          <h3 class="text-xl font-semibold text-text-light-primary dark:text-text-dark-primary mb-2">
            No Members Yet
          </h3>
          <p class="text-text-light-secondary dark:text-text-dark-secondary mb-6">
            {{ canAddMembers ? 'Start adding members to your list to organize your connections.' : 'This list will be populated automatically.' }}
          </p>
          <button 
            v-if="canAddMembers"
            @click="openAddMemberModal"
            class="inline-flex items-center gap-2 px-6 py-3 bg-primary-light dark:bg-primary-dark text-white rounded-full font-medium hover:bg-primary-light/90 dark:hover:bg-primary-dark/90 transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl"
          >
            <i class="ri-user-add-line text-lg"></i>
            <span>Add Your First Member</span>
          </button>
        </div>
      </div>

      <!-- Members Grid -->
      <div v-else class="space-y-6">
        <!-- Search and Filter Bar -->
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-4">
            <div class="relative">
              <i class="ri-search-line absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-lg"></i>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search members..."
                class="pl-10 pr-4 py-2 rounded-lg border border-white/20 dark:border-gray-700/50 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:bg-white/80 dark:focus:bg-gray-800/80 transition-all duration-200"
              />
            </div>
          </div>
          
          <div class="flex items-center gap-2 text-sm text-text-light-secondary dark:text-text-dark-secondary">
            <i class="ri-user-line"></i>
            <span>{{ filteredMembers.length }} of {{ members.length }} members</span>
          </div>
        </div>

        <!-- Members List -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div 
            v-for="member in filteredMembers" 
            :key="member.id" 
            class="group bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm rounded-2xl p-4 border border-white/20 dark:border-gray-700/50 shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105"
          >
            <div class="flex items-start justify-between mb-3">
              <div class="flex items-center gap-3">
                <div class="relative">
                  <img 
                    :src="member.avatar || '/default-avatar.png'" 
                    :alt="member.name"
                    class="w-12 h-12 rounded-full object-cover ring-2 ring-white/20 dark:ring-gray-700/30"
                  />
                  <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white dark:border-gray-900"></div>
                </div>
                <div class="flex flex-col">
                  <button 
                    @click.stop="viewProfile(member.username)"
                    class="font-semibold text-text-light-primary dark:text-text-dark-primary hover:text-primary-light dark:hover:text-primary-dark transition-colors text-left cursor-pointer"
                  >
                    {{ member.name }}
                  </button>
                  <button 
                    @click.stop="viewProfile(member.username)"
                    class="text-sm text-text-light-secondary dark:text-text-dark-secondary hover:text-primary-light dark:hover:text-primary-dark transition-colors text-left cursor-pointer"
                  >
                    @{{ member.username }}
                  </button>
                </div>
              </div>
              
              <!-- Remove Button -->
              <button
                v-if="canRemoveMembers"
                @click="removeMember(member.id)"
                class="p-2 rounded-full bg-red-100 dark:bg-red-900/20 text-red-500 hover:bg-red-200 dark:hover:bg-red-900/40 transition-all duration-200 opacity-0 group-hover:opacity-100 hover:scale-110"
                title="Remove member"
              >
                <i class="ri-delete-bin-line text-sm"></i>
              </button>
            </div>
            
            <!-- Member Actions -->
            <div class="flex items-center gap-2">
              <button
                v-if="isMutedAccountsList"
                @click="removeMember(member.id)"
                class="flex-1 py-2 px-3 rounded-lg bg-green-100 dark:bg-green-900/20 text-green-600 dark:text-green-400 hover:bg-green-200 dark:hover:bg-green-900/40 transition-colors text-sm font-medium"
              >
                <i class="ri-volume-up-line mr-1"></i>
                Unmute
              </button>
              <button
                v-else-if="isBlockedAccountsList"
                @click="removeMember(member.id)"
                class="flex-1 py-2 px-3 rounded-lg bg-red-100 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/40 transition-colors text-sm font-medium"
              >
                <i class="ri-user-unfollow-line mr-1"></i>
                Unblock
              </button>
              <button
                v-else-if="isFollowersList"
                @click="toggleFollow(member)"
                class="flex-1 py-2 px-3 rounded-lg bg-primary-light/10 dark:bg-primary-dark/10 text-primary-light dark:text-primary-dark hover:bg-primary-light/20 dark:hover:bg-primary-dark/20 transition-colors text-sm font-medium"
                :disabled="followInProgress[member.id]"
              >
                <i class="ri-user-follow-line mr-1"></i>
                {{ member.is_following ? 'Unfollow' : 'Follow Back' }}
              </button>
              <button
                v-else
                @click="viewProfile(member.username)"
                class="flex-1 py-2 px-3 rounded-lg bg-primary-light/10 dark:bg-primary-dark/10 text-primary-light dark:text-primary-dark hover:bg-primary-light/20 dark:hover:bg-primary-dark/20 transition-colors text-sm font-medium"
              >
                <i class="ri-user-line mr-1"></i>
                View Profile
              </button>
              <button
                @click="sendMessage(member.username)"
                class="flex-1 py-2 px-3 rounded-lg bg-blue-100 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 hover:bg-blue-200 dark:hover:bg-blue-900/40 transition-colors text-sm font-medium"
              >
                <i class="ri-message-2-line mr-1"></i>
                Message
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Member Modal -->
    <AddMemberModal
      v-if="showAddMemberModal"
      :listId="listId"
      :listName="list.name"
      @member-added="onMemberAdded"
      @close="closeAddMemberModal"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useListStore } from '@/stores/listStore'
import axiosInstance from '@/axios'
import { useToast } from 'vue-toastification'
import AddMemberModal from '@/components/lists/AddMemberModal.vue'

const route = useRoute()
const router = useRouter()
const listStore = useListStore()
const toast = useToast()
const listId = ref(route.params.id)
const showAddMemberModal = ref(false)
const searchQuery = ref('')

const list = computed(() => listStore.getListById(listId.value) || {})
const members = computed(() => listStore.getListMembers(listId.value))
const loading = computed(() => listStore.loading)
const error = computed(() => listStore.error)
const canRemoveMembers = computed(() => !['Followers', 'Following', 'Subscribed', 'Blocked Accounts', 'Muted Accounts'].includes(list.value.name))
const canAddMembers = computed(() => !['Followers', 'Following', 'Subscribed'].includes(list.value.name))
const isMutedAccountsList = computed(() => list.value.name === 'Muted Accounts' || listId.value == 5)
const isBlockedAccountsList = computed(() => list.value.name === 'Blocked Accounts' || listId.value == 1)
const isFollowersList = computed(() => list.value.name === 'Followers')
const followInProgress = ref({})

// Filtered members based on search
const filteredMembers = computed(() => {
  if (!searchQuery.value.trim()) {
    return members.value
  }
  
  const query = searchQuery.value.toLowerCase()
  return members.value.filter(member => 
    member.name.toLowerCase().includes(query) ||
    member.username.toLowerCase().includes(query)
  )
})

const fetchListData = async () => {
  try {
    await listStore.getListDetails(listId.value)
  } catch (err) {
    console.error('Failed to fetch list data:', err)
  }
}

const openAddMemberModal = () => {
  showAddMemberModal.value = true
}

const closeAddMemberModal = () => {
  showAddMemberModal.value = false
}

const onMemberAdded = async () => {
  await fetchListData()
  closeAddMemberModal()
  toast.success('Member added successfully!')
}

const removeMember = async (memberId) => {
  try {
    await listStore.removeMemberFromList(listId.value, memberId)
    await fetchListData()
    const message = isMutedAccountsList.value
      ? 'User unmuted successfully!'
      : isBlockedAccountsList.value
        ? 'User unblocked successfully!'
        : 'Member removed successfully!'
    toast.success(message)
  } catch (err) {
    console.error('Failed to remove member:', err)
    toast.error(err.message || 'Failed to remove member')
  }
}

const toggleFollow = async (member) => {
  if (followInProgress.value[member.id]) {
    return
  }

  followInProgress.value[member.id] = true
  try {
    const endpoint = member.is_following
      ? `/users/${member.id}/unfollow`
      : `/users/${member.id}/follow`
    const response = await axiosInstance.post(endpoint)
    if (response.data?.success) {
      member.is_following = !member.is_following
      toast.success(member.is_following ? 'Followed successfully!' : 'Unfollowed successfully!')
    } else {
      throw new Error(response.data?.error || 'Failed to update follow status')
    }
  } catch (err) {
    console.error('Failed to update follow status:', err)
    toast.error(err.response?.data?.error || err.message || 'Failed to update follow status')
  } finally {
    followInProgress.value[member.id] = false
  }
}

const viewProfile = (username) => {
  router.push(`/${username}/posts`)
}

const sendMessage = (username) => {
  router.push({
    name: 'messages',
    query: { user: username }
  })
}

const goBack = () => {
  router.back()
}

onMounted(fetchListData)
</script>
