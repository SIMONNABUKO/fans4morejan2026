<template>
  <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-gray-200/50 dark:border-gray-700/50">
    <!-- Header with Modern Design -->
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
          <i class="ri-user-star-line text-white text-lg"></i>
        </div>
        <div>
          <h2 class="text-xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
            {{ title }}
          </h2>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
            {{ currentPageProfiles.length }} {{ t('suggestions') }}
          </p>
        </div>
      </div>
      
      <!-- Enhanced Navigation Buttons -->
      <div class="flex items-center gap-2">
        <button
          @click="previousPage"
          :disabled="currentPage === 0"
          class="p-2 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed hover:scale-105"
        >
          <i class="ri-arrow-left-s-line text-xl"></i>
        </button>
        <button
          @click="nextPage"
          :disabled="currentPage >= totalPages - 1"
          class="p-2 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed hover:scale-105"
        >
          <i class="ri-arrow-right-s-line text-xl"></i>
        </button>
      </div>
    </div>

    <!-- Enhanced User Cards -->
    <div class="space-y-4">
              <div
          v-for="(profile, index) in currentPageProfiles"
          :key="profile.id"
          class="group relative bg-white/60 dark:bg-gray-700/60 backdrop-blur-sm rounded-xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 hover:scale-[1.02] animate-fadeInUp cursor-pointer hover:ring-2 hover:ring-primary-light/20 dark:hover:ring-primary-dark/20"
          :style="{ animationDelay: `${index * 100}ms` }"
          @click="navigateToProfile(profile)"
        >
        <!-- Cover Photo with Enhanced Overlay -->
        <div class="relative h-24 overflow-hidden">
          <img 
            :src="profile.cover_photo || '/default-cover.jpg'" 
            :alt="profile.name"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
          />
          <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
          
          <!-- Online Status Badge -->
          <div 
            v-if="profile.is_online"
            class="absolute top-3 right-3 bg-green-500 text-white text-xs px-2 py-1 rounded-full font-medium shadow-lg flex items-center gap-1"
          >
            <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
            {{ t('online') }}
          </div>
        </div>

        <!-- Profile Content -->
        <div class="p-4">
          <div class="flex items-start gap-3">
            <!-- Enhanced Avatar -->
            <div class="relative -mt-8">
              <div class="w-16 h-16 rounded-full border-4 border-white dark:border-gray-800 shadow-lg overflow-hidden bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-600 dark:to-gray-700">
                <img
                  :src="profile.avatar || '/default-avatar.png'"
                  :alt="profile.name"
                  class="w-full h-full object-cover"
                />
              </div>
              <div
                v-if="profile.is_online"
                class="absolute bottom-0 right-0 w-4 h-4 bg-green-500 rounded-full border-3 border-white dark:border-gray-800 shadow-lg"
              ></div>
            </div>

            <!-- User Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-1">
                <span 
                  class="font-bold text-gray-900 dark:text-white text-sm hover:text-primary-light dark:hover:text-primary-dark transition-colors duration-200 truncate cursor-pointer"
                >
                  {{ profile.name }}
                </span>
                <i
                  v-if="profile.role === 'admin'"
                  class="ri-verified-badge-fill text-blue-500 text-sm"
                ></i>
                <i
                  v-if="profile.role === 'creator'"
                  class="ri-star-fill text-yellow-500 text-sm"
                ></i>
              </div>
              
              <span 
                class="text-xs text-gray-500 dark:text-gray-400 hover:text-primary-light dark:hover:text-primary-dark transition-colors duration-200 block mb-2 cursor-pointer"
              >
                @{{ profile.handle }}
              </span>

              <!-- User Stats -->
              <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400 mb-3">
                <span class="flex items-center gap-1">
                  <i class="ri-user-follow-line"></i>
                  {{ profile.followers_count || 0 }}
                </span>
                <span class="flex items-center gap-1">
                  <i class="ri-image-line"></i>
                  {{ (profile.total_image_uploads || 0) + (profile.total_video_uploads || 0) }}
                </span>
                <span class="flex items-center gap-1">
                  <i class="ri-heart-line"></i>
                  {{ profile.total_likes_received || 0 }}
                </span>
              </div>

              <!-- Bio Preview -->
              <p v-if="profile.bio" class="text-xs text-gray-600 dark:text-gray-300 line-clamp-2 mb-3">
                {{ profile.bio }}
              </p>
            </div>

            <!-- Enhanced Follow Button -->
            <div class="flex-shrink-0">
              <button
                v-if="shouldShowFollowButton(profile)"
                @click.stop="toggleFollow(profile)"
                :disabled="feedStore.followingInProgress[profile.id]"
                :class="{
                  'bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 shadow-lg hover:shadow-xl': !profile.is_followed,
                  'bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 shadow-lg hover:shadow-xl': profile.is_followed
                }"
                class="px-4 py-2 text-white text-sm font-medium rounded-full transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed hover:scale-105 active:scale-95 flex items-center gap-2"
              >
                <i v-if="feedStore.followingInProgress[profile.id]" class="ri-loader-4-line animate-spin"></i>
                <i v-else-if="profile.is_followed" class="ri-user-unfollow-line"></i>
                <i v-else class="ri-user-follow-line"></i>
                {{ feedStore.followingInProgress[profile.id] ? t('processing') : (profile.is_followed ? t('unfollow') : t('follow')) }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Enhanced Pagination -->
    <div v-if="totalPages > 1" class="mt-6 flex justify-center gap-2">
      <div
        v-for="(_, index) in totalPages"
        :key="index"
        :class="{
          'bg-gradient-to-r from-primary-light to-primary-dark dark:from-primary-dark dark:to-primary-light': currentPage === index,
          'bg-gray-200 dark:bg-gray-600': currentPage !== index,
        }"
        class="h-2 transition-all duration-300 rounded-full cursor-pointer hover:scale-110"
        :style="currentPage === index ? 'width: 24px;' : 'width: 8px;'"
        @click="currentPage = index"
      ></div>
    </div>

    <!-- Empty State -->
    <div v-if="currentPageProfiles.length === 0" class="text-center py-8 animate-fadeIn">
      <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full mb-4">
        <i class="ri-user-search-line text-2xl text-gray-400"></i>
      </div>
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
        {{ t('no_suggestions_title', 'No Suggestions Yet') }}
      </h3>
      <p class="text-gray-500 dark:text-gray-400 text-sm">
        {{ t('no_suggestions_message', 'We\'ll show you creators to follow based on your interests') }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useFeedStore } from '@/stores/feedStore';
