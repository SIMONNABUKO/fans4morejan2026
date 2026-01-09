<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark text-text-light-primary dark:text-text-dark-primary">
    <div class="absolute inset-x-0 top-0 h-64 bg-gradient-to-br from-blue-50 via-cyan-50 to-white dark:from-gray-900 dark:via-gray-900 dark:to-gray-900"></div>
    <div class="absolute inset-x-0 top-0 h-64 opacity-50 bg-[radial-gradient(circle_at_20%_20%,rgba(59,130,246,0.25),transparent_45%),radial-gradient(circle_at_80%_30%,rgba(14,165,233,0.25),transparent_45%)]"></div>

    <!-- Header -->
    <header class="sticky top-0 z-10 bg-background-light/80 dark:bg-background-dark/80 backdrop-blur border-b border-border-light dark:border-border-dark">
      <div class="px-4 py-4 flex items-center gap-3">
        <router-link 
          to="/settings" 
          class="p-2 -ml-2 rounded-full text-text-light-secondary dark:text-text-dark-secondary hover:bg-surface-light dark:hover:bg-surface-dark"
        >
          <i class="ri-arrow-left-line text-xl"></i>
        </router-link>
        <div class="flex flex-col">
          <h1 class="text-xl font-semibold tracking-tight">
            {{ t('messages_settings') }}
          </h1>
          <span class="text-xs text-text-light-secondary dark:text-text-dark-secondary">
            Manage DMs, automation, and broadcast insights
          </span>
        </div>
      </div>
    </header>

    <!-- Tabs -->
    <div class="px-4 pt-6">
      <div class="bg-white/70 dark:bg-gray-900/60 backdrop-blur rounded-2xl border border-border-light dark:border-border-dark shadow-sm p-1 overflow-x-auto">
        <nav class="flex items-center gap-2 min-w-max">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              activeTab === tab.id
                ? 'bg-gradient-to-r from-blue-600 to-cyan-500 text-white shadow-md'
                : 'text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary',
              'whitespace-nowrap py-2.5 px-3 rounded-xl font-semibold text-xs uppercase tracking-wide transition-all shrink-0'
            ]"
          >
            {{ tab.name }}
          </button>
        </nav>
      </div>
    </div>

    <!-- Content -->
    <div class="p-4 relative z-10">
      <!-- DM Permissions Tab -->
      <div v-if="activeTab === 'dm_permissions'" class="space-y-6">
        <div v-if="isLoading" class="flex justify-center py-8">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-light dark:border-primary-dark"></div>
        </div>

        <div v-else-if="error" class="p-4 bg-red-900/20 text-red-400 rounded-lg">
          {{ error }}
          <button 
            @click="fetchSettings" 
            class="ml-2 underline hover:no-underline"
          >
            Try again
          </button>
        </div>

        <template v-else>
          <div class="text-sm text-text-light-secondary dark:text-text-dark-secondary mb-6">
            {{ t('configure_messaging_preferences') }}
          </div>

          <div class="bg-white/80 dark:bg-gray-900/70 backdrop-blur rounded-2xl border border-border-light dark:border-border-dark shadow-lg p-4">
            <MessagingSettings @settings-saved="showSuccessToast = true" />
          </div>
        </template>
      </div>

      <!-- Automated Messages Tab -->
      <div v-if="activeTab === 'automated_messages'" class="space-y-6">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-lg font-semibold">Automation Library</h2>
            <p class="text-xs text-text-light-secondary dark:text-text-dark-secondary">
              Triggered messages for new followers and events
            </p>
          </div>
          <button
            @click="showAutomatedMessageForm = true"
            class="px-4 py-2.5 bg-gradient-to-r from-blue-600 to-cyan-500 text-white rounded-xl hover:shadow-lg flex items-center gap-2 transition-all"
          >
            <i class="ri-add-line"></i>
            {{ t('add_new_message') }}
          </button>
        </div>

        <div v-if="loading" class="flex justify-center py-8">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-light dark:border-primary-dark"></div>
        </div>

        <div v-else class="space-y-4">
          <div v-for="message in store.automatedMessages" :key="message.id" 
               class="bg-white/80 dark:bg-gray-900/70 backdrop-blur rounded-2xl p-4 border border-border-light dark:border-border-dark shadow-lg">
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center space-x-2">
                <span class="px-2 py-1 text-xs rounded-full" :class="{
                  'bg-green-100 text-green-800': message.is_active,
                  'bg-gray-700 text-gray-300': !message.is_active
                }">
                  {{ message.is_active ? t('active') : t('inactive') }}
                </span>
                <span class="text-sm font-medium">{{ message.trigger }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <button
                  @click="editMessage(message)"
                  class="p-2 text-text-light-secondary dark:text-text-dark-secondary hover:text-primary-light dark:hover:text-primary-dark"
                >
                  <i class="ri-pencil-line"></i>
                </button>
                <button
                  @click="toggleMessageStatus(message)"
                  class="p-2 text-text-light-secondary dark:text-text-dark-secondary hover:text-primary-light dark:hover:text-primary-dark"
                >
                  <i class="ri-shut-down-line" :class="{ 'text-primary-light dark:text-primary-dark': message.is_active }"></i>
                </button>
                <button
                  @click="confirmDeleteMessage(message)"
                  class="p-2 text-text-light-secondary dark:text-text-dark-secondary hover:text-accent-danger"
                >
                  <i class="ri-delete-bin-line"></i>
                </button>
              </div>
            </div>
            <p class="text-sm text-text-light-primary dark:text-text-dark-primary">{{ message.content }}</p>
            <div class="mt-2 flex items-center space-x-4 text-xs text-text-light-secondary dark:text-text-dark-secondary">
              <span class="flex items-center">
                <i class="ri-time-line mr-1"></i>
                {{ t('delay') }}: {{ message.sent_delay }}s
              </span>
              <span class="flex items-center">
                <i class="ri-timer-line mr-1"></i>
                {{ t('cooldown') }}: {{ message.cooldown }}s
              </span>
            </div>
            <div v-if="message.media?.length" class="mt-2">
              <div class="flex space-x-2">
                <div
                  v-for="media in message.media"
                  :key="media.id"
                  class="relative w-16 h-16 rounded-lg overflow-hidden"
                >
                  <img
                    :src="media.full_url"
                    :alt="media.type"
                    class="w-full h-full object-cover"
                  >
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- No messages state -->
        <div
          v-if="!loading && store.automatedMessages.length === 0"
          class="text-center py-12 bg-white/80 dark:bg-gray-900/70 backdrop-blur rounded-2xl border border-border-light dark:border-border-dark shadow-lg"
        >
          <div class="flex justify-center mb-4">
            <i class="ri-message-2-line text-5xl text-text-light-secondary dark:text-text-dark-secondary"></i>
          </div>
          <h3 class="text-lg font-medium mb-2">{{ t('no_automated_messages') }}</h3>
          <p class="text-sm text-text-light-secondary dark:text-text-dark-secondary mb-4">
            {{ t('create_first_automated_message') }}
          </p>
          <button
            @click="showAutomatedMessageForm = true"
            class="px-4 py-2.5 bg-gradient-to-r from-blue-600 to-cyan-500 text-white rounded-xl hover:shadow-lg flex items-center gap-2 mx-auto transition-all"
          >
            <i class="ri-add-line"></i>
            {{ t('add_new_message') }}
          </button>
        </div>
      </div>

      <!-- Broadcast Message Stats Tab -->
      <div v-if="activeTab === 'broadcast_stats'" class="space-y-6">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-lg font-semibold">Broadcast Performance</h2>
            <p class="text-xs text-text-light-secondary dark:text-text-dark-secondary">
              Track delivery and engagement across mass message campaigns
            </p>
          </div>
          <div class="flex items-center gap-2">
            <button
              @click="refreshBroadcastStats"
              class="px-3 py-2 text-xs rounded-lg border border-border-light dark:border-border-dark text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary"
            >
              Refresh
            </button>
            <router-link
              to="/messages/compose"
              class="px-3 py-2 text-xs rounded-lg bg-gradient-to-r from-blue-600 to-cyan-500 text-white"
            >
              New Broadcast
            </router-link>
          </div>
        </div>

        <div v-if="broadcastLoading" class="flex justify-center py-10">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-light dark:border-primary-dark"></div>
        </div>

        <div v-else-if="broadcastError" class="p-4 bg-red-900/20 text-red-400 rounded-lg">
          {{ broadcastError }}
          <button
            @click="refreshBroadcastStats"
            class="ml-2 underline hover:no-underline"
          >
            Try again
          </button>
        </div>

        <div v-else-if="campaigns.length === 0" class="text-center py-12 bg-white/80 dark:bg-gray-900/70 backdrop-blur rounded-2xl border border-border-light dark:border-border-dark shadow-lg">
          <div class="flex justify-center mb-4">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-600 to-cyan-500 text-white flex items-center justify-center shadow-lg">
              <i class="ri-bar-chart-2-line text-3xl"></i>
            </div>
          </div>
          <h3 class="text-lg font-semibold mb-2">No broadcast campaigns yet</h3>
          <p class="text-sm text-text-light-secondary dark:text-text-dark-secondary">
            Create your first broadcast and stats will appear here.
          </p>
          <router-link
            to="/messages/compose"
            class="inline-flex mt-6 px-4 py-2.5 bg-gradient-to-r from-blue-600 to-cyan-500 text-white rounded-xl hover:shadow-lg"
          >
            Create Broadcast
          </router-link>
        </div>

        <div v-else class="grid gap-6 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,1fr)]">
          <div class="space-y-3">
            <div
              v-for="campaign in campaigns"
              :key="campaign.id"
              @click="selectCampaign(campaign.id)"
              class="cursor-pointer rounded-2xl border p-4 transition-all"
              :class="campaign.id === selectedCampaignId
                ? 'bg-white/90 dark:bg-gray-900/80 border-blue-500 shadow-lg'
                : 'bg-white/70 dark:bg-gray-900/60 border-border-light dark:border-border-dark hover:shadow-md'"
            >
              <div class="flex items-start justify-between gap-3">
                <div>
                  <div class="text-sm font-semibold">
                    {{ campaign.subject || 'Untitled broadcast' }}
                  </div>
                  <div class="text-xs text-text-light-secondary dark:text-text-dark-secondary mt-1">
                    {{ campaign.content_preview || 'No preview available' }}
                  </div>
                </div>
                <span
                  class="text-[10px] uppercase tracking-wide px-2 py-1 rounded-full"
                  :class="campaign.status === 'sent'
                    ? 'bg-green-100 text-green-700'
                    : campaign.status === 'sending'
                      ? 'bg-blue-100 text-blue-700'
                      : campaign.status === 'failed'
                        ? 'bg-red-100 text-red-700'
                        : 'bg-gray-200 text-gray-700'"
                >
                  {{ campaign.status }}
                </span>
              </div>

              <div class="mt-3 grid grid-cols-3 gap-3 text-xs text-text-light-secondary dark:text-text-dark-secondary">
                <div>
                  <div class="font-semibold text-text-light-primary dark:text-text-dark-primary">
                    {{ formatNumber(campaign.total_recipients || 0) }}
                  </div>
                  <div>Recipients</div>
                </div>
                <div>
                  <div class="font-semibold text-text-light-primary dark:text-text-dark-primary">
                    {{ formatPercent(campaign.delivery_rate) }}
                  </div>
                  <div>Delivered</div>
                </div>
                <div>
                  <div class="font-semibold text-text-light-primary dark:text-text-dark-primary">
                    {{ formatPercent(campaign.open_rate) }}
                  </div>
                  <div>Open rate</div>
                </div>
              </div>

              <div class="mt-3">
                <div class="h-2 rounded-full bg-gray-200 dark:bg-gray-800 overflow-hidden">
                  <div
                    class="h-full bg-gradient-to-r from-blue-600 to-cyan-500"
                    :style="{ width: `${Math.min(100, Math.max(0, campaign.progress || 0))}%` }"
                  ></div>
                </div>
                <div class="mt-1 text-[11px] text-text-light-secondary dark:text-text-dark-secondary">
                  Progress: {{ formatPercent(campaign.progress) }}
                </div>
              </div>
            </div>
          </div>

          <div class="rounded-2xl border border-border-light dark:border-border-dark bg-white/80 dark:bg-gray-900/70 backdrop-blur p-4 shadow-lg">
            <div v-if="selectedCampaign" class="space-y-4">
              <div>
                <div class="text-sm font-semibold">{{ selectedCampaign.subject || 'Untitled broadcast' }}</div>
                <div class="text-xs text-text-light-secondary dark:text-text-dark-secondary mt-1">
                  Created {{ formatDate(selectedCampaign.created_at) }}
                </div>
              </div>

              <div v-if="analyticsLoading" class="flex justify-center py-6">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary-light dark:border-primary-dark"></div>
              </div>

              <div v-else-if="analyticsError" class="text-xs text-red-400">
                {{ analyticsError }}
              </div>

              <div v-else-if="analytics" class="space-y-4">
                <div class="grid grid-cols-2 gap-3 text-sm">
                  <div class="p-3 rounded-xl bg-surface-light dark:bg-surface-dark">
                    <div class="text-xs text-text-light-secondary dark:text-text-dark-secondary">Sent</div>
                    <div class="text-lg font-semibold">{{ formatNumber(analytics.sent_count || 0) }}</div>
                  </div>
                  <div class="p-3 rounded-xl bg-surface-light dark:bg-surface-dark">
                    <div class="text-xs text-text-light-secondary dark:text-text-dark-secondary">Delivered</div>
                    <div class="text-lg font-semibold">{{ formatNumber(analytics.delivered_count || 0) }}</div>
                  </div>
                  <div class="p-3 rounded-xl bg-surface-light dark:bg-surface-dark">
                    <div class="text-xs text-text-light-secondary dark:text-text-dark-secondary">Opened</div>
                    <div class="text-lg font-semibold">{{ formatNumber(analytics.opened_count || 0) }}</div>
                  </div>
                  <div class="p-3 rounded-xl bg-surface-light dark:bg-surface-dark">
                    <div class="text-xs text-text-light-secondary dark:text-text-dark-secondary">Clicked</div>
                    <div class="text-lg font-semibold">{{ formatNumber(analytics.clicked_count || 0) }}</div>
                  </div>
                </div>

                <div class="grid grid-cols-3 gap-3 text-xs text-text-light-secondary dark:text-text-dark-secondary">
                  <div class="p-3 rounded-xl bg-surface-light dark:bg-surface-dark">
                    <div class="font-semibold text-text-light-primary dark:text-text-dark-primary">
                      {{ formatPercent(analytics.delivery_rate) }}
                    </div>
                    <div>Delivery rate</div>
                  </div>
                  <div class="p-3 rounded-xl bg-surface-light dark:bg-surface-dark">
                    <div class="font-semibold text-text-light-primary dark:text-text-dark-primary">
                      {{ formatPercent(analytics.open_rate) }}
                    </div>
                    <div>Open rate</div>
                  </div>
                  <div class="p-3 rounded-xl bg-surface-light dark:bg-surface-dark">
                    <div class="font-semibold text-text-light-primary dark:text-text-dark-primary">
                      {{ formatPercent(analytics.click_rate) }}
                    </div>
                    <div>Click rate</div>
                  </div>
                </div>

                <div class="text-xs text-text-light-secondary dark:text-text-dark-secondary">
                  Status: <span class="font-semibold text-text-light-primary dark:text-text-dark-primary">{{ analytics.status }}</span>
                </div>
              </div>

              <div v-else class="text-xs text-text-light-secondary dark:text-text-dark-secondary">
                Select a campaign to view analytics.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Success Toast -->
    <div 
      v-if="showSuccessToast"
      class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center gap-2"
    >
      <i class="ri-check-line"></i>
      <span>{{ t('settings_saved_successfully') }}</span>
    </div>

    <!-- Automated Message Form Modal -->
    <Modal
      v-if="showAutomatedMessageForm"
      @close="closeAutomatedMessageForm"
    >
      <AutomatedMessageForm
        :message="selectedMessage"
        @save="saveAutomatedMessage"
        @cancel="closeAutomatedMessageForm"
      />
    </Modal>

    <!-- Delete Confirmation Modal -->
    <Modal
      v-if="showDeleteConfirmation"
      @close="showDeleteConfirmation = false"
    >
      <div class="p-6">
        <h3 class="text-lg font-medium mb-4">{{ t('delete_automated_message') }}</h3>
        <p class="text-sm text-text-light-secondary dark:text-text-dark-secondary mb-6">
          {{ t('delete_message_confirmation') }}
        </p>
        <div class="flex justify-end space-x-4">
          <button
            @click="showDeleteConfirmation = false"
            class="px-4 py-2 text-sm border border-border-light dark:border-border-dark rounded-md hover:bg-surface-light dark:hover:bg-surface-dark"
          >
            {{ t('cancel') }}
          </button>
          <button
            @click="deleteMessage"
            class="px-4 py-2 text-sm bg-accent-danger text-white rounded-md hover:opacity-90"
          >
            {{ t('delete') }}
          </button>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useMessageSettingsStore } from '@/stores/messageSettingsStore'
