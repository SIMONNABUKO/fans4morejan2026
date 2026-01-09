<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark">
    <!-- Header with Back Navigation -->
    <header class="sticky top-0 z-30 bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-lg border-b border-border-light dark:border-border-dark">
      <div class="flex items-center justify-between px-4 py-4">
        <div class="flex items-center gap-3">
          <button 
            @click="goBack"
            class="p-2 -ml-2 rounded-full text-text-light-secondary dark:text-text-dark-secondary hover:bg-surface-light dark:hover:bg-surface-dark transition-colors"
          >
            <i class="ri-arrow-left-line text-xl"></i>
          </button>
          <div>
            <h1 class="text-lg font-semibold text-text-light-primary dark:text-text-dark-primary">
              {{ t('post') }}
            </h1>
            <p v-if="post" class="text-sm text-text-light-secondary dark:text-text-dark-secondary">
              {{ t('by') }} @{{ post.user.username }}
            </p>
          </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex items-center gap-2">
          <button
            v-if="post"
            @click="sharePost"
            class="p-2 text-text-light-secondary dark:text-text-dark-secondary hover:text-primary-light dark:hover:text-primary-dark bg-surface-light dark:bg-surface-dark hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors"
          >
            <i class="ri-share-line text-lg"></i>
          </button>
          <button
            v-if="post"
            @click="copyLink"
            class="p-2 text-text-light-secondary dark:text-text-dark-secondary hover:text-primary-light dark:hover:text-primary-dark bg-surface-light dark:bg-surface-dark hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors"
          >
            <i class="ri-link text-lg"></i>
          </button>
        </div>
      </div>
    </header>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center min-h-[60vh]">
      <div class="text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-light dark:border-primary-dark mx-auto mb-4"></div>
        <p class="text-text-light-secondary dark:text-text-dark-secondary">{{ t('loading_post') }}</p>
      </div>
    </div>
    
    <!-- Error State -->
    <div v-else-if="error" class="flex items-center justify-center min-h-[60vh]">
      <div class="text-center max-w-md mx-auto px-4">
        <div class="w-16 h-16 bg-red-100 dark:bg-red-900/20 rounded-full flex items-center justify-center mx-auto mb-4">
          <i class="ri-error-warning-line text-2xl text-red-500"></i>
        </div>
        <h3 class="text-lg font-semibold text-text-light-primary dark:text-text-dark-primary mb-2">
          {{ t('error_loading_post') }}
        </h3>
        <p class="text-text-light-secondary dark:text-text-dark-secondary mb-6">
          {{ error }}
        </p>
        <button 
          @click="fetchPost" 
          class="px-6 py-3 bg-primary-light dark:bg-primary-dark text-white rounded-full hover:bg-primary-dark dark:hover:bg-primary-light transition-colors font-medium"
        >
          <i class="ri-refresh-line mr-2"></i>
          {{ t('try_again') }}
        </button>
      </div>
    </div>

    <!-- Post Content -->
    <template v-else>
      <div v-if="post" class="max-w-4xl mx-auto">
        <!-- Main Post Card -->
        <div class="bg-background-light dark:bg-background-dark border-b border-border-light dark:border-border-dark">
          <Post 
            :post="post" 
            :needs-refresh="false"
            :is-single-post="true"
            @refresh="fetchPost"
          />
        </div>

        <!-- Comments Section -->
        <div class="bg-background-light dark:bg-background-dark">
          <div class="max-w-2xl mx-auto px-4 py-6">
            <!-- Comments Header -->
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-xl font-semibold text-text-light-primary dark:text-text-dark-primary">
                {{ t('comments') }} ({{ comments.length }})
              </h3>
              <button
                @click="openCommentModal"
                class="px-4 py-2 bg-primary-light dark:bg-primary-dark text-white rounded-full hover:bg-primary-dark dark:hover:bg-primary-light transition-colors font-medium text-sm"
              >
                <i class="ri-add-line mr-1"></i>
                {{ t('add_comment') }}
              </button>
            </div>

            <!-- Comments List -->
            <div v-if="comments.length > 0" class="space-y-4">
              <div 
                v-for="comment in comments" 
                :key="comment.id" 
                class="flex items-start gap-4 p-4 bg-surface-light dark:bg-surface-dark rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
              >
                <img 
                  :src="comment.user?.avatar || '/default-avatar.png'" 
                  :alt="comment.user?.username || 'User'" 
                  class="w-10 h-10 rounded-full object-cover flex-shrink-0"
                />
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 mb-1">
                    <span class="font-semibold text-text-light-primary dark:text-text-dark-primary">
                      {{ comment.user?.username || 'User' }}
                    </span>
                    <span class="text-xs text-text-light-secondary dark:text-text-dark-secondary">
                      {{ formatDate(comment.created_at) }}
                    </span>
                  </div>
                  <p class="text-text-light-primary dark:text-text-dark-primary text-sm leading-relaxed">
                    {{ comment.content }}
                  </p>
                  
                  <!-- Comment Actions -->
                  <div class="flex items-center gap-4 mt-3">
                    <button class="flex items-center gap-1 text-text-light-secondary dark:text-text-dark-secondary hover:text-primary-light dark:hover:text-primary-dark transition-colors text-sm">
                      <i class="ri-heart-line"></i>
                      <span>0</span>
                    </button>
                    <button class="flex items-center gap-1 text-text-light-secondary dark:text-text-dark-secondary hover:text-primary-light dark:hover:text-primary-dark transition-colors text-sm">
                      <i class="ri-reply-line"></i>
                      <span>{{ t('reply') }}</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Empty Comments State -->
            <div v-else class="text-center py-12">
              <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="ri-chat-1-line text-2xl text-gray-400"></i>
              </div>
              <h3 class="text-lg font-semibold text-text-light-primary dark:text-text-dark-primary mb-2">
                {{ t('no_comments_yet') }}
              </h3>
              <p class="text-text-light-secondary dark:text-text-dark-secondary mb-6">
                {{ t('be_first_to_comment') }}
              </p>
              <button
                @click="openCommentModal"
                class="px-6 py-3 bg-primary-light dark:bg-primary-dark text-white rounded-full hover:bg-primary-dark dark:hover:bg-primary-light transition-colors font-medium"
              >
                <i class="ri-add-line mr-2"></i>
                {{ t('add_comment') }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Post Not Found -->
      <div v-else class="flex items-center justify-center min-h-[60vh]">
        <div class="text-center max-w-md mx-auto px-4">
          <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="ri-file-damage-line text-2xl text-gray-400"></i>
          </div>
          <h3 class="text-lg font-semibold text-text-light-primary dark:text-text-dark-primary mb-2">
            {{ t('post_not_found') }}
          </h3>
          <p class="text-text-light-secondary dark:text-text-dark-secondary mb-6">
            {{ t('post_may_have_been_deleted') }}
          </p>
          <button 
            @click="goBack"
            class="px-6 py-3 bg-primary-light dark:bg-primary-dark text-white rounded-full hover:bg-primary-dark dark:hover:bg-primary-light transition-colors font-medium"
          >
            <i class="ri-arrow-left-line mr-2"></i>
            {{ t('go_back') }}
          </button>
        </div>
      </div>
    </template>

    <!-- Comment Modal -->
    <CommentModal
      :is-open="isCommentModalOpen"
      :post="post"
      :user-has-permission="post?.user_has_permission"
      :current-user="authStore.user"
      @close="closeCommentModal"
      @commentSubmitted="handleCommentSubmitted"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useFeedStore } from '@/stores/feedStore'
