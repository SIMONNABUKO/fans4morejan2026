<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark text-text-light-primary dark:text-text-dark-primary">
    <!-- Search Header -->
    <div class="sticky top-0 z-10 bg-background-light dark:bg-background-dark border-b border-border-light dark:border-border-dark">
      <div class="px-4 py-3">
        <div class="relative">
          <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-text-light-tertiary dark:text-text-dark-tertiary"></i>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search posts, hashtags, or users..."
            class="w-full pl-10 pr-3 py-2 bg-surface-light dark:bg-surface-dark rounded-full text-sm text-text-light-primary dark:text-text-dark-primary placeholder:text-text-light-tertiary dark:placeholder:text-text-dark-tertiary border border-border-light dark:border-border-dark focus:outline-none focus:ring-1 focus:ring-primary-light dark:focus:ring-primary-dark"
            @input="handleSearch"
            @keyup.enter="performSearch"
          />
          <button 
            v-if="searchQuery"
            @click="clearSearch"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-text-light-tertiary dark:text-text-dark-tertiary hover:text-text-light-primary dark:hover:text-text-dark-primary"
          >
            <i class="ri-close-line text-lg"></i>
          </button>
        </div>
      </div>

      <!-- Search Tabs -->
      <div class="flex items-center gap-2 px-4 pb-3">
        <button 
          v-for="tab in tabs" 
          :key="tab.id"
          @click="activeTab = tab.id"
          class="px-3 py-1 rounded-full text-sm transition-colors"
          :class="activeTab === tab.id ? 'bg-surface-light dark:bg-surface-dark text-text-light-primary dark:text-text-dark-primary' : 'text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary'"
        >
          {{ tab.label }}
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-primary-light dark:border-primary-dark"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="p-4 text-center text-red-500">
      {{ error }}
    </div>

    <!-- Search Results -->
    <div v-else>
      <!-- Posts Tab -->
      <div v-if="activeTab === 'posts'">
        <div v-if="searchQuery && posts.length === 0" class="p-4 text-center text-text-light-secondary dark:text-text-dark-secondary">
          No posts found for "{{ searchQuery }}"
        </div>
        <div v-else-if="!searchQuery" class="p-4 text-center text-text-light-secondary dark:text-text-dark-secondary">
          Enter a search term to find posts
        </div>
        <div v-else class="space-y-4">
          <Post 
            v-for="post in posts" 
            :key="post.id" 
            :post="post" 
          />
        </div>
      </div>

      <!-- Hashtags Tab -->
      <div v-if="activeTab === 'hashtags'">
        <div v-if="searchQuery && hashtags.length === 0" class="p-4 text-center text-text-light-secondary dark:text-text-dark-secondary">
          No hashtags found for "{{ searchQuery }}"
        </div>
        <div v-else-if="!searchQuery" class="p-4">
          <!-- Popular Hashtags -->
          <div class="mb-6">
            <h3 class="text-lg font-semibold mb-3 text-text-light-primary dark:text-text-dark-primary">Popular Hashtags</h3>
            <div class="grid grid-cols-2 gap-2">
              <button
                v-for="hashtag in popularHashtags"
                :key="hashtag.id"
                @click="searchByHashtag(hashtag.name)"
                class="p-3 bg-surface-light dark:bg-surface-dark rounded-lg text-left hover:bg-surface-light-secondary dark:hover:bg-surface-dark-secondary transition-colors"
              >
                <div class="text-primary-light dark:text-primary-dark font-medium">#{{ hashtag.name }}</div>
                <div class="text-sm text-text-light-secondary dark:text-text-dark-secondary">{{ hashtag.posts_count }} posts</div>
              </button>
            </div>
          </div>

          <!-- Trending Hashtags -->
          <div v-if="trendingHashtags.length > 0">
            <h3 class="text-lg font-semibold mb-3 text-text-light-primary dark:text-text-dark-primary">Trending</h3>
            <div class="grid grid-cols-2 gap-2">
              <button
                v-for="hashtag in trendingHashtags"
                :key="hashtag.id"
                @click="searchByHashtag(hashtag.name)"
                class="p-3 bg-surface-light dark:bg-surface-dark rounded-lg text-left hover:bg-surface-light-secondary dark:hover:bg-surface-dark-secondary transition-colors"
              >
                <div class="text-primary-light dark:text-primary-dark font-medium">#{{ hashtag.name }}</div>
                <div class="text-sm text-text-light-secondary dark:text-text-dark-secondary">{{ hashtag.posts_count }} posts</div>
              </button>
            </div>
          </div>
        </div>
        <div v-else class="space-y-2">
          <button
            v-for="hashtag in hashtags"
            :key="hashtag.id"
            @click="searchByHashtag(hashtag.name)"
            class="w-full p-3 bg-surface-light dark:bg-surface-dark rounded-lg text-left hover:bg-surface-light-secondary dark:hover:bg-surface-dark-secondary transition-colors"
          >
            <div class="text-primary-light dark:text-primary-dark font-medium">#{{ hashtag.name }}</div>
            <div class="text-sm text-text-light-secondary dark:text-text-dark-secondary">{{ hashtag.posts_count }} posts</div>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import Post from '@/components/posts/Post.vue'
