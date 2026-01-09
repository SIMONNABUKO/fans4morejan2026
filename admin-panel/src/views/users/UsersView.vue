<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useUserStore } from '@/stores/user'
import { useRouter } from 'vue-router'
import axios from '@/plugins/axios'
import { PlusIcon, MagnifyingGlassIcon, EyeIcon, Cog6ToothIcon } from '@heroicons/vue/24/outline'
import Modal from '@/components/common/Modal.vue'
import UserForm from '@/components/users/UserForm.vue'
import { format } from 'date-fns'
import type { User } from '@/types/store'

const router = useRouter()
const userStore = useUserStore()
const showCreateModal = ref(false)
const showEditModal = ref(false)
const isLoading = ref(false)
const selectedUser = ref<User | null>(null)
const searchQuery = ref('')
const selectedRole = ref('')
const selectedStatus = ref('')
const users = ref<User[]>([])
const totalUsers = ref(0)
const currentPage = ref(1)
const perPage = ref(10)
const error = ref(null)

const totalPages = computed(() => Math.ceil(totalUsers.value / perPage.value))

const filteredUsers = computed(() => {
  let users = userStore.users

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    users = users.filter(user => 
      user.name.toLowerCase().includes(query) ||
      user.email.toLowerCase().includes(query)
    )
  }

  if (selectedRole.value) {
    users = users.filter(user => user.role === selectedRole.value)
  }

  if (selectedStatus.value) {
    users = users.filter(user => user.status === selectedStatus.value)
  }

  return users
})

onMounted(async () => {
  await userStore.fetchUsers()
  users.value = userStore.users
  totalUsers.value = userStore.users.length
})

const handleCreateUser = async () => {
  showCreateModal.value = false
  await userStore.fetchUsers()
}

const handleEditUser = (user: User) => {
  selectedUser.value = user
  showEditModal.value = true
}

const handleUpdateUser = async () => {
  showEditModal.value = false
  selectedUser.value = null
  await userStore.fetchUsers()
}

const handleDeleteUser = async (id: number) => {
  try {
    await userStore.deleteUser(id)
  } catch (error) {
    console.error('Failed to delete user:', error)
  }
}

const formatDate = (date: string) => {
  return format(new Date(date), 'MMM d, yyyy')
}

const viewUser = (user: User) => {
  router.push({ name: 'user-details', params: { id: user.id.toString() } })
}

const handleSettings = (user: User) => {
  router.push({ name: 'user-settings', params: { id: user.id.toString() } })
}

const applyFilters = () => {
  currentPage.value = 1
  fetchUsers()
}

const fetchUsers = async () => {
  isLoading.value = true
  error.value = null
  try {
    const response = await axios.get('/admin/users', {
      params: {
        search: searchQuery.value || undefined,
        role: selectedRole.value || undefined,
        status: selectedStatus.value || undefined
      }
    })
    users.value = response.data.data
    totalUsers.value = response.data.total || response.data.meta?.total || 0
  } catch (err: any) {
    console.error('Failed to fetch users:', err)
    error.value = err.response?.data?.message || 'Failed to fetch users'
  } finally {
    isLoading.value = false
  }
}

async function updateUserStatus(user: any) {
  const newStatus = prompt('Enter new status (active, suspended, banned):', user.status)
  if (!newStatus || !['active', 'suspended', 'banned'].includes(newStatus)) return
  
  try {
    await axios.patch(`/admin/users/${user.id}/status`, { status: newStatus })
    await fetchUsers()
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to update user status'
  }
}

function setPage(page: number) {
  currentPage.value = page
  fetchUsers()
}

const getRoleBadgeClasses = (role: string) => {
  switch (role) {
    case 'admin':
      return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'
    case 'creator':
      return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
  }
}

const getStatusIndicator = (user: any) => {
  if (user.is_banned) return { text: 'Banned', class: 'bg-red-100 text-red-800' }
  if (user.is_suspended) return { text: 'Suspended', class: 'bg-yellow-100 text-yellow-800' }
  return { text: 'Active', class: 'bg-green-100 text-green-800' }
}
</script>

<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">User Management</h1>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
      <div class="flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search by name, email, or username"
            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
          />
        </div>
        <div class="flex gap-4">
          <select
            v-model="selectedRole"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
          >
            <option value="">All Roles</option>
            <option value="admin">Admin</option>
            <option value="creator">Creator</option>
            <option value="user">User</option>
          </select>
          <select
            v-model="selectedStatus"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
          >
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="suspended">Suspended</option>
            <option value="banned">Banned</option>
          </select>
          <button
            @click="fetchUsers"
            class="px-6 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            Apply Filters
          </button>
        </div>
      </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead>
            <tr class="bg-gray-50 dark:bg-gray-700">
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Stats</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Joined</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="h-10 w-10 flex-shrink-0">
                    <img
                      class="h-10 w-10 rounded-full"
                      :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}`"
                      :alt="user.name"
                    />
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ user.name }}
                      <span v-if="user.display_name" class="text-gray-500 dark:text-gray-400 text-xs ml-1">
                        ({{ user.display_name }})
                      </span>
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</div>
                    <div class="text-xs text-gray-400 dark:text-gray-500">{{ user.handle }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="[
                    'inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium',
                    getRoleBadgeClasses(user.role)
                  ]"
                >
                  {{ user.role }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="[
                    'inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium',
                    getStatusIndicator(user).class
                  ]"
                >
                  {{ getStatusIndicator(user).text }}
                </span>
                <div v-if="user.has_2fa" class="mt-1">
                  <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    2FA Enabled
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                  <div>Followers: {{ user.followers_count }}</div>
                  <div>Media: {{ user.total_image_uploads + user.total_video_uploads }}</div>
                  <div>Likes: {{ user.total_likes_received }}</div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                {{ formatDate(user.created_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                <button
                  @click="viewUser(user)"
                  class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                  title="View User Details"
                >
                  <EyeIcon class="h-5 w-5" />
                </button>
                <button
                  @click="handleSettings(user)"
                  class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300"
                  title="User Settings"
                >
                  <Cog6ToothIcon class="h-5 w-5" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center items-center mt-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
    </div>

    <!-- Error State -->
    <div v-if="error" class="mt-4 p-4 bg-red-100 text-red-700 rounded-lg">
      {{ error }}
    </div>
  </div>
</template>

<style scoped>
@media (max-width: 1024px) {
  .overflow-x-auto {
    -webkit-overflow-scrolling: touch;
  }
  
  table {
    display: block;
    width: 100%;
  }
}

@media (max-width: 768px) {
  th:nth-child(3),
  th:nth-child(4),
  td:nth-child(3),
  td:nth-child(4) {
    display: none;
  }
}

@media (max-width: 640px) {
  th:nth-child(2),
  td:nth-child(2),
  th:nth-child(5),
  td:nth-child(5) {
    display: none;
  }
  
  .flex-col {
    gap: 1rem;
  }
}
</style>