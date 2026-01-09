<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark">
    <!-- Modern Header -->
    <header class="sticky top-0 z-20 bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-lg border-b border-border-light dark:border-border-dark shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20 py-6">
          <!-- Left Side: Navigation and Title -->
          <div class="flex items-center gap-4">
            <router-link 
              to="/settings" 
              class="p-2 rounded-lg bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/20 dark:border-gray-700/50 text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary hover:bg-white/70 dark:hover:bg-gray-800/70 transition-all duration-200 hover:scale-105 shadow-sm hover:shadow-md"
            >
              <i class="ri-arrow-left-line text-lg"></i>
            </router-link>
            
            <div class="flex flex-col">
              <h1 class="text-2xl font-bold text-text-light-primary dark:text-text-dark-primary leading-tight">
                {{ t('account') }}
              </h1>
              <p class="text-text-light-secondary dark:text-text-dark-secondary text-sm mt-0.5">
                Manage your account settings and security
              </p>
            </div>
          </div>
          
          <!-- Right Side: Account Status -->
          <div class="hidden md:flex items-center gap-4">
            <div class="text-right">
              <div class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Account Status</div>
              <div class="text-lg font-bold text-green-600 dark:text-green-400">Active</div>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Loading State -->
      <div v-if="settingsStore.isLoading" class="flex justify-center items-center py-16">
        <div class="flex flex-col items-center gap-4">
          <div class="animate-spin rounded-full h-12 w-12 border-4 border-primary-light dark:border-primary-dark border-t-transparent"></div>
          <p class="text-text-light-secondary dark:text-text-dark-secondary">Loading account settings...</p>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="settingsStore.error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl p-6 mb-6">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center">
            <i class="ri-error-warning-line text-red-600 dark:text-red-400 text-lg"></i>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-red-900 dark:text-red-100">Error Loading Settings</h3>
            <p class="text-red-600 dark:text-red-400">{{ settingsStore.error }}</p>
          </div>
        </div>
      </div>

      <div v-else class="space-y-8">
        <!-- Username Section -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
          <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
              <i class="ri-user-line text-blue-600 dark:text-blue-400 text-xl"></i>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ t('username_profile') }}</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">Your unique profile identifier</p>
            </div>
          </div>
          
          <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-4 mb-4">
            <p class="font-mono text-gray-900 dark:text-white">fans4more.com/{{ settingsStore.account.username }}</p>
          </div>
          
          <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
            <div class="flex items-start gap-3">
              <i class="ri-information-line text-blue-600 dark:text-blue-400 mt-1"></i>
              <p class="text-sm text-blue-800 dark:text-blue-200">
                Usernames can be changed once every 30 days for your privacy protection.
              </p>
            </div>
          </div>
        </div>

        <!-- Display Name Section -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
          <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
              <i class="ri-user-settings-line text-green-600 dark:text-green-400 text-xl"></i>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ t('display_name') }}</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">How your name appears to others</p>
            </div>
          </div>
          
          <div class="space-y-3">
            <input
              v-model="displayName"
              type="text"
              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-2 border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 text-gray-900 dark:text-white"
              placeholder="Enter your display name"
            />
            <div v-if="displayNameMessage" class="p-3 rounded-lg text-sm"
                 :class="displayNameMessageType === 'success' ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400' : 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400'">
              {{ displayNameMessage }}
            </div>
            <button 
              @click="saveDisplayName"
              :disabled="!displayName || displayName === settingsStore.account.display_name || isSavingDisplayName"
              class="w-full px-4 py-2 bg-primary-light dark:bg-primary-dark text-white rounded-lg font-semibold hover:bg-primary-light/90 dark:hover:bg-primary-dark/90 transition-all duration-200 hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100">
              <i v-if="isSavingDisplayName" class="ri-loader-4-line animate-spin mr-2"></i>
              {{ isSavingDisplayName ? 'Saving...' : 'Save Display Name' }}
            </button>
          </div>
        </div>

        <!-- Email Section -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
          <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
              <i class="ri-mail-line text-purple-600 dark:text-purple-400 text-xl"></i>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ t('email') }}</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">Your account email address</p>
            </div>
          </div>
          
          <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700 rounded-xl p-4 mb-4">
            <div class="flex items-center gap-3">
              <span class="text-gray-700 dark:text-gray-300">{{ settingsStore.account.email }}</span>
              <div v-if="settingsStore.account.is_email_verified" class="flex items-center gap-1">
                <i class="ri-checkbox-circle-fill text-green-600 dark:text-green-400"></i>
                <span class="text-sm text-green-600 dark:text-green-400 font-medium">Verified</span>
              </div>
              <div v-else class="flex items-center gap-1">
                <i class="ri-error-warning-line text-yellow-600 dark:text-yellow-400"></i>
                <span class="text-sm text-yellow-600 dark:text-yellow-400 font-medium">Unverified</span>
              </div>
            </div>
          </div>
          
          <button @click="settingsStore.openChangeEmailModal" 
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-200 hover:scale-105">
            {{ t('change_email') }}
          </button>
        </div>

        <!-- 2FA Section -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
          <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-xl flex items-center justify-center">
              <i class="ri-shield-keyhole-line text-orange-600 dark:text-orange-400 text-xl"></i>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ t('manage_2fa') }}</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">Two-factor authentication</p>
            </div>
          </div>
          
          <div v-if="settingsStore.account.has_2fa" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4 mb-4">
            <div class="flex items-center gap-3">
              <i class="ri-checkbox-circle-fill text-green-600 dark:text-green-400"></i>
              <span class="text-green-800 dark:text-green-200 font-medium">Your account is protected with 2FA!</span>
            </div>
          </div>
          <div v-else class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4 mb-4">
            <div class="flex items-center gap-3">
              <i class="ri-error-warning-line text-yellow-600 dark:text-yellow-400"></i>
              <span class="text-yellow-800 dark:text-yellow-200 font-medium">2FA is not enabled for your account.</span>
            </div>
          </div>
          
          <button @click="openConfirm2FAModal" 
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-200 hover:scale-105">
            {{ settingsStore.account.has_2fa ? t('remove_2fa') : t('enable_2fa') }}
          </button>
        </div>

        <!-- Password Section -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
          <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center">
              <i class="ri-lock-password-line text-red-600 dark:text-red-400 text-xl"></i>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ t('password') }}</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">Change your account password</p>
            </div>
          </div>
          
          <button @click="settingsStore.openChangePasswordModal" 
            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-200 hover:scale-105">
            {{ t('change_password') }}
          </button>
        </div>

        <!-- Account Status Section -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
          <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center">
              <i class="ri-delete-bin-line text-red-600 dark:text-red-400 text-xl"></i>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ t('account_status') }}</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">Dangerous account actions</p>
            </div>
          </div>
          
          <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4 mb-4">
            <div class="flex items-start gap-3">
              <i class="ri-error-warning-line text-red-600 dark:text-red-400 mt-1"></i>
              <p class="text-sm text-red-800 dark:text-red-200">
                Deleting your account is permanent and cannot be undone. All your data will be permanently removed.
              </p>
            </div>
          </div>
          
          <button @click="openDeleteAccountModal" 
            class="px-4 py-2 bg-red-100 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-lg font-semibold hover:bg-red-200 dark:hover:bg-red-900/30 transition-all duration-200 hover:scale-105">
            {{ t('delete_account') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <ChangeEmailModal 
      v-if="settingsStore.isChangeEmailModalOpen"
      :current-email="settingsStore.account.email"
      :error="emailError"
      :success="emailSuccess"
      :saving="isSavingEmail"
      @close="settingsStore.closeChangeEmailModal"
      @update="changeEmail"
    />

    <ChangePasswordModal 
      v-if="settingsStore.isChangePasswordModalOpen"
      :error="passwordError"
      :success="passwordSuccess"
      :saving="isSavingPassword"
      @close="settingsStore.closeChangePasswordModal"
      @update="changePassword"
    />

    <Confirm2FAModal 
      v-if="isConfirm2FAModalOpen"
      :is-enabled="settingsStore.account.has_2fa"
      @close="closeConfirm2FAModal"
      @confirm="toggle2FA"
    />

    <DeleteAccountModal 
      v-if="isDeleteAccountModalOpen"
      @close="closeDeleteAccountModal"
      @confirm="deleteAccount"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useSettingsStore } from '@/stores/settingsStore'
import ChangeEmailModal from '@/components/modals/ChangeEmailModal.vue'
import ChangePasswordModal from '@/components/modals/ChangePasswordModal.vue'
import Confirm2FAModal from '@/components/modals/Confirm2FAModal.vue'
import DeleteAccountModal from '@/components/modals/DeleteAccountModal.vue'
import { useI18n } from 'vue-i18n'

const settingsStore = useSettingsStore()
const { t } = useI18n()

const isConfirm2FAModalOpen = ref(false)
const isDeleteAccountModalOpen = ref(false)
const displayName = ref('')
const displayNameMessage = ref('')
const displayNameMessageType = ref('')
const isSavingDisplayName = ref(false)
const passwordError = ref('')
const passwordSuccess = ref('')
const isSavingPassword = ref(false)
const emailError = ref('')
const emailSuccess = ref('')
const isSavingEmail = ref(false)

onMounted(() => {
  settingsStore.fetchAccountSettings()
  displayName.value = settingsStore.account.display_name || ''
  // Clear any previous errors when settings load
  settingsStore.error = null
})

// Watch for changes in settingsStore.account.display_name
watch(() => settingsStore.account.display_name, (newValue) => {
  if (newValue) {
    displayName.value = newValue
  }
})

// Watch for password modal opening to clear errors
watch(() => settingsStore.isChangePasswordModalOpen, (isOpen) => {
  if (isOpen) {
    passwordError.value = ''
    passwordSuccess.value = ''
  }
})

// Watch for email modal opening to clear errors
watch(() => settingsStore.isChangeEmailModalOpen, (isOpen) => {
  if (isOpen) {
    emailError.value = ''
    emailSuccess.value = ''
  }
})

const saveDisplayName = async () => {
  if (!displayName.value || displayName.value === settingsStore.account.display_name) {
    return
  }
  
  isSavingDisplayName.value = true
  displayNameMessage.value = ''
  
  try {
    await settingsStore.updateDisplayName(displayName.value)
    displayNameMessage.value = 'Display name updated successfully!'
    displayNameMessageType.value = 'success'
    
    // Clear message after 3 seconds
    setTimeout(() => {
      displayNameMessage.value = ''
    }, 3000)
  } catch (error) {
    console.error('Failed to update display name:', error)
    displayNameMessage.value = error.response?.data?.message || 'Failed to update display name'
    displayNameMessageType.value = 'error'
    
    // Reset to original value on error
    displayName.value = settingsStore.account.display_name || ''
  } finally {
    isSavingDisplayName.value = false
  }
}

const changeEmail = async (newEmail, password) => {
  emailError.value = ''
  emailSuccess.value = ''
  isSavingEmail.value = true
  
  // Clear any previous store errors
  settingsStore.error = null
  
  try {
    await settingsStore.changeEmail(newEmail, password)
    emailSuccess.value = 'Email changed successfully! Verification email sent.'
    
    // Close modal after 2 seconds
    setTimeout(() => {
      settingsStore.closeChangeEmailModal()
      emailSuccess.value = ''
    }, 2000)
  } catch (error) {
    console.error('Failed to change email:', error)
    
    // Handle different error types
    if (error.response?.data?.message) {
      emailError.value = error.response.data.message
    } else if (error.response?.data?.errors) {
      // Handle validation errors
      const errors = error.response.data.errors
      const errorMessages = Object.values(errors).flat()
      emailError.value = errorMessages.join(', ')
    } else {
      emailError.value = 'Failed to change email. Please try again.'
    }
  } finally {
    isSavingEmail.value = false
  }
}

const changePassword = async (currentPassword, newPassword) => {
  passwordError.value = ''
  passwordSuccess.value = ''
  isSavingPassword.value = true
  
  // Clear any previous store errors
  settingsStore.error = null
  
  try {
    await settingsStore.changePassword(currentPassword, newPassword)
    passwordSuccess.value = 'Password changed successfully!'
    
    // Close modal after 1.5 seconds
    setTimeout(() => {
      settingsStore.closeChangePasswordModal()
      passwordSuccess.value = ''
    }, 1500)
  } catch (error) {
    console.error('Failed to change password:', error)
    
    // Handle different error types
    if (error.response?.data?.message) {
      passwordError.value = error.response.data.message
    } else if (error.response?.data?.errors) {
      // Handle validation errors
      const errors = error.response.data.errors
      const errorMessages = Object.values(errors).flat()
      passwordError.value = errorMessages.join(', ')
    } else {
      passwordError.value = 'Failed to change password. Please try again.'
    }
  } finally {
    isSavingPassword.value = false
  }
}

const openConfirm2FAModal = () => {
  isConfirm2FAModalOpen.value = true
}

const closeConfirm2FAModal = () => {
  isConfirm2FAModalOpen.value = false
}

const toggle2FA = async (password) => {
  try {
    await settingsStore.toggle2FA(password)
    closeConfirm2FAModal()
  } catch (error) {
    console.error('Failed to toggle 2FA:', error)
  }
}

const openDeleteAccountModal = () => {
  isDeleteAccountModalOpen.value = true
}

const closeDeleteAccountModal = () => {
  isDeleteAccountModalOpen.value = false
}

const deleteAccount = async (password) => {
  try {
    await settingsStore.deleteAccount(password)
    closeDeleteAccountModal()
    // Handle successful account deletion (e.g., logout and redirect)
  } catch (error) {
    console.error('Failed to delete account:', error)
  }
}
</script>

