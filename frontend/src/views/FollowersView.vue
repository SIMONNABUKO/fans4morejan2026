<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark">
    <!-- Header -->
    <ProfileHeader
      :avatar-url="user?.avatar"
      :cover-photo="user?.cover_photo"
      :username="user?.username"
      :handle="user?.handle"
      :status="userStatus"
      :stats="userStats"
      :bio="user?.bio"
      :role="user?.role"
      :social-links="user?.social_links"
      :last-seen-at="user?.last_seen_at"
      :can-be-followed="user?.can_be_followed"
    />

    <!-- Tabs -->
    <div class="flex">
      <button 
        @click="setActiveTab('followers')"
        class="flex-1 py-4 text-center relative"
        :class="[
          activeTab === 'followers' 
            ? 'text-text-light-primary dark:text-text-dark-primary font-semibold' 
            : 'text-text-light-secondary dark:text-text-dark-secondary'
        ]"
      >
        {{ t('followers') }}
        <div 
          class="absolute bottom-0 left-0 right-0 h-1 bg-primary-light dark:bg-primary-dark rounded-full transition-transform"
          :class="{ 'scale-x-100': activeTab === 'followers', 'scale-x-0': activeTab !== 'followers' }"
        ></div>
      </button>
      <button 
        @click="setActiveTab('following')"
        class="flex-1 py-4 text-center relative"
        :class="[
          activeTab === 'following' 
            ? 'text-text-light-primary dark:text-text-dark-primary font-semibold' 
            : 'text-text-light-secondary dark:text-text-dark-secondary'
        ]"
      >
        {{ t('following') }}
        <div 
          class="absolute bottom-0 left-0 right-0 h-1 bg-primary-light dark:bg-primary-dark rounded-full transition-transform"
          :class="{ 'scale-x-100': activeTab === 'following', 'scale-x-0': activeTab !== 'following' }"
        ></div>
      </button>
    </div>

    <!-- Search -->
    <div class="p-4 sticky top-[105px] z-10 bg-background-light dark:bg-background-dark border-b border-border-light dark:border-border-dark">
      <div class="relative">
        <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-text-light-secondary dark:text-text-dark-secondary"></i>
        <input 
          type="text"
          v-model="searchQuery"
          :placeholder="t(activeTab === 'followers' ? 'search_followers' : 'search_following')"
          class="w-full bg-surface-light dark:bg-surface-dark text-text-light-primary dark:text-text-dark-primary rounded-full py-2 pl-10 pr-4 border border-border-light dark:border-border-dark focus:outline-none focus:border-primary-light dark:focus:border-primary-dark"
          @input="handleSearch"
        >
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="followStore.loading" class="flex justify-center items-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-primary-light dark:border-primary-dark"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="followStore.error" class="text-center py-8 text-red-500">
      {{ followStore.error }}
    </div>

    <!-- Users List -->
    <div v-else class="divide-y divide-border-light dark:divide-border-dark">
      <div 
        v-for="user in filteredUsers" 
        :key="user.id"
        class="flex items-center justify-between p-4 hover:bg-surface-light dark:hover:bg-surface-dark transition-colors"
      >
        <div class="flex items-center gap-3">
          <div class="relative">
            <img 
              :src="user.avatar" 
              :alt="user.name"
              class="w-12 h-12 rounded-full object-cover"
            >
            <div 
              v-if="user.is_online"
              class="absolute bottom-0 right-0 w-3 h-3 bg-accent-success border-2 border-background-light dark:border-background-dark rounded-full"
            ></div>
          </div>
          <div>
            <div class="flex items-center gap-1">
              <button 
                @click="$router.push(`/${user.username}/posts`)"
                class="font-medium text-text-light-primary dark:text-text-dark-primary hover:text-primary-light dark:hover:text-primary-dark transition-colors cursor-pointer"
              >
                {{ user.name }}
              </button>
              <i v-if="user.role === 'admin'" class="ri-checkbox-circle-fill text-primary-light dark:text-primary-dark"></i>
            </div>
            <button 
              @click="$router.push(`/${user.username}/posts`)"
              class="text-sm text-text-light-secondary dark:text-text-dark-secondary hover:text-primary-light dark:hover:text-primary-dark transition-colors cursor-pointer block w-full text-left"
            >
              {{ user.handle }}
            </button>
          </div>
        </div>
        <button 
          v-if="user.is_following || user.can_be_followed"
          @click="toggleFollow(user)"
          class="px-6 py-1.5 rounded-full text-sm font-medium transition-colors"
          :class="[
            user.is_following
              ? 'bg-surface-light dark:bg-surface-dark text-text-light-primary dark:text-text-dark-primary border border-border-light dark:border-border-dark hover:border-red-500 hover:text-red-500'
              : 'bg-primary-light dark:bg-primary-dark text-white hover:bg-primary-light/90 dark:hover:bg-primary-dark/90'
          ]"
          :disabled="feedStore.followingInProgress[user.id]"
        >
          <span v-if="feedStore.followingInProgress[user.id]">
            <i class="ri-loader-4-line animate-spin mr-1"></i>
            {{ user.is_following ? t('unfollowing') : t('following_in_progress') }}
          </span>
          <span v-else>
            {{ user.is_following ? t('unfollow') : (activeTab === 'followers' ? 'Follow Back' : t('follow')) }}
          </span>
        </button>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="flex justify-center mt-4 space-x-2">
      <button
        v-for="page in totalPages"
        :key="page"
        @click="changePage(page)"
        class="px-3 py-1 rounded"
        :class="currentPage === page ? 'bg-primary-light dark:bg-primary-dark text-white' : 'bg-surface-light dark:bg-surface-dark'"
      >
        {{ page }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useFollowStore } from '@/stores/followStore'
