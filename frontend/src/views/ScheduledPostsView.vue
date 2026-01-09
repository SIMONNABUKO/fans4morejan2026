<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark">
    <!-- Modern Header -->
    <header class="sticky top-0 z-20 bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-lg border-b border-border-light dark:border-border-dark shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20 py-6">
          <!-- Left Side: Navigation and Title -->
          <div class="flex items-center gap-4">
            <router-link 
              to="/dashboard" 
              class="p-2 rounded-lg bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/20 dark:border-gray-700/50 text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary hover:bg-white/70 dark:hover:bg-gray-800/70 transition-all duration-200 hover:scale-105 shadow-sm hover:shadow-md"
            >
              <i class="ri-arrow-left-line text-lg"></i>
            </router-link>
            
            <div class="flex flex-col">
              <h1 class="text-2xl font-bold text-text-light-primary dark:text-text-dark-primary leading-tight">
                {{ t('scheduled_post_queue') }}
              </h1>
              <p class="text-text-light-secondary dark:text-text-dark-secondary text-sm mt-0.5">
                Manage your scheduled content and posts
              </p>
            </div>
          </div>
          
          <!-- Right Side: Quick Stats -->
          <div class="hidden md:flex items-center gap-6">
            <div class="text-right">
              <div class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Scheduled</div>
              <div class="text-lg font-bold text-primary-light dark:text-primary-dark">{{ scheduledPostsStore.sortedPosts.length }}</div>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Loading State -->
      <div v-if="scheduledPostsStore.loading && !scheduledPostsStore.hasPosts" class="flex justify-center items-center py-16">
        <div class="flex flex-col items-center gap-4">
          <div class="animate-spin rounded-full h-12 w-12 border-4 border-primary-light dark:border-primary-dark border-t-transparent"></div>
          <p class="text-text-light-secondary dark:text-text-dark-secondary">{{ t('loading') }}</p>
        </div>
      </div>

      <!-- Error States -->
      <div v-if="scheduledPostsStore.error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl p-6 mb-6">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center">
            <i class="ri-error-warning-line text-red-600 dark:text-red-400 text-lg"></i>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-red-900 dark:text-red-100">Error Loading Posts</h3>
            <p class="text-red-600 dark:text-red-400">{{ scheduledPostsStore.error }}</p>
          </div>
        </div>
      </div>

      <div v-if="actionError" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl p-6 mb-6">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center">
            <i class="ri-error-warning-line text-red-600 dark:text-red-400 text-lg"></i>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-red-900 dark:text-red-100">Action Failed</h3>
            <p class="text-red-600 dark:text-red-400">{{ actionError }}</p>
          </div>
        </div>
      </div>

      <!-- Posts Content -->
      <template v-if="scheduledPostsStore.hasPosts">
        <div class="space-y-6">
          <ScheduledPostCard
            v-for="post in scheduledPostsStore.sortedPosts"
            :key="post.id"
            :post="post"
            @deleted="deleteScheduledPost"
            @updated="updateScheduledPost"
          />
        </div>
      </template>

      <!-- Empty State -->
      <div v-else class="flex flex-col items-center justify-center py-16 text-center">
        <div class="w-20 h-20 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-6">
          <i class="ri-time-line text-3xl text-gray-400 dark:text-gray-500"></i>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">{{ t('no_scheduled_posts') }}</h3>
        <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md">
          You haven't scheduled any posts yet. Create your first scheduled post to start planning your content.
        </p>
        <router-link 
          to="/create-post"
          class="inline-flex items-center gap-2 px-6 py-3 bg-primary-light dark:bg-primary-dark text-white rounded-lg font-semibold hover:bg-primary-light/90 dark:hover:bg-primary-dark/90 transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl"
        >
          <i class="ri-add-line"></i>
          Create Scheduled Post
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useScheduledPostsStore } from '@/stores/scheduledPostsStore';
import ScheduledPostCard from '@/components/posts/ScheduledPostCard.vue';
import axiosInstance from '@/axios';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const scheduledPostsStore = useScheduledPostsStore();
const actionError = ref(null);

onMounted(() => {
  scheduledPostsStore.fetchScheduledPosts();
});

async function deleteScheduledPost(postId) {
  actionError.value = null;
  try {
    await axiosInstance.delete(`/posts/${postId}`);
    scheduledPostsStore.posts = scheduledPostsStore.posts.filter(p => p.id !== postId);
  } catch (err) {
    actionError.value = t('failed_to_delete_post');
    console.error(err);
  }
}

async function updateScheduledPost(updatedPost) {
  actionError.value = null;
  try {
    const { id, content, scheduled_for } = updatedPost;
    const response = await axiosInstance.put(`/posts/${id}`, { content, scheduled_for });
    // Use the updated post from the server
    const newPost = response.data.data || response.data;
    const idx = scheduledPostsStore.posts.findIndex(p => p.id === id);
    if (idx !== -1) scheduledPostsStore.posts[idx] = newPost;
  } catch (err) {
    actionError.value = t('failed_to_update_post');
    console.error(err);
  }
}
</script> 