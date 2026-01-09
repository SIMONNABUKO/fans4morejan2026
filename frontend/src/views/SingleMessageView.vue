<template>
  <div class="flex flex-col h-screen bg-background-light dark:bg-background-dark">
    <div v-if="loading" class="flex items-center justify-center h-full">
      <p class="text-text-light-secondary dark:text-text-dark-secondary">{{ t('loading_conversation') }}</p>
    </div>
    <div v-else-if="error" class="flex items-center justify-center h-full">
      <p class="text-red-500">{{ error }}</p>
    </div>
    <template v-else-if="receiver">
      <!-- Custom Header -->
      <header class="fixed top-0 left-0 right-0 z-10 px-4 py-3 bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-lg border-b border-border-light dark:border-border-dark shadow-sm">
        <div class="flex items-center gap-4">
          <router-link 
            to="/messages" 
            class="p-2 -ml-2 rounded-full text-text-light-secondary dark:text-text-dark-secondary hover:bg-surface-light dark:hover:bg-surface-dark transition-colors"
            tabindex="0"
          >
            <i class="ri-arrow-left-line text-xl"></i>
          </router-link>
          
          <div class="flex items-center gap-3 flex-1 min-w-0">
            <div class="relative">
              <img v-if="receiver.avatar" :src="receiver.avatar" :alt="receiver.name" class="w-10 h-10 rounded-full object-cover ring-2 ring-border-light dark:ring-border-dark">
              <div v-else class="w-10 h-10 rounded-full bg-surface-light dark:bg-surface-dark flex items-center justify-center ring-2 ring-border-light dark:ring-border-dark">
                <i class="ri-user-3-line text-lg text-text-light-secondary dark:text-text-dark-secondary"></i>
              </div>
              <span v-if="isUserOnline" class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-background-light dark:border-background-dark shadow-sm"></span>
            </div>
            
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2">
                <span class="font-semibold truncate text-text-light-primary dark:text-text-dark-primary text-lg">{{ receiver.name }}</span>
                <i v-if="receiver.verified" class="ri-verified-badge-fill text-primary-light dark:text-primary-dark text-base"></i>
              </div>
              <div class="flex items-center gap-2 text-sm text-text-light-secondary dark:text-text-dark-secondary">
                <span>@{{ receiver.username }}</span>
                <span v-if="isUserOnline" class="flex items-center gap-1 text-green-500">
                  <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                  {{ t('online') }}
                </span>
              </div>
            </div>
          </div>
  
          <button 
            class="p-2 text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary hover:bg-surface-light dark:hover:bg-surface-dark rounded-full transition-colors"
            tabindex="0"
          >
            <i class="ri-more-fill text-xl"></i>
          </button>
        </div>
      </header>
  
      <!-- Messages -->
      <div ref="messagesContainer" class="flex-1 overflow-y-auto px-4 py-6 space-y-6 mt-[72px]" :style="{ paddingBottom: inputAreaHeight + 'px' }">
        <template v-if="messages.length">
          <template v-for="(message, index) in messages" :key="message.id">
            <!-- Date Separator -->
            <div v-if="showDateSeparator(message, index)" class="flex items-center justify-center my-6">
              <div class="bg-surface-light dark:bg-surface-dark px-4 py-2 rounded-full">
                <span class="text-sm font-medium text-text-light-secondary dark:text-text-dark-secondary">{{ formatDate(message.created_at) }}</span>
              </div>
            </div>
  
            <!-- Message -->
            <div :class="[
              'flex flex-col gap-2',
              message.sender_id === sender.id ? 'items-end' : 'items-start max-w-[80%]'
            ]">
              <div :class="[
                'flex items-end gap-3', 
                message.sender_id === sender.id ? 'flex-row-reverse' : ''
              ]">
                <!-- Profile Picture Container -->
                <div v-if="message.sender_id !== sender.id" 
                     class="flex-shrink-0 w-8 h-8 rounded-full bg-surface-light dark:bg-surface-dark flex items-center justify-center shadow-sm"
                     :class="{'mt-2': message.media && message.media.length > 0}">
                  <img v-if="receiver.avatar" 
                       :src="receiver.avatar" 
                       :alt="receiver.name" 
                       class="w-8 h-8 rounded-full object-cover">
                  <i v-else class="ri-user-3-line text-sm text-text-light-secondary dark:text-text-dark-secondary"></i>
                </div>
  
                <!-- Message Content Container -->
                <div class="flex flex-col gap-3">
                  <!-- Media Content -->
                  <div v-if="message.media && message.media.length > 0" 
                       class="media-container overflow-hidden rounded-2xl shadow-sm">
                    
                    <!-- PostGrid component for media display -->
                    <PostGrid
                      :media="hasMessagePermission(message) ? message.media : message.media_previews"
                      :author="message.sender_id === sender.id ? sender : receiver"
                      :description="message.content"
                      :user-has-permission="hasMessagePermission(message)"
                      :required-permissions="message.required_permissions"
                      :total-media-count="message.media.length"
                      :is-message-media="true"
                      @mediaLike="handleMediaLike"
                      @mediaBookmark="handleMediaBookmark"
                      @mediaStats="handleMediaStats"
                      @unlock="() => handleUnlock(message)"
                    />
                  </div>
  
                  <!-- Text Content -->
                  <div v-if="message.content" 
                       :class="[
                         message.sender_id === sender.id 
                           ? 'bg-gradient-to-r from-primary-light to-primary-dark text-white shadow-lg' 
                           : 'bg-white dark:bg-gray-800 text-text-light-primary dark:text-text-dark-primary shadow-sm border border-gray-100 dark:border-gray-700',
                         'rounded-2xl px-5 py-3 max-w-sm',
                         message.sender_id === sender.id ? 'rounded-br-md' : 'rounded-bl-md'
                       ]">
                    <p class="text-sm leading-relaxed">{{ message.content }}</p>
                  </div>
                </div>
              </div>
  
              <!-- Timestamp and Read Status -->
              <div class="flex items-center gap-2 text-xs text-text-light-tertiary dark:text-text-dark-tertiary" 
                   :class="{ 'justify-end': message.sender_id === sender.id }">
                <span class="font-medium">{{ formatTime(message.created_at) }}</span>
                <!-- Only show read receipts if receiver has enabled them -->
                <span v-if="message.sender_id === sender.id && message.read_at && receiverSettings.show_read_receipts" class="text-primary-light dark:text-primary-dark">
                  <i class="ri-check-double-line text-sm"></i>
                </span>
                <span v-else-if="message.sender_id === sender.id" class="text-text-light-tertiary dark:text-text-dark-tertiary">
                  <i class="ri-check-line text-sm"></i>
                </span>
              </div>
            </div>
          </template>
        </template>
        <div v-else class="flex items-center justify-center h-full">
          <div class="text-center">
            <i class="ri-message-3-line text-4xl text-text-light-tertiary dark:text-text-dark-tertiary mb-3"></i>
            <p class="text-text-light-secondary dark:text-text-dark-secondary font-medium">{{ t('no_messages_yet') }}</p>
            <p class="text-sm text-text-light-tertiary dark:text-text-dark-tertiary mt-1">Start a conversation by sending a message</p>
          </div>
        </div>
      </div>
  
      <!-- Message Input Section -->
      <div ref="inputArea" class="input-area-transition fixed bottom-[56px] left-0 right-0 z-50 border-t border-border-light dark:border-border-dark bg-background-light/98 dark:bg-background-dark/98 backdrop-blur-xl shadow-2xl">
        <!-- Typing Indicator -->
        <div v-if="isTyping" class="px-4 pt-3 pb-2">
          <div class="flex items-center gap-3 text-sm text-text-light-secondary dark:text-text-dark-secondary">
            <div class="flex gap-1">
              <span class="w-2 h-2 bg-primary-light dark:bg-primary-dark rounded-full animate-bounce"></span>
              <span class="w-2 h-2 bg-primary-light dark:bg-primary-dark rounded-full animate-bounce" style="animation-delay: 0.1s"></span>
              <span class="w-2 h-2 bg-primary-light dark:bg-primary-dark rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
            </div>
            <span class="font-medium">{{ t('is_typing', { name: receiver.name }) }}</span>
          </div>
        </div>
        
        <!-- Media Preview Area -->
        <div v-if="mediaPreviews && mediaPreviews.length > 0" class="px-4 pt-3 pb-2">
          <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-thin scrollbar-thumb-border-light dark:scrollbar-thumb-border-dark scrollbar-track-transparent">
            <div 
              v-for="preview in mediaPreviews" 
              :key="preview.id"
              class="relative w-20 h-20 flex-shrink-0 group"
            >
              <img 
                v-if="preview.type === 'image'" 
                :src="preview.url" 
                class="w-full h-full object-cover rounded-xl border-2 border-border-light dark:border-border-dark shadow-sm"
                alt="Media preview"
              />
              <video 
                v-else 
                :src="preview.url" 
                class="w-full h-full object-cover rounded-xl border-2 border-border-light dark:border-border-dark shadow-sm"
              ></video>
              <button 
                @click="removeMedia(preview.id)"
                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center shadow-lg hover:bg-red-600 transition-colors"
                tabindex="0"
              >
                <i class="ri-close-line text-sm"></i>
              </button>
            </div>
          </div>
        </div>
        
        <!-- Input Area -->
        <div class="px-4 py-4">
          <!-- Collapsed State: Single line input with action buttons -->
          <Transition name="expand" mode="out-in">
            <div v-if="!isExpanded" key="collapsed" class="flex items-center gap-3">
              <!-- Action Buttons -->
              <div class="flex gap-1">
                <ImageUploadMenu 
                  @upload-new="handleUploadNew"
                  @from-vault="handleFromVault"
                />
                <button class="p-3 text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary hover:bg-surface-light dark:hover:bg-surface-dark rounded-full transition-colors">
                  <i class="ri-file-gif-line text-xl"></i>
                </button>
                <button class="p-3 text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary hover:bg-surface-light dark:hover:bg-surface-dark rounded-full transition-colors">
                  <i class="ri-menu-line text-xl"></i>
                </button>
                <button class="p-3 text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary hover:bg-surface-light dark:hover:bg-surface-dark rounded-full transition-colors">
                  <i class="ri-mic-line text-xl"></i>
                </button>
              </div>
              
              <!-- Single Line Input Container -->
              <div class="flex-1 message-input-container rounded-2xl shadow-sm px-4 py-3">
                <input
                  type="text"
                  v-model="newMessage"
                  :placeholder="t('message_user', { username: receiver.name })"
                  class="w-full bg-transparent text-text-light-primary dark:text-text-dark-primary placeholder:text-text-light-tertiary dark:placeholder:text-text-dark-tertiary focus:outline-none text-sm"
                  @focus="expandInput"
                  @input="handleTyping"
                  @keydown.enter.prevent="sendMessage"
                />
              </div>
              
              <!-- Send Button (Collapsed) -->
              <button 
                v-if="canSendMessage"
                @click="sendMessage"
                :disabled="sendingMessage"
                class="send-button p-3 bg-gradient-to-r from-primary-light to-primary-dark text-white rounded-full shadow-lg disabled:opacity-50"
                tabindex="0"
              >
                <i v-if="sendingMessage" class="ri-loader-4-line text-xl animate-spin"></i>
                <i v-else class="ri-send-plane-fill text-xl"></i>
              </button>
            </div>

            <!-- Expanded State: Full-width textarea -->
            <div v-else key="expanded" class="w-full relative">
              <div class="message-input-container rounded-2xl shadow-sm">
                <textarea
                  ref="textareaRef"
                  v-model="newMessage"
                  :placeholder="t('message_user', { username: receiver.name })"
                  class="w-full bg-transparent text-text-light-primary dark:text-text-dark-primary placeholder:text-text-light-tertiary dark:placeholder:text-text-dark-tertiary focus:outline-none resize-none px-4 py-4 pr-16 text-sm leading-relaxed min-h-[120px]"
                  rows="4"
                  @blur="handleBlur"
                  @input="handleTyping"
                  @keydown="handleKeyDown"
                ></textarea>
                
                <!-- Send Button (Expanded) -->
                <button 
                  @click="sendMessage"
                  :disabled="!canSendMessage || sendingMessage"
                  class="send-button absolute bottom-3 right-3 p-3 bg-gradient-to-r from-primary-light to-primary-dark text-white rounded-full shadow-lg disabled:opacity-50"
                  tabindex="0"
                >
                  <i v-if="sendingMessage" class="ri-loader-4-line text-lg animate-spin"></i>
                  <i v-else class="ri-send-plane-fill text-lg"></i>
                </button>
              </div>
            </div>
          </Transition>
        </div>
      </div>
  
      <!-- Media Upload Modal -->
      <MediaUploadModal 
        :is-open="isMediaUploadModalOpen"
        :context-id="getMessageContextId"
        @close="closeUploadModal"
        @upload="handleMediaUpload"
      />
      
      <!-- UnlockBundleModal for message media -->
      <UnlockBundleModal
        v-if="selectedMessage"
        :is-open="showUnlockModal"
        :required-permissions="selectedMessage ? selectedMessage.required_permissions || [] : []"
        :post-owner-id="String(receiver ? receiver.id : '')"
        :post-id="String(selectedMessage ? selectedMessage.id : '')"
        :entity-type="'App\\Models\\Message'"
        @close="closeUnlockModal"
        @follow="handleFollow"
        @subscribe="handleSubscribe"
        @pay="handlePayment"
      />
  
      <!-- Tip Modal - Updated to use dollars -->
      <div v-if="showTipModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
        <div class="bg-background-light dark:bg-background-dark rounded-2xl p-8 max-w-md w-full mx-4 shadow-2xl border border-border-light dark:border-border-dark">
          <div class="text-center mb-6">
            <div class="w-16 h-16 bg-gradient-to-r from-primary-light to-primary-dark rounded-full flex items-center justify-center mx-auto mb-4">
              <i class="ri-heart-fill text-white text-2xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-text-light-primary dark:text-text-dark-primary mb-2">Send a Tip</h3>
            <p class="text-text-light-secondary dark:text-text-dark-secondary">
              {{ receiver.name }} requires a tip to receive messages.
            </p>
          </div>
          
          <div class="space-y-6 mb-8">
            <div class="grid grid-cols-3 gap-3">
              <button 
                v-for="amount in [5, 10, 20]" 
                :key="amount"
                @click="tipAmount = amount"
                :class="[
                  'py-4 rounded-xl border-2 transition-all duration-200 font-semibold',
                  tipAmount === amount 
                    ? 'bg-gradient-to-r from-primary-light to-primary-dark border-primary-light dark:border-primary-dark text-white shadow-lg scale-105' 
                    : 'border-border-light dark:border-border-dark hover:bg-surface-light dark:hover:bg-surface-dark hover:border-primary-light dark:hover:border-primary-dark hover:scale-105'
                ]"
              >
                ${{ amount }}
              </button>
            </div>
            
            <div class="flex items-center gap-3">
              <div class="relative flex-1">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-text-light-tertiary dark:text-text-dark-tertiary text-lg font-medium">$</span>
                <input
                  type="number"
                  v-model.number="customTipAmount"
                  placeholder="Custom amount"
                  min="1"
                  class="w-full p-4 pl-8 rounded-xl border-2 border-border-light dark:border-border-dark bg-surface-light dark:bg-surface-dark text-text-light-primary dark:text-text-dark-primary focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:border-primary-light dark:focus:border-primary-dark transition-all"
                  @input="tipAmount = customTipAmount"
                />
              </div>
            </div>
          </div>
          
          <div class="flex gap-3">
            <button 
              @click="closeTipModal"
              class="flex-1 px-6 py-3 text-text-light-primary dark:text-text-dark-primary hover:bg-surface-light dark:hover:bg-surface-dark rounded-xl transition-colors border border-border-light dark:border-border-dark font-medium"
            >
              Cancel
            </button>
            <button 
              @click="sendTip"
              :disabled="!tipAmount || tipAmount < 1 || sendingTip"
              class="flex-1 px-6 py-3 bg-gradient-to-r from-primary-light to-primary-dark text-white rounded-xl hover:shadow-lg transition-all disabled:opacity-50 font-semibold"
            >
              <span v-if="sendingTip" class="flex items-center justify-center gap-2">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                Sending...
              </span>
              <span v-else>Send ${{ tipAmount }}</span>
            </button>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useMessagesStore } from '@/stores/messagesStore';
