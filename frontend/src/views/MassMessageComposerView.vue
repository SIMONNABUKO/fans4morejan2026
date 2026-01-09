<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Header -->
    <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <div class="flex items-center space-x-4">
            <button
              @click="handleBack"
              class="p-2 rounded-lg text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            >
              <i class="ri-arrow-left-line text-xl"></i>
            </button>
            <div>
              <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Mass Message</h1>
              <p class="text-sm text-gray-500 dark:text-gray-400">Send to {{ recipientData?.totalCount || 0 }} recipients</p>
            </div>
          </div>
          
          <div class="flex items-center space-x-3">
            <button
              @click="saveDraft"
              :disabled="!message.trim() || isSaving"
              class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <i class="ri-save-line mr-2"></i>
              {{ isSaving ? 'Saving...' : 'Save Draft' }}
            </button>
            
            <button
              @click="showSendOptions = true"
              :disabled="!canSend"
              class="px-6 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 rounded-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
            >
              <i class="ri-send-plane-fill mr-2"></i>
              Send Message
            </button>
          </div>
        </div>
      </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Main Composer Area -->
        <div class="lg:col-span-2 space-y-6">
          
          <!-- Message Composer -->
          <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="border-b border-gray-200 dark:border-gray-700 p-6 pb-4">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Compose Message</h2>
              <p class="text-sm text-gray-600 dark:text-gray-400">Write your message to send to your audience</p>
            </div>
            
            <div class="p-6">
              <!-- Subject Line -->
              <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Subject (Optional)
                </label>
                <input
                  v-model="subject"
                  type="text"
                  placeholder="Enter a subject for your message..."
                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 dark:focus:border-blue-400 transition-colors"
                />
              </div>

              <!-- Rich Text Editor -->
              <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Message *
                </label>
                
                <!-- Toolbar -->
                <div class="border border-gray-300 dark:border-gray-600 rounded-t-lg bg-gray-50 dark:bg-gray-700 px-4 py-2">
                  <div class="flex items-center space-x-1">
                    <button
                      v-for="tool in editorTools"
                      :key="tool.id"
                      @click="applyFormat(tool.command)"
                      :title="tool.title"
                      class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors"
                    >
                      <i :class="tool.icon" class="text-sm"></i>
                    </button>
                    
                    <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-2"></div>
                    
                    <button
                      @click="showEmojiPicker = !showEmojiPicker"
                      title="Add Emoji"
                      class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors"
                    >
                      <i class="ri-emotion-line text-sm"></i>
                    </button>
                    
                    <ImageUploadMenu 
                      @upload-new="handleUploadNew"
                      @from-vault="handleFromVault"
                    />
                  </div>
                </div>

                <!-- Message Input -->
                <div class="relative">
                  <textarea
                    ref="messageEditor"
                    v-model="message"
                    placeholder="Write your message here... You can use formatting and add media!"
                    class="w-full px-4 py-4 border-x border-b border-gray-300 dark:border-gray-600 rounded-b-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 dark:focus:border-blue-400 transition-colors resize-none"
                    rows="8"
                    @input="updateCharCount"
                    @keydown.meta.enter="quickSend"
                    @keydown.ctrl.enter="quickSend"
                  ></textarea>
                  
                  <!-- Character Count -->
                  <div class="absolute bottom-3 right-3 text-xs text-gray-400 dark:text-gray-500">
                    {{ message.length }} / 2000
                  </div>
                  
                  <!-- Emoji Picker -->
                  <div
                    v-if="showEmojiPicker"
                    class="absolute top-full left-0 z-20 mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg p-4"
                  >
                    <div class="grid grid-cols-8 gap-2">
                      <button
                        v-for="emoji in commonEmojis"
                        :key="emoji"
                        @click="addEmoji(emoji)"
                        class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-lg"
                      >
                        {{ emoji }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Media Attachments -->
              <div v-if="attachedMedia.length > 0" class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                  Media Attachments
                </label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                  <div
                    v-for="(media, index) in attachedMedia"
                    :key="index"
                    class="relative group"
                  >
                    <img
                      v-if="media.type === 'image'"
                      :src="media.preview"
                      :alt="media.name"
                      class="w-full h-24 object-cover rounded-lg border border-gray-200 dark:border-gray-700"
                    />
                    <div
                      v-else
                      class="w-full h-24 bg-gray-100 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-700 flex items-center justify-center"
                    >
                      <i class="ri-file-line text-2xl text-gray-400"></i>
                    </div>
                    <!-- Permissions indicator -->
                    <div
                      v-if="mediaPermissions.length > 0"
                      class="absolute top-1 left-1 bg-blue-500 text-white text-xs px-1.5 py-0.5 rounded flex items-center gap-1"
                    >
                      <i class="ri-lock-line"></i>
                      <span>{{ mediaPermissions.length }}{{ mediaPermissions.length > 1 ? ' sets' : ' set' }}</span>
                    </div>

                    <button
                      @click="removeMedia(index)"
                      class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                    >
                      <i class="ri-close-line text-xs"></i>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Message Templates -->
              <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                <div class="flex items-center justify-between mb-3">
                  <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Quick Templates
                  </label>
                  <button
                    @click="saveAsTemplate"
                    :disabled="!message.trim()"
                    class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    Save as Template
                  </button>
                </div>
                <div class="flex flex-wrap gap-2">
                  <button
                    v-for="template in messageTemplates"
                    :key="template.id"
                    @click="loadTemplate(template)"
                    class="px-3 py-1.5 text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                  >
                    {{ template.name }}
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Message Preview -->
          <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="border-b border-gray-200 dark:border-gray-700 p-6 pb-4">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Preview</h2>
              <p class="text-sm text-gray-600 dark:text-gray-400">How your message will appear to recipients</p>
            </div>
            
            <div class="p-6">
              <div class="max-w-md mx-auto bg-gray-50 dark:bg-gray-700 rounded-lg border p-4">
                <div class="flex items-center gap-3 mb-3">
                  <img
                    :src="currentUser?.avatar || '/placeholder.svg?height=32&width=32'"
                    :alt="currentUser?.name"
                    class="w-8 h-8 rounded-full"
                  />
                  <div>
                    <div class="font-medium text-gray-900 dark:text-white text-sm">{{ currentUser?.name }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">just now</div>
                  </div>
                </div>
                
                <div v-if="subject" class="font-medium text-gray-900 dark:text-white mb-2 text-sm">
                  {{ subject }}
                </div>
                
                <div class="text-gray-800 dark:text-gray-200 text-sm whitespace-pre-wrap">
                  {{ message || "Your message will appear here..." }}
                </div>
                
                <div v-if="attachedMedia.length > 0" class="mt-3 grid grid-cols-2 gap-2">
                  <div
                    v-for="(media, index) in attachedMedia.slice(0, 2)"
                    :key="index"
                    class="aspect-square bg-gray-200 dark:bg-gray-600 rounded"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          
          <!-- Recipients Summary -->
          <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="border-b border-gray-200 dark:border-gray-700 p-6 pb-4">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recipients</h2>
              <p class="text-sm text-gray-600 dark:text-gray-400">{{ recipientData?.totalCount || 0 }} users will receive this message</p>
            </div>
            
            <div class="p-6 space-y-4">
              <!-- Selected Lists -->
              <div v-if="recipientData?.lists?.length > 0">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Selected Lists</h3>
                <div class="space-y-2">
                  <div
                    v-for="list in recipientData.lists"
                    :key="list.uniqueId"
                    class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
                  >
                    <div>
                      <div class="font-medium text-gray-900 dark:text-white text-sm">{{ list.name }}</div>
                      <div class="text-xs text-gray-500 dark:text-gray-400">{{ list.count }} users</div>
                    </div>
                    <i class="ri-group-line text-gray-400"></i>
                  </div>
                </div>
              </div>

              <!-- Recently Active -->
              <div v-if="recipientData?.isRecentlyActive">
                <div class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                  <div>
                    <div class="font-medium text-gray-900 dark:text-white text-sm">Recently Active</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Smart targeting enabled</div>
                  </div>
                  <i class="ri-flashlight-line text-green-500"></i>
                </div>
              </div>

              <!-- Exclusions -->
              <div v-if="recipientData?.excludedUsers?.length > 0">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Exclusions</h3>
                <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                  <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-900 dark:text-white">{{ recipientData.excludedUsers.length }} users excluded</div>
                    <i class="ri-user-forbid-line text-red-500"></i>
                  </div>
                </div>
              </div>

              <!-- Edit Recipients -->
              <button
                @click="editRecipients"
                class="w-full px-4 py-2 text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 border border-blue-200 dark:border-blue-600 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
              >
                <i class="ri-edit-line mr-2"></i>
                Edit Recipients
              </button>
            </div>
          </div>

          <!-- Delivery Settings -->
          <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="border-b border-gray-200 dark:border-gray-700 p-6 pb-4">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Delivery Settings</h2>
            </div>
            
            <div class="p-6 space-y-4">
              <div>
                <label class="flex items-center gap-3 cursor-pointer">
                  <input
                    v-model="deliverySettings.sendImmediately"
                    type="radio"
                    value="true"
                    name="delivery"
                    class="text-blue-600"
                  />
                  <div>
                    <div class="font-medium text-gray-900 dark:text-white text-sm">Send Now</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Deliver immediately</div>
                  </div>
                </label>
              </div>

              <div>
                <label class="flex items-center gap-3 cursor-pointer">
                  <input
                    v-model="deliverySettings.sendImmediately"
                    type="radio"
                    value="false"
                    name="delivery"
                    class="text-blue-600"
                  />
                  <div>
                    <div class="font-medium text-gray-900 dark:text-white text-sm">Schedule for Later</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Pick a specific time</div>
                  </div>
                </label>
              </div>

              <!-- Scheduling Options -->
              <div v-if="deliverySettings.sendImmediately === 'false'" class="ml-6 space-y-3 pt-3">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Date & Time
                  </label>
                  <input
                    v-model="scheduledDateTime"
                    type="datetime-local"
                    :min="minDateTime"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Timezone
                  </label>
                  <select
                    v-model="deliverySettings.timezone"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                  >
                    <option value="UTC">UTC</option>
                    <option value="America/New_York">Eastern Time</option>
                    <option value="America/Chicago">Central Time</option>
                    <option value="America/Denver">Mountain Time</option>
                    <option value="America/Los_Angeles">Pacific Time</option>
                  </select>
                </div>

                <!-- Recurring Messages -->
                <div>
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input
                      v-model="deliverySettings.isRecurring"
                      type="checkbox"
                      class="text-blue-600 rounded"
                    />
                    <span class="text-sm text-gray-700 dark:text-gray-300">Make this recurring</span>
                  </label>
                </div>

                <div v-if="deliverySettings.isRecurring" class="space-y-3">
                  <select
                    v-model="deliverySettings.recurringType"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                  >
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                  </select>

                  <input
                    v-model="deliverySettings.recurringEnd"
                    type="date"
                    :min="minDate"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                    placeholder="End date"
                  />
                </div>
              </div>

              <!-- Advanced Options -->
              <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Advanced Options</h3>
                
                <div class="space-y-3">
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input
                      v-model="deliverySettings.trackOpens"
                      type="checkbox"
                      class="text-blue-600 rounded"
                    />
                    <span class="text-sm text-gray-700 dark:text-gray-300">Track message opens</span>
                  </label>

                  <label class="flex items-center gap-2 cursor-pointer">
                    <input
                      v-model="deliverySettings.trackClicks"
                      type="checkbox"
                      class="text-blue-600 rounded"
                    />
                    <span class="text-sm text-gray-700 dark:text-gray-300">Track link clicks</span>
                  </label>

                  <label class="flex items-center gap-2 cursor-pointer">
                    <input
                      v-model="deliverySettings.staggerDelivery"
                      type="checkbox"
                      class="text-blue-600 rounded"
                    />
                    <span class="text-sm text-gray-700 dark:text-gray-300">Stagger delivery (prevent spam)</span>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <!-- Message Statistics (if available) -->
          <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="border-b border-gray-200 dark:border-gray-700 p-6 pb-4">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Estimated Reach</h2>
            </div>
            
            <div class="p-6 space-y-4">
              <div class="grid grid-cols-2 gap-4 text-center">
                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                  <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ recipientData?.totalCount || 0 }}</div>
                  <div class="text-xs text-gray-600 dark:text-gray-400">Recipients</div>
                </div>
                <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                  <div class="text-2xl font-bold text-green-600 dark:text-green-400">~{{ Math.round((recipientData?.totalCount || 0) * 0.85) }}</div>
                  <div class="text-xs text-gray-600 dark:text-gray-400">Est. Opens</div>
                </div>
              </div>
              
              <div class="text-xs text-gray-500 dark:text-gray-400 text-center">
                Based on your historical engagement rates
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Send Options Modal -->
    <TransitionRoot appear :show="showSendOptions" as="template">
      <Dialog as="div" @close="showSendOptions = false" class="relative z-50">
        <TransitionChild
          as="template"
          enter="duration-300 ease-out"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="duration-200 ease-in"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-black/30 backdrop-blur-sm" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4">
            <TransitionChild
              as="template"
              enter="duration-300 ease-out"
              enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100"
              leave="duration-200 ease-in"
              leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95"
            >
              <DialogPanel class="w-full max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-xl">
                <div class="p-6">
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Send Message</h3>
                  
                  <div class="space-y-4 mb-6">
                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                      <div class="flex items-center justify-between mb-2">
                        <span class="font-medium text-gray-900 dark:text-white">Recipients</span>
                        <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ recipientData?.totalCount || 0 }}</span>
                      </div>
                      <div class="text-sm text-gray-600 dark:text-gray-400">
                        {{ deliverySettings.sendImmediately === 'true' ? 'Will be sent immediately' : `Scheduled for ${formatScheduledTime}` }}
                      </div>
                    </div>

                    <div v-if="deliverySettings.isRecurring" class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                      <div class="flex items-center gap-2 mb-2">
                        <i class="ri-repeat-line text-yellow-600 dark:text-yellow-400"></i>
                        <span class="font-medium text-gray-900 dark:text-white">Recurring Message</span>
                      </div>
                      <div class="text-sm text-gray-600 dark:text-gray-400">
                        {{ deliverySettings.recurringType }} until {{ deliverySettings.recurringEnd }}
                      </div>
                    </div>
                  </div>

                  <div class="flex gap-3">
                    <button
                      @click="showSendOptions = false"
                      class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                    >
                      Cancel
                    </button>
                    <button
                      @click="sendMessage"
                      :disabled="isSending"
                      class="flex-1 px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 rounded-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      {{ isSending ? 'Sending...' : (deliverySettings.sendImmediately === 'true' ? 'Send Now' : 'Schedule') }}
                    </button>
                  </div>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Media Upload Modal -->
    <MediaUploadModal 
      :is-open="isMediaUploadModalOpen"
      :context-id="getMassMessageContextId"
      @close="closeUploadModal"
      @upload="handleMediaUpload"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useMessagesStore } from '@/stores/messagesStore'
