<!-- components/MessagingSettings.vue -->
<template>
  <div class="space-y-6">
    <div v-if="isLoading" class="flex justify-center py-4">
      <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary-light dark:border-primary-dark"></div>
    </div>

    <div v-else-if="error" class="p-4 bg-red-900/20 text-red-400 rounded-lg">
      {{ error }}
      <button 
        @click="fetchSettings" 
        class="ml-2 underline hover:no-underline"
      >
        {{ t('try_again') }}
      </button>
    </div>

    <div v-else class="space-y-6">
      <!-- Show read receipts toggle -->
      <div class="flex items-center justify-between">
        <div class="space-y-1">
          <h3 class="text-lg font-medium">{{ t('show_read_receipts') }}</h3>
          <p class="text-gray-500">
            {{ t('read_receipts_description') }}
          </p>
        </div>
        <!-- Toggle with checkmark -->
        <button 
          @click="toggleSetting('show_read_receipts')"
          class="w-12 h-6 rounded-full flex items-center transition-colors duration-200 focus:outline-none"
          :class="[settings.show_read_receipts ? 'bg-primary-light dark:bg-primary-dark justify-end' : 'bg-gray-300 dark:bg-gray-600 justify-start']"
        >
          <div class="w-6 h-6 rounded-full bg-white flex items-center justify-center shadow-md">
            <i v-if="settings.show_read_receipts" class="ri-check-line text-primary-light dark:text-primary-dark"></i>
          </div>
        </button>
      </div>
      
      <!-- Don't accept messages without a Tip toggle -->
      <div class="flex items-center justify-between">
        <div class="space-y-1">
          <h3 class="text-lg font-medium">{{ t('no_messages_without_tip') }}</h3>
        </div>
        <!-- Toggle with checkmark -->
        <button 
          @click="toggleSetting('require_tip_for_messages')"
          class="w-12 h-6 rounded-full flex items-center transition-colors duration-200 focus:outline-none"
          :class="[settings.require_tip_for_messages ? 'bg-primary-light dark:bg-primary-dark justify-end' : 'bg-gray-300 dark:bg-gray-600 justify-start']"
        >
          <div class="w-6 h-6 rounded-full bg-white flex items-center justify-center shadow-md">
            <i v-if="settings.require_tip_for_messages" class="ri-check-line text-primary-light dark:text-primary-dark"></i>
          </div>
        </button>
      </div>
      
      <!-- Accept messages from followers you follow back toggle - only show when require_tip_for_messages is true -->
      <div v-if="settings.require_tip_for_messages" class="flex items-center justify-between">
        <div class="space-y-1">
          <h3 class="text-lg font-medium">{{ t('accept_messages_from_followers') }}</h3>
        </div>
        <!-- Toggle with checkmark -->
        <button 
          @click="toggleSetting('accept_messages_from_followed')"
          class="w-12 h-6 rounded-full flex items-center transition-colors duration-200 focus:outline-none"
          :class="[settings.accept_messages_from_followed ? 'bg-primary-light dark:bg-primary-dark justify-end' : 'bg-gray-300 dark:bg-gray-600 justify-start']"
        >
          <div class="w-6 h-6 rounded-full bg-white flex items-center justify-center shadow-md">
            <i v-if="settings.accept_messages_from_followed" class="ri-check-line text-primary-light dark:text-primary-dark"></i>
          </div>
        </button>
      </div>
    </div>
    
    <div v-if="hasChanges" class="flex justify-end space-x-4 mt-6">
      <button
        @click="resetSettings"
        class="px-4 py-2 text-sm border border-border-light dark:border-border-dark rounded-md hover:bg-surface-light dark:hover:bg-surface-dark"
      >
        {{ t('cancel') }}
      </button>
      <button
        @click="saveSettings"
        class="px-4 py-2 text-sm bg-primary-light dark:bg-primary-dark text-white rounded-md hover:opacity-90"
        :disabled="isSaving"
      >
        {{ t('save_changes') }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useMessageSettingsStore } from '@/stores/messageSettingsStore';
import { storeToRefs } from 'pinia';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const store = useMessageSettingsStore();
const { messagingSettings, loading, saving, error: storeError } = storeToRefs(store);

const settings = ref({
  show_read_receipts: false,
  require_tip_for_messages: false,
  accept_messages_from_followed: true
});
const originalSettings = ref({});
const isLoading = ref(false);
const isSaving = ref(false);
const error = ref('');

const hasChanges = computed(() => {
  return JSON.stringify(settings.value) !== JSON.stringify(originalSettings.value);
});

// Watch for changes in the store's messagingSettings
watch(messagingSettings, (newSettings) => {
  if (newSettings) {
    settings.value = { ...newSettings };
    originalSettings.value = JSON.parse(JSON.stringify(newSettings));
  }
}, { immediate: true });

const fetchSettings = async () => {
  try {
    isLoading.value = true;
    error.value = '';
    const data = await store.fetchMessagingSettings();
    // Convert string 'true'/'false' to boolean
    settings.value = {
      show_read_receipts: data.show_read_receipts === true || data.show_read_receipts === 'true',
      require_tip_for_messages: data.require_tip_for_messages === true || data.require_tip_for_messages === 'true',
      accept_messages_from_followed: data.accept_messages_from_followed === true || data.accept_messages_from_followed === 'true',
    };
    originalSettings.value = JSON.parse(JSON.stringify(settings.value));
  } catch (err) {
    error.value = err.response?.data?.message || t('failed_to_load_settings');
    console.error('Error fetching settings:', err);
  } finally {
    isLoading.value = false;
  }
};

const saveSettings = async () => {
  try {
    isSaving.value = true;
    error.value = '';
    
    // Convert all values to proper booleans before sending
    const settingsToSend = {
      show_read_receipts: Boolean(settings.value.show_read_receipts),
      require_tip_for_messages: Boolean(settings.value.require_tip_for_messages),
      accept_messages_from_followed: Boolean(settings.value.accept_messages_from_followed)
    };

    const data = await store.updateMessagingSettings(settingsToSend);

    // Convert string 'true'/'false' to boolean for the response
    settings.value = {
      show_read_receipts: data.show_read_receipts === true || data.show_read_receipts === 'true',
      require_tip_for_messages: data.require_tip_for_messages === true || data.require_tip_for_messages === 'true',
      accept_messages_from_followed: data.accept_messages_from_followed === true || data.accept_messages_from_followed === 'true',
    };
    originalSettings.value = JSON.parse(JSON.stringify(settings.value));

    // Emit success event
    emit('settings-saved');
  } catch (err) {
    error.value = err.response?.data?.message || t('failed_to_save_settings');
    console.error('Error saving settings:', err);
  } finally {
    isSaving.value = false;
  }
};

const resetSettings = () => {
  settings.value = JSON.parse(JSON.stringify(originalSettings.value));
};

const toggleSetting = (key) => {
  settings.value[key] = !settings.value[key];
};

// Define emits
const emit = defineEmits(['settings-saved']);

onMounted(() => {
  fetchSettings();
});
</script>