import { useMediaUploadStore } from '@/stores/mediaUploadStore';
import { useAuthStore } from '@/stores/authStore';
import { format, isToday, isYesterday, isSameDay } from 'date-fns';
import MediaUploadModal from '@/components/posts/MediaUploadModal.vue';
import ImageUploadMenu from '@/components/posts/ImageUploadMenu.vue';
import PostGrid from '@/components/user/PostGrid.vue';
import { useWebSocketService } from '@/services/websocketService';
import axiosInstance from '@/axios';
import UnlockBundleModal from '@/components/modals/UnlockBundleModal.vue';
import { useSubscriptionStore } from '@/stores/subscriptionStore';
import { useToast } from 'vue-toastification';
import { useI18n } from 'vue-i18n';

const route = useRoute();
const router = useRouter();
const messagesStore = useMessagesStore();
const authStore = useAuthStore();
const mediaUploadStore = useMediaUploadStore();
const websocketService = useWebSocketService();
const subscriptionStore = useSubscriptionStore();
const toast = useToast();
const { t } = useI18n();

// Debug mode for development
const debugMode = ref(false);

// Add this after the debugMode ref declaration (around line 118)
const logMessagePermissions = (message, context = 'general') => {
  // Simplified for production
  console.log(`Message ${message.id} permissions checked`);
};

