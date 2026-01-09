<template>
  <div class="flex flex-col h-screen bg-background-light dark:bg-background-dark">
    <div v-if="loading" class="flex items-center justify-center h-full">
      <p class="text-text-light-secondary dark:text-text-dark-secondary">Loading conversation...</p>
    </div>
    <div v-else-if="error" class="flex items-center justify-center h-full">
      <p class="text-red-500">{{ error }}</p>
    </div>
    <template v-else-if="receiver">
      <!-- Custom Header -->
      <header class="fixed top-0 left-0 right-0 z-10 px-3 py-2 bg-background-light dark:bg-background-dark border-b border-border-light dark:border-border-dark">
        <div class="flex items-center gap-3">
          <router-link 
            to="/messages" 
            class="p-2 -ml-2 rounded-full text-text-light-secondary dark:text-text-dark-secondary hover:bg-surface-light dark:hover:bg-surface-dark"
            tabindex="0"
          >
            <i class="ri-arrow-left-line text-xl"></i>
          </router-link>
          
          <div class="flex items-center gap-2 flex-1 min-w-0">
            <div class="relative">
              <img v-if="receiver.avatar" :src="receiver.avatar" :alt="receiver.name" class="w-8 h-8 rounded-full object-cover">
              <div v-else class="w-8 h-8 rounded-full bg-surface-light dark:bg-surface-dark flex items-center justify-center">
                <i class="ri-user-3-line text-lg text-text-light-secondary dark:text-text-dark-secondary"></i>
              </div>
              <span v-if="isUserOnline" class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 rounded-full border-2 border-background-light dark:border-background-dark"></span>
            </div>
            
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-1">
                <span class="font-medium truncate text-text-light-primary dark:text-text-dark-primary">{{ receiver.name }}</span>
                <i v-if="receiver.verified" class="ri-verified-badge-fill text-primary-light dark:text-primary-dark text-sm"></i>
              </div>
              <div class="flex items-center gap-1 text-xs text-text-light-secondary dark:text-text-dark-secondary">
                <span>{{ receiver.username }}</span>
                <span v-if="isUserOnline" class="text-green-500">• Online</span>
              </div>
            </div>
          </div>

          <button 
            class="p-2 text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary"
            tabindex="0"
          >
            <i class="ri-more-fill text-xl"></i>
          </button>
        </div>
      </header>

      <!-- Messages -->
      <div ref="messagesContainer" class="flex-1 overflow-y-auto px-3 py-4 space-y-4 mt-[52px] mb-[120px]">
        <template v-if="messages.length">
          <template v-for="(message, index) in messages" :key="message.id">
            <!-- Date Separator -->
            <div v-if="showDateSeparator(message, index)" class="flex items-center justify-center">
              <span class="text-xs text-text-light-tertiary dark:text-text-dark-tertiary">{{ formatDate(message.created_at) }}</span>
            </div>

            <!-- Message -->
            <div :class="[
              'flex flex-col gap-1',
              message.sender_id === sender.id ? 'items-end' : 'items-start max-w-[85%]'
            ]">
              <div :class="[
                'flex items-start gap-2', 
                message.sender_id === sender.id ? 'flex-row-reverse' : ''
              ]">
                <!-- Profile Picture Container -->
                <div v-if="message.sender_id !== sender.id" 
                     class="flex-shrink-0 w-6 h-6 rounded-full bg-surface-light dark:bg-surface-dark flex items-center justify-center"
                     :class="{'mt-1': message.media && message.media.length > 0}">
                  <img v-if="receiver.avatar" 
                       :src="receiver.avatar" 
                       :alt="receiver.name" 
                       class="w-6 h-6 rounded-full object-cover">
                  <i v-else class="ri-user-3-line text-sm text-text-light-secondary dark:text-text-dark-secondary"></i>
                </div>

                <!-- Message Content Container -->
                <div class="flex flex-col gap-2">
                  <!-- Media Content -->
                  <div v-if="message.media && message.media.length > 0" 
                       class="media-container overflow-hidden rounded-2xl">
                    <PostGrid
                      :media="message.user_has_permission ? message.media : message.media_previews"
                      :author="message.sender_id === sender.id ? sender : receiver"
                      :description="message.content"
                      :user-has-permission="message.user_has_permission === true"
                      :required-permissions="message.required_permissions"
                      :total-media-count="message.media.length"
                      @mediaLike="handleMediaLike"
                      @mediaBookmark="handleMediaBookmark"
                      @mediaStats="handleMediaStats"
                      @unlock="() => handleUnlock(message)"
                    />
                  </div>

                  <!-- Text Content -->
                  <div v-if="message.content" 
                       :class="[
                         message.sender_id === sender.id ? 'bg-primary-light dark:bg-primary-dark text-white' : 'bg-surface-light dark:bg-surface-dark text-text-light-primary dark:text-text-dark-primary',
                         'rounded-2xl px-4 py-2',
                         message.sender_id === sender.id ? 'rounded-br-none' : 'rounded-bl-none'
                       ]">
                    <p>{{ message.content }}</p>
                  </div>
                </div>
              </div>

              <!-- Timestamp and Read Status -->
              <div class="flex items-center gap-1 text-xs text-text-light-tertiary dark:text-text-dark-tertiary" 
                   :class="{ 'justify-end': message.sender_id === sender.id }">
                <span>{{ formatTime(message.created_at) }}</span>
                <!-- Only show read receipts if receiver has enabled them -->
                <span v-if="message.sender_id === sender.id && message.read_at && receiverSettings.show_read_receipts" class="text-primary-light dark:text-primary-dark">
                  <i class="ri-check-double-line"></i>
                </span>
                <span v-else-if="message.sender_id === sender.id" class="text-text-light-tertiary dark:text-text-dark-tertiary">
                  <i class="ri-check-line"></i>
                </span>
              </div>
            </div>
          </template>
        </template>
        <div v-else class="flex items-center justify-center h-full">
          <p class="text-text-light-secondary dark:text-text-dark-secondary">No messages yet. Start a conversation!</p>
        </div>
      </div>

      <!-- Message Input Section -->
      <div class="fixed bottom-[56px] left-0 right-0 z-50 border-t border-border-light dark:border-border-dark bg-background-light/80 dark:bg-background-dark/80 backdrop-blur-lg">
        <!-- Typing Indicator -->
        <div v-if="isTyping" class="px-3 pt-1 text-xs text-text-light-tertiary dark:text-text-dark-tertiary">
          <span>{{ receiver.name }} is typing...</span>
        </div>
        
        <!-- Media Preview Area - Only rendered when there are actual media previews -->
        <div v-if="mediaPreviews && mediaPreviews.length > 0" class="px-3 pt-2">
          <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-thin scrollbar-thumb-border-light dark:scrollbar-thumb-border-dark scrollbar-track-transparent">
            <div 
              v-for="preview in mediaPreviews" 
              :key="preview.id"
              class="relative w-16 h-16 flex-shrink-0 group"
            >
              <img 
                v-if="preview.type === 'image'" 
                :src="preview.url" 
                class="w-full h-full object-cover rounded-lg border border-border-light dark:border-border-dark"
                alt="Media preview"
              />
              <video 
                v-else 
                :src="preview.url" 
                class="w-full h-full object-cover rounded-lg border border-border-light dark:border-border-dark"
              ></video>
              <button 
                @click="removeMedia(preview.id)"
                class="absolute -top-1.5 -right-1.5 bg-background-light dark:bg-background-dark text-text-light-secondary dark:text-text-dark-secondary rounded-full w-5 h-5 flex items-center justify-center shadow-lg hover:text-text-light-primary dark:hover:text-text-dark-primary transition-colors"
                tabindex="0"
              >
                <i class="ri-close-line text-xs"></i>
              </button>
            </div>
          </div>
        </div>
        

        <!-- Input Area -->
        <div class="px-3 py-2">
          <div v-if="!isExpanded" class="flex items-center gap-2">
            <div class="flex gap-2">
              <ImageUploadMenu 
                @upload-new="handleUploadNew"
                @from-vault="handleFromVault"
              />
              <button class="p-2 text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary">
                <i class="ri-file-gif-line text-xl"></i>
              </button>
              <button class="p-2 text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary">
                <i class="ri-menu-line text-xl"></i>
              </button>
              <button class="p-2 text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary">
                <i class="ri-mic-line text-xl"></i>
              </button>
            </div>
            
            <input
              type="text"
              v-model="newMessage"
              :placeholder="`Message @${receiver.name}`"
              class="flex-1 bg-transparent text-text-light-primary dark:text-text-dark-primary placeholder:text-text-light-tertiary dark:placeholder:text-text-dark-tertiary focus:outline-none"
              @focus="expandInput"
              @input="handleTyping"
              @keydown.enter.prevent="sendMessage"
            />
            
            <button 
              v-if="newMessage.trim() || mediaUploadStore.previews.length > 0"
              @click="sendMessage"
              :disabled="sendingMessage"
              class="p-2 text-primary-light dark:text-primary-dark hover:opacity-80 transition-opacity disabled:opacity-50"
              tabindex="0"
            >
              <i class="ri-send-plane-fill text-xl"></i>
            </button>
          </div>

          <div v-else class="w-full relative">
            <textarea
              v-model="newMessage"
              :placeholder="`Message @${receiver.name}`"
              class="w-full bg-surface-light dark:bg-surface-dark rounded-xl p-3 pr-14 text-text-light-primary dark:text-text-dark-primary placeholder:text-text-light-tertiary dark:placeholder:text-text-dark-tertiary focus:outline-none focus:ring-1 focus:ring-primary-light dark:focus:ring-primary-dark resize-none min-h-[100px]"
              rows="3"
              @blur="newMessage.trim() === '' && !mediaUploadStore.previews.length && collapseInput()"
              @input="handleTyping"
              @keydown.enter.prevent="handleEnterKeyPress"
              ref="textareaRef"
              tabindex="0"
            ></textarea>
            <button 
              @click="sendMessage"
              :disabled="(!newMessage.trim() && !mediaUploadStore.previews.length) || sendingMessage"
              class="absolute bottom-3 right-3 p-2 bg-primary-light dark:bg-primary-dark text-white rounded-lg hover:bg-primary-light-hover dark:hover:bg-primary-dark-hover transition-colors disabled:opacity-50"
              tabindex="0"
            >
              <i class="ri-send-plane-fill text-lg"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Media Upload Modal -->
      <MediaUploadModal 
        :is-open="isMediaUploadModalOpen"
        :context-id="getMessageContextId"
        @close="closeUploadModal"
        @upload="handleMediaUpload"
      />
      
      <!-- Permission Unlock Modal -->
      <div v-if="showUnlockModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-background-light dark:bg-background-dark rounded-xl p-6 max-w-md w-full">
          <h3 class="text-xl font-bold text-text-light-primary dark:text-text-dark-primary mb-4">Unlock Content</h3>
          
          <div v-if="selectedMessage && selectedMessage.required_permissions" class="mb-4">
            <p class="text-text-light-secondary dark:text-text-dark-secondary mb-2">
              This content requires the following permissions:
            </p>
            
            <div class="space-y-2">
              <div 
                v-for="(permissionGroup, groupIndex) in selectedMessage.required_permissions" 
                :key="groupIndex"
                class="p-3 bg-surface-light dark:bg-surface-dark rounded-lg"
              >
                <p class="text-sm text-text-light-tertiary dark:text-text-dark-tertiary mb-2">
                  Satisfy one of the following:
                </p>
                
                <div class="space-y-2">
                  <div 
                    v-for="(permission, permIndex) in permissionGroup" 
                    :key="permIndex"
                    class="flex items-center justify-between"
                  >
                    <div>
                      <span v-if="permission.type === 'subscribed_all_tiers'" class="text-text-light-primary dark:text-text-dark-primary">
                        Subscribe to creator
                      </span>
                      <span v-else-if="permission.type === 'add_price'" class="text-text-light-primary dark:text-text-dark-primary">
                        Pay {{ permission.value }} credits
                      </span>
                      <span v-else-if="permission.type === 'following'" class="text-text-light-primary dark:text-text-dark-primary">
                        Follow creator
                      </span>
                      <span v-else-if="permission.type === 'limited_time'" class="text-text-light-primary dark:text-text-dark-primary">
                        Time-limited access
                      </span>
                    </div>
                    
                    <button 
                      v-if="!permission.is_met"
                      @click="processPermission(permission)"
                      class="px-3 py-1 bg-primary-light dark:bg-primary-dark text-white rounded-lg hover:bg-primary-light-hover dark:hover:bg-primary-dark-hover transition-colors"
                    >
                      Unlock
                    </button>
                    <span v-else class="text-green-500">
                      <i class="ri-check-line"></i> Met
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="flex justify-end gap-2 mt-4">
            <button 
              @click="closeUnlockModal"
              class="px-4 py-2 text-text-light-primary dark:text-text-dark-primary hover:bg-surface-light dark:hover:bg-surface-dark rounded-lg transition-colors"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>

      <!-- Tip Modal - Updated to use dollars -->
      <div v-if="showTipModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-background-light dark:bg-background-dark rounded-xl p-6 max-w-md w-full">
          <h3 class="text-xl font-bold text-text-light-primary dark:text-text-dark-primary mb-2">Send a Tip</h3>
          <p class="text-text-light-secondary dark:text-text-dark-secondary mb-4">
            {{ receiver.name }} requires a tip to receive messages.
          </p>
          
          <div class="space-y-4 mb-6">
            <div class="grid grid-cols-3 gap-2">
              <button 
                v-for="amount in [5, 10, 20]" 
                :key="amount"
                @click="tipAmount = amount"
                :class="[
                  'py-2 rounded-lg border transition-colors',
                  tipAmount === amount 
                    ? 'bg-primary-light/10 dark:bg-primary-dark/10 border-primary-light dark:border-primary-dark text-primary-light dark:text-primary-dark' 
                    : 'border-border-light dark:border-border-dark hover:bg-surface-light dark:hover:bg-surface-dark'
                ]"
              >
                ${{ amount }}
              </button>
            </div>
            
            <div class="flex items-center gap-2">
              <div class="relative flex-1">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-text-light-tertiary dark:text-text-dark-tertiary">$</span>
                <input
                  type="number"
                  v-model.number="customTipAmount"
                  placeholder="Custom amount"
                  min="1"
                  class="w-full p-2 pl-7 rounded-lg border border-border-light dark:border-border-dark bg-surface-light dark:bg-surface-dark text-text-light-primary dark:text-text-dark-primary focus:outline-none focus:ring-1 focus:ring-primary-light dark:focus:ring-primary-dark"
                  @input="tipAmount = customTipAmount"
                />
              </div>
            </div>
          </div>
          
          <div class="flex justify-end gap-2">
            <button 
              @click="closeTipModal"
              class="px-4 py-2 text-text-light-primary dark:text-text-dark-primary hover:bg-surface-light dark:hover:bg-surface-dark rounded-lg transition-colors"
            >
              Cancel
            </button>
            <button 
              @click="sendTip"
              :disabled="!tipAmount || tipAmount < 1 || sendingTip"
              class="px-4 py-2 bg-primary-light dark:bg-primary-dark text-white rounded-lg hover:bg-primary-light-hover dark:hover:bg-primary-dark-hover transition-colors disabled:opacity-50"
            >
              {{ sendingTip ? 'Sending...' : `Send $${tipAmount}` }}
            </button>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useMessagesStore } from '@/stores/messagesStore'