import messageSchedulerService from '@/services/messageSchedulerService'
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
} from '@headlessui/vue'
import ImageUploadMenu from '@/components/posts/ImageUploadMenu.vue'
import MediaUploadModal from '@/components/posts/MediaUploadModal.vue'
import { useMediaUploadStore } from '@/stores/mediaUploadStore'

const router = useRouter()
const authStore = useAuthStore()
const messagesStore = useMessagesStore()
const mediaUploadStore = useMediaUploadStore()

// Component state
const message = ref('')
const subject = ref('')
const attachedMedia = ref([])
const showEmojiPicker = ref(false)
const isMediaUploadModalOpen = ref(false)
const mediaPermissions = ref([])
const showSendOptions = ref(false)
const isSaving = ref(false)
const isSending = ref(false)

// Template refs
const messageEditor = ref(null)
const mediaInput = ref(null)

// Get recipient data from sessionStorage
const getRecipientData = () => {
  try {
    const storedData = sessionStorage.getItem('massMessageComposerData')
    if (storedData) {
      const parsedData = JSON.parse(storedData)
      // Clean up old data (older than 1 hour)
      if (Date.now() - parsedData.timestamp > 3600000) {
        sessionStorage.removeItem('massMessageComposerData')
        return null
      }
      return parsedData.recipients
    }
  } catch (error) {
    console.error('Error parsing recipient data:', error)
    sessionStorage.removeItem('massMessageComposerData')
  }
  return null
}