import { useToast } from 'vue-toastification';
import { useSettingsStore } from '@/stores/settingsStore';
import { useCreatorSettingsStore } from '@/stores/creatorSettingsStore';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const router = useRouter();
const toast = useToast();
const feedStore = useFeedStore();
const settingsStore = useSettingsStore();
const creatorSettingsStore = useCreatorSettingsStore();

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  profiles: {
    type: Array,
    required: true
  }
});

const currentPage = ref(0);
const itemsPerPage = 4;

const totalPages = computed(() => Math.ceil(props.profiles.length / itemsPerPage));

const currentPageProfiles = computed(() => {
  const start = currentPage.value * itemsPerPage;
  return props.profiles.slice(start, start + itemsPerPage);
});

const nextPage = () => {
  if (currentPage.value < totalPages.value - 1) {
    currentPage.value++;
  }
};

const previousPage = () => {
  if (currentPage.value > 0) {
    currentPage.value--;
  }
};

// Add computed property to check if follow button should be shown
const shouldShowFollowButton = (profile) => {
  // Only show follow button if:
  // 1. The profile is a creator
  // 2. The creator has enabled their follow button
  return profile.role === 'creator' && creatorSettingsStore.followButtonEnabled;
};

const navigateToProfile = (profile) => {
  // Navigate to user profile using username or handle
  const username = profile.username || profile.handle;
  if (username) {
    router.push({ name: 'userProfile', params: { username } });
  } else {
    console.error('No username or handle found for profile:', profile);
    toast.error(t('profile_not_found'));
  }
};

const toggleFollow = async (profile) => {
  if (profile.role !== 'creator') {
    toast.error(t('only_creators_can_be_followed'));
    return;
  }

  // Check if the creator has enabled their follow button
  if (!creatorSettingsStore.followButtonEnabled) {
    toast.error(t('creator_disabled_following'));
    return;
  }

  // Check if user's email is verified
  if (!settingsStore.account.is_email_verified) {
    toast.error(t('verify_email_before_following'));
    settingsStore.triggerEmailVerificationModal();
    return;
  }

  const action = profile.is_followed ? feedStore.unfollowUser : feedStore.followUser;
  const result = await action(profile.id);
  if (result.success) {
    toast.success(result.message);
  } else {
    toast.error(result.error);
  }
};
</script>

<style scoped>
/* Custom animations */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.animate-fadeInUp {
  animation: fadeInUp 0.6s ease-out;
}

.animate-fadeIn {
  animation: fadeIn 0.5s ease-out;
}

/* Line clamp utility */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Enhanced focus states */
button:focus-visible {
  outline: 2px solid currentColor;
  outline-offset: 2px;
}

/* Smooth transitions */
* {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}
</style>

