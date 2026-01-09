<template>
  <div class="space-y-6">
    <div class="grid lg:grid-cols-[1fr,400px] gap-6">
      <div class="space-y-6">
        <UserSuggestions
          :title="t('who_to_follow')"
          :profiles="feedStore.suggestedUsers"
          class="mb-4"
        />
        <PostComposer />
        <Navigation />

        <!-- Posts Section -->
        <section class="mt-6 space-y-6">
          <!-- New Posts Notification -->
          <div 
            v-if="feedStore.hasNewPosts" 
            class="sticky top-4 z-10 bg-blue-500 text-white rounded-full py-2 px-4 flex items-center justify-center gap-2 cursor-pointer hover:bg-blue-600 transition-colors shadow-md"
            @click="loadNewPosts"
          >
            <div class="flex -space-x-2 mr-1">
              <img 
                v-for="(info, index) in displayedPostInfo" 
                :key="info.id"
                :src="info.avatar" 
                :alt="info.username"
                class="w-6 h-6 rounded-full border-2 border-blue-500"
                :style="{ zIndex: displayedPostInfo.length - index }"
              />
            </div>
            <span>{{ feedStore.newPostsCount }} {{ feedStore.newPostsCount === 1 ? t('new_post') : t('new_posts') }}</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
            </svg>
          </div>

          <div v-if="feedStore.loading && !feedStore.hasPosts" class="text-center py-4">
            {{ t('loading_posts') }}
          </div>
          <div v-else-if="feedStore.error" class="text-center py-4 text-red-500">
            {{ feedStore.error }}
          </div>
          <template v-else-if="feedStore.hasPosts">
            <div v-for="post in feedStore.posts" :key="post.id">
              <Post :post="post" />
            </div>
            <button 
              v-if="feedStore.hasMorePages"
              @click="loadMorePosts"
              class="w-full py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors"
            >
              {{ t('load_more') }}
            </button>
          </template>
          <div v-else class="text-center py-4">
            {{ t('no_posts_available') }}
          </div>
        </section>
      </div>
      <UserSuggestions
        :title="t('suggestions')"
        :profiles="feedStore.suggestedUsers"
        class="hidden lg:block"
      />
    </div>
  </div>
</template>

<script setup>
import { onMounted, computed, watch } from "vue";
import { useFeedStore } from "@/stores/feedStore";
import { useFeedFilterStore } from "@/stores/feedFilterStore";
import PostComposer from "@/components/posts/Create.vue";
import Navigation from "@/components/Navigation.vue";
import UserSuggestions from "@/components/UserSuggestions.vue";
import Post from "@/components/posts/Post.vue";
import { useWebSocketService } from "@/services/websocketService";
import { useI18n } from 'vue-i18n';


const { t } = useI18n();
const feedStore = useFeedStore();
const feedFilterStore = useFeedFilterStore();
const websocketService = useWebSocketService();

// Only show up to 3 avatars in the new posts indicator
const displayedPostInfo = computed(() => {
  return feedStore.newPostsInfo.slice(0, 3);
});

// Helper to generate a random index within a chunk of 6
function getRandomIndexInChunk(chunkSize) {
  // Only allow between 1 and 5 (not at start or end)
  return Math.floor(Math.random() * 5) + 1;
}

onMounted(async () => {
  await feedFilterStore.fetchUserFilterPreferences();
  if (feedFilterStore.activeFilter.startsWith('list_')) {
    await feedStore.fetchFilteredFeed(feedFilterStore.activeFilter);
  } else {
    await feedStore.fetchFeed(feedFilterStore.activeFilter);
  }
});

// Watch for filter changes and refresh feed
watch(() => feedFilterStore.activeFilter, async (newFilter, oldFilter) => {
  console.log('👁️ Filter watcher triggered!')
  console.log('👁️ Old filter:', oldFilter)
  console.log('👁️ New filter:', newFilter)
  console.log('👁️ Filter starts with list_?', newFilter.startsWith('list_'))
  
  if (newFilter.startsWith('list_')) {
    console.log('👁️ Calling fetchFilteredFeed from watcher')
    await feedStore.fetchFilteredFeed(newFilter);
  } else {
    console.log('👁️ Calling fetchFeed from watcher')
    await feedStore.fetchFeed(newFilter);
  }
  console.log('👁️ Filter watcher completed')
});

const loadMorePosts = async () => {
  if (feedFilterStore.activeFilter.startsWith('list_')) {
    await feedStore.fetchFilteredFeed(feedFilterStore.activeFilter);
  } else {
    await feedStore.fetchFeed(feedFilterStore.activeFilter);
  }
};

const loadNewPosts = async () => {
  await feedStore.fetchNewPosts();
};

const refreshPost = async (postId) => {
  await feedStore.refreshPost(postId);
};


</script>

<style scoped>
/* Add any scoped styles here */
</style>