const recipientData = ref(getRecipientData())

// Message templates
const messageTemplates = ref([
  { id: 1, name: 'Welcome', content: 'Welcome to my page! Thanks for following me.' },
  { id: 2, name: 'New Content', content: 'I just posted some amazing new content! Check it out.' },
  { id: 3, name: 'Thank You', content: 'Thank you so much for your support! It means the world to me.' }
])

// Editor tools
const editorTools = [
  { id: 'bold', command: 'bold', icon: 'ri-bold', title: 'Bold' },
  { id: 'italic', command: 'italic', icon: 'ri-italic', title: 'Italic' },
  { id: 'underline', command: 'underline', icon: 'ri-underline', title: 'Underline' }
]

// Common emojis
const commonEmojis = ['😀', '😂', '😍', '🥰', '😘', '🤗', '🙌', '👏', '🔥', '💯', '❤️', '💕', '🎉', '🎊', '✨', '⭐']

// Delivery settings
const deliverySettings = ref({
  sendImmediately: 'true',
  timezone: 'UTC',
  isRecurring: false,
  recurringType: 'weekly',
  recurringEnd: '',
  trackOpens: true,
  trackClicks: true,
  staggerDelivery: true
})

// Scheduled date/time
const scheduledDateTime = ref('')

// Computed properties
const currentUser = computed(() => authStore.user)