// Add this function after the logAllMessagesPermissions function (around line 150)
const logConversationData = () => {
  console.log('Conversation data logged');
};

const loading = ref(true);
const error = ref(null);
const sender = computed(() => authStore.user);
const receiver = ref(null);
const messages = ref([]);
const newMessage = ref('');
const isExpanded = ref(false);
const textareaRef = ref(null);
const inputArea = ref(null);
const inputAreaHeight = ref(140); // Default height
const isMediaUploadModalOpen = ref(false);
const sendingMessage = ref(false);
const showUnlockModal = ref(false);
const selectedMessage = ref(null);
const processingPermission = ref(false);
const messagesContainer = ref(null);
const isTyping = ref(false);
const isUserOnline = ref(false);
const typingTimeout = ref(null);

// New ref to store permissions from the media upload modal
const mediaPermissions = ref([]);

// New refs for user settings and tip functionality
const receiverSettings = ref({
  show_read_receipts: false,
  require_tip_for_messages: true, // Default to true to be safe
  accept_messages_from_followed: true
});
const showTipModal = ref(false);
const tipAmount = ref(10);
const customTipAmount = ref(null);
const sendingTip = ref(false);
const settingsLoaded = ref(false);
const isFollowingReceiver = ref(false);
const receiverFollowsSender = ref(false);
const isReceiverCreator = computed(() => receiver.value && receiver.value.role === 'creator');

