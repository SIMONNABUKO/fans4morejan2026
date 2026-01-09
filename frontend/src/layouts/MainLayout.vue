<template>
  <div
    class="min-h-screen overflow-x-hidden bg-background-light dark:bg-background-dark text-text-light-primary dark:text-text-dark-primary">
    <!-- Loading Screen -->
    <div v-if="isLoading"
      class="fixed inset-0 z-50 flex items-center justify-center bg-background-light dark:bg-background-dark">
      <div class="text-center">
        <div
          class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary-light dark:border-primary-dark">
        </div>
        <p class="mt-4 text-lg">Loading...</p>
      </div>
    </div>

    <!-- Notification Toast - Using primitive values, not refs -->
    <NotificationToast 
      v-if="showToast"
      :notification="latestNotification" 
      :show="showToast"
      @dismiss="dismissToast" 
    />

    <!-- Mobile Menu Overlay -->
    <div v-if="!isAuthRoute && !isAgeVerificationRoute && isMobileMenuOpen"
      class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 lg:hidden transition-all duration-300"
      :class="isMobileMenuOpen ? 'opacity-100' : 'opacity-0'" @click="closeMobileMenu"></div>

    <!-- Mobile Menu -->
    <div v-if="!isAuthRoute && !isAgeVerificationRoute"
      class="fixed inset-y-0 left-0 w-80 bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl border-r border-white/20 dark:border-gray-700/50 z-50 transform transition-all duration-300 lg:hidden flex flex-col shadow-2xl"
      :class="isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full'">
      
      <!-- Enhanced Profile Header -->
      <div v-if="user" class="relative p-6 border-b border-white/20 dark:border-gray-700/50 bg-gradient-to-br from-primary-light/10 dark:from-primary-dark/10 to-transparent flex-shrink-0">
        <!-- Close Button -->
        <button 
          @click="closeMobileMenu"
          class="absolute top-4 right-4 p-2 rounded-full bg-white/20 dark:bg-gray-800/20 backdrop-blur-sm text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-white/30 dark:hover:bg-gray-800/30 transition-all duration-200 hover:scale-110"
        >
          <i class="ri-close-line text-xl"></i>
        </button>
        
        <!-- Profile Section -->
        <div class="flex items-center gap-4 mb-6">
          <div class="relative">
            <div class="w-16 h-16 rounded-full ring-4 ring-white/20 dark:ring-gray-700/30 overflow-hidden">
              <img :src="user.avatar" :alt="user.name" class="w-full h-full object-cover" />
            </div>
            <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-500 rounded-full border-3 border-white dark:border-gray-900 shadow-lg"></div>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
              <h3 class="text-lg font-bold text-gray-900 dark:text-white truncate">{{ user.name }}</h3>
              <i class="ri-checkbox-circle-fill text-primary-light dark:text-primary-dark text-lg"></i>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ user.handle }}</p>
          </div>
        </div>
        
        <!-- Enhanced Stats -->
        <div class="grid grid-cols-2 gap-4">
          <div class="text-center p-3 rounded-xl bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/20 dark:border-gray-700/30">
            <div class="text-xl font-bold text-gray-900 dark:text-white">{{ user.total_likes_received }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ $t('likes') }}</div>
          </div>
          <router-link :to="{ name: 'followers', params: { username: user.id } }" class="cursor-pointer">
            <div class="text-center p-3 rounded-xl bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/20 dark:border-gray-700/30 hover:bg-white/70 dark:hover:bg-gray-800/70 transition-all duration-200 hover:scale-105">
              <div class="text-xl font-bold text-gray-900 dark:text-white">{{ user.total_followers }}</div>
              <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ $t('followers') }}</div>
            </div>
          </router-link>
        </div>
      </div>

      <!-- Enhanced Menu Items - Scrollable Container -->
      <div class="flex-1 overflow-y-auto">
        <nav class="p-4 space-y-2">
          <!-- Main Navigation Section -->
          <div class="mb-6">
            <h4 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3 px-2">Main</h4>
            <div class="space-y-1">
              <router-link v-for="item in mainMenuItems" :key="item.label" :to="item.path" custom
                v-slot="{ navigate, isActive }">
                <a @click="handleMenuClick(navigate)"
                  class="flex items-center gap-3 px-3 py-3 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-primary-light/10 dark:hover:bg-primary-dark/10 hover:text-primary-light dark:hover:text-primary-dark transition-all duration-200 group"
                  :class="{ 'bg-primary-light/15 dark:bg-primary-dark/15 text-primary-light dark:text-primary-dark': isActive }">
                  <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center group-hover:bg-primary-light/20 dark:group-hover:bg-primary-dark/20 transition-all duration-200">
                    <i :class="[item.icon, 'text-lg']"></i>
                  </div>
                  <span class="font-medium">{{ item.label }}</span>
                  <span v-if="item.badge || (item.label === t('notifications') && unreadCount > 0)" 
                        class="ml-auto text-xs bg-red-500 text-white px-2 py-1 rounded-full font-medium">
                    {{ item.badge || formatCount(unreadCount) }}
                  </span>
                </a>
              </router-link>
            </div>
          </div>

          <!-- Creator Tools Section -->
          <div v-if="user?.role === 'creator'" class="mb-6">
            <h4 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3 px-2">Creator Tools</h4>
            <div class="space-y-1">
              <router-link v-for="item in creatorMenuItems" :key="item.label" :to="item.path" custom
                v-slot="{ navigate, isActive }">
                <a @click="handleMenuClick(navigate)"
                  class="flex items-center gap-3 px-3 py-3 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-200 group"
                  :class="{ 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400': isActive }">
                  <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center group-hover:bg-blue-200 dark:group-hover:bg-blue-900/50 transition-all duration-200">
                    <i :class="[item.icon, 'text-lg']"></i>
                  </div>
                  <span class="font-medium">{{ item.label }}</span>
                </a>
              </router-link>
            </div>
          </div>

          <!-- User Tools Section -->
          <div v-if="user?.role === 'user'" class="mb-6">
            <h4 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3 px-2">Tools</h4>
            <div class="space-y-1">
              <router-link v-for="item in userMenuItems" :key="item.label" :to="item.path" custom
                v-slot="{ navigate, isActive }">
                <a @click="handleMenuClick(navigate)"
                  class="flex items-center gap-3 px-3 py-3 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-green-50 dark:hover:bg-green-900/20 hover:text-green-600 dark:hover:text-green-400 transition-all duration-200 group"
                  :class="{ 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400': isActive }">
                  <div class="w-8 h-8 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center group-hover:bg-green-200 dark:group-hover:bg-green-900/50 transition-all duration-200">
                    <i :class="[item.icon, 'text-lg']"></i>
                  </div>
                  <span class="font-medium">{{ item.label }}</span>
                </a>
              </router-link>
            </div>
          </div>

          <!-- Settings Section -->
          <div class="mb-6">
            <h4 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3 px-2">Settings</h4>
            <div class="space-y-1">
              <router-link v-for="item in settingsMenuItems" :key="item.label" :to="item.path" custom
                v-slot="{ navigate, isActive }">
                <a @click="handleMenuClick(navigate)"
                  class="flex items-center gap-3 px-3 py-3 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-all duration-200 group"
                  :class="{ 'bg-gray-50 dark:bg-gray-800/50': isActive }">
                  <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center group-hover:bg-gray-200 dark:group-hover:bg-gray-700 transition-all duration-200">
                    <i :class="[item.icon, 'text-lg']"></i>
                  </div>
                  <span class="font-medium">{{ item.label }}</span>
                </a>
              </router-link>
            </div>
          </div>

          <!-- Theme Toggle -->
          <div class="mb-6">
            <button @click="toggleTheme"
              class="w-full flex items-center gap-3 px-3 py-3 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 hover:text-yellow-600 dark:hover:text-yellow-400 transition-all duration-200 group">
              <div class="w-8 h-8 rounded-lg bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center group-hover:bg-yellow-200 dark:group-hover:bg-yellow-900/50 transition-all duration-200">
                <i :class="theme === 'dark' ? 'ri-sun-line' : 'ri-moon-line'" class="text-lg"></i>
              </div>
              <span class="font-medium">{{ theme === 'dark' ? 'Light Mode' : 'Dark Mode' }}</span>
            </button>
          </div>

          <!-- Logout Section -->
          <div class="border-t border-white/20 dark:border-gray-700/50 pt-4">
            <button @click="handleLogout"
              class="w-full flex items-center gap-3 px-3 py-3 rounded-xl text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all duration-200 group">
              <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center group-hover:bg-red-200 dark:group-hover:bg-red-900/50 transition-all duration-200">
                <i class="ri-logout-box-line text-lg"></i>
              </div>
              <span class="font-medium">{{ $t('logout') }}</span>
            </button>
          </div>
        </nav>
      </div>
    </div>

    <!-- Fixed Header -->
    <Header v-if="showHeader && !isAuthRoute && !isAgeVerificationRoute" 
      :user="user"
      @toggle-mobile-menu="toggleMobileMenu" />

    <!-- Main Content Wrapper -->
    <div :class="[
      'min-h-screen w-full transition-transform duration-300 lg:transform-none flex justify-center',
      isAgeVerificationRoute ? 'items-center' : '',
      isMobileMenuOpen ? 'translate-x-72' : 'translate-x-0'
    ]">
      <!-- Main Content -->
      <main :class="[
        'w-full',
        isAgeVerificationRoute ? '' : 'pt-14 pb-28'
      ]">
        <div :class="[
          'mx-auto',
          isAgeVerificationRoute ? '' : 'max-w-7xl px-0 sm:px-6 lg:px-8'
        ]">
          <slot></slot>
          
          <!-- Footer -->
          <Footer v-if="!isAuthRoute && !isAgeVerificationRoute && !isMessagesRoute" />
        </div>
      </main>
    </div>
    
    <MobileNavigation v-if="!isAuthRoute && !isAgeVerificationRoute" />
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch, onUnmounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useAuthStore } from "../stores/authStore";
import Header from "../components/Header.vue";
import MobileNavigation from "../components/MobileNavigation.vue";
import NotificationToast from "../components/NotificationToast.vue";
import Footer from "../components/Footer.vue";
import { useNotificationService } from "../services/notificationService";
import { RouterLink } from 'vue-router';
import { useSettingsStore } from '@/stores/settingsStore'
import { useTheme } from '@/composables/useTheme'
import { useI18n } from 'vue-i18n'

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const notificationService = useNotificationService();
const isMobileMenuOpen = ref(false);
const isLoading = ref(true);
const unreadCount = ref(0);
const showToast = ref(false);
const latestNotification = ref(null);
const { theme, setTheme } = useTheme();
const { t } = useI18n();

