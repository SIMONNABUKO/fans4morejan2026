<!-- Message Monitoring View -->
<template>
  <div class="p-6">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
      <div v-for="stat in stats" :key="stat.title" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ stat.title }}</p>
            <p class="text-2xl font-semibold mt-2" v-if="!loading.stats">{{ stat.value }}</p>
            <div v-else class="h-8 w-24 bg-gray-200 dark:bg-gray-700 animate-pulse rounded mt-2"></div>
          </div>
          <div :class="stat.iconClass">
            <component :is="stat.icon" class="w-8 h-8" />
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Message Type</label>
          <select v-model="filters.type" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
            <option value="">All Types</option>
            <option value="regular">Regular Messages</option>
            <option value="automated">Automated Messages</option>
            <option value="tip">Messages with Tips</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
          <select v-model="filters.status" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
            <option value="">All Status</option>
            <option value="read">Read</option>
            <option value="unread">Unread</option>
            <option value="reported">Reported</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date Range</label>
          <input type="date" v-model="filters.startDate" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 mb-2">
          <input type="date" v-model="filters.endDate" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
          <input type="text" v-model="filters.search" placeholder="Search messages..." class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
        </div>
      </div>
    </div>

    <!-- Messages Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
      <div class="p-6">
        <h2 class="text-lg font-semibold mb-4">Message History</h2>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Sender</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Receiver</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Content</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <template v-if="!loading.messages">
                <tr v-for="message in messages" :key="message.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                  <td class="px-6 py-4 whitespace-nowrap text-sm">{{ formatDate(message.created_at) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">{{ message.sender.username }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">{{ message.receiver.username }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <span :class="getMessageTypeClass(message.type)">{{ message.type }}</span>
                  </td>
                  <td class="px-6 py-4 text-sm">
                    <div class="max-w-xs truncate">{{ message.content }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <span :class="getStatusClass(message.status)">{{ message.status }}</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <button @click="viewMessageDetails(message)" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 mr-3">
                      View
                    </button>
                    <button v-if="message.status === 'reported'" @click="reviewMessage(message)" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                      Review
                    </button>
                  </td>
                </tr>
              </template>
              <tr v-else>
                <td colspan="7" class="px-6 py-4 text-center">
                  <div class="flex justify-center items-center space-x-4">
                    <div class="w-4 h-4 border-2 border-t-blue-500 rounded-full animate-spin"></div>
                    <div>Loading messages...</div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
        <div class="flex justify-between items-center">
          <div class="text-sm text-gray-500 dark:text-gray-400">
            Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} messages
          </div>
          <div class="flex space-x-2">
            <button
              @click="changePage(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
              class="px-3 py-1 rounded border border-gray-300 dark:border-gray-600 disabled:opacity-50"
            >
              Previous
            </button>
            <button
              @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.last_page"
              class="px-3 py-1 rounded border border-gray-300 dark:border-gray-600 disabled:opacity-50"
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Message Details Modal -->
    <div v-if="selectedMessage" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-gray-800 rounded-lg max-w-2xl w-full mx-4">
        <div class="p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Message Details</h3>
            <button @click="selectedMessage = null" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <div class="space-y-4">
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Sender</p>
              <p>{{ selectedMessage.sender.username }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Receiver</p>
              <p>{{ selectedMessage.receiver.username }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Date</p>
              <p>{{ formatDate(selectedMessage.created_at) }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Content</p>
              <p class="whitespace-pre-wrap">{{ selectedMessage.content }}</p>
            </div>
            <div v-if="selectedMessage.media && selectedMessage.media.length">
              <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Media</p>
              <div class="grid grid-cols-2 gap-4">
                <div v-for="media in selectedMessage.media" :key="media.id" class="relative">
                  <img v-if="media.type === 'image'" :src="media.url" class="rounded-lg w-full h-auto">
                  <video v-else-if="media.type === 'video'" :src="media.url" controls class="rounded-lg w-full h-auto"></video>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from '@/plugins/axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

// State
const loading = ref({
  stats: true,
  messages: true
})

const stats = ref([
  { title: 'Total Messages', value: 0, icon: 'ChatIcon', iconClass: 'text-blue-500' },
  { title: 'Messages Today', value: 0, icon: 'ClockIcon', iconClass: 'text-green-500' },
  { title: 'Unread Messages', value: 0, icon: 'MailIcon', iconClass: 'text-yellow-500' },
  { title: 'Reported Messages', value: 0, icon: 'FlagIcon', iconClass: 'text-red-500' }
])

const filters = ref({
  type: '',
  status: '',
  startDate: '',
  endDate: '',
  search: ''
})

const messages = ref([])
const selectedMessage = ref(null)
const pagination = ref({
  current_page: 1,
  from: 0,
  to: 0,
  total: 0,
  last_page: 1
})

// Methods
const fetchStats = async () => {
  try {
    loading.value.stats = true
    const response = await axios.get('/admin/messages/stats')
    const data = response.data

    stats.value = [
      { title: 'Total Messages', value: data.total_messages, icon: 'ChatIcon', iconClass: 'text-blue-500' },
      { title: 'Messages Today', value: data.messages_today, icon: 'ClockIcon', iconClass: 'text-green-500' },
      { title: 'Unread Messages', value: data.unread_messages, icon: 'MailIcon', iconClass: 'text-yellow-500' },
      { title: 'Reported Messages', value: data.reported_messages, icon: 'FlagIcon', iconClass: 'text-red-500' }
    ]
  } catch (error) {
    toast.error('Failed to fetch message statistics')
    console.error('Error fetching stats:', error)
  } finally {
    loading.value.stats = false
  }
}

const fetchMessages = async () => {
  try {
    loading.value.messages = true
    const response = await axios.get('/admin/messages', {
      params: {
        page: pagination.value.current_page,
        type: filters.value.type,
        status: filters.value.status,
        start_date: filters.value.startDate,
        end_date: filters.value.endDate,
        search: filters.value.search
      }
    })

    messages.value = response.data.data
    pagination.value = {
      current_page: response.data.current_page,
      from: response.data.from,
      to: response.data.to,
      total: response.data.total,
      last_page: response.data.last_page
    }
  } catch (error) {
    toast.error('Failed to fetch messages')
    console.error('Error fetching messages:', error)
  } finally {
    loading.value.messages = false
  }
}

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    pagination.value.current_page = page
    fetchMessages()
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleString()
}

const getMessageTypeClass = (type) => {
  const classes = {
    regular: 'text-blue-600 dark:text-blue-400',
    automated: 'text-green-600 dark:text-green-400',
    tip: 'text-purple-600 dark:text-purple-400'
  }
  return classes[type] || 'text-gray-600 dark:text-gray-400'
}

const getStatusClass = (status) => {
  const classes = {
    read: 'text-green-600 dark:text-green-400',
    unread: 'text-yellow-600 dark:text-yellow-400',
    reported: 'text-red-600 dark:text-red-400'
  }
  return classes[status] || 'text-gray-600 dark:text-gray-400'
}

const viewMessageDetails = (message) => {
  selectedMessage.value = message
}

const reviewMessage = async (message) => {
  try {
    await axios.post(`/admin/messages/${message.id}/review`)
    toast.success('Message review status updated')
    fetchMessages()
  } catch (error) {
    toast.error('Failed to update message review status')
    console.error('Error reviewing message:', error)
  }
}

// Watchers
watch(filters, () => {
  pagination.value.current_page = 1
  fetchMessages()
}, { deep: true })

// Lifecycle
onMounted(() => {
  fetchStats()
  fetchMessages()
})
</script> 