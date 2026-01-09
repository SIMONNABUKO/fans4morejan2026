<template>
  <div class="min-h-full">
    <div class="py-6">
      <div class="px-4 sm:px-6 lg:px-8">
        <!-- Back Button -->
        <div class="mb-4">
          <button
            @click="router.back()"
            class="flex items-center text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Users
          </button>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center py-12">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="bg-red-50 dark:bg-red-900/50 p-4 rounded-md">
          <p class="text-red-700 dark:text-red-200">{{ error }}</p>
        </div>

        <!-- User Details -->
        <div v-else-if="user" class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
          <!-- User Header -->
          <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center">
              <img
                :src="user.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}`"
                :alt="user.name"
                class="h-16 w-16 rounded-full"
              />
              <div class="ml-4">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ user.name }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">@{{ user.username }}</p>
              </div>
            </div>
          </div>

          <!-- User Statistics -->
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Statistics</h3>
            <dl class="grid grid-cols-1 gap-5 sm:grid-cols-4">
              <div class="bg-gray-50 dark:bg-gray-900/50 px-4 py-5 rounded-lg">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Posts</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">{{ user.posts_count || 0 }}</dd>
              </div>
              <div class="bg-gray-50 dark:bg-gray-900/50 px-4 py-5 rounded-lg">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Followers</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">{{ user.followers_count || 0 }}</dd>
              </div>
              <div class="bg-gray-50 dark:bg-gray-900/50 px-4 py-5 rounded-lg">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Following</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">{{ user.following_count || 0 }}</dd>
              </div>
              <div class="bg-gray-50 dark:bg-gray-900/50 px-4 py-5 rounded-lg">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Media Items</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">{{ user.media_count || 0 }}</dd>
              </div>
            </dl>
          </div>

          <!-- User Management -->
          <div class="px-4 py-5 sm:p-6 border-t border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Account Management</h3>
            
            <!-- Role Management -->
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Role</label>
              <div class="flex items-center space-x-4">
                <select
                  v-model="selectedRole"
                  class="mt-1 block w-48 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  :disabled="updating"
                >
                  <option value="user">User</option>
                  <option value="creator">Creator</option>
                  <option value="admin">Admin</option>
                </select>
                <button
                  @click="updateUserRole"
                  class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                  :disabled="updating || selectedRole === user.role"
                >
                  Update Role
                </button>
              </div>
            </div>

            <!-- Status Management -->
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
              <div class="flex items-center space-x-4">
                <select
                  v-model="selectedStatus"
                  class="mt-1 block w-48 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  :disabled="updating"
                >
                  <option value="active">Active</option>
                  <option value="suspended">Suspended</option>
                  <option value="banned">Banned</option>
                </select>
                <button
                  @click="updateUserStatus"
                  class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                  :disabled="updating || selectedStatus === user.status"
                >
                  Update Status
                </button>
              </div>
            </div>

            <!-- Security Information -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
              <div>
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Two-Factor Authentication</h4>
                <div class="flex items-center">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="{
                      'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300': user.two_factor_enabled,
                      'bg-gray-100 text-gray-800 dark:bg-gray-900/50 dark:text-gray-300': !user.two_factor_enabled
                    }"
                  >
                    {{ user.two_factor_enabled ? 'Enabled' : 'Disabled' }}
                  </span>
                </div>
              </div>

              <div>
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email Verification</h4>
                <div class="flex items-center">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="{
                      'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300': user.email_verified_at,
                      'bg-gray-100 text-gray-800 dark:bg-gray-900/50 dark:text-gray-300': !user.email_verified_at
                    }"
                  >
                    {{ user.email_verified_at ? 'Verified' : 'Not Verified' }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- User Settings -->
          <div class="mt-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">User Settings</h3>
            <UserSettings :userId="Number(route.params.id)" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from '../../plugins/axios'
import UserSettings from '@/components/users/UserSettings.vue'

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
  two_factor_enabled: boolean
  email_verified_at: string | null
  posts_count: number
  followers_count: number
  following_count: number
  media_count: number
}

const route = useRoute()
const router = useRouter()
const loading = ref(true)
const error = ref<string | null>(null)
const user = ref<User | null>(null)
const updating = ref(false)
const selectedRole = ref('')
const selectedStatus = ref('')

async function fetchUser() {
  loading.value = true
  error.value = null
  try {
    const response = await axios.get(`/admin/users/${route.params.id}`)
    console.log('User API Response:', response.data)
    const userData = response.data.data
    if (!userData) {
      throw new Error('Invalid user data received from server')
    }
    console.log('Processed User Data:', userData)
    user.value = {
      ...userData,
      posts_count: parseInt(userData.posts_count || 0),
      followers_count: parseInt(userData.followers_count || 0),
      following_count: parseInt(userData.following_count || 0),
      media_count: parseInt(userData.media_count || 0)
    }
    selectedRole.value = userData.role || 'user'
    selectedStatus.value = userData.status || 'active'
  } catch (err: any) {
    console.error('Failed to fetch user:', err)
    error.value = err.response?.data?.message || err.message || 'Failed to fetch user'
  } finally {
    loading.value = false
  }
}

async function updateUserRole() {
  if (!user.value || selectedRole.value === user.value.role) return

  updating.value = true
  try {
    await axios.patch(`/admin/users/${user.value.id}/role`, { role: selectedRole.value })
    await fetchUser()
  } catch (err: any) {
    console.error('Failed to update user role:', err)
    error.value = err.response?.data?.message || 'Failed to update user role'
  } finally {
    updating.value = false
  }
}

async function updateUserStatus() {
  if (!user.value || selectedStatus.value === user.value.status) return

  updating.value = true
  try {
    await axios.patch(`/admin/users/${user.value.id}/status`, { status: selectedStatus.value })
    await fetchUser()
  } catch (err: any) {
    console.error('Failed to update user status:', err)
    error.value = err.response?.data?.message || 'Failed to update user status'
  } finally {
    updating.value = false
  }
}

onMounted(() => {
  fetchUser()
})
</script> 