import { useMediaUploadStore } from '@/stores/mediaUploadStore'
import { useAuthStore } from '@/stores/authStore'
import { format, isToday, isYesterday, isSameDay } from 'date-fns'
import MediaUploadModal from '@/components/posts/MediaUploadModal.vue'
import ImageUploadMenu from '@/components/posts/ImageUploadMenu.vue'
import PostGrid from '@/components/user/PostGrid.vue'
import { useWebSocketService } from '@/services/websocketService'
import axiosInstance from '@/axios'

const route = useRoute()
const router = useRouter()
const messagesStore = useMessagesStore()
const authStore = useAuthStore()
const mediaUploadStore = useMediaUploadStore()
const websocketService = useWebSocketService()

const loading = ref(true)
const error = ref(null)
const sender = computed(() => authStore.user)
const receiver = ref(null)
const messages = ref([])
const newMessage = ref('')
const isExpanded = ref(false)
const textareaRef = ref(null)
const isMediaUploadModalOpen = ref(false)
const sendingMessage = ref(false)
const showUnlockModal = ref(false)
const selectedMessage = ref(null)
const processingPermission = ref(false)
const messagesContainer = ref(null)
const isTyping = ref(false)
const isUserOnline = ref(false)
const typingTimeout = ref(null)

// New refs for user settings and tip functionality
const receiverSettings = ref({
  show_read_receipts: false,
  require_tip_for_messages: true, // Default to true to be safe
  accept_messages_from_followed: true
})
const showTipModal = ref(false)
const tipAmount = ref(10)
const customTipAmount = ref(null)
const sendingTip = ref(false)
const settingsLoaded = ref(false)
const isFollowingReceiver = ref(false)
const receiverFollowsSender = ref(false)
const isReceiverCreator = computed(() => receiver.value && receiver.value.role === 'creator')