const canSend = computed(() => {
  return message.value.trim() && recipientData.value?.totalCount > 0
})

const minDateTime = computed(() => {
  const now = new Date()
  return now.toISOString().slice(0, 16)
})

const minDate = computed(() => {
  const now = new Date()
  return now.toISOString().slice(0, 10)
})

const formatScheduledTime = computed(() => {
  if (!scheduledDateTime.value) return ''
  return new Date(scheduledDateTime.value).toLocaleString()
})

// Create a context ID for this mass message
const getMassMessageContextId = computed(() => {
  return `mass-message-${Date.now()}`
})

// Computed property to access media previews from the store
const mediaPreviews = computed(() => {
  return mediaUploadStore.previews || []
})

// Methods
const handleBack = () => {
  if (message.value.trim()) {
    if (confirm('You have unsaved changes. Are you sure you want to go back?')) {
      router.back()
    }
  } else {
    router.back()
  }
}

const editRecipients = () => {
  // Store current message data for restoration later
  const currentMessageData = {
    subject: subject.value,
    content: message.value,
    media: attachedMedia.value,
    deliverySettings: deliverySettings.value,
    scheduledDateTime: scheduledDateTime.value,
    timestamp: Date.now()
  }
  
  // Save current message data to sessionStorage
  try {
    sessionStorage.setItem('massMessageDraft', JSON.stringify(currentMessageData))
    console.log('Saved current message data for recipient editing')
  } catch (error) {
    console.error('Error saving message data:', error)
  }
  
  // Navigate to messages page with flag to open recipient modal
  router.push({ 
    name: 'messages',
    query: { 
      openModal: 'true',
      mode: 'mass' // Indicate this is for mass messaging
    }
  })
}

