<template>
<div class="fixed inset-0 z-50 flex items-center justify-center px-4">
  <!-- Backdrop -->
  <div class="absolute inset-0 bg-black/75" @click="$emit('close')"></div>

  <!-- Modal -->
  <div class="relative w-full max-w-md bg-surface-light dark:bg-surface-dark rounded-lg shadow-xl">
    <div class="p-6 space-y-4">
      <h2 class="text-xl font-semibold text-text-light-primary dark:text-text-dark-primary">Create Management Session</h2>

      <div class="space-y-4">
        <!-- Session Name -->
        <div class="space-y-2">
          <label class="text-sm font-medium text-text-light-primary dark:text-text-dark-primary">Session Name</label>
          <input
            v-model="sessionName"
            type="text"
            placeholder="Enter session name"
            class="w-full px-3 py-2 bg-surface-light dark:bg-surface-dark rounded-lg text-sm text-text-light-primary dark:text-text-dark-primary placeholder-text-light-secondary dark:placeholder-text-dark-secondary border border-border-light dark:border-border-dark"
          />
        </div>

        <!-- Permissions -->
        <div class="space-y-2">
          <label class="text-sm font-medium text-text-light-primary dark:text-text-dark-primary">Permissions</label>
          <div class="space-y-2">
            <label 
              v-for="permission in availablePermissions"
              :key="permission.value"
              class="flex items-center gap-2"
            >
              <input
                type="checkbox"
                v-model="selectedPermissions"
                :value="permission.value"
                class="rounded border-border-light dark:border-border-dark text-primary-light dark:text-primary-dark"
              />
              <span class="text-sm text-text-light-primary dark:text-text-dark-primary">{{ permission.label }}</span>
            </label>
          </div>
        </div>

        <!-- Expiration -->
        <div class="space-y-2">
          <label class="text-sm font-medium text-text-light-primary dark:text-text-dark-primary">Expires After</label>
          <select
            v-model="expiration"
            class="w-full px-3 py-2 bg-surface-light dark:bg-surface-dark rounded-lg text-sm text-text-light-primary dark:text-text-dark-primary border border-border-light dark:border-border-dark"
          >
            <option value="1">1 day</option>
            <option value="7">7 days</option>
            <option value="30">30 days</option>
            <option value="never">Never</option>
          </select>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex justify-end gap-3 pt-4">
        <button
          @click="$emit('close')"
          class="px-4 py-2 text-sm text-text-light-secondary dark:text-text-dark-secondary hover:bg-surface-light dark:hover:bg-surface-dark rounded-lg"
        >
          Cancel
        </button>
        <button
          @click="createSession"
          :disabled="!isValid"
          class="px-4 py-2 text-sm text-white bg-primary-light dark:bg-primary-dark hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg"
        >
          Create Session
        </button>
      </div>
    </div>
  </div>
</div>
</template>

<script setup>
import { ref, computed } from 'vue'

const sessionName = ref('')
const selectedPermissions = ref([])
const expiration = ref('7')

const availablePermissions = [
  { value: 'manage_posts', label: 'Manage Posts' },
  { value: 'manage_messages', label: 'Manage Messages' },
  { value: 'manage_comments', label: 'Manage Comments' },
  { value: 'view_analytics', label: 'View Analytics' }
]

const isValid = computed(() => {
  return sessionName.value.trim() && selectedPermissions.value.length > 0
})

const createSession = () => {
  if (!isValid.value) return

  emit('create', {
    name: sessionName.value,
    permissions: selectedPermissions.value,
    expiration: expiration.value
  })
}

const emit = defineEmits(['close', 'create'])
</script>

