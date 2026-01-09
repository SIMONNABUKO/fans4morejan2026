<template>
  <div class="min-h-full">
    <div class="py-6 w-full">
      <div class="px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Content Moderation</h1>
      </div>

      <!-- Stats Overview -->
      <div class="mt-6 px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5">
          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Posts</dt>
              <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                <div v-if="postStore.statsLoading" class="animate-pulse h-8 w-16 bg-gray-200 dark:bg-gray-700 rounded"></div>
                <span v-else>{{ postStore.postStats.total }}</span>
              </dd>
            </div>
          </div>
          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Pending Review</dt>
              <dd class="mt-1 text-3xl font-semibold text-yellow-600 dark:text-yellow-400">
                <div v-if="postStore.statsLoading" class="animate-pulse h-8 w-16 bg-gray-200 dark:bg-gray-700 rounded"></div>
                <span v-else>{{ postStore.postStats.pending }}</span>
              </dd>
            </div>
          </div>
          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Published</dt>
              <dd class="mt-1 text-3xl font-semibold text-green-600 dark:text-green-400">
                <div v-if="postStore.statsLoading" class="animate-pulse h-8 w-16 bg-gray-200 dark:bg-gray-700 rounded"></div>
                <span v-else>{{ postStore.postStats.published }}</span>
              </dd>
            </div>
          </div>
          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Rejected</dt>
              <dd class="mt-1 text-3xl font-semibold text-red-600 dark:text-red-400">
                <div v-if="postStore.statsLoading" class="animate-pulse h-8 w-16 bg-gray-200 dark:bg-gray-700 rounded"></div>
                <span v-else>{{ postStore.postStats.rejected }}</span>
              </dd>
            </div>
          </div>
          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Reported</dt>
              <dd class="mt-1 text-3xl font-semibold text-orange-600 dark:text-orange-400">
                <div v-if="postStore.statsLoading" class="animate-pulse h-8 w-16 bg-gray-200 dark:bg-gray-700 rounded"></div>
                <span v-else>{{ postStore.postStats.reported }}</span>
              </dd>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="mt-6 px-4 sm:px-6 lg:px-8">
        <PostFilters
          :initial-filters="postStore.filters"
          @update:filters="postStore.updateFilters"
          @apply="postStore.fetchPosts"
        />
      </div>

      <!-- Content List -->
      <div class="mt-6 px-4 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
          <div class="px-4 sm:px-6 lg:px-8">
            <div class="sm:flex sm:items-center">
              <div class="sm:flex-auto">
                <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">A list of all posts including their title, author, status, and publication date.</p>
              </div>
            </div>
            <div class="mt-8 flow-root">
              <div v-if="postStore.loading" class="flex justify-center items-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
              </div>
              <div v-else-if="postStore.error" class="text-center py-8 text-red-600">
                {{ postStore.error }}
              </div>
              <div v-else>
                <!-- Posts Grid -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                  <div v-for="post in postStore.filteredPosts" :key="post.id" class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                    <!-- Post Header -->
                    <div class="px-4 py-5 sm:px-6 flex items-center space-x-3">
                      <img 
                        :src="post.user.avatar || '/default-avatar.png'" 
                        :alt="post.user.name"
                        class="h-10 w-10 rounded-full"
                      />
                      <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ post.user.name }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">@{{ post.user.username }}</p>
                      </div>
                      <div class="ml-auto">
                        <span :class="[
                          post.status === 'published' ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' :
                          post.status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300' :
                          post.status === 'rejected' ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300' :
                          'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300',
                          'inline-flex rounded-full px-2 text-xs font-semibold leading-5'
                        ]">{{ post.status }}</span>
                      </div>
                    </div>

                    <!-- Post Content -->
                    <div class="px-4 py-5 sm:p-6">
                      <div class="text-sm text-gray-900 dark:text-white" v-html="post.processed_content"></div>
                      
                      <!-- Media Preview -->
                      <div v-if="post.media && post.media.length > 0" class="mt-4 grid grid-cols-2 gap-2">
                        <div v-for="media in post.media.slice(0, 4)" :key="media.id" class="relative aspect-square">
                          <img 
                            v-if="media.type === 'image'" 
                            :src="media.previews[0]?.url || media.url" 
                            class="w-full h-full object-cover rounded-lg"
                            alt="Media preview"
                          />
                          <video 
                            v-else-if="media.type === 'video'" 
                            :src="media.previews[0]?.url || media.url"
                            class="w-full h-full object-cover rounded-lg"
                            controls
                          ></video>
                          <div v-if="post.media.length > 4 && media === post.media[3]" class="absolute inset-0 bg-black bg-opacity-50 rounded-lg flex items-center justify-center">
                            <span class="text-white text-lg font-medium">+{{ post.media.length - 4 }}</span>
                          </div>
                        </div>
                      </div>

                      <!-- Post Stats -->
                      <div class="mt-4 flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                        <div class="flex space-x-4">
                          <span class="flex items-center">
                            <i class="ri-heart-line mr-1"></i>
                            {{ post.stats.total_likes }}
                          </span>
                          <span class="flex items-center">
                            <i class="ri-chat-1-line mr-1"></i>
                            {{ post.stats.total_comments }}
                          </span>
                          <span class="flex items-center">
                            <i class="ri-bookmark-line mr-1"></i>
                            {{ post.stats.total_bookmarks }}
                          </span>
                        </div>
                        <div class="flex items-center">
                          <i class="ri-money-dollar-circle-line mr-1"></i>
                          {{ post.stats.total_tips }}
                        </div>
                      </div>

                      <!-- Tagged Users -->
                      <div v-if="post.tagged_users && post.tagged_users.length > 0" class="mt-4">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Tagged Users</h4>
                        <div class="flex flex-wrap gap-2">
                          <span 
                            v-for="user in post.tagged_users" 
                            :key="user.id"
                            :class="[
                              user.status === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300',
                              'inline-flex rounded-full px-2 py-1 text-xs font-medium'
                            ]"
                          >
                            @{{ user.username }}
                          </span>
                        </div>
                      </div>

                      <!-- Permissions -->
                      <div v-if="post.permission_sets && post.permission_sets.length > 0" class="mt-6 bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3 flex items-center">
                          <i class="ri-lock-line mr-2"></i>
                          Access Requirements
                        </h4>
                        <div class="space-y-3">
                          <div v-for="permission in post.permission_sets" :key="permission.id" class="flex items-start">
                            <div class="flex-1">
                              <div class="flex items-center">
                                <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium"
                                  :class="{
                                    'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300': permission.type === 'subscription',
                                    'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300': permission.type === 'tier',
                                    'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300': permission.type === 'purchase',
                                    'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300': permission.type === 'follow'
                                  }"
                                >
                                  <i class="mr-1" :class="{
                                    'ri-vip-crown-line': permission.type === 'subscription',
                                    'ri-medal-line': permission.type === 'tier',
                                    'ri-shopping-cart-line': permission.type === 'purchase',
                                    'ri-user-follow-line': permission.type === 'follow'
                                  }"></i>
                                  {{ permission.type.charAt(0).toUpperCase() + permission.type.slice(1) }}
                                </span>
                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ permission.value }}</span>
                              </div>
                              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ getPermissionDescription(permission) }}
                              </p>
                            </div>
                            <div v-if="post.purchases && post.purchases.length > 0" class="ml-4">
                              <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">
                                {{ post.purchases.length }} purchases
                              </span>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Post Actions -->
                    <div class="px-4 py-4 sm:px-6 bg-gray-50 dark:bg-gray-700">
                      <PostModeration
                        :post="post"
                        @moderate="handleModerate"
                        @view="handleView"
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="mt-6 px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
          <div class="flex-1 flex justify-between sm:hidden">
            <button
              @click="postStore.setPage(postStore.pagination.currentPage - 1)"
              :disabled="postStore.pagination.currentPage === 1"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
            >
              Previous
            </button>
            <button
              @click="postStore.setPage(postStore.pagination.currentPage + 1)"
              :disabled="postStore.pagination.currentPage === postStore.pagination.totalPages"
              class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
            >
              Next
            </button>
          </div>
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700 dark:text-gray-300">
                Showing
                <span class="font-medium">{{ ((postStore.pagination.currentPage - 1) * postStore.pagination.perPage) + 1 }}</span>
                to
                <span class="font-medium">{{ Math.min(postStore.pagination.currentPage * postStore.pagination.perPage, postStore.pagination.totalItems) }}</span>
                of
                <span class="font-medium">{{ postStore.pagination.totalItems }}</span>
                results
              </p>
            </div>
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <button
                  v-for="page in postStore.pagination.totalPages"
                  :key="page"
                  @click="postStore.setPage(page)"
                  :class="[
                    page === postStore.pagination.currentPage
                      ? 'z-10 bg-indigo-50 dark:bg-indigo-900/50 border-indigo-500 text-indigo-600 dark:text-indigo-300'
                      : 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600',
                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                  ]"
                >
                  {{ page }}
                </button>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { usePostStore } from '@/stores/post'