// Create a context ID for this conversation
const getMessageContextId = computed(() => {
  return receiver.value ? `message-${receiver.value.id}` : 'message-new'
})

// Set the context when the component mounts or receiver changes
watch(() => getMessageContextId.value, (newContextId) => {
  mediaUploadStore.setContext(newContextId)
}, { immediate: true })

// Computed property to access the previews
const mediaPreviews = computed(() => {
  return mediaUploadStore.previews || []
})

// Replace the canSendWithoutTip computed property with this corrected version
const canSendWithoutTip = computed(() => {
  // If sender is a creator...
  if (sender.value && sender.value.role === 'creator') {
    // If receiver is also a creator and requires tips, then it's not free
    if (isReceiverCreator.value && receiverSettings.value.require_tip_for_messages) {
      console.log('👑➡️👑 Creator sending to creator who requires tips, tip is required');
      return false;
    }
    
    // Otherwise, creator can send without tip (to non-creators or creators who don't require tips)
    console.log('👑 Sender is a creator sending to user or creator without tip requirement');
    return true;
  }
  
  // If receiver is not a creator, anyone can send messages
  if (!isReceiverCreator.value) {
    console.log('👤 Receiver is not a creator, can send without tip');
    return true;
  }
  
  // If receiver is a creator but doesn't require tips, anyone can send
  // (user to creator with no permission requirements)
  if (!receiverSettings.value.require_tip_for_messages) {
    console.log('⚙️ Receiver is a creator but does not require tips, can send without tip');
    return true;
  }
  
  // If receiver accepts messages from followed users and receiver follows sender
  if (receiverSettings.value.accept_messages_from_followed && receiverFollowsSender.value) {
    console.log('👥 Receiver follows sender and accepts messages from followed users');
    return true;
  }
  
  // In all other cases, a tip is required
  console.log('💰 Tip is required to send message');
  return false;
});