// Watch for changes in the notification service
watch(() => notificationService.showToast.value, (newValue) => {
  showToast.value = newValue;
});

watch(() => notificationService.latestNotification.value, (newValue) => {
  latestNotification.value = newValue;
});

watch(() => notificationService.unreadCount.value, (newValue) => {
  unreadCount.value = newValue;
});

// Method to dismiss the toast
const dismissToast = () => {
  notificationService.dismissToast();
};

const user = computed(() => authStore.user);

const showHeader = computed(() => {
  const routesWithoutHeader = ["/messages/:id"];
  return !routesWithoutHeader.includes(route.matched[0]?.path || "");
});

const isAuthRoute = computed(() => route.name === "auth");
const isAgeVerificationRoute = computed(() => route.name === 'age-verification');
const isMessagesRoute = computed(() => route.path.startsWith('/messages'));

// Format large notification counts (e.g., 1000 -> 999+)
const formatCount = (count) => {
  if (count > 999) {
    return '999+';
  }
  return count;
};

// Base menu items without theme
const baseMenuItems = ref([
  {
    label: t('home'),
    path: '/',
    icon: 'ri-home-line',
  },
  {
    label: t('profile_statistics'),
    path: '/dashboard/statistics',
    icon: 'ri-line-chart-line',
  },
  {
    label: t('tracking_links'),
    path: '/dashboard/tracking-links',
    icon: 'ri-link',
  },
  {
    label: t('referrals'),
    path: '/referrals',
    icon: 'ri-user-add-line',
  },
  { label: t('profile'), path: `/${user.value?.username || ''}`, icon: "ri-user-line" },
  { label: t('subscriptions'), path: "/subscriptions", icon: "ri-vip-crown-line" },
  { label: t('my_media'), path: "/media", icon: "ri-gallery-line" },
  { label: t('scheduled_post_queue'), path: "/posts/scheduled", icon: "ri-time-line" },
  { label: t('lists'), path: "/dashboard/lists", icon: "ri-list-check" },
  { label: t('messages'), path: "/messages", icon: "ri-message-3-line" },
  {
    label: t('notifications'),
    path: "/notifications",
    icon: "ri-notification-3-line",
  },
  { label: t('creator_dashboard'), path: "/dashboard", icon: "ri-dashboard-line" },
  {
    label: t('earning_statistics'),
    path: "/dashboard/earnings",
    icon: "ri-money-dollar-circle-line",
  },
  {
    label: t('wallet'),
    path: "/wallet",
    icon: "ri-wallet-3-line",
  },
  { label: t('terms'), path: "/terms", icon: "ri-file-text-line" },
  { label: t('privacy_policy'), path: "/privacy", icon: "ri-shield-line" },
  { label: t('settings'), path: "/settings", icon: "ri-settings-line" },
  { label: t('language'), path: "/language", icon: "ri-global-line" },
  { label: t('logout'), path: "/logout", icon: "ri-logout-box-line" },
]);