const applyFormat = (command) => {
  document.execCommand(command, false, null)
}

const addEmoji = (emoji) => {
  const textarea = messageEditor.value
  if (!textarea) return
  
  const start = textarea.selectionStart
  const end = textarea.selectionEnd
  message.value = message.value.slice(0, start) + emoji + message.value.slice(end)
  showEmojiPicker.value = false
  
  // Set cursor position after emoji
  nextTick(() => {
    textarea.focus()
    textarea.setSelectionRange(start + emoji.length, start + emoji.length)
  })
}

// Media upload methods for permissions-enabled upload
const handleUploadNew = () => {
  // Set media upload context and open modal
  mediaUploadStore.setContext(getMassMessageContextId.value)
  isMediaUploadModalOpen.value = true
  console.log('🖼️ Opening media upload modal for mass message')
}

const handleFromVault = () => {
  // Handle vault media selection
  console.log('🗄️ Opening vault selector for mass message')
  // Note: Vault functionality would be implemented here
  // For now, we'll just open the upload modal
  handleUploadNew()
}

const closeUploadModal = () => {
  isMediaUploadModalOpen.value = false
  console.log('🖼️ Closed media upload modal')
}

const handleMediaUpload = ({ files, permissions }) => {
  console.log('🖼️ Media uploaded with permissions:', { files, permissions })
  
  // Store the permissions for later use when sending
  if (permissions && permissions.length > 0) {
    mediaPermissions.value = permissions
    console.log('🔒 Updated media permissions:', mediaPermissions.value)
  }
  
  // Update attachedMedia for display in the UI
  attachedMedia.value = files.map(file => ({
    file: file.file || file,
    type: file.type || (file.file?.type?.startsWith('image/') ? 'image' : 'file'),
    preview: file.url,
    name: file.name || file.file?.name || 'Unknown',
    id: file.id,
    permissions: permissions // Store permissions with each media item
  }))
  
  closeUploadModal()
}