import PostFilters from '@/components/content/PostFilters.vue'
import PostModeration from '@/components/content/PostModeration.vue'
import type { Post } from '@/types/post'

const postStore = usePostStore()

onMounted(async () => {
  await postStore.fetchPosts()
})

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString()
}

interface ModerateEvent {
  post: Post;
  action: string;
}

const handleModerate = async ({ post, action }: ModerateEvent) => {
  try {
    if (action === 'review') {
      await postStore.reviewReport(post.id)
    } else {
      await postStore.moderatePost({ post, action })
    }
  } catch (error) {
    console.error('Failed to moderate post:', error)
  }
}

const handleView = (post: Post) => {
  // TODO: Implement post detail view
  console.log('View post:', post)
}

const getPermissionDescription = (permission: { type: string; value: string }) => {
  switch (permission.type) {
    case 'subscription':
      return 'Requires an active subscription to view this content'
    case 'tier':
      return `Available only to subscribers of ${permission.value} tier or higher`
    case 'purchase':
      return `One-time purchase required to access this content`
    case 'follow':
      return 'Must be following the creator to view this content'
    default:
      return ''
  }
}
</script>

<style scoped>
.content-view {
  @apply p-6;
}

.permission-icon {
  @apply mr-1.5 text-lg;
}
</style> 