// Computed property for menu items that includes theme toggle
const menuItems = computed(() => {
  if (!baseMenuItems.value) return [];
  
  const themeItem = {
    label: theme.value === 'dark' ? 'Light Mode' : 'Dark Mode',
    path: '#',
    icon: theme.value === 'dark' ? 'ri-sun-line' : 'ri-moon-line'
  };
  
  // Insert theme toggle before logout
  const items = [...baseMenuItems.value];
  items.splice(items.length - 1, 0, themeItem);
  return items;
});

// Organized menu sections for the redesigned side menu
const mainMenuItems = computed(() => {
  const items = [
    { label: t('profile'), path: `/${user.value?.username || ''}`, icon: "ri-user-line" },
    { label: t('subscriptions'), path: "/subscriptions", icon: "ri-vip-crown-line" },
    { label: t('lists'), path: "/dashboard/lists", icon: "ri-list-check" },
    { label: t('wallet'), path: "/wallet", icon: "ri-wallet-3-line" },
  ];
  
  // Add become creator for users
  if (user.value?.role === 'user') {
    items.push({ label: t('become_a_creator'), path: "/become-creator", icon: "ri-user-star-line" });
  }
  
  return items;
});

const creatorMenuItems = computed(() => {
  if (user.value?.role !== 'creator' && user.value?.role !== 'admin') return [];
  
  return [
    { label: t('creator_dashboard'), path: "/dashboard", icon: "ri-dashboard-line" },
    { label: t('my_media'), path: "/media", icon: "ri-gallery-line" },
    { label: t('scheduled_post_queue'), path: "/posts/scheduled", icon: "ri-time-line" },
    { label: t('earning_statistics'), path: "/dashboard/earnings", icon: "ri-money-dollar-circle-line" },
    { label: t('profile_statistics'), path: '/dashboard/statistics', icon: 'ri-line-chart-line' },
    { label: t('tracking_links'), path: '/dashboard/tracking-links', icon: 'ri-link' },
    { label: t('referrals'), path: '/referrals', icon: 'ri-user-add-line' },
  ];
});