const removeMedia = (index) => {
  // Remove from both our local array and the media upload store
  attachedMedia.value.splice(index, 1)
  
  // Also remove from media upload store if using previews
  if (mediaPreviews.value.length > index) {
    mediaUploadStore.removeMedia(index)
  }
  
  console.log(`🗑️ Removed media at index ${index}`)
}

const loadTemplate = (template) => {
  if (message.value.trim()) {
    if (confirm('This will replace your current message. Continue?')) {
      message.value = template.content
    }
  } else {
    message.value = template.content
  }
}

const saveAsTemplate = () => {
  const name = prompt('Enter a name for this template:')
  if (name && name.trim()) {
    messageTemplates.value.push({
      id: Date.now(),
      name: name.trim(),
      content: message.value
    })
  }
}

const saveDraft = async () => {
  if (!message.value.trim()) return
  
  isSaving.value = true
  try {
    const draftData = {
      subject: subject.value,
      content: message.value,
      recipients: recipientData.value,
      media: attachedMedia.value,
      mediaPermissions: mediaPermissions.value,
      deliverySettings: deliverySettings.value,
      draftName: `Mass Message Draft ${new Date().toLocaleString()}`
    }
    
    const result = await messageSchedulerService.saveDraft(draftData)
    
    if (result.success) {
      // Show success notification
      console.log('Draft saved successfully')
      alert('Draft saved successfully!')
    } else {
      // Handle validation errors
      if (result.validationErrors) {
        console.error('Validation errors:', result.validationErrors)
        const errorMessages = Object.values(result.validationErrors).flat()
        alert('Validation errors:\n' + errorMessages.join('\n'))
      } else {
        throw new Error(result.error)
      }
    }
  } catch (error) {
    console.error('Failed to save draft:', error)
    alert('Failed to save draft: ' + error.message)
  } finally {
    isSaving.value = false
  }
}

const quickSend = () => {
  if (canSend.value) {
    deliverySettings.value.sendImmediately = 'true'
    sendMessage()
  }
}

