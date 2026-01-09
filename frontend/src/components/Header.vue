<template>
  <div>
  <header class="fixed top-0 left-0 right-0 z-30 bg-background-light dark:bg-background-dark border-b border-border-light dark:border-border-dark">
    <!-- Mobile Header (visible on small screens) -->
    <div class="flex lg:hidden w-full items-center justify-between px-4 py-3">
      <!-- Left side with Avatar -->
      <div class="flex items-center gap-4">
        <!-- Profile Avatar Button -->
        <button 
          v-if="user"
          @click="$emit('toggle-mobile-menu')"
          class="w-8 h-8 rounded-full overflow-hidden"
        >
          <img 
            :src="user.avatar" 
            :alt="user.name"
            class="w-full h-full object-cover"
          />
        </button>

        <!-- Logo -->
        <a href="/" class="flex items-center gap-2">
          <div class="flex items-center gap-2">
            <svg class="h-8 w-8 text-primary-light dark:text-primary-dark" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
            </svg>
            <span class="text-lg font-medium text-text-light-primary dark:text-text-dark-primary">{{ t('fansformore') }}</span>
          </div>
        </a>
      </div>

      <!-- Right side icons -->
      <div class="flex items-center gap-4">
        <!-- Theme Toggle (Mobile) -->
        <button
          @click="toggleTheme"
          class="text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary transition-colors"
        >
          <i :class="theme === 'dark' ? 'ri-sun-line' : 'ri-moon-line'" class="text-2xl"></i>
        </button>

        <!-- Wallet Balance (Mobile) -->
        <div class="relative">
          <button @click="toggleWalletPopup" class="text-text-light-primary dark:text-text-dark-primary text-sm font-medium">
            ${{ walletStore.walletBalance }}
          </button>
          <WalletPopup :is-open="isWalletPopupOpen" @close="closeWalletPopup" />
        </div>
      </div>
    </div>

    <!-- Desktop Header (hidden on small screens) -->
    <div class="hidden lg:flex w-full items-center justify-between">
      <!-- Left side with Avatar -->
      <div class="flex items-center gap-4">
        <!-- Profile Avatar Button -->
        <button 
          v-if="user"
          @click="$emit('toggle-mobile-menu')"
          class="w-8 h-8 rounded-full overflow-hidden"
        >
          <img 
            :src="user.avatar" 
            :alt="user.name"
            class="w-full h-full object-cover"
          />
        </button>

        <!-- Logo -->
        <a href="/" class="flex items-center gap-2">
          <div class="flex items-center gap-2">
            <svg class="h-8 w-8 text-primary-light dark:text-primary-dark" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
            </svg>
            <span class="text-lg font-medium text-text-light-primary dark:text-text-dark-primary">{{ t('fansformore') }}</span>
          </div>
        </a>
      </div>

      <!-- Right side icons -->
      <div class="flex items-center gap-6">
        <button class="text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary transition-colors">
          <i class="ri-search-line text-2xl"></i>
        </button>

        <button class="text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary transition-colors">
          <i class="ri-global-line text-2xl"></i>
        </button>

        <button class="relative text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary transition-colors">
          <i class="ri-mail-line text-2xl"></i>
          <span class="absolute -top-1.5 -right-1.5 h-5 w-5 rounded-full bg-primary-light dark:bg-primary-dark text-xs text-white flex items-center justify-center">{{ formatCount(unreadNotificationCount) }}</span>
        </button>

        <!-- Theme Toggle (Desktop) -->
        <button
          @click="toggleTheme"
          class="text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary transition-colors"
        >
          <i :class="theme === 'dark' ? 'ri-sun-line' : 'ri-moon-line'" class="text-2xl"></i>
        </button>

        <!-- Wallet Balance (Desktop) -->
        <div class="relative">
          <button @click="toggleWalletPopup" class="text-text-light-primary dark:text-text-dark-primary text-sm font-medium">
            ${{ walletStore.walletBalance }}
          </button>
          <WalletPopup :is-open="isWalletPopupOpen" @close="closeWalletPopup" />
        </div>

        <!-- User Menu (Desktop) -->
        <UserMenu v-if="user" :username="username" :handle="handle" :likes="likes" :followers="followers" :subscribers="subscribers" />
      </div>
    </div>
  </header>
    <div class="pt-14">
      <EmailVerificationAlert
        v-if="showEmailAlert"
        class="mt-2"
        @open-verification="openVerificationModal"
        @resend-code="resendCode"
      />
    </div>
    <EmailVerificationCodeModal
      v-if="isVerificationModalOpen"
      :request-code="requestCode"
      :verify-code="verifyCode"
      @close="isVerificationModalOpen = false"
      @verified="onVerified"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, computed } from 'vue'