// Add a new ref to track if permissions are fully loaded
const permissionsLoaded = ref(false);

// Fetch receiver's messaging settings
const fetchReceiverSettings = async () => {
  if (!receiver.value || !receiver.value.id) return
  
  try {
    // Only check message settings if the receiver is a creator
    if (isReceiverCreator.value) {
      console.log(`🔍 Fetching messaging settings for creator ${receiver.value.id}`)
      
      // Use the store method to get messaging settings
      const result = await messagesStore.checkMessagePermissions(receiver.value.id)
      
      if (result.success) {
        // For creators, ensure require_tip_for_messages defaults to true if not explicitly set
        receiverSettings.value = {
          ...result.data,
          require_tip_for_messages: result.data.require_tip_for_messages !== false
        }
        console.log('✅ Creator settings loaded:', receiverSettings.value)
        settingsLoaded.value = true
      } else {
        // Use default settings if fetch fails - for creators, default to requiring tips
        receiverSettings.value = {
          show_read_receipts: false,
          require_tip_for_messages: true, // Always default to true for creators
          accept_messages_from_followed: true
        }
        console.log('⚠️ Using default creator settings:', receiverSettings.value)
        settingsLoaded.value = true
      }
    } else {
      // For non-creators, use default settings that allow messaging without restrictions
      console.log(`👤 User ${receiver.value.id} is not a creator, using default settings`)
      receiverSettings.value = {
        show_read_receipts: true,
        require_tip_for_messages: false,
        accept_messages_from_followed: true
      }
      settingsLoaded.value = true
    }
    
    // Set permissionsLoaded to true after settings are loaded
    permissionsLoaded.value = true;
    console.log('✅ Permissions fully loaded and ready');
    console.log('🔒 Final settings state:', {
      isReceiverCreator: isReceiverCreator.value,
      requireTip: receiverSettings.value.require_tip_for_messages,
      canSendWithoutTip: canSendWithoutTip.value,
      permissionsLoaded: permissionsLoaded.value
    });
    
  } catch (error) {
    console.error('❌ Error fetching receiver settings:', error)
    // Use default settings if fetch fails - for creators, default to requiring tips
    receiverSettings.value = {
      show_read_receipts: false,
      require_tip_for_messages: isReceiverCreator.value ? true : false, // Default based on role
      accept_messages_from_followed: true
    }
    settingsLoaded.value = true
    
    // Even on error, mark permissions as loaded with defaults
    permissionsLoaded.value = true;
    console.log('⚠️ Permissions loaded with defaults due to error');
    console.log('🔒 Final settings state (error case):', {
      isReceiverCreator: isReceiverCreator.value,
      requireTip: receiverSettings.value.require_tip_for_messages,
      canSendWithoutTip: canSendWithoutTip.value,
      permissionsLoaded: permissionsLoaded.value
    });
  }
}

