<template>
<div class="fixed inset-0 z-50 flex items-center justify-center px-4">
  <!-- Backdrop -->
  <div class="absolute inset-0 bg-black/50 dark:bg-gray-900/50" @click="$emit('close')"></div>

  <!-- Modal -->
  <div class="relative w-full max-w-md bg-surface-light dark:bg-surface-dark rounded-lg shadow-xl">
    <div class="p-6 space-y-4">
      <h2 class="text-xl font-semibold text-text-light-primary dark:text-text-dark-primary">Add New Moderator</h2>

      <div class="space-y-4">
        <!-- Username -->
        <div class="space-y-2">
          <label class="text-sm font-medium text-text-light-primary dark:text-text-dark-primary">Username</label>
          <input
            v-model="username"
            type="text"
            placeholder="Enter username"
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

        <!-- Notes -->
        <div class="space-y-2">
          <label class="text-sm font-medium text-text-light-primary dark:text-text-dark-primary">Notes (Optional)</label>
          <textarea
            v-model="notes"
            rows="3"
            placeholder="Add any notes about this moderator"
            class="w-full px-3 py-2 bg-surface-light dark:bg-surface-dark rounded-lg text-sm text-text-light-primary dark:text-text-dark-primary placeholder-text-light-secondary dark:placeholder-text-dark-secondary border border-border-light dark:border-border-dark resize-none"
          ></textarea>
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
          @click="addModerator"
          :disabled="!isValid"
          class="px-4 py-2 text-sm text-white bg-primary-light dark:bg-primary-dark hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg"
        >
          Add Moderator
        </button>
      </div>
    </div>
  </div>
</div>
</template>

<script setup>
import { ref, computed } from 'vue'

const username = ref('')
const selectedPermissions = ref([])
const notes = ref('')

const availablePermissions = [
  { value: 'moderate_posts', label: 'Moderate Posts' },
  { value: 'moderate_comments', label: 'Moderate Comments' },
  { value: 'moderate_chat', label: 'Moderate Stream Chat' },
  { value: 'ban_users', label: 'Ban Users' }
]

const isValid = computed(() => {
  return username.value.trim() && selectedPermissions.value.length > 0
})

const addModerator = () => {
  if (!isValid.value) return

  emit('add', {
    username: username.value,
    permissions: selectedPermissions.value,
    notes: notes.value,
    avatar: '/placeholder.svg?height=40&width=40' // In a real app, this would be fetched from the user's profile
  })
}

const emit = defineEmits(['close', 'add'])
</script>