import UserMenu from './UserMenu.vue'
import WalletPopup from './WalletPopup.vue'
import { useTheme } from '../composables/useTheme'
import { useNotificationService } from '../services/notificationService'
import { useWalletStore } from '../stores/walletStore'
import { useAuthStore } from '../stores/authStore'
import EmailVerificationAlert from './EmailVerificationAlert.vue'
import EmailVerificationCodeModal from './modals/EmailVerificationCodeModal.vue'
import { useSettingsStore, shouldShowEmailVerificationModal } from '@/stores/settingsStore'
import { useI18n } from 'vue-i18n'

defineEmits(['toggle-mobile-menu'])

const { t } = useI18n();

const props = defineProps({
  user: {
    type: Object,
    default: () => ({
      name: '',
      handle: '',
      avatar: '/placeholder.svg?height=32&width=32',
      support_count: 0
    })
  }
});

// Get stores
const walletStore = useWalletStore();
const authStore = useAuthStore();
const settingsStore = useSettingsStore()

// Mock data for UserMenu component
const username = props.user?.name || ''
const handle = props.user?.handle || ''
const likes = props.user?.support_count || 0
const followers = 2463
const subscribers = 23

const { theme, setTheme } = useTheme();
const notificationService = useNotificationService();
const unreadNotificationCount = ref(0);

const toggleTheme = () => {
  setTheme(theme.value === 'dark' ? 'light' : 'dark');
};

const isWalletPopupOpen = ref(false);

const toggleWalletPopup = () => {
  isWalletPopupOpen.value = !isWalletPopupOpen.value;
};

const closeWalletPopup = () => {
  isWalletPopupOpen.value = false;
};

// Format large notification counts (e.g., 1000 -> 999+)
const formatCount = (count) => {
  if (count > 999) {
    return '999+';
  }
  return count;
};

// Initialize notification service and set up watchers
onMounted(async () => {
  // Apply the initial theme
  document.documentElement.classList.toggle('dark', theme.value === 'dark');
  
  // Initialize notification service
  notificationService.initialize();
  
  // Get initial unread count
  unreadNotificationCount.value = notificationService.unreadCount.value;
  
  // Set up watcher for unread count changes
  const unsubscribe = watch(
    () => notificationService.unreadCount.value,
    (newCount) => {
      unreadNotificationCount.value = newCount;
    }
  );

  // Fetch wallet balance if user is authenticated
  if (authStore.isAuthenticated) {
    await walletStore.fetchWalletBalance();
  }
  
  // Store the unsubscribe function for cleanup
  onUnmounted(() => {
    unsubscribe();
  });
});

const isVerificationModalOpen = ref(false)

const showEmailAlert = computed(() => {
  return settingsStore.account.email && !settingsStore.account.is_email_verified
})

function openVerificationModal() {
  isVerificationModalOpen.value = true
}

async function resendCode() {
  await settingsStore.requestEmailVerificationCode()
}

async function requestCode() {
  await settingsStore.requestEmailVerificationCode()
}

async function verifyCode(code) {
  await settingsStore.verifyEmailCode(code)
}

function onVerified() {
  isVerificationModalOpen.value = false
}

watch(
  shouldShowEmailVerificationModal,
  (val) => {
    if (val) {
      isVerificationModalOpen.value = true
      shouldShowEmailVerificationModal.value = false
    }
  }
)
</script>