// Check if the current user is following the receiver
const checkFollowingStatus = async () => {
  if (!receiver.value || !receiver.value.id || !authStore.user) return
  
  // Only check follow status if the receiver is a creator
  if (!isReceiverCreator.value) return
  
  try {
    console.log(`🔍 Checking if user ${authStore.user.id} is following ${receiver.value.id}`)
    const response = await axiosInstance.get(`/users/${receiver.value.id}/following-status`)
    
    isFollowingReceiver.value = response.data.is_following || false
    console.log(`✅ Following status: ${isFollowingReceiver.value ? 'Following' : 'Not following'}`)
  } catch (error) {
    console.error('❌ Error checking following status:', error)
    isFollowingReceiver.value = false
  }
}

// Check if the receiver follows the sender
const checkReceiverFollowsSender = async () => {
  if (!receiver.value || !receiver.value.id || !authStore.user) return
  
  // Only check if the receiver is a creator
  if (!isReceiverCreator.value) return
  
  try {
    console.log(`🔍 Checking if user ${receiver.value.id} is following ${authStore.user.id}`)
    const result = await messagesStore.checkFollowStatus(receiver.value.id)
    
    receiverFollowsSender.value = result.isFollowing || false
    console.log(`✅ Receiver follows sender: ${receiverFollowsSender.value ? 'Yes' : 'No'}`)
  } catch (error) {
    console.error('❌ Error checking if receiver follows sender:', error)
    receiverFollowsSender.value = false
  }
}

// Scroll to bottom of messages
const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
      console.log('📜 Scrolled to bottom of messages container');
    }
  });
};

// Mark all unread messages from the other user as read
const markUnreadMessagesAsRead = (messages) => {
  // Check if authStore.user exists before proceeding
  if (!authStore.user) {
    console.warn("⚠️ Cannot mark messages as read: User not authenticated");
    return;
  }

  // Find messages where the current user is the receiver and read_at is null
  const unreadMessages = messages.filter((message) => 
    message.receiver_id === authStore.user.id && !message.read_at
  );

  if (unreadMessages.length > 0) {
    console.log(`✓ Marking ${unreadMessages.length} messages as read`);
    // Mark each unread message as read
    unreadMessages.forEach((message) => {
      messagesStore.markMessageAsRead(message.id);
    });
  }
};

// Now the watch statement can reference the function
// Watch for changes in messages to scroll to bottom and mark unread messages as read
watch(() => messages.value.length, () => {
  console.log(`📜 Message count changed to ${messages.value.length}, scrolling to bottom`);
  scrollToBottom();
  
  // Mark unread messages as read when messages are loaded or updated
  if (messages.value.length > 0) {
    markUnreadMessagesAsRead(messages.value);
  }
}, { immediate: true });

// Update the onMounted function to ensure we wait for all data to load
onMounted(async () => {
  const receiverId = route.params.id;
  console.log(`🔄 Mounting SingleMessage component for conversation with user ${receiverId}`);
  loading.value = true;
  error.value = null;
  permissionsLoaded.value = false; // Reset permissions loaded state
  
  try {
    // First, ensure the auth state is loaded
    console.log('🔑 Checking authentication state...');
    
    // Make sure we have a valid token before proceeding
    const token = localStorage.getItem("auth_token");
    if (!token) {
      console.error('❌ No authentication token found');
      router.push('/login');
      return;
    }
    
    // Fetch the current user if not already loaded
    if (!authStore.user) {
      console.log('👤 Fetching current user data...');
      const authResult = await authStore.fetchCurrentUser();
      
      if (!authResult.success) {
        console.error('❌ Authentication failed:', authResult.error);
        router.push('/login');
        return;
      }
    }
    
    console.log('✅ Authentication successful, user:', authStore.user);
    
    // Initialize WebSocket listeners
    console.log('🔌 Initializing WebSocket connection');
    messagesStore.initializeWebSocketListeners();
    
    // Wait a moment for the WebSocket connection to establish
    await new Promise(resolve => setTimeout(resolve, 500));
    
    // Get or create conversation
    console.log(`🔍 Fetching conversation with user ${receiverId}`);
    const result = await messagesStore.getOrCreateConversation(receiverId);
    console.log("📊 Conversation result:", result);
    
    if (result.success) {
      receiver.value = result.conversation.user;
      messages.value = result.conversation.messages;
      console.log(`✅ Loaded conversation with ${messages.value.length} messages`);
      console.log(`👤 Receiver details:`, {
        id: receiver.value.id,
        name: receiver.value.name,
        role: receiver.value.role || 'user'
      });
      
      // Explicitly check and log if the receiver is a creator
      if (receiver.value.role === 'creator') {
        console.log('👑 Receiver is a creator, special messaging rules apply');
      } else {
        console.log('👤 Receiver is a regular user, no special messaging rules');
      }
      
      // Set the context for media uploads
      mediaUploadStore.setContext(getMessageContextId.value);
      
      // Join presence channel to see if user is online
      console.log('👥 Joining presence channel to check online status');
      joinPresenceChannel();
      
      // Mark unread messages as read
      markUnreadMessagesAsRead(messages.value);
      
      // Fetch receiver's messaging settings
      await fetchReceiverSettings();
      
      // Only check following statuses if receiver is a creator
      if (isReceiverCreator.value) {
        // Check if the current user is following the receiver
        await checkFollowingStatus();
        
        // Check if the receiver follows the sender
        await checkReceiverFollowsSender();
      }
      
      // Scroll to bottom of messages
      nextTick(() => {
        scrollToBottom();
      });

      // Log messages with permission status
      console.log('🔒 Messages with permission status:', messages.value.map(m => ({
        id: m.id,
        has_permission: m.user_has_permission,
        has_media: m.media && m.media.length > 0,
        has_previews: m.media_previews && m.media_previews.length > 0
      })));
      
      // Log final permission state
      console.log('🔒 Final permission state:', {
        isReceiverCreator: isReceiverCreator.value,
        requireTip: receiverSettings.value.require_tip_for_messages,
        canSendWithoutTip: canSendWithoutTip.value,
        permissionsLoaded: permissionsLoaded.value
      });
    } else {
      error.value = result.error;
      console.error('❌ Error loading conversation:', result.error);
    }
  } catch (err) {
    error.value = "Failed to load conversation";
    console.error('❌ Exception in onMounted:', err);
  } finally {
    loading.value = false;
  }
});

