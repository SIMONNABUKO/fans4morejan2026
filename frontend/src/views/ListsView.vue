<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark">
    <!-- Modern Header -->
    <header class="sticky top-0 z-20 bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-lg border-b border-border-light dark:border-border-dark shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 py-3">
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
                My Lists
              </h1>
              <p class="text-text-light-secondary dark:text-text-dark-secondary text-sm mt-0.5">
                Organize and manage your user lists
              </p>
            </div>
          </div>
          
          <!-- Right Side: Create List Button -->
          <button 
            @click="showCreateListModal = true"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-light dark:bg-primary-dark text-white rounded-lg font-semibold hover:bg-primary-light/90 dark:hover:bg-primary-dark/90 transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark"
          >
            <div class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center">
              <i class="ri-add-line text-sm"></i>
            </div>
            <span class="text-sm">Create List</span>
          </button>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Stats Section -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm rounded-2xl p-6 border border-white/20 dark:border-gray-700/50 shadow-lg">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/20 rounded-full flex items-center justify-center">
              <i class="ri-list-check text-blue-600 dark:text-blue-400 text-xl"></i>
            </div>
            <div>
              <p class="text-2xl font-bold text-text-light-primary dark:text-text-dark-primary">
                {{ listStore.lists.length }}
              </p>
              <p class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Total Lists</p>
            </div>
          </div>
        </div>
        
        <div class="bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm rounded-2xl p-6 border border-white/20 dark:border-gray-700/50 shadow-lg">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-green-100 dark:bg-green-900/20 rounded-full flex items-center justify-center">
              <i class="ri-user-follow-fill text-green-600 dark:text-green-400 text-xl"></i>
            </div>
            <div>
              <p class="text-2xl font-bold text-text-light-primary dark:text-text-dark-primary">
                {{ totalMembers }}
              </p>
              <p class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Total Members</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Lists Section -->
      <div class="space-y-6">
        <!-- Loading State -->
        <div v-if="listStore.loading" class="text-center py-12">
          <div class="inline-flex items-center gap-3">
            <div class="w-6 h-6 border-2 border-primary-light dark:border-primary-dark border-t-transparent rounded-full animate-spin"></div>
            <span class="text-text-light-secondary dark:text-text-dark-secondary">Loading your lists...</span>
          </div>
        </div>

        <!-- Error State -->
        <div v-else-if="listStore.error" class="text-center py-12">
          <div class="max-w-md mx-auto">
            <div class="w-16 h-16 bg-red-100 dark:bg-red-900/20 rounded-full flex items-center justify-center mx-auto mb-4">
              <i class="ri-error-warning-line text-2xl text-red-500"></i>
            </div>
            <h3 class="text-lg font-semibold text-text-light-primary dark:text-text-dark-primary mb-2">
              Error Loading Lists
            </h3>
            <p class="text-text-light-secondary dark:text-text-dark-secondary mb-4">
              {{ listStore.error }}
            </p>
            <button 
              @click="refreshLists"
              class="px-4 py-2 bg-primary-light dark:bg-primary-dark text-white rounded-full hover:bg-primary-light/90 dark:hover:bg-primary-dark/90 transition-colors"
            >
              Try Again
            </button>
          </div>
        </div>

        <!-- Lists Grid -->
        <div v-else-if="listStore.lists.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div 
            v-for="list in listStore.lists"
            :key="list.id"
            class="group bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm rounded-2xl p-6 border border-white/20 dark:border-gray-700/50 shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 relative"
          >
            <!-- Delete Button (for non-default lists) -->
            <button
              v-if="!list.is_default"
              @click.stop="openDeleteModal(list)"
              class="absolute top-3 right-3 p-2 rounded-full bg-red-100 dark:bg-red-900/20 text-red-500 hover:bg-red-200 dark:hover:bg-red-900/40 transition-all duration-200 md:opacity-0 md:group-hover:opacity-100 hover:scale-110"
              title="Delete list"
            >
              <i class="ri-delete-bin-line text-sm"></i>
            </button>

            <!-- List Content -->
            <div @click="openList(list.id)" class="cursor-pointer">
              <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 bg-primary-light/20 dark:bg-primary-dark/20 rounded-full flex items-center justify-center">
                  <i class="ri-list-check text-primary-light dark:text-primary-dark text-xl"></i>
                </div>
                <div class="flex items-center gap-2">
                  <span v-if="list.is_default" class="px-2 py-1 bg-blue-100 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 text-xs rounded-full">
                    Default
                  </span>
                  <i class="ri-arrow-right-s-line text-xl text-text-light-secondary dark:text-text-dark-secondary group-hover:text-primary-light dark:group-hover:text-primary-dark transition-colors"></i>
                </div>
              </div>
              
              <h3 class="text-xl font-bold text-text-light-primary dark:text-text-dark-primary mb-2">
                {{ list.name }}
              </h3>
              
              <p v-if="list.description" class="text-text-light-secondary dark:text-text-dark-secondary text-sm mb-4 line-clamp-2">
                {{ list.description }}
              </p>
              
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <i class="ri-user-line text-text-light-secondary dark:text-text-dark-secondary"></i>
                  <span class="text-sm font-medium text-text-light-primary dark:text-text-dark-primary">
                    {{ list.count }} {{ list.count === 1 ? 'Member' : 'Members' }}
                  </span>
                </div>
                
                <div class="flex items-center gap-1">
                  <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                  <span class="text-xs text-text-light-secondary dark:text-text-dark-secondary">Active</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-16">
          <div class="max-w-md mx-auto">
            <div class="w-24 h-24 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-6">
              <i class="ri-list-check text-4xl text-gray-400 dark:text-gray-500"></i>
            </div>
            <h3 class="text-xl font-semibold text-text-light-primary dark:text-text-dark-primary mb-2">
              No Lists Yet
            </h3>
            <p class="text-text-light-secondary dark:text-text-dark-secondary mb-6">
              Create your first list to start organizing users and building meaningful connections.
            </p>
            <button 
              @click="showCreateListModal = true"
              class="inline-flex items-center gap-2 px-6 py-3 bg-primary-light dark:bg-primary-dark text-white rounded-full font-medium hover:bg-primary-light/90 dark:hover:bg-primary-dark/90 transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl"
            >
              <i class="ri-add-line text-lg"></i>
              <span>Create Your First List</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create List Modal -->
    <CreateListModal
      v-if="showCreateListModal"
      @close="showCreateListModal = false"
      @create="handleCreateList"
    />

    <!-- Delete List Modal -->
    <DeleteListModal
      v-if="showDeleteModal"
      :is-open="showDeleteModal"
      :list="listToDelete"
      @close="closeDeleteModal"
      @deleted="handleDeleteList"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useListStore } from '@/stores/listStore'