// Create a context ID for this conversation
const getMessageContextId = computed(() => {
  return receiver.value ? `message-${receiver.value.id}` : 'message-new';
});

// Set the context when the component mounts or receiver changes
watch(() => getMessageContextId.value, (newContextId) => {
  mediaUploadStore.setContext(newContextId);
}, { immediate: true });

// Computed property to access the previews
const mediaPreviews = computed(() => {
  return mediaUploadStore.previews || [];
});

// Computed property to check if message can be sent
const canSendMessage = computed(() => {
  return (newMessage.value.trim() || mediaPreviews.value.length > 0) && !sendingMessage.value;
});

// Helper function to determine if user has permission to view message media
const hasMessagePermission = (message) => {
  // The sender of the message always has permission
  if (message.sender_id === sender.value?.id) {
    return true;
  }

  // Check direct user_has_permission flag
  if (message.user_has_permission === true) {
    return true;
  }

  // If no explicit permission and not the sender, assume no permission
  return false;
};

// Add this after the hasMessagePermission function (around line 150)
const logAllMessagesPermissions = () => {
  console.log('All message permissions logged');
};

// Add this function after the logAllMessagesPermissions function
const logPermissions = (permissions, context = 'general') => {
  console.log(`🔑 [PERMISSIONS LOG - ${context}]`, permissions);
  
  // Also log the structure to help debug
  if (permissions) {
    console.log(`🔑 [PERMISSIONS STRUCTURE - ${context}]`, 
      `Is Array: ${Array.isArray(permissions)}`,
      `Length: ${Array.isArray(permissions) ? permissions.length : 'N/A'}`,
      `First item is Array: ${Array.isArray(permissions) && permissions.length > 0 ? Array.isArray(permissions[0]) : 'N/A'}`
    );
  }
};

// Replace the canSendWithoutTip computed property with this corrected version
const canSendWithoutTip = computed(() => {
  // If sender is a creator...
  if (sender.value && sender.value.role === 'creator') {
    // If receiver is also a creator and requires tips, then it's not free
    if (isReceiverCreator.value && receiverSettings.value.require_tip_for_messages) {
      return false;
    }
    
    // Otherwise, creator can send without tip (to non-creators or creators who don't require tips)
    return true;
  }

  // If receiver is not a creator, anyone can send messages
  if (!isReceiverCreator.value) {
    return true;
  }

  // If receiver is a creator but doesn't require tips, anyone can send
  // (user to creator with no permission requirements)
  if (!receiverSettings.value.require_tip_for_messages) {
    return true;
  }

  // If receiver accepts messages from followed users and receiver follows sender
  if (receiverSettings.value.accept_messages_from_followed && receiverFollowsSender.value) {
    return true;
  }

  // In all other cases, a tip is required
  return false;
});

// Add a new ref to track if permissions are fully loaded
const permissionsLoaded = ref(false);

// Add a flag to track if this is the first load
const isFirstLoad = ref(true);