// Clean up WebSocket connections when component is unmounted
onBeforeUnmount(() => {
  console.log('🧹 Cleaning up WebSocket connections');
  messagesStore.cleanup();
  leavePresenceChannel();
  
  // Clear typing timeout if it exists
  if (typingTimeout.value) {
    clearTimeout(typingTimeout.value);
  }
});

// Join presence channel to see who's online
const joinPresenceChannel = () => {
  if (websocketService.echo && receiver.value) {
    console.log('👥 Joining online presence channel');
    websocketService.echo.join('online')
      .here((users) => {
        // Check if receiver is in the list of online users
        const isOnline = users.some(user => user.id === receiver.value.id);
        isUserOnline.value = isOnline;
        console.log(`👤 User ${receiver.value.name} is ${isOnline ? 'online' : 'offline'}`);
        console.log('👥 Online users:', users.map(u => u.name).join(', '));
      })
      .joining((user) => {
        // If receiver joins, mark them as online
        if (user.id === receiver.value.id) {
          isUserOnline.value = true;
          console.log(`👤 User ${user.name} has come online`);
        }
      })
      .leaving((user) => {
        // If receiver leaves, mark them as offline
        if (user.id === receiver.value.id) {
          isUserOnline.value = false;
          console.log(`👤 User ${user.name} has gone offline`);
        }
      });
      
    // Join private typing channel
    console.log(`👂 Listening for typing events on private-user.${authStore.user.id}`);
    websocketService.echo.private(`private-user.${authStore.user.id}`)
      .listenForWhisper('typing', (e) => {
        // Only show typing indicator if it's from the current conversation
        if (e.sender_id === receiver.value.id) {
          console.log(`⌨️ ${e.sender_name || receiver.value.name} is typing...`);
          isTyping.value = true;
          
          // Clear typing indicator after 3 seconds
          if (typingTimeout.value) {
            clearTimeout(typingTimeout.value);
          }
          
          typingTimeout.value = setTimeout(() => {
            console.log(`⌨️ ${receiver.value.name} stopped typing`);
            isTyping.value = false;
          }, 3000);
        }
      });
  }
};

// Leave presence channel
const leavePresenceChannel = () => {
  if (websocketService.echo) {
    console.log('👋 Leaving online presence channel');
    websocketService.echo.leave('online');
  }
};

// Handle typing indicator with debounce
const handleTyping = () => {
  if (websocketService.echo && receiver.value) {
    // Emit typing event to the receiver's private channel
    console.log(`⌨️ Sending typing event to user ${receiver.value.id}`);
    
    // Make sure we're using the correct private channel format
    websocketService.echo.private(`private-user.${receiver.value.id}`)
      .whisper('typing', {
        sender_id: authStore.user.id,
        sender_name: authStore.user.name,
        receiver_id: receiver.value.id
      });
    
    // Clear existing timeout
    if (typingTimeout.value) {
      clearTimeout(typingTimeout.value);
    }
    
    // Set a new timeout - this prevents sending too many typing events
    typingTimeout.value = setTimeout(() => {
      typingTimeout.value = null;
    }, 2000); // Only send typing event every 2 seconds at most
  }
};

const handleEnterKeyPress = (event) => {
  // If shift+enter is pressed, allow new line
  if (event.shiftKey) {
    return;
  }
  
  // Otherwise, send the message
  sendMessage();
};

const expandInput = async () => {
  isExpanded.value = true;
  console.log('📝 Expanded input area');
  await nextTick();
  if (textareaRef.value) {
    textareaRef.value.focus();
  }
};

const collapseInput = () => {
  isExpanded.value = false;
  console.log('📝 Collapsed input area');
};

const handleUploadNew = () => {
  isMediaUploadModalOpen.value = true;
  console.log('🖼️ Opening media upload modal');
};

const handleFromVault = async () => {
  console.log('🗄️ Opening vault selector');
  
  // Show a modal or drawer with the user's vault content
  // For now, we'll simulate opening the vault with a simple alert
  try {
    // In a real implementation, you would:
    // 1. Fetch the user's vault albums/media
    // 2. Show a modal with the media items
    // 3. Allow the user to select items
    // 4. Add the selected items to the message
    
    // Simulate selecting media from vault
    const mockVaultMedia = [
      {
        id: 'vault-' + Date.now(),
        file: null, // No file since it's already uploaded
        url: 'https://example.com/vault-image.jpg',
        type: 'image',
        // Add preview versions if needed
        previewVersions: []
      }
    ];
    
    // Add the selected media to the message
    mediaUploadStore.addMediaFromVault(mockVaultMedia);
    
    console.log('✅ Added media from vault:', mockVaultMedia);
  } catch (error) {
    console.error('❌ Error accessing vault:', error);
  }
};