import { useAuthStore } from '@/stores/authStore'
import Post from '@/components/posts/Post.vue'
import CommentModal from '@/components/modals/CommentModal.vue'
import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toastification'

const route = useRoute()
const router = useRouter()
const feedStore = useFeedStore()
const authStore = useAuthStore()
const toast = useToast()
const { t } = useI18n()

const post = ref(null)
const loading = ref(true)
const error = ref(null)
const isCommentModalOpen = ref(false)

const fetchPost = async () => {
  loading.value = true
  error.value = null
  try {
    post.value = await feedStore.fetchSinglePost(route.params.id)
  } catch (err) {
    console.error('Error fetching post:', err)
    error.value = t('failed_to_load_post')
  } finally {
    loading.value = false
  }
}

const comments = computed(() => post.value?.comments || [])

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  const now = new Date()
  const diffInHours = Math.floor((now - date) / (1000 * 60 * 60))
  
  if (diffInHours < 1) {
    return t('just_now')
  } else if (diffInHours < 24) {
    return t('hours_ago', { hours: diffInHours })
  } else {
    const diffInDays = Math.floor(diffInHours / 24)
    return t('days_ago', { days: diffInDays })
  }
}

const goBack = () => {
  if (window.history.length > 1) {
    router.back()
  } else {
    router.push('/')
  }
}

const sharePost = async () => {
  if (navigator.share) {
    try {
      await navigator.share({
        title: `${post.value.user.username}'s post`,
        text: post.value.content,
        url: window.location.href
      })
    } catch (err) {
      console.log('Share cancelled')
    }
  } else {
    copyLink()
  }
}

const copyLink = async () => {
  try {
    await navigator.clipboard.writeText(window.location.href)
    toast.success(t('link_copied'))
  } catch (err) {
    console.error('Failed to copy link:', err)
    toast.error(t('failed_to_copy_link'))
  }
}

const openCommentModal = () => {
  if (!authStore.user) {
    toast.error(t('please_login_to_comment'))
    return
  }
  isCommentModalOpen.value = true
}

const closeCommentModal = () => {
  isCommentModalOpen.value = false
}

const handleCommentSubmitted = async () => {
  closeCommentModal()
  // Refresh the post to get updated comments
  await fetchPost()
  toast.success(t('comment_added_successfully'))
}

onMounted(() => {
  fetchPost()
})

watch(() => route.params.id, (newId, oldId) => {
  if (newId !== oldId) {
    fetchPost()
  }
})
</script>

<style scoped>
/* Custom scrollbar for comments */
.comments-container::-webkit-scrollbar {
  width: 6px;
}

.comments-container::-webkit-scrollbar-track {
  background: transparent;
}

.comments-container::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.3);
  border-radius: 3px;
}

.comments-container::-webkit-scrollbar-thumb:hover {
  background: rgba(156, 163, 175, 0.5);
}
</style> 