// Update the fetchReceiverSettings function to be more aggressive and synchronous
const fetchReceiverSettings = async () => {
  if (!receiver.value || !receiver.value.id) return;

  try {
    // Only check message settings if the receiver is a creator
    if (isReceiverCreator.value) {
      // IMPORTANT: For creators, ALWAYS set require_tip_for_messages to true first
      receiverSettings.value = {
        ...receiverSettings.value,
        require_tip_for_messages: true
      };

      // Use the store method to get messaging settings
      // IMPORTANT: Make sure we wait for the API call to complete
      const result = await messagesStore.checkMessagePermissions(receiver.value.id);

      if (result.success) {
        // For creators, ONLY set require_tip_for_messages to false if explicitly false in API
        receiverSettings.value = {
          show_read_receipts: result.data.show_read_receipts === true || result.data.show_read_receipts === 'true',
          require_tip_for_messages: result.data.require_tip_for_messages === true || result.data.require_tip_for_messages === 'true',
          accept_messages_from_followed: result.data.accept_messages_from_followed === true || result.data.accept_messages_from_followed === 'true'
        };
        settingsLoaded.value = true;
      } else {
        // Use default settings if fetch fails - for creators, default to requiring tips
        receiverSettings.value = {
          show_read_receipts: false,
          require_tip_for_messages: true, // Always default to true for creators
          accept_messages_from_followed: true
        };
        settingsLoaded.value = true;
      }
    } else {
      // For non-creators, use default settings that allow messaging without restrictions
      receiverSettings.value = {
        show_read_receipts: true,
        require_tip_for_messages: false,
        accept_messages_from_followed: true
      };
      settingsLoaded.value = true;
    }
    // Set permissionsLoaded to true after settings are loaded
    permissionsLoaded.value = true;
  } catch (error) {
    // Handle error if needed
    settingsLoaded.value = true;
    permissionsLoaded.value = true;
  }
};

// Check if the current user is following the receiver
const checkFollowingStatus = async () => {
  if (!receiver.value || !receiver.value.id || !authStore.user) return;

  // Only check follow status if the receiver is a creator
  if (!isReceiverCreator.value) return;

  try {
    // Use the same endpoint that messagesStore.checkFollowStatus uses
    const result = await messagesStore.checkFollowStatus(receiver.value.id);
    
    isFollowingReceiver.value = result.isFollowing || false;
  } catch (error) {
    console.error('❌ Error checking following status:', error);
    isFollowingReceiver.value = false;
  }
};

// Check if the receiver follows the sender
const checkReceiverFollowsSender = async () => {
  if (!receiver.value || !receiver.value.id || !authStore.user) return;

  // Only check if the receiver is a creator
  if (!isReceiverCreator.value) return;

  try {
    const result = await messagesStore.checkFollowStatus(receiver.value.id);
    
    receiverFollowsSender.value = result.isFollowing || false;
  } catch (error) {
    console.error('❌ Error checking if receiver follows sender:', error);
    receiverFollowsSender.value = false;
  }
};

// Join presence channel to see if user is online
const joinPresenceChannel = () => {
  if (!receiver.value || !receiver.value.id) return;

  try {
    // Join the presence channel for this user
    websocketService.joinPresenceChannel(`user.${receiver.value.id}`, {
      onJoin: (userId) => {
        // If the receiver joins, mark them as online
        if (userId === receiver.value.id) {
          isUserOnline.value = true;
        }
      },
      onLeave: (userId) => {
        // If the receiver leaves, mark them as offline
        if (userId === receiver.value.id) {
          isUserOnline.value = false;
        }
      },
      onPresenceList: (users) => {
        // Check if the receiver is in the presence list
        isUserOnline.value = users.some(user => user.id === receiver.value.id);
      }
    });
  } catch (error) {
    // Silently fail if presence channel can't be joined
    isUserOnline.value = false;
  }
};

// Expand input to full width textarea
const expandInput = () => {
  isExpanded.value = true;
  nextTick(() => {
    if (textareaRef.value) {
      textareaRef.value.focus();
    }
    updateInputAreaHeight();
  });
};

// Collapse input back to single line with buttons
const collapseInput = () => {
  isExpanded.value = false;
  nextTick(() => {
    updateInputAreaHeight();
  });
};

// Handle blur event - only collapse if no content and no media
const handleBlur = () => {
  // Small delay to allow click events to register first
  setTimeout(() => {
    if (newMessage.value.trim() === '' && !mediaPreviews.value.length) {
      collapseInput();
    }
  }, 150);
};

// Update input area height
const updateInputAreaHeight = () => {
  if (inputArea.value) {
    const rect = inputArea.value.getBoundingClientRect();
    inputAreaHeight.value = rect.height + 20; // Add some extra padding
  }
};

// Handle key down events
const handleKeyDown = (e) => {
  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault();
    if (canSendMessage.value) {
      sendMessage();
    }
  }
};

const handleTyping = () => {
  // Clear any existing timeout
  if (typingTimeout.value) {
    clearTimeout(typingTimeout.value);
  }

  // Emit typing event to the server
  try {
    websocketService.emitTyping(receiver.value.id);
  } catch (error) {
    // Silently fail if typing event can't be sent
  }

  // Set a timeout to clear the typing status
  typingTimeout.value = setTimeout(() => {
    // Could emit a "stopped typing" event here if needed
  }, 3000);
};

const removeMedia = (mediaId) => {
  mediaUploadStore.removeMedia(mediaId);
};

const handleUploadNew = () => {
  isMediaUploadModalOpen.value = true;
};

const handleFromVault = () => {
  // This would open a media vault selection modal
  // Not implemented in this example
};

const closeUploadModal = () => {
  isMediaUploadModalOpen.value = false;
};

// Replace the handleMediaUpload function with this simple version
const handleMediaUpload = (media) => {
  console.log('🔍 MEDIA UPLOAD EVENT RECEIVED:', media);
  
  // Log permissions from the store
  if (mediaUploadStore.currentContent && mediaUploadStore.currentContent.permissions) {
    logPermissions(mediaUploadStore.currentContent.permissions, 'AFTER UPLOAD');
  } else {
    console.log('❌ NO PERMISSIONS IN STORE AFTER UPLOAD');
  }
  
  isMediaUploadModalOpen.value = false;
};

// Scroll to bottom of messages
const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
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
    // Mark each unread message as read
    unreadMessages.forEach((message) => {
      messagesStore.markMessageAsRead(message.id);
    });
  }
};

// Now the watch statement can reference the function
// Watch for changes in messages to scroll to bottom and mark unread messages as read
watch(() => messages.value.length, () => {
  scrollToBottom();

  // Mark unread messages as read when messages are loaded or updated
  if (messages.value.length > 0) {
    markUnreadMessagesAsRead(messages.value);
  }
}, { immediate: true });