import { useFeedStore } from '@/stores/feedStore'
import ProfileHeader from '@/components/user/ProfileHeader.vue'
import { useToast } from 'vue-toastification'
import { useSettingsStore } from '@/stores/settingsStore'
import { useI18n } from 'vue-i18n'

const route = useRoute()
const authStore = useAuthStore()
const followStore = useFollowStore()
const feedStore = useFeedStore()
const toast = useToast()
const activeTab = ref('followers')
const searchQuery = ref('')
const currentPage = ref(1)
const totalPages = ref(1)
const settingsStore = useSettingsStore()
const { t } = useI18n()

// Use the userId from the route param for the profile being viewed
const profileUserId = computed(() => route.params.username || authStore.user?.id)

const user = ref(null)

const fetchProfileUser = async () => {
  if (!profileUserId.value) return
  
  try {
    // Fetch user data for the profile being viewed (by username)
    user.value = await feedStore.fetchUserByUsername(profileUserId.value)
  } catch (error) {
    console.error('Error fetching profile user:', error)
    toast.error(t('failed_to_load_user_profile'))
  }
}

const setActiveTab = (tab) => {
  activeTab.value = tab
  currentPage.value = 1
  fetchUsers()
}

const fetchUsers = async () => {
  if (!profileUserId.value) return

  try {
    let response
    if (activeTab.value === 'followers') {
      response = await followStore.fetchFollowers(profileUserId.value, currentPage.value)
    } else {
      response = await followStore.fetchFollowing(profileUserId.value, currentPage.value)
    }
    totalPages.value = response.last_page
  } catch (error) {
    console.error(`Error fetching ${activeTab.value}:`, error)
    toast.error(`Failed to fetch ${activeTab.value}. Please try again.`)
  }
}

const userStatus = computed(() => user.value?.is_online ? 'Active Now' : 'Offline')

const userStats = computed(() => ({
  likes: user.value?.total_likes_received || 0,
  posts: user.value?.posts?.length || 0,
  followers: user.value?.followers_count || 0,
  media: (user.value?.total_video_uploads || 0) + (user.value?.total_image_uploads || 0)
}))

const users = computed(() => {
  return activeTab.value === 'followers' ? followStore.followers : followStore.following
})

const filteredUsers = computed(() => {
  return users.value.filter(user => {
    const searchLower = searchQuery.value.toLowerCase()
    return (
      user.name.toLowerCase().includes(searchLower) ||
      user.handle.toLowerCase().includes(searchLower)
    )
  })
})

const toggleFollow = async (toggledUser) => {
  if (!toggledUser.can_be_followed && !toggledUser.is_following) {
    toast.error(t('cannot_follow_user'))
    return
  }

  // Check if user's email is verified
  if (!settingsStore.account.is_email_verified) {
    toast.error(t('verify_email_before_following'))
    settingsStore.triggerEmailVerificationModal()
    return
  }

  try {
    let result
    if (toggledUser.is_following) {
      result = await feedStore.unfollowUser(toggledUser.id)
    } else {
      result = await feedStore.followUser(toggledUser.id)
    }

    if (result.success) {
      toast.success(t(toggledUser.is_following ? 'unfollow_success' : 'follow_success'))
      
      // Update the user's following status
      toggledUser.is_following = !toggledUser.is_following
      
      if (activeTab.value === 'following') {
        if (!toggledUser.is_following) {
          // Remove the user from the following list if unfollowed
          followStore.removeFromFollowing(toggledUser.id)
        } else {
          // Update the following status in the store
          followStore.updateFollowingStatus(toggledUser.id, true)
        }
      }
    } else {
      toast.error(t(toggledUser.is_following ? 'unfollow_error' : 'follow_error'))
    }
  } catch (error) {
    console.error('Error toggling follow:', error)
    toast.error(t(toggledUser.is_following ? 'unfollow_error' : 'follow_error'))
  }
}

const changePage = (page) => {
  currentPage.value = page
  fetchUsers()
}

onMounted(() => {
  fetchProfileUser()
  fetchUsers()
})

watch(() => profileUserId.value, () => {
  fetchProfileUser()
    fetchUsers()
})

</script>