const userMenuItems = computed(() => {
  if (user.value?.role !== 'user') return [];
  
  return [
    { label: t('bookmarks'), path: "/bookmarks", icon: "ri-bookmark-line" },
  ];
});

const settingsMenuItems = computed(() => {
  return [
    { label: t('settings'), path: "/settings", icon: "ri-settings-line" },
    { label: t('language'), path: "/language", icon: "ri-global-line" },
    { label: t('terms'), path: "/terms", icon: "ri-file-text-line" },
    { label: t('privacy_policy'), path: "/privacy", icon: "ri-shield-line" },
  ];
});

const filteredMenuItems = computed(() => {
  if (!user.value) return menuItems.value;

  const creatorOnlyItems = [
    t('media_collection'), 
    t('creator_dashboard'), 
    t('earning_statistics'), 
    t('profile_statistics'), 
    t('creator_guide'),
    t('tracking_links')
  ];

  if (user.value.role === 'admin') {
    return menuItems.value;
  } else if (user.value.role === 'creator') {
    return menuItems.value;
  } else if (user.value.role === 'user') {
    const userItems = menuItems.value.filter(item => !creatorOnlyItems.includes(item.label));
    const notificationsIndex = userItems.findIndex(item => item.label === t('notifications'));
    if (notificationsIndex !== -1) {
      userItems.splice(notificationsIndex + 1, 0, { label: t('become_a_creator'), path: "/become-creator", icon: "ri-user-star-line" });
    } else {
      userItems.push({ label: t('become_a_creator'), path: "/become-creator", icon: "ri-user-star-line" });
    }
    return userItems;
  }

  return menuItems.value;
});