import Modal from '@/components/common/Modal.vue'
import AutomatedMessageForm from '@/components/AutomatedMessageForm.vue'
import MessagingSettings from '@/components/MessagingSettings.vue'
import messageSchedulerService from '@/services/messageSchedulerService'
import { useI18n } from 'vue-i18n'

const store = useMessageSettingsStore()
const activeTab = ref('dm_permissions')
const isLoading = ref(true)
const error = ref('')
const showSuccessToast = ref(false)

// Automated Messages state
const loading = ref(false)
const showAutomatedMessageForm = ref(false)
const showDeleteConfirmation = ref(false)
const selectedMessage = ref(null)
const messageToDelete = ref(null)

// Broadcast stats state
const broadcastLoading = ref(false)
const broadcastError = ref('')
const campaigns = ref([])
const selectedCampaignId = ref(null)
const analytics = ref(null)
const analyticsLoading = ref(false)
const analyticsError = ref('')

const t = useI18n().t

const tabs = [
  { id: 'dm_permissions', name: t('dm_permissions') },
  { id: 'automated_messages', name: t('automated_messages') },
  { id: 'broadcast_stats', name: t('broadcast_stats') }
]

const selectedCampaign = computed(() => {
  return campaigns.value.find((campaign) => campaign.id === selectedCampaignId.value) || null
})