const closeUploadModal = () => {
  isMediaUploadModalOpen.value = false;
  console.log('🖼️ Closed media upload modal');
};

const handleMediaUpload = ({ files }) => {
  console.log('🖼️ Media uploaded:', files);
  closeUploadModal();
  
  // Force the component to re-render to show the previews
  nextTick(() => {
    console.log('🖼️ Media previews after upload:', mediaUploadStore.previews);
  });
};

const removeMedia = (id) => {
  console.log(`🗑️ Removing media with ID ${id}`);
  mediaUploadStore.removeMedia(id);
};

// Add more debugging logs in the sendMessage function to see what's happening
const sendMessage = async () => {
  // First check if permissions are loaded
  if (!permissionsLoaded.value) {
    console.log('⚠️ Cannot send message yet - permissions still loading');
    // Show a loading indicator or message to the user
    return;
  }

  if ((newMessage.value.trim() || mediaPreviews.value.length > 0) && !sendingMessage.value) {
    // Debug logs to understand what's happening
    console.log('📊 Message sending check:', {
      isReceiverCreator: isReceiverCreator.value,
      receiverRole: receiver.value?.role,
      canSendWithoutTip: canSendWithoutTip.value,
      requireTip: receiverSettings.value.require_tip_for_messages,
      acceptFromFollowed: receiverSettings.value.accept_messages_from_followed,
      receiverFollowsSender: receiverFollowsSender.value,
      permissionsLoaded: permissionsLoaded.value
    });
    
    // Check if we need to show the tip modal - only for creators
    if (isReceiverCreator.value && !canSendWithoutTip.value) {
      console.log('💰 Tip required to send message to creator');
      showTipModal.value = true;
      return;
    }
    
    // Otherwise proceed with sending the message
    await sendMessageToReceiver();
  }
};

// Actual message sending logic
const sendMessageToReceiver = async () => {
  try {
    sendingMessage.value = true;
    console.log('📤 Preparing to send message');
    
    // Use the mediaUploadStore.previews directly
    const mediaFiles = mediaUploadStore.previews || [];
    
    // Send the message using the store method
    console.log(`📤 Sending message to ${receiver.value.id} with ${mediaFiles.length} media files`);
    const result = await messagesStore.sendMessage(
      receiver.value.id,
      newMessage.value.trim(),
      mediaFiles
    );
    
    if (result.success) {
      console.log('✅ Message sent successfully');
      // Clear the input and media
      newMessage.value = '';
      mediaUploadStore.clearMedia();
      collapseInput();
      
      // The WebSocket will handle adding the message to the conversation
      // We don't need to manually update the state here
    } else {
      console.error('❌ Failed to send message:', result.error);
    }
  } catch (error) {
    console.error('❌ Error sending message:', error);
  } finally {
    sendingMessage.value = false;
  }
};

// Tip modal functions
const closeTipModal = () => {
  showTipModal.value = false;
  tipAmount.value = 10;
  customTipAmount.value = null;
};

// Update the sendTip function to only use wallet balance for message tips
const sendTip = async () => {
  if (!tipAmount.value || tipAmount.value < 1 || sendingTip.value) return;

  sendingTip.value = true;
  
  try {
    console.log(`💰 Sending tip of $${tipAmount.value} to ${receiver.value.id}`);
    
    // First process the tip payment BEFORE creating the message
    try {
      console.log('💰 Sending payment request to API');
      
      const response = await axiosInstance.post(`/tip`, {
        receiver_id: receiver.value.id,
        amount: tipAmount.value,
        currency: 'USD',
        context: 'message',
        payment_method: 'wallet' // Always use wallet for message tips
      });
      
      console.log('✅ Tip API response:', response.data);
      
      if (response.data.success) {
        // Close the tip modal
        closeTipModal();
        
        // For message tips, we should never get a redirect_required=true response
        // But just in case, let's handle it properly
        if (response.data.data.redirect_required) {
          console.error('❌ Unexpected redirect required for wallet payment');
          alert('Error: Unexpected payment redirect. Please contact support.');
          return;
        }
        
        // Payment was processed immediately with wallet
        console.log('✅ Tip processed successfully with wallet');
        
        // Now create and send the message since payment was successful
        const messageContent = newMessage.value.trim();
        const messageMedia = [...mediaUploadStore.previews || []];
        
        if (messageContent || messageMedia.length > 0) {
          console.log('📤 Creating message after successful payment');
          const messageResult = await messagesStore.sendMessage(
            receiver.value.id,
            messageContent,
            messageMedia,
            true // Set visibility to true since payment was successful
          );
          
          if (messageResult.success) {
            console.log(`✅ Message created with ID: ${messageResult.message.id}`);
            
            // Link the tip to the message
            if (response.data.data.transaction_id) {
              await messagesStore.linkTipToMessage(
                response.data.data.transaction_id,
                messageResult.message.id
              );
            }
            
            // Clear the input and media
            newMessage.value = '';
            mediaUploadStore.clearMedia();
            collapseInput();
          } else {
            console.error('❌ Failed to create message:', messageResult.error);
            alert(`Message could not be sent: ${messageResult.error}`);
          }
        }
      } else {
        throw new Error(response.data.message || 'Failed to process tip');
      }
    } catch (error) {
      console.error('❌ Error sending tip:', error);
      
      // Show appropriate error message
      if (error.response && error.response.data && error.response.data.error === 'insufficient_balance') {
        // Show a specific message for insufficient balance
        const walletBalance = error.response.data.data?.wallet_balance || 0;
        const requiredAmount = error.response.data.data?.required_amount || tipAmount.value;
        
        alert(`Insufficient wallet balance. Your current balance is $${walletBalance.toFixed(2)}. You need $${requiredAmount.toFixed(2)} to send this tip. Please top up your account and try again.`);
      } else if (error.response && error.response.data && error.response.data.error === 'wallet_required') {
        // Show a message that only wallet payments are allowed for message tips
        alert('Message tips can only be paid using wallet balance. Please top up your account and try again.');
      } else {
        // Show a generic error message for other errors
        alert(`Error sending tip: ${error.response?.data?.message || error.message || 'Network error occurred'}`);
      }
    }
  } catch (error) {
    console.error('❌ Error in tip process:', error);
    alert(`Error: ${error.message || 'Unknown error occurred'}`);
  } finally {
    sendingTip.value = false;
  }
};