// Add a watch for messages to log when new messages are added
// Add this after the existing watch for messages.value.length
watch(messages, (newMessages, oldMessages) => {
}, { deep: true });

// Watch for input area height changes
watch([mediaPreviews, isTyping, isExpanded], () => {
  nextTick(() => {
    updateInputAreaHeight();
  });
}, { deep: true });

// Update the onMounted function to ensure permissions are loaded immediately
onMounted(async () => {
  const receiverId = route.params.id;
  loading.value = true;
  error.value = null;
  permissionsLoaded.value = false; // Reset permissions loaded state

  // Enable debug mode in development
  if (process.env.NODE_ENV !== 'production') {
    debugMode.value = true;
    console.log('🐞 Debug mode enabled');
  }

  try {
    // First, ensure the auth state is loaded
    
    // Make sure we have a valid token before proceeding
    const token = localStorage.getItem("auth_token");
    if (!token) {
      console.error('❌ No authentication token found');
      router.push('/login');
      return;
    }
    
    // Fetch the current user if not already loaded
    if (!authStore.user) {
      const authResult = await authStore.fetchCurrentUser();
      
      if (!authResult.success) {
        console.error('❌ Authentication failed:', authResult.error);
        router.push('/login');
        return;
      }
    }
    
    // Initialize WebSocket listeners
    messagesStore.initializeWebSocketListeners();
    
    // Wait a moment for the WebSocket connection to establish
    await new Promise(resolve => setTimeout(resolve, 500));
    
    // Get or create conversation
    const result = await messagesStore.getOrCreateConversation(receiverId);
    
    if (result.success) {
      receiver.value = result.conversation.user;
      messages.value = result.conversation.messages;

      
      // Explicitly check and log if the receiver is a creator
      if (receiver.value.role === 'creator') {
        console.log('🔒 Receiver is a creator, setting default permissions');
        receiverSettings.value = {
          show_read_receipts: false,
          require_tip_for_messages: true, // Default to requiring tips for creators
          accept_messages_from_followed: true
        };
      }
      
      // Set the context for media uploads
      mediaUploadStore.setContext(getMessageContextId.value);
      
      // Join presence channel to see if user is online
      joinPresenceChannel();
      
      // Mark unread messages as read
      markUnreadMessagesAsRead(messages.value);
      
      // IMPORTANT: Fetch receiver's messaging settings IMMEDIATELY
      // This is now a blocking operation - we wait for it to complete
      await fetchReceiverSettings();
      
      // Only check following statuses if receiver is a creator
      if (isReceiverCreator.value) {
        try {
          // These can run in parallel for better performance
          await Promise.all([
            checkFollowingStatus(),
            checkReceiverFollowsSender()
          ]);
          
        } catch (error) {
          console.error('❌ Error checking follow status:', error);
          // Set safe defaults if follow status check fails
          isFollowingReceiver.value = false;
          receiverFollowsSender.value = false;
        }
      }
      
      // Now that all permissions are loaded, set permissionsLoaded to true
      permissionsLoaded.value = true;
      
      // Scroll to bottom of messages
      nextTick(() => {
        scrollToBottom();
        // Update input area height after everything is loaded
        updateInputAreaHeight();
      });
    } else {
      error.value = result.error;
      console.error('❌ Failed to load conversation:', result.error);
    }
  } catch (err) {
    error.value = "Failed to load conversation";
    console.error('❌ Exception in onMounted:', err);
  } finally {
    loading.value = false;
    // Ensure input area height is set even on error
    nextTick(() => {
      updateInputAreaHeight();
    });
  }
});

// Improved handleUnlock method with better logging and error handling
const handleUnlock = (message) => {
  // Check if the message has media that needs unlocking
  if (!message.media || message.media.length === 0) {
    console.log('⚠️ No media to unlock in this message');
    return;
  }

  // Check if the message already has permissions
  if (hasMessagePermission(message)) {
    console.log('✅ User already has permission for this message');
    return;
  }

  // Check if the message has required permissions
  if (!message.required_permissions || !Array.isArray(message.required_permissions) || message.required_permissions.length === 0) {
    console.log('⚠️ No required permissions defined for this message:', message.required_permissions);
    toast.error("Permission requirements not defined for this content");
    return;
  }

  // Set the selected message and show the unlock modal
  selectedMessage.value = message;
  showUnlockModal.value = true;
};

// Improved closeUnlockModal method
const closeUnlockModal = () => {
  showUnlockModal.value = false;
  
  // Small delay before clearing the selected message to prevent UI flicker
  setTimeout(() => {
    selectedMessage.value = null;
  }, 300);
};

// Improved refreshMessage method with proper error handling and full message update
const refreshMessage = async (messageId) => {
  try {
    const result = await messagesStore.getMessageById(messageId);
    
    if (result.success && result.message) {
      
      // Find and update the message in the messages array
      const index = messages.value.findIndex(m => m.id === messageId);
      
      if (index !== -1) {
        
        // Replace the entire message with the refreshed version
        messages.value[index] = result.message;
        
        // Force UI update
        messages.value = [...messages.value];
      } else {
        console.warn(`⚠️ Message ${messageId} not found in current message list`);
      }
      
      return true;
    } else {
      console.error(`❌ Failed to refresh message:`, result.error);
      return false;
    }
  } catch (error) {
    console.error('❌ Error refreshing message:', error);
    return false;
  }
};

// Handle follow action for messages
const handleFollow = async (userId) => {
  try {
    const result = await messagesStore.followUser(userId);
    
    if (result.success) {
      toast.success(result.message || "Successfully followed user");
      
      // Update following status
      isFollowingReceiver.value = true;
      
      // Refresh the message to update permissions
      if (selectedMessage.value) {
        await refreshMessage(selectedMessage.value.id);
      }
      
      closeUnlockModal();
    } else {
      toast.error(result.error || "Failed to follow user");
    }
  } catch (error) {
    console.error('❌ Error following user:', error);
    toast.error("Failed to follow user");
  }
};