const sendMessage = async () => {
  if (!canSend.value) return
  
  isSending.value = true
  try {
    const messageData = {
      subject: subject.value,
      content: message.value,
      media: attachedMedia.value,
      mediaPermissions: mediaPermissions.value,
      recipients: recipientData.value,
      delivery: {
        immediate: deliverySettings.value.sendImmediately === 'true',
        scheduledFor: scheduledDateTime.value,
        timezone: deliverySettings.value.timezone,
        recurring: deliverySettings.value.isRecurring ? {
          type: deliverySettings.value.recurringType,
          endDate: deliverySettings.value.recurringEnd
        } : null,
        options: {
          trackOpens: deliverySettings.value.trackOpens,
          trackClicks: deliverySettings.value.trackClicks,
          staggerDelivery: deliverySettings.value.staggerDelivery
        }
      }
    }
    
    const result = await messageSchedulerService.processAndSendMessage(messageData)
    
    if (result.success) {
      showSendOptions.value = false
      
      // Redirect to messages with success feedback
      router.push({ 
        name: 'messages',
        query: { 
          sent: 'true',
          type: deliverySettings.value.sendImmediately === 'true' ? 'sent' : 'scheduled'
        }
      })
    } else {
      throw new Error(result.error)
    }
    
  } catch (error) {
    console.error('Failed to send message:', error)
    alert('Failed to send message: ' + error.message)
  } finally {
    isSending.value = false
  }
}

const updateCharCount = () => {
  // Character count is handled by computed property in template
}

// Handle clicking outside emoji picker
const handleClickOutside = (event) => {
  if (showEmojiPicker.value && !event.target.closest('.emoji-picker')) {
    showEmojiPicker.value = false
  }
}

// Lifecycle hooks
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  
  // If no recipient data, redirect back
  if (!recipientData.value) {
    console.warn('No recipient data found, redirecting to messages')
    router.push({ name: 'messages' })
    return
  }
  
  // Restore saved message data if it exists (from editing recipients)
  try {
    const savedMessageData = sessionStorage.getItem('massMessageDraft')
    if (savedMessageData) {
      const parsedData = JSON.parse(savedMessageData)
      
      // Check if data is not too old (1 hour)
      if (Date.now() - parsedData.timestamp < 3600000) {
        console.log('Restoring saved message data')
        
        // Restore form data
        subject.value = parsedData.subject || ''
        message.value = parsedData.content || ''
        attachedMedia.value = parsedData.media || []
        
        // Restore delivery settings
        if (parsedData.deliverySettings) {
          Object.assign(deliverySettings.value, parsedData.deliverySettings)
        }
        
        // Restore scheduled date/time
        if (parsedData.scheduledDateTime) {
          scheduledDateTime.value = parsedData.scheduledDateTime
        }
        
        console.log('Message data restored successfully')
      }
      
      // Clean up the saved data
      sessionStorage.removeItem('massMessageDraft')
    }
  } catch (error) {
    console.error('Error restoring saved message data:', error)
    sessionStorage.removeItem('massMessageDraft')
  }
  
  // Clean up recipient data after successfully loading
  try {
    sessionStorage.removeItem('massMessageComposerData')
  } catch (error) {
    console.error('Error cleaning up sessionStorage:', error)
  }
  
  // Initialize media upload store context
  mediaUploadStore.setContext(getMassMessageContextId.value)
  
  console.log('Mass message composer initialized with recipients:', recipientData.value?.totalCount)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
/* Custom scrollbar for textarea */
textarea::-webkit-scrollbar {
  width: 6px;
}

textarea::-webkit-scrollbar-track {
  @apply bg-gray-100 dark:bg-gray-700;
}

textarea::-webkit-scrollbar-thumb {
  @apply bg-gray-300 dark:bg-gray-600 rounded-full;
}

textarea::-webkit-scrollbar-thumb:hover {
  @apply bg-gray-400 dark:bg-gray-500;
}
</style> 