import { useToast } from 'vue-toastification'
import CreateListModal from '@/components/lists/CreateListModal.vue'
import DeleteListModal from '@/components/lists/DeleteListModal.vue'

const router = useRouter()
const listStore = useListStore()
const toast = useToast()
const showCreateListModal = ref(false)
const showDeleteModal = ref(false)
const listToDelete = ref(null)

// Computed properties
const totalMembers = computed(() => {
  // Get all unique user IDs across all lists
  const uniqueUserIds = new Set()
  
  listStore.lists.forEach(list => {
    // Get members for this list from the store
    const listMembers = listStore.getListMembers(list.id)
    if (listMembers && Array.isArray(listMembers)) {
      listMembers.forEach(member => {
        uniqueUserIds.add(member.id)
      })
    }
  })
  
  return uniqueUserIds.size
})

onMounted(async () => {
  try {
    await listStore.fetchLists()
    console.log('Lists loaded:', listStore.lists)
    
    // Fetch details for all lists to get accurate member counts
    await Promise.all(
      listStore.lists.map(list => listStore.getListDetails(list.id))
    )
  } catch (error) {
    console.error('Failed to fetch lists:', error)
    toast.error('Failed to load lists')
  }
})

const openList = (id) => {
  router.push(`/dashboard/lists/${id}`)
}

const handleCreateList = async (listData) => {
  try {
    await listStore.createList(listData)
    showCreateListModal.value = false
    toast.success('List created successfully!')
  } catch (error) {
    console.error('Failed to create list:', error)
    toast.error('Failed to create list')
  }
}

const refreshLists = async () => {
  try {
    await listStore.fetchLists()
    toast.success('Lists refreshed successfully!')
  } catch (error) {
    console.error('Failed to refresh lists:', error)
    toast.error('Failed to refresh lists')
  }
}

const openDeleteModal = (list) => {
  listToDelete.value = list
  showDeleteModal.value = true
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  listToDelete.value = null
}

const handleDeleteList = async () => {
  if (!listToDelete.value) return

  try {
    await listStore.deleteList(listToDelete.value.id)
    showDeleteModal.value = false
    listToDelete.value = null
    toast.success('List deleted successfully!')
  } catch (error) {
    console.error('Failed to delete list:', error)
    toast.error('Failed to delete list')
  }
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>