import { useFeedStore } from '@/stores/feedStore'
import { useToast } from 'vue-toastification'
import axiosInstance from '@/axios'

const router = useRouter()
const route = useRoute()
const feedStore = useFeedStore()
const toast = useToast()

const searchQuery = ref('')
const activeTab = ref('posts')
const loading = ref(false)
const error = ref(null)
const posts = ref([])
const hashtags = ref([])
const popularHashtags = ref([])
const trendingHashtags = ref([])

const tabs = [
  { id: 'posts', label: 'Posts' },
  { id: 'hashtags', label: 'Hashtags' }
]

// Debounce search
let searchTimeout = null

const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    if (searchQuery.value.trim()) {
      performSearch()
    } else {
      clearResults()
    }
  }, 300)
}

const performSearch = async () => {
  if (!searchQuery.value.trim()) return

  // Don't search for queries that are too short
  if (searchQuery.value.trim().length < 2) {
    clearResults()
    return
  }

  loading.value = true
  error.value = null

  try {
    if (activeTab.value === 'posts') {
      const response = await axiosInstance.get('/search/posts', {
        params: { query: searchQuery.value }
      })
      posts.value = response.data.posts || []
    } else if (activeTab.value === 'hashtags') {
      const response = await axiosInstance.get('/search/hashtags/search', {
        params: { query: searchQuery.value }
      })
      hashtags.value = response.data.hashtags || []
    }
  } catch (err) {
    console.error('Search error:', err)
    error.value = 'Failed to perform search'
    toast.error('Search failed. Please try again.')
  } finally {
    loading.value = false
  }
}

const searchByHashtag = (hashtagName) => {
  searchQuery.value = `#${hashtagName}`
  activeTab.value = 'posts'
  performSearch()
}

const clearSearch = () => {
  searchQuery.value = ''
  clearResults()
}

const clearResults = () => {
  posts.value = []
  hashtags.value = []
  error.value = null
}

const loadPopularHashtags = async () => {
  try {
    const response = await axiosInstance.get('/search/hashtags/popular')
    popularHashtags.value = response.data.hashtags || []
  } catch (err) {
    console.error('Failed to load popular hashtags:', err)
  }
}

const loadTrendingHashtags = async () => {
  try {
    const response = await axiosInstance.get('/search/hashtags/trending')
    trendingHashtags.value = response.data.hashtags || []
  } catch (err) {
    console.error('Failed to load trending hashtags:', err)
  }
}

watch(activeTab, (newTab) => {
  if (newTab === 'hashtags' && !searchQuery.value) {
    loadPopularHashtags()
    loadTrendingHashtags()
  }
})

onMounted(() => {
  // Check if there's a query parameter
  if (route.query.q) {
    searchQuery.value = route.query.q
    performSearch()
  } else {
    loadPopularHashtags()
    loadTrendingHashtags()
  }
})
</script> 