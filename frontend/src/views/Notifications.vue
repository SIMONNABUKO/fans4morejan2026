<template>
  <div class="min-h-screen w-full bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
    <!-- Header with Glassmorphism -->
    <header class="sticky top-0 z-20 bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl border-b border-gray-200/50 dark:border-gray-700/50 shadow-sm">
      <div class="px-4 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <router-link 
            to="/dashboard" 
            class="p-2 -ml-2 rounded-full text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-200 hover:scale-105"
            aria-label="Go back"
          >
            <i class="ri-arrow-left-line text-xl"></i>
          </router-link>
          <div>
            <h1 class="text-xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
              {{ t('notifications') }}
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
              {{ notificationsStore.unreadCount }} {{ t('unread') }}
            </p>
          </div>
        </div>
        <button 
          class="px-4 py-2 bg-gradient-to-r from-primary-light to-primary-dark dark:from-primary-dark dark:to-primary-light text-white rounded-full text-sm font-medium shadow-lg hover:shadow-xl hover:scale-105 active:scale-95 transition-all duration-200"
          @click="notificationsStore.toggleFilters"
          aria-label="Toggle filters"
        >
          <i class="ri-filter-3-line mr-1"></i>
          {{ t('select_filters') }}
        </button>
      </div>
      
      <!-- Enhanced Filters with Animation -->
      <div v-if="notificationsStore.showFilters" 
           class="px-4 py-4 border-t border-gray-200/50 dark:border-gray-700/50 animate-slideDown">
        <div class="flex flex-wrap gap-3">
          <button 
            @click="notificationsStore.setFilter('all')" 
            :class="[
              'px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 hover:scale-105 active:scale-95',
              notificationsStore.activeFilter === 'all' 
                ? 'bg-gradient-to-r from-primary-light to-primary-dark dark:from-primary-dark dark:to-primary-light text-white shadow-lg' 
                : 'bg-white/80 dark:bg-gray-800/80 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 shadow-sm'
            ]"
          >
            <i class="ri-notification-line mr-1"></i>
            {{ t('all') }}
          </button>
          <button 
            @click="notificationsStore.setFilter('follow')" 
            :class="[
              'px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 hover:scale-105 active:scale-95',
              notificationsStore.activeFilter === 'follow' 
                ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg' 
                : 'bg-white/80 dark:bg-gray-800/80 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 shadow-sm'
            ]"
          >
            <i class="ri-user-follow-line mr-1"></i>
            {{ t('follows') }}
          </button>
          <button 
            @click="notificationsStore.setFilter('like')" 
            :class="[
              'px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 hover:scale-105 active:scale-95',
              notificationsStore.activeFilter === 'like' 
                ? 'bg-gradient-to-r from-red-500 to-red-600 text-white shadow-lg' 
                : 'bg-white/80 dark:bg-gray-800/80 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 shadow-sm'
            ]"
          >
            <i class="ri-heart-line mr-1"></i>
            {{ t('likes') }}
          </button>
          <button 
            @click="notificationsStore.setFilter('tag')" 
            :class="[
              'px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 hover:scale-105 active:scale-95',
              notificationsStore.activeFilter === 'tag' 
                ? 'bg-gradient-to-r from-green-500 to-green-600 text-white shadow-lg' 
                : 'bg-white/80 dark:bg-gray-800/80 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 shadow-sm'
            ]"
          >
            <i class="ri-price-tag-3-line mr-1"></i>
            {{ t('tags') }}
          </button>
          <button 
            @click="notificationsStore.setFilter('unread')" 
            :class="[
              'px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 hover:scale-105 active:scale-95',
              notificationsStore.activeFilter === 'unread' 
                ? 'bg-gradient-to-r from-purple-500 to-purple-600 text-white shadow-lg' 
                : 'bg-white/80 dark:bg-gray-800/80 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 shadow-sm'
            ]"
          >
            <i class="ri-mail-unread-line mr-1"></i>
            {{ t('unread') }}
          </button>
        </div>
      </div>
    </header>

    <!-- Content Container -->
    <div class="w-full max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Mark all as read button -->
      <div v-if="notificationsStore.filteredNotifications.length > 0 && notificationsStore.hasUnreadNotifications" 
           class="mb-6 flex justify-end animate-fadeIn">
        <button 
          @click="notificationsStore.markAllAsRead" 
          class="px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-full text-sm font-medium shadow-lg hover:shadow-xl hover:scale-105 active:scale-95 transition-all duration-200"
          :disabled="notificationsStore.markingAllAsRead"
          aria-label="Mark all as read"
        >
          <i class="ri-check-double-line mr-1"></i>
          {{ t('mark_all_as_read') }}
        </button>
      </div>
      
      <!-- Loading State -->
      <div v-if="notificationsStore.loading" class="text-center py-12 animate-fadeIn">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-primary-light border-t-transparent shadow-lg"></div>
        <p class="mt-4 text-gray-600 dark:text-gray-400 font-medium">{{ t('loading_notifications') }}</p>
      </div>

      <!-- Error State -->
      <div v-else-if="notificationsStore.error" class="text-center py-12 animate-fadeIn">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 dark:bg-red-900/20 rounded-full mb-4">
          <i class="ri-error-warning-line text-3xl text-red-500"></i>
        </div>
        <p class="text-red-600 dark:text-red-400 font-medium mb-4">{{ notificationsStore.error }}</p>
        <button @click="notificationsStore.fetchNotifications" 
                class="px-6 py-3 bg-gradient-to-r from-primary-light to-primary-dark dark:from-primary-dark dark:to-primary-light text-white rounded-full font-medium shadow-lg hover:shadow-xl hover:scale-105 active:scale-95 transition-all duration-200">
          <i class="ri-refresh-line mr-2"></i>
          {{ t('try_again') }}
        </button>
      </div>

      <!-- Notifications List -->
      <div v-else class="space-y-4">
        <!-- Notification Items -->
        <div 
          v-for="(notification, index) in notificationsStore.filteredNotifications" 
          :key="notification.id" 
          class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-[1.02] animate-fadeInUp cursor-pointer"
          :style="{ animationDelay: `${index * 50}ms` }"
          :class="{ 
            'ring-2 ring-blue-500/20 bg-blue-50/80 dark:bg-blue-900/20': !notification.read,
            'border-l-4 border-l-blue-500': !notification.read
          }"
          @click="handleNotificationClick(notification, $event)"
        >
          <div class="flex gap-4">
            <!-- Notification Icon -->
            <div class="flex-shrink-0">
              <div :class="getIconClass(notification)" 
                   class="w-12 h-12 rounded-full flex items-center justify-center shadow-lg relative overflow-hidden">
                <i :class="getIconName(notification)" class="text-white text-xl relative z-10"></i>
                <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent"></div>
              </div>
            </div>

            <!-- Notification Content -->
            <div class="flex-1 min-w-0">
              <div class="flex justify-between items-start mb-2">
                <div class="flex items-center gap-2">
                  <span v-if="['creator_application_approved', 'creator_application_rejected'].includes(notification.data.type)" 
                        class="font-bold text-gray-900 dark:text-white">
                    Fans4more
                    <i class="ri-verified-badge-fill text-primary-light dark:text-primary-dark ml-1"></i>
                  </span>
                  <router-link 
                    v-else
                    :to="`/${getNotificationUsernameForRoute(notification)}/posts`"
                    class="font-bold text-gray-900 dark:text-white hover:text-primary-light dark:hover:text-primary-dark transition-colors duration-200 cursor-pointer"
                    @click.stop
                  >
                    {{ getNotificationUsername(notification) }}
                  </router-link>
                  <router-link 
                    v-if="!['creator_application_approved', 'creator_application_rejected'].includes(notification.data.type)"
                    :to="`/${getNotificationUsernameForRoute(notification)}/posts`"
                    class="text-gray-500 dark:text-gray-400 text-sm hover:text-primary-light dark:hover:text-primary-dark transition-colors duration-200 cursor-pointer"
                    @click.stop
                  >
                    @{{ getNotificationHandle(notification) }}
                  </router-link>
                  <i v-if="getNotificationVerified(notification)" 
                     class="ri-checkbox-circle-fill text-blue-500 text-sm"></i>
                </div>
                <div class="flex items-center gap-2">
                  <span v-if="!notification.read" 
                        class="w-3 h-3 bg-blue-500 rounded-full animate-pulse shadow-lg"></span>
                  <span class="text-gray-500 dark:text-gray-400 text-sm font-medium">
                    {{ formatTime(notification.created_at) }}
                  </span>
                </div>
              </div>

              <!-- Notification Message -->
              <p class="text-gray-700 dark:text-gray-300 mb-3 leading-relaxed">
                {{ notification.data.message }}
              </p>

              <!-- Creator Application Feedback -->
              <div v-if="['creator_application_approved', 'creator_application_rejected'].includes(notification.data.type) && notification.data.feedback" 
                   :class="[
                     'p-4 rounded-xl text-sm font-medium border-l-4',
                     notification.data.type === 'creator_application_approved' 
                       ? 'bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-200 border-green-500' 
                       : 'bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-200 border-red-500'
                   ]"
              >
                <i :class="[
                  notification.data.type === 'creator_application_approved' ? 'ri-check-double-line' : 'ri-information-line',
                  'mr-2'
                ]"></i>
                {{ notification.data.feedback }}
              </div>

              <!-- Tag Request Status -->
              <div v-if="notification.data.type === 'tag_request'" class="mt-4">
                <div v-if="notification.data.tag && notification.data.tag.status" 
                     :class="[
                       'py-3 px-4 rounded-xl text-sm font-medium inline-flex items-center gap-2',
                       notification.data.tag.status.toLowerCase() === 'approved' 
                         ? 'bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-200 border border-green-200 dark:border-green-800' :
                       notification.data.tag.status.toLowerCase() === 'rejected' 
                         ? 'bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-200 border border-red-200 dark:border-red-800' : ''
                     ]"
                >
                  <i :class="[
                    notification.data.tag.status.toLowerCase() === 'approved' ? 'ri-check-double-line' : 'ri-close-line',
                    'text-lg'
                  ]"></i>
                  {{ t('tag_request') }} {{ t(notification.data.tag.status.toLowerCase()) }}
                </div>
              </div>

              <!-- Post Content Preview -->
              <div v-if="hasPostContent(notification)" 
                   class="mt-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
                   @click="notificationsStore.markAsRead(notification)"
              >
                <p class="text-gray-800 dark:text-gray-200 text-sm leading-relaxed">
                  {{ getPostContent(notification) }}
                </p>
              </div>
              
              <!-- Enhanced Post Preview for Tag Requests -->
              <div 
                v-if="notification.data.type === 'tag_request' && notification.data.post && notification.data.post.user" 
                class="mt-4 p-4 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800 rounded-xl border border-gray-200 dark:border-gray-600 cursor-pointer hover:shadow-lg transition-all duration-200 hover:scale-[1.02]"
                @click="router.push(`/post/${notification.data.post.id}`)"
              >
                <!-- Post Header -->
                <div class="flex items-start gap-3 mb-3">
                  <img 
                    :src="notification.data.post.user.avatar || '/default-avatar.png'" 
                    :alt="notification.data.post.user.name || 'User'"
                    class="w-10 h-10 rounded-full object-cover shadow-md"
                  />
                  <div>
                    <div class="flex items-center gap-1">
                      <router-link 
                        :to="`/${notification.data.post.user.username || 'user'}/posts`"
                        class="font-semibold text-gray-900 dark:text-white hover:text-primary-light dark:hover:text-primary-dark transition-colors duration-200 cursor-pointer"
                        @click.stop
                      >
                        {{ notification.data.post.user.name || 'User' }}
                      </router-link>
                      <i v-if="notification.data.post.user.verified" 
                         class="ri-checkbox-circle-fill text-primary-light dark:text-primary-dark text-sm"></i>
                    </div>
                    <router-link 
                      :to="`/${notification.data.post.user.username || 'user'}/posts`"
                      class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary-light dark:hover:text-primary-dark transition-colors duration-200 cursor-pointer"
                      @click.stop
                    >
                      @{{ notification.data.post.user.username || 'user' }}
                    </router-link>
                  </div>
                </div>

                <!-- Post Content -->
                <p class="text-gray-800 dark:text-gray-200 whitespace-pre-line mb-3 text-sm leading-relaxed">
                  {{ notification.data.post.content }}
                </p>

                <!-- Post Media Grid -->
                <div v-if="notification.data.post.media && notification.data.post.media.length > 0" 
                     class="grid grid-cols-2 gap-2 mb-3">
                  <div v-for="media in notification.data.post.media.slice(0, 4)" 
                       :key="media.id" 
                       class="relative aspect-square rounded-lg overflow-hidden shadow-md">
                    <img 
                      v-if="media.type === 'image'"
                      :src="media.url" 
                      :alt="notification.data.post.content"
                      class="w-full h-full object-cover"
                    />
                    <video 
                      v-else-if="media.type === 'video'"
                      :src="media.url"
                      class="w-full h-full object-cover"
                      controls
                    ></video>
                  </div>
                </div>

                <!-- Post Stats -->
                <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                  <span class="flex items-center gap-1">
                    <i class="ri-heart-line"></i>
                    {{ notification.data.post.stats.total_likes }}
                  </span>
                  <span class="flex items-center gap-1">
                    <i class="ri-chat-1-line"></i>
                    {{ notification.data.post.stats.total_comments }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="notificationsStore.filteredNotifications.length === 0" 
             class="text-center py-16 animate-fadeIn">
          <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 dark:bg-gray-800 rounded-full mb-6">
            <i class="ri-notification-off-line text-3xl text-gray-400"></i>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
            {{ getEmptyStateTitle() }}
          </h3>
          <p class="text-gray-500 dark:text-gray-400 text-sm max-w-md mx-auto">
            {{ getEmptyStateMessage() }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue'
import { useNotificationsStore } from '@/stores/notificationsStore'
import { useAuthStore } from '@/stores/authStore'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'

// Initialize stores, router and i18n
const notificationsStore = useNotificationsStore()
const authStore = useAuthStore()
const router = useRouter()
const { t } = useI18n()

// Helper function to get tag ID from notification
const getTagId = (notification) => {
  // First try to get it from the tag object
  if (notification.data.tag && notification.data.tag.id) {
    return notification.data.tag.id
  }
  
  // Fall back to the tag_id field
  return notification.data.tag_id
}

// Respond to a tag request (approve or reject)
const respondToTagRequest = async (notification, response) => {
  console.log('Responding to tag request:', notification.id, response)
  console.log('Before response - Tag status:', notification.data.tag ? notification.data.tag.status : 'undefined')
  
  try {
    // Call the store method to handle the tag response
    const result = await notificationsStore.respondToTagRequest(notification, response)
    console.log('Tag response result:', result)
    console.log('After response - Tag status:', notification.data.tag ? notification.data.tag.status : 'undefined')
    
    // The store will handle updating the notification data and refreshing
    // No need to update the data here or call fetchNotifications again
  } catch (error) {
    console.error('Error responding to tag request:', error)
  }
}

// Lifecycle hooks
onMounted(() => {
  // Initialize WebSockets
  notificationsStore.initializeEcho()
  
  // Fetch notifications
  notificationsStore.fetchNotifications().then(() => {
    // Log all notifications data
    
    // Log specifically tag request notifications
    const tagRequests = notificationsStore.notifications.filter(n => n.data.type === 'tag_request')
  })
})

onUnmounted(() => {
  // Clean up WebSocket listeners
  notificationsStore.cleanup()
})

// Helper functions
const getIconClass = (notification) => {
  const type = notification.data.type
  
  switch (type) {
    case 'creator_application_approved':
      return 'bg-gradient-to-br from-green-500 to-green-600'
    case 'creator_application_rejected':
      return 'bg-gradient-to-br from-red-500 to-red-600'
    case 'follow':
      return 'bg-gradient-to-br from-blue-500 to-blue-600'
    case 'like':
      const reactionType = notification.data.reaction_type || 'heart'
      switch (reactionType) {
        case 'heart': return 'bg-gradient-to-br from-red-500 to-red-600'
        case 'thumbsup': return 'bg-gradient-to-br from-yellow-500 to-yellow-600'
        case 'fire': return 'bg-gradient-to-br from-orange-500 to-orange-600'
        default: return 'bg-gradient-to-br from-red-500 to-red-600'
      }
    case 'tag_request':
      return 'bg-gradient-to-br from-green-500 to-green-600'
    case 'tag_approved':
      return 'bg-gradient-to-br from-green-600 to-green-700'
    case 'tag_rejected':
      return 'bg-gradient-to-br from-red-600 to-red-700'
    default:
      return 'bg-gradient-to-br from-blue-500 to-blue-600'
  }
}

const getIconName = (notification) => {
  const type = notification.data.type
  
  switch (type) {
    case 'creator_application_approved':
      return 'ri-verified-badge-fill'
    case 'creator_application_rejected':
      return 'ri-close-circle-fill'
    case 'follow':
      return 'ri-user-follow-fill'
    case 'like':
      const reactionType = notification.data.reaction_type || 'heart'
      switch (reactionType) {
        case 'heart': return 'ri-heart-fill'
        case 'thumbsup': return 'ri-thumb-up-fill'
        case 'fire': return 'ri-fire-fill'
        default: return 'ri-heart-fill'
      }
    case 'tag_request':
      return 'ri-price-tag-3-fill'
    case 'tag_approved':
      return 'ri-check-double-fill'
    case 'tag_rejected':
      return 'ri-close-fill'
    default:
      return 'ri-notification-fill'
  }
}

const getNotificationText = (notification) => {
  const type = notification.data.type
  
  switch (type) {
    case 'follow':
      return t('started_following')
    case 'like':
      return t('liked_post')
    case 'tag_request':
      return t('wants_to_tag')
    case 'tag_approved':
      return t('approved_tag_request')
    case 'tag_rejected':
      return t('rejected_tag_request')
    default:
      return t('sent_notification')
  }
}

const getNotificationUsername = (notification) => {
  const type = notification.data.type
  
  switch (type) {
    case 'follow':
      // Try multiple possible field names
      return notification.data.follower_name || 
             notification.data.follower?.name || 
             notification.data.user_name ||
             t('user')
    case 'like':
      return notification.data.liker_name || 
             notification.data.liker?.name || 
             notification.data.user_name ||
             t('user')
    case 'tag_request':
      return notification.data.tagger_name || 
             notification.data.tagger?.name || 
             notification.data.user_name ||
             t('user')
    case 'tag_approved':
    case 'tag_rejected':
      return notification.data.tagged_user_name || 
             notification.data.tagged_user?.name || 
             notification.data.user_name ||
             t('user')
    default:
      return t('user')
  }
}

const getNotificationHandle = (notification) => {
  const type = notification.data.type
  
  switch (type) {
    case 'follow':
      return notification.data.follower_username || 
             notification.data.follower?.username || 
             notification.data.user_username ||
             t('default_user')
    case 'like':
      return notification.data.liker_username || 
             notification.data.liker?.username || 
             notification.data.user_username ||
             t('default_user')
    case 'tag_request':
      return notification.data.tagger_username || 
             notification.data.tagger?.username || 
             notification.data.user_username ||
             t('default_user')
    case 'tag_approved':
    case 'tag_rejected':
      return notification.data.tagged_user_username || 
             notification.data.tagged_user?.username || 
             notification.data.user_username ||
             t('default_user')
    default:
      return t('default_user')
  }
}

const getNotificationVerified = (notification) => {
  const type = notification.data.type
  
  switch (type) {
    case 'follow':
      return notification.data.follower?.verified || 
             notification.data.follower_verified || 
             false
    case 'like':
      return notification.data.liker?.verified || 
             notification.data.liker_verified || 
             false
    case 'tag_request':
      return notification.data.tagger?.verified || 
             notification.data.tagger_verified || 
             false
    case 'tag_approved':
    case 'tag_rejected':
      return notification.data.tagged_user?.verified || 
             notification.data.tagged_user_verified || 
             false
    default:
      return false
  }
}

const hasPostContent = (notification) => {
  const type = notification.data.type
  
  if (type === 'like' && notification.data.post && notification.data.post.content) {
    return true
  } else if (['tag_request', 'tag_approved', 'tag_rejected'].includes(type) && 
             notification.data.post && notification.data.post.content) {
    return true
  }
  
  return false
}

const getPostContent = (notification) => {
  if (notification.data.post && notification.data.post.content) {
    return notification.data.post.content
  } else if (notification.data.message) {
    return notification.data.message
  }
  
  return ''
}

const getEmptyStateTitle = () => {
  if (notificationsStore.activeFilter !== 'all') {
    if (notificationsStore.activeFilter === 'tag') {
      return t('no_tag_notifications_title', 'No Tag Notifications')
    } else if (notificationsStore.activeFilter === 'unread') {
      return t('no_unread_notifications_title', 'All Caught Up!')
    } else {
      return t('no_notifications_filter_title', 'No Notifications')
    }
  }
  return t('no_notifications_title', 'No Notifications Yet')
}

const getEmptyStateMessage = () => {
  if (notificationsStore.activeFilter !== 'all') {
    if (notificationsStore.activeFilter === 'tag') {
      return t('no_tag_notifications')
    } else if (notificationsStore.activeFilter === 'unread') {
      return t('no_unread_notifications')
    } else {
      return t('no_notifications_filter', { filter: notificationsStore.activeFilter })
    }
  }
  return t('no_notifications')
}

const formatTime = (timestamp) => {
  const date = new Date(timestamp)
  const now = new Date()
  const diffInSeconds = Math.floor((now - date) / 1000)
  
  if (diffInSeconds < 60) {
    return t('just_now')
  } else if (diffInSeconds < 3600) {
    const minutes = Math.floor(diffInSeconds / 60)
    return t('minutes_ago', { minutes })
  } else if (diffInSeconds < 86400) {
    const hours = Math.floor(diffInSeconds / 3600)
    return t('hours_ago', { hours })
  } else if (diffInSeconds < 604800) {
    const days = Math.floor(diffInSeconds / 86400)
    return t('days_ago', { days })
  } else {
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
  }
}

const getTagStatus = (notification) => {
  // For tag requests, if there's no status, it means it's pending
  if (notification.data.type === 'tag_request') {
    // If there's a tag object with status, use that
    if (notification.data.tag && notification.data.tag.status) {
      return notification.data.tag.status.toLowerCase();
    }
    // If there's no tag object or status, it means the request is pending
    return 'pending';
  }
  return null; // For non-tag request notifications
}

const getNotificationUsernameForRoute = (notification) => {
  const type = notification.data.type
  
  switch (type) {
    case 'follow':
      return notification.data.follower_username || 
             notification.data.follower?.username || 
             notification.data.user_username ||
             t('default_user')
    case 'like':
      return notification.data.liker_username || 
             notification.data.liker?.username || 
             notification.data.user_username ||
             t('default_user')
    case 'tag_request':
      return notification.data.tagger_username || 
             notification.data.tagger?.username || 
             notification.data.user_username ||
             t('default_user')
    case 'tag_approved':
    case 'tag_rejected':
      return notification.data.tagged_user_username || 
             notification.data.tagged_user?.username || 
             notification.data.user_username ||
             t('default_user')
    default:
      return t('default_user')
  }
}

const handleNotificationClick = (notification, event) => {
  // Don't mark as read if it's already read
  if (notification.read) {
    return;
  }
  
  // Mark the notification as read
  notificationsStore.markAsRead(notification);
};
</script>

<style scoped>
/* Custom animations */
@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
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

.animate-slideDown {
  animation: slideDown 0.3s ease-out;
}

.animate-fadeIn {
  animation: fadeIn 0.5s ease-out;
}

.animate-fadeInUp {
  animation: fadeInUp 0.6s ease-out;
}

/* Enhanced focus states */
button:focus-visible,
a:focus-visible {
  outline: 2px solid currentColor;
  outline-offset: 2px;
}

/* Smooth scrolling */
html {
  scroll-behavior: smooth;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.5);
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: rgba(156, 163, 175, 0.7);
}
</style>