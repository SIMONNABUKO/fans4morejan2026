<template>
  <article class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 mb-4">
    <header class="flex items-start justify-between mb-4">
      <div class="flex items-center">
        <img :src="post.user.avatar" :alt="post.user.username" class="w-10 h-10 rounded-full mr-3">
        <div>
          <button 
            @click="$router.push(`/${post.user.username}/posts`)"
            class="font-semibold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors cursor-pointer"
          >
            {{ post.user.username }}
          </button>
          <p class="text-sm text-gray-500 dark:text-gray-400">{{ formatDate(post.created_at) }}</p>
        </div>
      </div>
      <button class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300" aria-label="Post options">
        <i class="ri-more-2-fill text-xl"></i>
      </button>
    </header>

    <div class="mb-4">
      <p class="text-gray-700 dark:text-gray-300">{{ post.content }}</p>
    </div>

    <PostMediaGrid v-if="post.media && post.media.length > 0" :media="post.media" />

    <footer class="mt-4 flex items-center justify-between">
      <div class="flex space-x-4">
        <button @click="handleLike" class="flex items-center text-gray-500 hover:text-blue-500 transition-colors" :class="{ 'text-blue-500': post.is_liked }">
          <i class="ri-heart-line mr-1" :class="{ 'ri-heart-fill': post.is_liked }"></i>
          <span>{{ post.likes_count }}</span>
          <span class="sr-only">Likes</span>
        </button>
        <button class="flex items-center text-gray-500 hover:text-blue-500 transition-colors">
          <i class="ri-chat-1-line mr-1"></i>
          <span>{{ post.comments_count }}</span>
          <span class="sr-only">Comments</span>
        </button>
        <button class="flex items-center text-gray-500 hover:text-blue-500 transition-colors">
          <i class="ri-share-line mr-1"></i>
          <span class="sr-only">Share</span>
        </button>
      </div>
      <button @click="handleTip" class="flex items-center text-gray-500 hover:text-green-500 transition-colors">
        <i class="ri-money-dollar-circle-line mr-1"></i>
        <span>Tip</span>
      </button>
    </footer>
  </article>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';
import PostMediaGrid from '@/components/posts/PostMediaGrid.vue';

const props = defineProps({
  post: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['like', 'tip']);

const formatDate = (dateString) => {
  const options = { year: 'numeric', month: 'short', day: 'numeric' };
  return new Date(dateString).toLocaleDateString(undefined, options);
};

const handleLike = () => {
  emit('like', props.post.id);
};

const handleTip = () => {
  emit('tip', props.post.id);
};
</script>