// Function to fetch settings based on active tab
const fetchSettings = async () => {
  try {
    isLoading.value = true
    error.value = ''
    
    if (activeTab.value === 'dm_permissions') {
      await store.fetchMessagingSettings()
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load settings'
    console.error('Error fetching settings:', err)
  } finally {
    isLoading.value = false
  }
}

// Automated Messages methods
const editMessage = (message) => {
  selectedMessage.value = message
  showAutomatedMessageForm.value = true
}

const closeAutomatedMessageForm = () => {
  showAutomatedMessageForm.value = false
  selectedMessage.value = null
}

const saveAutomatedMessage = async (data) => {
  try {
    loading.value = true
    if (selectedMessage.value) {
      await store.updateAutomatedMessage(selectedMessage.value.id, data)
    } else {
      await store.createAutomatedMessage(data)
    }
    await store.fetchAutomatedMessages()
    closeAutomatedMessageForm()
  } catch (error) {
    console.error('Failed to save automated message:', error)
  } finally {
    loading.value = false
  }
}

const confirmDeleteMessage = (message) => {
  messageToDelete.value = message
  showDeleteConfirmation.value = true
}

const deleteMessage = async () => {
  if (!messageToDelete.value) return;

  try {
    loading.value = true;
    console.log(`Deleting message with ID: ${messageToDelete.value.id}`);
    
    // Call the store method to delete the message
    await store.deleteAutomatedMessage(messageToDelete.value.id);
    
    // Refresh the messages list
    await store.fetchAutomatedMessages();
    
    // Close the confirmation modal
    showDeleteConfirmation.value = false;
    messageToDelete.value = null;
    
    // Show success toast
    showSuccessToast.value = true;
    setTimeout(() => {
      showSuccessToast.value = false;
    }, 3000);
  } catch (error) {
    console.error('Failed to delete automated message:', error);
  } finally {
    loading.value = false;
  }
};

// Modified toggleMessageStatus function to ensure only one message is active
const toggleMessageStatus = async (message) => {
  try {
    loading.value = true
    
    // If we're activating a message (it's currently inactive)
    if (!message.is_active) {
      // First, deactivate all other messages
      const deactivationPromises = store.automatedMessages
        .filter(msg => msg.id !== message.id && msg.is_active)
        .map(msg => store.toggleAutomatedMessageStatus(msg.id))
      
      // Wait for all deactivations to complete
      if (deactivationPromises.length > 0) {
        await Promise.all(deactivationPromises)
      }
    }
    
    // Now toggle the status of the selected message
    await store.toggleAutomatedMessageStatus(message.id)
    
    // Refresh the list
    await store.fetchAutomatedMessages()
  } catch (error) {
    console.error('Failed to toggle message status:', error)
  } finally {
    loading.value = false
  }
}

const formatNumber = (value) => {
  if (value === null || value === undefined) return '0'
  return new Intl.NumberFormat().format(Number(value))
}

const formatPercent = (value) => {
  const numeric = Number(value)
  if (Number.isNaN(numeric)) return '0%'
  return `${Math.round(numeric * 100) / 100}%`
}

const formatDate = (value) => {
  if (!value) return 'Unknown'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return 'Unknown'
  return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' })
}

const fetchBroadcastCampaigns = async () => {
  try {
    broadcastLoading.value = true
    broadcastError.value = ''
    const result = await messageSchedulerService.getCampaigns()
    if (!result.success) {
      throw new Error(result.error || 'Failed to load campaigns')
    }
    campaigns.value = result.campaigns || []

    if (campaigns.value.length > 0 && !selectedCampaignId.value) {
      selectedCampaignId.value = campaigns.value[0].id
      await fetchCampaignAnalytics(selectedCampaignId.value)
    }
  } catch (err) {
    broadcastError.value = err.message || 'Failed to load broadcast campaigns'
  } finally {
    broadcastLoading.value = false
  }
}

const fetchCampaignAnalytics = async (campaignId) => {
  if (!campaignId) return
  try {
    analyticsLoading.value = true
    analyticsError.value = ''
    const result = await messageSchedulerService.getCampaignAnalytics(campaignId)
    if (!result.success) {
      throw new Error(result.error || 'Failed to load analytics')
    }
    analytics.value = result.analytics
  } catch (err) {
    analyticsError.value = err.message || 'Failed to load analytics'
    analytics.value = null
  } finally {
    analyticsLoading.value = false
  }
}

const selectCampaign = async (campaignId) => {
  if (selectedCampaignId.value === campaignId) return
  selectedCampaignId.value = campaignId
  await fetchCampaignAnalytics(campaignId)
}

const refreshBroadcastStats = async () => {
  selectedCampaignId.value = null
  analytics.value = null
  await fetchBroadcastCampaigns()
}

// Watch for tab changes
watch(activeTab, async (newTab) => {
  if (newTab === 'automated_messages') {
    try {
      loading.value = true
      await store.fetchAutomatedMessages()
      console.log(" automated messages: ", store.automatedMessages);
    } catch (error) {
      console.error('Failed to fetch automated messages:', error)
    } finally {
      loading.value = false
    }
  } else if (newTab === 'dm_permissions') {
    fetchSettings()
  } else if (newTab === 'broadcast_stats') {
    fetchBroadcastCampaigns()
  }
})

onMounted(() => {
  fetchSettings()
  if (activeTab.value === 'automated_messages') {
    store.fetchAutomatedMessages()
  }
})
</script>
