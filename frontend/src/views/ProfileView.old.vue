<template>
  <div class="max-w-2xl mx-auto">
    <div v-if="loading" class="p-4 text-center">
      <p class="text-lg">{{ t('loading_profile') }}</p>
    </div>
    <div v-else-if="error" class="p-4 text-center">
      <p class="text-lg text-red-500">{{ error }}</p>
    </div>
    <div v-else>
      <ProfileHeader
        :avatar-url="user.avatar || ''"
        :cover-photo="user.cover_photo || ''"
        :username="user.name || ''"
        :handle="user.handle || ''"
        :status="userStatus"
        :stats="userStats"
        :bio="user.bio || ''"
        :role="user.role || 'user'"
        :social-links="user.social_links || {}"
        :last-seen-at="user.last_seen_at || ''"
        :can-be-followed="user.can_be_followed || false"
        :bio_color="user.bio_color || '#222222'"
        :bio_font="user.bio_font || 'inherit'"
        @update:profile="handleProfileUpdate"
      />

      <SubscriptionTiers />

      <ProfileTabs v-model="activeTab" />

      <TimelineSearch />

      <!-- Posts Section -->
      <div v-if="activeTab === 'posts'" class="divide-y divide-border">
        <UserPost 
          v-for="post in user.posts" 
          :key="post.id" 
          :post="post"
          @edit="handleEditPost"
        />
      </div>

      <!-- Media Section -->
      <div v-else-if="activeTab === 'media'" class="grid grid-cols-3 gap-2 p-0">
        <div v-for="media in user.media" :key="media.id" class="aspect-square overflow-hidden rounded-lg">
          <img :src="media.url" :alt="media.type" class="w-full h-full object-cover" />
        </div>
      </div>

      <!-- Post Edit Modal -->
      <MobilePostModal
        v-if="selectedPost"
        :is-open="showEditModal"
        :post="selectedPost"
        :edit-mode="true"
        @close="closeEditModal"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { useUploadStore } from '@/stores/uploadStore'
import ProfileHeader from '@/components/user/ProfileHeader.vue'
import ProfileTabs from '@/components/user/ProfileTabs.vue'
import SubscriptionTiers from '@/components/user/SubscriptionTiers.vue'
import TimelineSearch from '@/components/user/TimelineSearch.vue'
import UserPost from '@/components/user/UserPost.vue'
import MobilePostModal from '@/components/posts/MobilePostModal.vue'
import axiosInstance from '@/axios'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const authStore = useAuthStore()
const uploadStore = useUploadStore()
const activeTab = ref('posts')
const showEditModal = ref(false)
const selectedPost = ref(null)
const loading = ref(true)
const error = ref(null)

// Local profile user state
const profileUser = ref(null)

const fetchProfile = async () => {
  loading.value = true
  error.value = null
  try {
    let url
    if (authStore.user?.username) {
      url = `/users/${encodeURIComponent(authStore.user.username)}`
      const { data } = await axiosInstance.get(url)
      profileUser.value = data.data || data
    } else {
      // fallback: fetch current user
      await authStore.fetchCurrentUser()
      profileUser.value = authStore.user
    }
  } catch (err) {
    error.value = t('failed_to_load_profile')
    profileUser.value = null
  } finally {
    loading.value = false
  }
}

const user = computed(() => profileUser.value || authStore.user || {
  name: '',
  handle: '',
  avatar: '',
  cover_photo: '',
  bio: '',
  role: 'user',
  social_links: {},
  last_seen_at: '',
  can_be_followed: false,
  posts: [],
  media: [],
  total_likes_received: 0,
  total_followers: 0,
  total_video_uploads: 0,
  total_image_uploads: 0
})

const userStatus = computed(() => user.value.is_online ? t('active_now') : t('offline'))

const userStats = computed(() => ({
  likes: user.value.total_likes_received || 0,
  posts: user.value.posts?.length || 0,
  followers: user.value.total_followers || 0,
  media: (user.value.total_video_uploads || 0) + (user.value.total_image_uploads || 0)
}))

const handleEditPost = async (post) => {
  try {
    // Fetch the latest post data with all relationships
    const response = await axiosInstance.get(`/posts/${post.id}`);
    const postData = response.data.data || response.data;

    // Initialize the upload store with the post data and context
    const contextId = `edit-post-${postData.id}`;
    uploadStore.initializeFromPost(postData, contextId);
    uploadStore.setContext(contextId);

    selectedPost.value = postData;
    showEditModal.value = true;
  } catch (err) {
    // Handle error (show toast, etc.)
    console.error(t('failed_to_load_post_for_editing'), err);
  }
}

const closeEditModal = () => {
  selectedPost.value = null;
  showEditModal.value = false;
};

onMounted(async () => {
  await fetchProfile()
})
</script>