<script setup lang="ts">
import { ref, computed } from 'vue'

interface User {
  id: number
  name: string
  email: string
  role: string
  created_at: string
}

interface Props {
  users: User[]
  loading: boolean
}

const props = defineProps<Props>()
const emit = defineEmits<{
  (e: 'edit', user: User): void
  (e: 'delete', id: number): void
}>()

const searchQuery = ref('')
const selectedRole = ref('all')

const roles = [
  { value: 'all', label: 'All Roles' },
  { value: 'admin', label: 'Administrator' },
  { value: 'moderator', label: 'Moderator' },
  { value: 'user', label: 'User' }
]

const filteredUsers = computed(() => {
  let filtered = props.users

  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(user => 
      user.name.toLowerCase().includes(query) ||
      user.email.toLowerCase().includes(query)
    )
  }

  // Filter by role
  if (selectedRole.value !== 'all') {
    filtered = filtered.filter(user => user.role === selectedRole.value)
  }

  return filtered
})

const handleDelete = (id: number) => {
  if (confirm('Are you sure you want to delete this user?')) {
    emit('delete', id)
  }
}
</script>

<template>
  <!-- Add w-full to ensure the component takes full width -->
  <div class="w-full space-y-4">
    <!-- Filters -->
    <div class="flex flex-col sm:flex-row gap-4">
      <div class="flex-1">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search users..."
          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
        />
      </div>
      <div class="w-full sm:w-48">
        <select
          v-model="selectedRole"
          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
        >
          <option v-for="role in roles" :key="role.value" :value="role.value">
            {{ role.label }}
          </option>
        </select>
      </div>
    </div>

    <!-- Table - Add w-full to ensure the table container takes full width -->
    <div class="w-full overflow-x-auto shadow ring-1 ring-black ring-opacity-5 rounded-lg">
      <table class="w-full divide-y divide-gray-300 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-800">
          <tr>
            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-white sm:pl-6">Name</th>
            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Email</th>
            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Role</th>
            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Created</th>
            <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
              <span class="sr-only">Actions</span>
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
          <tr v-if="loading" class="animate-pulse">
            <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
              Loading users...
            </td>
          </tr>
          <tr v-else-if="filteredUsers.length === 0" class="hover:bg-gray-50 dark:hover:bg-gray-800">
            <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
              No users found
            </td>
          </tr>
          <tr
            v-for="user in filteredUsers"
            :key="user.id"
            class="hover:bg-gray-50 dark:hover:bg-gray-800"
          >
            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-white sm:pl-6">
              {{ user.name }}
            </td>
            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
              {{ user.email }}
            </td>
            <td class="whitespace-nowrap px-3 py-4 text-sm">
              <span
                class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                :class="{
                  'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200': user.role === 'admin',
                  'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': user.role === 'moderator',
                  'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': user.role === 'user'
                }"
              >
                {{ user.role }}
              </span>
            </td>
            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
              {{ new Date(user.created_at).toLocaleDateString() }}
            </td>
            <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
              <button
                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3"
                @click="emit('edit', user)"
              >
                Edit
              </button>
              <button
                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                @click="handleDelete(user.id)"
              >
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>