// Handle subscribe action for messages
const handleSubscribe = async ({ tierId, duration, postOwnerId }) => {
  try {
    // Prevent any default behavior
    event?.preventDefault?.();
    
    const result = await messagesStore.subscribeTier(
      tierId,
      duration,
      postOwnerId
    );
    
    if (result.success) {
      // Check if payment was processed via wallet (no redirect needed)
      if (result.data?.payment_method === 'wallet' && !result.data?.redirect_required) {
        toast.success(result.message || "Payment processed successfully using your wallet balance");
        
        // Refresh the message to update permissions
        if (selectedMessage.value) {
          await refreshMessage(selectedMessage.value.id);
        }
        
        closeUnlockModal();
      } else if (result.data?.payment_url && typeof result.data.payment_url === 'string') {
        // For CCBill payments, show a different message and open payment URL
        toast.success("Please complete your payment on the payment page");
        // Open in new tab instead of changing current URL
        window.open(result.data.payment_url, "_blank");
      } else {
        toast.success(result.message || "Subscription initiated");
      }
    } else {
      // Handle specific error cases
      if (result.error === 'insufficient_balance') {
        toast.error("Insufficient wallet balance. Please add funds to your wallet.");
      } else {
        toast.error(result.error || "Failed to process subscription");
      }
    }
  } catch (error) {
    console.error('❌ Error in handleSubscribe:', error);
    toast.error("Failed to subscribe to tier");
  }
};

// Improved handlePayment for messages
const handlePayment = async (amount) => {
  try {
    // Prevent any default behavior
    event?.preventDefault?.();
    
    if (!selectedMessage.value || !selectedMessage.value.id) {
      toast.error("No message selected for purchase");
      return;
    }
    
    // Add this logging to verify entity type
    
    const result = await messagesStore.purchaseMessage(
      selectedMessage.value.id, 
      receiver.value.id, 
      amount
    );
    
    if (result.success) {
      // Check if payment was processed via wallet (no redirect needed)
      if (result.data?.payment_method === 'wallet' && !result.data?.redirect_required) {
        toast.success("Content unlocked successfully using your wallet balance");

        // Modify the handlePayment function to add more logging (around line 650)
        // Add this after the successful payment but before refreshing the message
        if (result.success && result.data?.payment_method === 'wallet' && !result.data?.redirect_required) {
          
          // IMPORTANT: Refresh the message to update permissions
          const refreshed = await refreshMessage(selectedMessage.value.id);
          
          // After refreshing the message, add:
          if (refreshed) {
            
          }
        }
        
        // IMPORTANT: Refresh the message to update permissions
        const refreshed = await refreshMessage(selectedMessage.value.id);
        
        if (refreshed) {
          
        } else {
          console.error('❌ Failed to refresh message after purchase');
          // Try one more time after a brief delay
          setTimeout(async () => {
            await refreshMessage(selectedMessage.value.id);
          }, 1000);
        }
        
        closeUnlockModal();
      } else if (result.data?.payment_url && typeof result.data.payment_url === 'string') {
        // For CCBill payments, show a different message and open payment URL
        toast.success("Please complete your payment on the payment page");
        window.open(result.data.payment_url, "_blank");
      } else {
        toast.success(result.message || "Payment initiated");
      }
    } else {
      // Handle specific error cases
      if (result.error === 'insufficient_balance') {
        toast.error("Insufficient wallet balance. Please add funds to your wallet.");
      } else {
        toast.error(result.error || "Failed to process payment");
      }
    }
  } catch (error) {
    console.error('❌ Error processing payment:', error);
    toast.error("Failed to process payment");
  }
};

// Update the sendMessage function to double-check permissions before sending
const sendMessage = async () => {
  // First check if permissions are loaded
  if (!permissionsLoaded.value) {
    console.log('⚠️ Cannot send message: Permissions not yet loaded');
    return;
  }

  if ((newMessage.value.trim() || mediaPreviews.value.length > 0) && !sendingMessage.value) {
    // IMPORTANT: Double-check if receiver is a creator and requires tip
    if (isReceiverCreator.value) {
      
      // For creators, always check if we need to show the tip modal
      if (!canSendWithoutTip.value) {
        
        showTipModal.value = true;
        return;
      } else {
        
      }
    }
    
    // Otherwise proceed with sending the message
    await sendMessageToReceiver();
  }
};

// Add a watch for receiverSettings to ensure we update canSendWithoutTip when settings change
watch(() => receiverSettings.value, (newSettings) => {
}, { deep: true });

// Add a watch for receiver.role to ensure we update isReceiverCreator when role changes
watch(() => receiver.value?.role, (newRole) => {

  // If role changes, we should re-fetch settings
  if (receiver.value) {
    
    fetchReceiverSettings();
  }
});

// Find the sendMessageToReceiver function and update it to ensure we're not flattening the permissions array
const sendMessageToReceiver = async () => {
  try {
    sendingMessage.value = true;
    // Use the mediaUploadStore.previews directly
    const mediaFiles = mediaUploadStore.previews || [];
    // Get permissions from the mediaUploadStore if available
    const permissions = mediaUploadStore.currentContent?.permissions;
    // Send the message using the store method with permissions if available
    const result = await messagesStore.sendMessage(
      receiver.value.id,
      newMessage.value.trim(),
      mediaFiles,
      false, // visibility flag
      permissions // Pass permissions from the store without modifying structure
    );
    // Only clear the input and media if successful, do NOT add the message to the UI here
    if (result.success) {
      newMessage.value = '';
      mediaUploadStore.clearMedia();
      collapseInput();
      // Do NOT add the message to the UI here. WebSocket will handle it.
    } else {
      console.error('❌ Failed to send message:', result.error);
    }
  } catch (error) {
    console.error('❌ Error sending message:', error);
  } finally {
    sendingMessage.value = false;
  }
};

