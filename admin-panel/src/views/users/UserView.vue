<template>
  <div class="min-h-full">
    <div class="py-6">
      <div class="px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">User Management</h1>
      </div>

      <!-- Filters -->
      <div class="mt-6 px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
          <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Search -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
              <input
                v-model="filters.search"
                type="text"
                placeholder="Search by name, email, or username"
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              />
            </div>

            <!-- Role Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
              <select
                v-model="filters.role"
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              >
                <option value="">All Roles</option>
                <option value="user">User</option>
                <option value="creator">Creator</option>
                <option value="admin">Admin</option>
              </select>
            </div>

            <!-- Status Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
              <select
                v-model="filters.status"
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              >
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="suspended">Suspended</option>
                <option value="banned">Banned</option>
              </select>
            </div>

            <div class="flex items-end">
              <button
                @click="applyFilters"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                Apply Filters
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- User List -->
      <div class="mt-6 px-4 sm:px-6 lg:px-8">
        <div v-if="loading" class="flex justify-center py-12">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
        </div>

        <div v-else-if="error" class="bg-red-50 dark:bg-red-900/50 p-4 rounded-md">
          <p class="text-red-700 dark:text-red-200">{{ error }}</p>
        </div>

        <div v-else class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
          <ResponsiveTable
            :columns="columns"
            :data="users"
            :loading="loading"
          >
            <template #empty>
              <div class="flex flex-col items-center py-12">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <p class="mt-4 text-gray-500 dark:text-gray-400">No users found</p>
              </div>
            </template>
          </ResponsiveTable>
        </div>

        <!-- Pagination -->
        <div v-if="totalItems > 0" class="mt-6 flex items-center justify-between">
          <div class="flex-1 flex justify-between sm:hidden">
            <button
              :disabled="currentPage === 1"
              @click="setPage(currentPage - 1)"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
            >
              Previous
            </button>
            <button
              :disabled="currentPage * perPage >= totalItems"
              @click="setPage(currentPage + 1)"
              class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
            >
              Next
            </button>
          </div>
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700 dark:text-gray-300">
                Showing
                <span class="font-medium">{{ (currentPage - 1) * perPage + 1 }}</span>
                to
                <span class="font-medium">
                  {{ Math.min(currentPage * perPage, totalItems) }}
                </span>
                of
                <span class="font-medium">{{ totalItems }}</span>
                results
              </p>
            </div>
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                <button
                  v-for="page in totalPages"
                  :key="page"
                  @click="setPage(page)"
                  :class="[
                    page === currentPage
                      ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                      : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                  ]"
                >
                  {{ page }}
                </button>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import axios from '../../plugins/axios'
import { useRouter } from 'vue-router'
import ResponsiveTable from '@/components/ui/ResponsiveTable.vue'
import { formatDate } from '@/utils/date'

interface User {
  id: number
  name: string
  email: string
  username: string
  avatar?: string
  role: 'user' | 'creator' | 'admin'
  status: 'active' | 'suspended' | 'banned'
  created_at: string
  updated_at: string
}

interface UserFilters {
  search?: string
  role?: string
  status?: string
}

const loading = ref(false)
const error = ref<string | null>(null)
const users = ref<User[]>([])
const totalItems = ref(0)
const currentPage = ref(1)
const perPage = ref(10)
const filters = ref<UserFilters>({
  search: '',
  role: '',
  status: ''
})

const totalPages = computed(() => Math.ceil(totalItems.value / perPage.value))

const router = useRouter()

// Define table columns with mobile priorities
const columns = [
  {
    key: 'name',
    label: 'Name',
    priority: 1 // Show first on mobile
  },
  {
    key: 'email',
    label: 'Email',
    priority: 2
  },
  {
    key: 'role',
    label: 'Role',
    priority: 3,
    formatter: (value: string) => value ? value.charAt(0).toUpperCase() + value.slice(1) : 'N/A'
  },
  {
    key: 'status',
    label: 'Status',
    priority: 4,
    formatter: (value: string) => value ? value.charAt(0).toUpperCase() + value.slice(1) : 'N/A'
  },
  {
    key: 'created_at',
    label: 'Joined',
    formatter: (value: string) => value ? formatDate(value) : 'N/A'
  }
]

async function fetchUsers() {
  loading.value = true
  error.value = null
  try {
    const response = await axios.get('/admin/users', {
      params: {
        page: currentPage.value,
        limit: perPage.value,
        ...filters.value
      }
    })
    users.value = response.data.data
    totalItems.value = response.data.total || response.data.meta?.total || 0
  } catch (err: any) {
    console.error('Failed to fetch users:', err)
    error.value = err.response?.data?.message || 'Failed to fetch users'
  } finally {
    loading.value = false
  }
}

async function updateUserStatus(user: User) {
  const newStatus = prompt('Enter new status (active, suspended, banned):', user.status)
  if (!newStatus || !['active', 'suspended', 'banned'].includes(newStatus)) {
    return
  }

  try {
    await axios.patch(`/admin/users/${user.id}/status`, { status: newStatus })
    await fetchUsers()
  } catch (err: any) {
    console.error('Failed to update user status:', err)
    error.value = err.response?.data?.message || 'Failed to update user status'
  }
}

function viewUser(user: User) {
  router.push({ name: 'user-details', params: { id: user.id.toString() } })
}

function setPage(page: number) {
  currentPage.value = page
  fetchUsers()
}

function applyFilters() {
  currentPage.value = 1
  fetchUsers()
}

onMounted(() => {
  fetchUsers();

  console.log(users.value);
})
</script> 