const formatDate = (date) => {
  const messageDate = new Date(date);
  if (isToday(messageDate)) {
    return 'Today';
  } else if (isYesterday(messageDate)) {
    return 'Yesterday';
  } else {
    return format(messageDate, 'MMM d, yyyy');
  }
};

const formatTime = (date) => {
  return format(new Date(date), 'h:mm a');
};

const showDateSeparator = (message, index) => {
  if (index === 0) return true;
  
  // Make sure we have a valid previous message before accessing its properties
  const prevMessage = messages.value[index - 1];
  if (!prevMessage || !prevMessage.created_at || !message.created_at) {
    return false;
  }
  
  return !isSameDay(new Date(message.created_at), new Date(prevMessage.created_at));
};

// Media interaction handlers
const handleMediaLike = async (mediaId) => {
  try {
    console.log(`👍 Liking media ${mediaId}`);
    await messagesStore.likeMedia(mediaId);
  } catch (error) {
    console.error('❌ Error liking media:', error);
  }
};

const handleMediaBookmark = async (mediaId) => {
  try {
    console.log(`🔖 Bookmarking media ${mediaId}`);
    await messagesStore.bookmarkMedia(mediaId);
  } catch (error) {
    console.error('❌ Error bookmarking media:', error);
  }
};

const handleMediaStats = async (mediaId) => {
  try {
    console.log(`👁️ Viewing media stats for ${mediaId}`);
    await messagesStore.viewMedia(mediaId);
  } catch (error) {
    console.error('❌ Error viewing media stats:', error);
  }
};

// Permission handling
const handleUnlock = (message) => {
  console.log(`🔓 Opening unlock modal for message ${message.id}`, message);

  // Check if the message has media that needs unlocking
  if (!message.media || message.media.length === 0) {
    console.log('⚠️ No media to unlock in this message');
    return;
  }

  // Check if the message already has permissions
  if (message.user_has_permission) {
    console.log('✅ User already has permission for this message');
    return;
  }

  // Check if the message has required permissions
  if (!message.required_permissions || message.required_permissions.length === 0) {
    console.log('⚠️ No required permissions defined for this message');
    return;
  }

  // Set the selected message and show the unlock modal
  selectedMessage.value = message;
  showUnlockModal.value = true;
  
  console.log('🔓 Unlock modal opened with permissions:', message.required_permissions);
};

const closeUnlockModal = () => {
  console.log('🔓 Closing unlock modal');
  showUnlockModal.value = false;
  selectedMessage.value = null;
};

const processPermission = async (permission) => {
  if (processingPermission.value || !selectedMessage.value) return;
  
  processingPermission.value = true;
  try {
    console.log(`🔓 Processing permission for message ${selectedMessage.value.id}:`, permission);
    
    // Call the API to unlock the message
    const result = await messagesStore.unlockMessage({
      messageId: selectedMessage.value.id,
      permissions: [{ type: permission.type, value: permission.value }]
    });
    
    if (result) {
      console.log('✅ Permission processed successfully:', result);
      
      // Update the message in the messages array
      const index = messages.value.findIndex(m => m.id === selectedMessage.value.id);
      if (index !== -1) {
        // Update the message with the new data
        messages.value[index] = {
          ...messages.value[index],
          user_has_permission: true,
          media: result.media || messages.value[index].media
        };
        
        // Remove required_permissions since the user now has permission
        delete messages.value[index].required_permissions;
        
        console.log('✅ Updated message with new permissions');
      }
      
      // Close the modal
      closeUnlockModal();
    }
  } catch (error) {
    console.error('❌ Error processing permission:', error);
    // Show an error message to the user
  } finally {
    processingPermission.value = false;
  }
};
</script>

<style scoped>
/* Custom scrollbar styling */
.scrollbar-thin {
  scrollbar-width: thin;
}

.scrollbar-thin::-webkit-scrollbar {
  height: 4px;
}

.scrollbar-thin::-webkit-scrollbar-track {
  background: transparent;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
  background-color: var(--border-light);
  border-radius: 2px;
}

.dark .scrollbar-thin::-webkit-scrollbar-thumb {
  background-color: var(--border-dark);
}

/* Media container styling */
.media-container {
  max-width: 300px;
  width: 100%;
}

/* Add spacing between media and text when both are present */
.media-container + div {
  margin-top: 0.5rem;
}

/* Media preview container styling with animation */
.media-preview-container {
  height: auto;
  overflow: hidden;
}

/* Slide transition for media preview container */
.slide-enter-active,
.slide-leave-active {
  transition: all 0.3s ease;
  max-height: 100px;
}

.slide-enter-from,
.slide-leave-to {
  max-height: 0;
  opacity: 0;
  padding-top: 0;
  padding-bottom: 0;
}
</style>