const handleMenuClick = (navigate) => {
  navigate();
  closeMobileMenu();
};

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
  // Prevent body scroll when menu is open
  document.body.style.overflow = isMobileMenuOpen.value ? "hidden" : "";
};

const closeMobileMenu = () => {
  isMobileMenuOpen.value = false;
  document.body.style.overflow = "";
};

const toggleTheme = () => {
  setTheme(theme.value === 'dark' ? 'light' : 'dark');
};

const handleLogout = () => {
  // Call the logout method from the auth store
  authStore.logout();
  
  // Redirect to the auth page
  router.push('/auth');
};

const fetchUser = async () => {
  isLoading.value = true;
  try {
    const result = await authStore.checkAuth();
    
    // After user is authenticated, initialize notification service
    if (authStore.isAuthenticated) {
      notificationService.initialize();
      const settingsStore = useSettingsStore()
      settingsStore.fetchAccountSettings()
    } else if (!result.success && result.error === "Token validation failed") {
      // Token exists but validation failed - don't redirect, just show error
      console.error("Token validation failed:", result.error);
    }
  } catch (error) {
    console.error("Failed to fetch user:", error);
    // Only redirect if we're not already on auth page
    if (!isAuthRoute.value && !isAgeVerificationRoute.value) {
      router.push("/auth");
    }
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  // Only fetch user if not on auth or age verification routes
  if (!isAuthRoute.value && !isAgeVerificationRoute.value) {
    fetchUser();
  } else {
    // On auth or age verification routes, don't show loader
    isLoading.value = false;
  }
  
  // Mark all notifications as read when navigating to notifications page
  watch(
    () => route.path,
    (newPath) => {
      if (newPath === '/notifications') {
        notificationService.markAllAsRead();
      }
    }
  );
});

// Watch for route changes
watch(
  () => route.fullPath,
  (newPath, oldPath) => {
    // Only fetch user if we're not on auth route and the path actually changed
    if (!isAuthRoute.value && newPath !== oldPath) {
      fetchUser();
    }
  }
);

// Clean up on component unmount
onUnmounted(() => {
  document.body.style.overflow = "";
  closeMobileMenu();
  
  // Clean up notification service
  notificationService.cleanup();
});
</script>