// Also update the sendTip function to only clear input/media after success, do NOT add message to UI
const sendTip = async () => {
  if (!tipAmount.value || tipAmount.value < 1 || sendingTip.value) return;
  sendingTip.value = true;
  try {
    // First process the tip payment
    const response = await axiosInstance.post(`/tip`, {
      receiver_id: receiver.value.id,
      amount: tipAmount.value,
      currency: 'USD',
      context: 'message',
      payment_method: 'wallet',
      tippable_type: 'message',
      tippable_id: null // Will be updated after message creation
    });
    if (response.data.success) {
      if (response.data.data.redirect_required) {
        alert('Error: Unexpected payment redirect. Please contact support.');
        return;
      }
      // Payment was successful, now create the message
      const messageContent = newMessage.value.trim();
      const messageMedia = [...mediaUploadStore.previews || []];
      const permissions = mediaUploadStore.currentContent?.permissions;
      const messageResult = await messagesStore.sendMessage(
        receiver.value.id,
        messageContent,
        messageMedia,
        true, // Set visibility to true since payment was successful
        permissions
      );
      if (!messageResult.success || !messageResult.message) {
        alert('Payment was successful but message could not be sent. Please contact support.');
        return;
      }
      // After message is created, link it to the tip
      try {
        await axiosInstance.post(`/transactions/${response.data.data.transaction_id}/link`, {
          tippable_type: 'message',
          tippable_id: messageResult.message.id
        });
      } catch (linkError) {
        // Optionally show a toast or alert
      }
      // Close the tip modal and reset values
      closeTipModal();
      // Clear the input and media
      newMessage.value = '';
      mediaUploadStore.clearMedia();
      collapseInput();
      // Do NOT add the message to the UI here. WebSocket will handle it.
      toast.success('Message sent successfully with tip');
    } else {
      throw new Error(response.data.message || 'Failed to process tip');
    }
  } catch (error) {
    // Show appropriate error message
    if (error.response && error.response.data && error.response.data.error === 'insufficient_balance') {
      const walletBalance = error.response.data.data?.wallet_balance || 0;
      const requiredAmount = error.response.data.data?.required_amount || tipAmount.value;
      alert(`Insufficient wallet balance. Your current balance is $${walletBalance.toFixed(2)}. You need $${requiredAmount.toFixed(2)} to send this tip. Please top up your account and try again.`);
    } else if (error.response && error.response.data && error.response.data.error === 'wallet_required') {
      alert('Message tips can only be paid using wallet balance. Please top up your account and try again.');
    } else {
      alert(`Error sending tip: ${error.response?.data?.message || error.message || 'Network error occurred'}`);
    }
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
    await messagesStore.likeMedia(mediaId);
  } catch (error) {
    console.error('❌ Error liking media:', error);
  }
};

const handleMediaBookmark = async (mediaId) => {
  try {
    await messagesStore.bookmarkMedia(mediaId);
  } catch (error) {
    console.error('❌ Error bookmarking media:', error);
  }
};

const handleMediaStats = async (mediaId) => {
  try {
    await messagesStore.viewMedia(mediaId);
  } catch (error) {
    console.error('❌ Error viewing media stats:', error);
  }
};

// Add this with other refs at the top of the script
const closeTipModal = () => {
  showTipModal.value = false;
  tipAmount.value = 10; // Reset to default
  customTipAmount.value = null; // Reset custom amount
};

onBeforeUnmount(() => {
  // Clear typing timeout first
  if (typingTimeout.value) {
    clearTimeout(typingTimeout.value);
    typingTimeout.value = null;
  }
  
  // Leave presence channel before cleanup
  try {
    leavePresenceChannel();
  } catch (error) {
    console.warn('Error leaving presence channel:', error);
  }
  
  // Cleanup messages store last, with error handling
  try {
    messagesStore.cleanup();
  } catch (error) {
    console.warn('Error during messages store cleanup:', error);
  }
});
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

/* Textarea auto-resize styling */
textarea {
  transition: height 0.2s ease;
  overflow-y: hidden;
  line-height: 1.5;
}

/* Input area smooth transitions */
.input-area-transition {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Expand/collapse animations */
.expand-enter-active,
.expand-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  opacity: 1;
  transform: translateY(0);
}

.expand-enter-from,
.expand-leave-to {
  opacity: 0;
  transform: translateY(10px);
}

/* Send button hover animation */
.send-button {
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.send-button:hover {
  transform: scale(1.05);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

/* Typing indicator animation improvements */
.typing-dot {
  animation: bounce 1.4s ease-in-out infinite both;
}

.typing-dot:nth-child(1) { animation-delay: -0.32s; }
.typing-dot:nth-child(2) { animation-delay: -0.16s; }

@keyframes bounce {
  0%, 80%, 100% {
    transform: scale(0);
  } 40% {
    transform: scale(1);
  }
}

/* Focus ring improvements */
.focus-ring:focus-within {
  ring-width: 2px;
  ring-color: rgb(var(--primary-light));
  ring-opacity: 0.5;
}

/* Message input container improvements */
.message-input-container {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border: 2px solid transparent;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.message-input-container:focus-within {
  border-color: rgb(var(--primary-light));
  box-shadow: 0 0 0 3px rgba(var(--primary-light), 0.1);
}

.dark .message-input-container {
  background: rgba(31, 41, 55, 0.95);
}

.dark .message-input-container:focus-within {
  border-color: rgb(var(--primary-dark));
  box-shadow: 0 0 0 3px rgba(var(--primary-dark), 0.1);
}
</style>

