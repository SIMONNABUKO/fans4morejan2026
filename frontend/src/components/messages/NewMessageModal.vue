<template>
  <TransitionRoot appear :show="isOpen" as="div">
    <Dialog as="div" @close="close" class="relative z-50">
      <TransitionChild
        as="template"
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gradient-to-br from-black/40 via-black/30 to-black/40 backdrop-blur-md" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
          <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-from="opacity-0 scale-95 translate-y-4"
            enter-to="opacity-100 scale-100 translate-y-0"
            leave="duration-200 ease-in"
            leave-from="opacity-100 scale-100 translate-y-0"
            leave-to="opacity-0 scale-95 translate-y-4"
          >
            <DialogPanel class="w-full max-w-md mx-auto bg-white dark:bg-gray-900 rounded-2xl shadow-2xl ring-1 ring-black/5 dark:ring-white/10 overflow-hidden">
              <!-- Header with gradient -->
              <div class="relative bg-gradient-to-r from-blue-600 via-purple-600 to-blue-700 dark:from-blue-500 dark:via-purple-500 dark:to-blue-600 px-6 py-4">
                <div class="absolute inset-0 bg-black/10"></div>
                <div class="relative flex items-center justify-between">
                  <div class="flex items-center gap-4">
                    <button
                      @click="close"
                      class="p-2 rounded-full text-white/90 hover:text-white hover:bg-white/10 transition-all duration-200"
                    >
                      <i class="ri-arrow-left-line text-xl"></i>
                    </button>
                    <div>
                      <h1 class="text-xl font-semibold text-white">
                        New Message
                      </h1>
                      <p class="text-sm text-white/80 mt-0.5">
                        {{ getSubtitleText() }}
                      </p>
                    </div>
                  </div>
                  <button
                    :disabled="!canProceed"
                    @click="handleNext"
                    class="px-5 py-2.5 text-sm font-medium text-white bg-white/20 backdrop-blur-sm rounded-xl hover:bg-white/30 transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed border border-white/20 shadow-lg"
                    :class="{
                      'animate-pulse': canProceed,
                      'hover:scale-105 active:scale-95': canProceed
                    }"
                  >
                    <span class="flex items-center gap-2">
                      Send
                      <i class="ri-send-plane-fill text-sm"></i>
                    </span>
                  </button>
                </div>
              </div>

              <!-- Search -->
              <div class="p-6 border-b border-gray-100 dark:border-gray-800">
                <div class="relative">
                  <div class="absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none">
                    <i class="ri-search-line text-gray-400 text-lg"></i>
                  </div>
                  <input
                    ref="searchInput"
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search users and lists..."
                    class="w-full pl-12 pr-4 py-3.5 bg-gray-50 dark:bg-gray-800 rounded-xl text-gray-900 dark:text-white placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:bg-white dark:focus:bg-gray-700 border border-transparent focus:border-blue-200 dark:focus:border-gray-600 transition-all duration-200"
                    @input="handleSearch"
                  />
                  <div v-if="searchQuery" class="absolute right-3 top-1/2 -translate-y-1/2">
                    <button
                      @click="clearSearch"
                      class="p-1 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                    >
                      <i class="ri-close-line text-sm"></i>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Tabs -->
              <div class="px-6 pt-4">
                <div class="flex bg-gray-100 dark:bg-gray-800 rounded-xl p-1">
                  <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    @click="activeTab = tab.id"
                    class="flex-1 px-4 py-2.5 text-sm font-medium rounded-lg transition-all duration-200"
                    :class="[
                      activeTab === tab.id
                        ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm ring-1 ring-black/5 dark:ring-white/10'
                        : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-gray-700/50'
                    ]"
                  >
                    <span class="flex items-center justify-center gap-2">
                      <i :class="getTabIcon(tab.id)" class="text-sm"></i>
                      {{ tab.label }}
                    </span>
                  </button>
                </div>
              </div>

              <!-- Content -->
              <div class="flex-1 overflow-y-auto max-h-96">
                <div v-if="activeTab === 'send'" class="p-6 pt-4">
                  <!-- Search Results -->
                  <div v-if="searchQuery && messagesStore.searchResults.length > 0" class="mb-6">
                    <div class="flex items-center gap-2 mb-4">
                      <i class="ri-search-2-line text-blue-500"></i>
                      <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                        Search Results ({{ messagesStore.searchResults.length }})
                      </h3>
                    </div>
                    <div class="space-y-2">
                      <button
                        v-for="user in messagesStore.searchResults"
                        :key="user.id"
                        @click="selectUser(user)"
                        class="w-full flex items-center gap-4 p-4 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-all duration-200 group border border-transparent hover:border-gray-200 dark:hover:border-gray-700"
                      >
                        <div class="relative">
                          <img
                            :src="user.avatar || '/placeholder.svg?height=40&width=40'"
                            :alt="user.name"
                            class="w-12 h-12 rounded-full object-cover ring-2 ring-white dark:ring-gray-800 shadow-sm"
                          />
                          <div v-if="user.isOnline" class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 rounded-full ring-2 ring-white dark:ring-gray-800"></div>
                        </div>
                        <div class="flex-1 text-left min-w-0">
                          <div class="flex items-center gap-2 mb-1">
                            <span class="font-semibold text-gray-900 dark:text-white truncate">{{ user.name }}</span>
                            <i v-if="user.isSubscriber" class="ri-verified-badge-fill text-blue-500 text-sm flex-shrink-0"></i>
                            <i v-if="user.isVip" class="ri-vip-crown-fill text-yellow-500 text-sm flex-shrink-0"></i>
                          </div>
                          <div class="text-sm text-gray-500 dark:text-gray-400 truncate">
                            @{{ user.username }}
                          </div>
                        </div>
                        <i class="ri-arrow-right-s-line text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors"></i>
                      </button>
                    </div>
                  </div>

                  <!-- Lists -->
                  <div v-else-if="isCreator">
                    <div class="flex items-center gap-2 mb-4">
                      <i class="ri-group-line text-purple-500"></i>
                      <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ currentUser?.role === 'creator' ? 'Your Audience' : 'Available Lists' }}
                      </h3>
                    </div>
                    
                    <div class="space-y-3">
                      <!-- Recently Active (for creators only) -->
                      <button
                        v-if="currentUser?.role === 'creator'"
                        @click="selectRecentlyActive"
                        class="w-full flex items-center gap-4 p-4 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-all duration-200 group border border-transparent hover:border-gray-200 dark:hover:border-gray-700"
                      >
                        <div class="relative">
                          <div class="w-5 h-5 rounded-full border-2 border-gray-300 dark:border-gray-600 flex items-center justify-center group-hover:border-blue-400 transition-colors">
                            <i v-if="isRecentlyActiveSelected" class="ri-check-line text-blue-500 text-sm"></i>
                          </div>
                        </div>
                        <div class="flex-1 text-left">
                          <div class="flex items-center gap-2 mb-1">
                            <span class="font-medium text-gray-900 dark:text-white">Recently Active</span>
                            <div class="px-2 py-0.5 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-full text-xs font-medium">
                              Smart
                            </div>
                          </div>
                          <div class="text-sm text-gray-500 dark:text-gray-400">
                            Users active in the last 7 days
                          </div>
                        </div>
                        <i class="ri-calendar-line text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300"></i>
                      </button>

                      <!-- Fans/Subscribers List -->
                      <button
                        v-if="fansList && shouldShowList(fansList)"
                        @click="selectList(fansList)"
                        class="w-full flex items-center gap-4 p-4 rounded-xl transition-all duration-200 group border"
                        :class="[
                          isSelected(fansList) 
                            ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-700' 
                            : 'hover:bg-gray-50 dark:hover:bg-gray-800 border-transparent hover:border-gray-200 dark:hover:border-gray-700'
                        ]"
                      >
                        <div class="relative">
                          <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors"
                               :class="[
                                 isSelected(fansList) 
                                   ? 'border-blue-500 bg-blue-500' 
                                   : 'border-gray-300 dark:border-gray-600 group-hover:border-blue-400'
                               ]">
                            <i v-if="isSelected(fansList)" class="ri-check-line text-white text-sm"></i>
                          </div>
                        </div>
                        <div class="flex-1 text-left">
                          <div class="flex items-center gap-2 mb-1">
                            <span class="font-medium text-gray-900 dark:text-white">{{ fansList.name }}</span>
                            <div class="px-2 py-0.5 bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 rounded-full text-xs font-medium">
                              {{ formatNumber(fansList.count) }}
                            </div>
                          </div>
                          <div class="text-sm text-gray-500 dark:text-gray-400">
                            Your most engaged audience
                          </div>
                        </div>
                        <div class="flex -space-x-1">
                          <div class="w-8 h-8 rounded-full bg-gradient-to-r from-purple-400 to-purple-600 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                            F
                          </div>
                        </div>
                      </button>

                      <!-- Other Lists (with role-based filtering) -->
                      <button
                        v-for="list in filteredLists"
                        :key="list.uniqueId"
                        @click="selectList(list)"
                        class="w-full flex items-center gap-4 p-4 rounded-xl transition-all duration-200 group border"
                        :class="[
                          isSelected(list) 
                            ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-700' 
                            : 'hover:bg-gray-50 dark:hover:bg-gray-800 border-transparent hover:border-gray-200 dark:hover:border-gray-700'
                        ]"
                      >
                        <div class="relative">
                          <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors"
                               :class="[
                                 isSelected(list) 
                                   ? 'border-blue-500 bg-blue-500' 
                                   : 'border-gray-300 dark:border-gray-600 group-hover:border-blue-400'
                               ]">
                            <i v-if="isSelected(list)" class="ri-check-line text-white text-sm"></i>
                          </div>
                        </div>
                        <div class="flex-1 text-left">
                          <div class="flex items-center gap-2 mb-1">
                            <span class="font-medium text-gray-900 dark:text-white">{{ list.name }}</span>
                            <div class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-full text-xs font-medium">
                              {{ formatNumber(list.count) }}
                            </div>
                            <div v-if="list.type === 'custom'" class="px-2 py-0.5 bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded-full text-xs font-medium">
                              Custom
                            </div>
                          </div>
                          <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ getListDescription(list) }}
                          </div>
                        </div>
                        <div class="flex -space-x-1">
                          <template v-if="list.preview_users && list.preview_users.length > 0">
                            <img 
                              v-for="(user, index) in list.preview_users.slice(0, 3)"
                              :key="user.id"
                              :src="user.avatar || '/placeholder.svg?height=32&width=32'"
                              :alt="user.name"
                              class="w-8 h-8 rounded-full ring-2 ring-white dark:ring-gray-800 shadow-sm"
                              :style="{ zIndex: 10 - index }"
                            />
                          </template>
                          <div v-else class="w-8 h-8 rounded-full bg-gradient-to-r from-gray-300 to-gray-400 dark:from-gray-600 dark:to-gray-700 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                            {{ list.name.charAt(0).toUpperCase() }}
                          </div>
                        </div>
                      </button>
                    </div>
                  </div>
                </div>

                <div v-else-if="activeTab === 'exclude'" class="p-6 pt-4">
                  <div class="flex items-center gap-2 mb-4">
                    <i class="ri-user-forbid-line text-red-500"></i>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                      Exclude Users ({{ excludedUserIds.length }})
                    </h3>
                  </div>
                  
                  <div v-if="allSelectedUsers.length === 0" class="text-center py-12">
                    <div class="mx-auto w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-4">
                      <i class="ri-group-line text-gray-400 text-2xl"></i>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">No recipients selected</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Select at least one group or list first</p>
                  </div>
                  
                  <div v-else class="space-y-2">
                    <div 
                      v-for="user in allSelectedUsers" 
                      :key="user.id" 
                      class="flex items-center gap-4 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group"
                    >
                      <label :for="'exclude-' + user.id" class="relative cursor-pointer">
                        <input
                          type="checkbox"
                          :id="'exclude-' + user.id"
                          :value="user.id"
                          v-model="excludedUserIds"
                          class="sr-only"
                        />
                        <div class="w-5 h-5 rounded border-2 transition-colors flex items-center justify-center"
                             :class="[
                               excludedUserIds.includes(user.id) 
                                 ? 'bg-red-500 border-red-500' 
                                 : 'border-gray-300 dark:border-gray-600 group-hover:border-red-400'
                             ]">
                          <i v-if="excludedUserIds.includes(user.id)" class="ri-close-line text-white text-sm"></i>
                        </div>
                      </label>
                      <div class="relative">
                        <img 
                          :src="user.avatar || '/placeholder.svg?height=32&width=32'" 
                          :alt="user.name"
                          class="w-8 h-8 rounded-full ring-2 ring-white dark:ring-gray-800" 
                          :class="{ 'opacity-50': excludedUserIds.includes(user.id) }"
                        />
                        <div v-if="excludedUserIds.includes(user.id)" class="absolute inset-0 rounded-full bg-red-500/20 flex items-center justify-center">
                          <i class="ri-forbid-line text-red-500 text-sm"></i>
                        </div>
                      </div>
                      <div class="flex-1" :class="{ 'opacity-50': excludedUserIds.includes(user.id) }">
                        <div class="font-medium text-gray-900 dark:text-white">{{ user.name }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">@{{ user.username }}</div>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Summary -->
                  <div v-if="allSelectedUsers.length > 0" class="mt-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-xl">
                    <div class="flex items-center justify-between text-sm">
                      <span class="text-gray-600 dark:text-gray-400">Recipients:</span>
                      <span class="font-medium text-gray-900 dark:text-white">
                        {{ allSelectedUsers.length - excludedUserIds.length }} of {{ allSelectedUsers.length }}
                      </span>
                    </div>
                  </div>
                </div>

                <div v-else class="p-6 pt-4">
                  <!-- Users tab content -->
                  <div class="flex items-center gap-2 mb-4">
                    <i class="ri-user-search-line text-green-500"></i>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                      Individual Users
                    </h3>
                  </div>
                  
                  <div v-if="searchQuery && messagesStore.searchResults.length > 0">
                    <div class="space-y-2">
                      <div
                        v-for="user in messagesStore.searchResults"
                        :key="user.id"
                        class="flex items-center gap-4 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group"
                      >
                        <label :for="'user-select-' + user.id" class="relative cursor-pointer">
                          <input
                            type="checkbox"
                            :id="'user-select-' + user.id"
                            :checked="selectedUser && selectedUser.id === user.id"
                            @change="(e) => {
                              if (e.target.checked) {
                                selectUser(user)
                              } else {
                                selectedUser = null
                              }
                            }"
                            class="sr-only"
                          />
                          <div class="w-5 h-5 rounded-full border-2 transition-colors flex items-center justify-center"
                               :class="[
                                 selectedUser && selectedUser.id === user.id 
                                   ? 'bg-blue-500 border-blue-500' 
                                   : 'border-gray-300 dark:border-gray-600 group-hover:border-blue-400'
                               ]">
                            <i v-if="selectedUser && selectedUser.id === user.id" class="ri-check-line text-white text-sm"></i>
                          </div>
                        </label>
                        <div class="relative">
                          <img 
                            :src="user.avatar || '/placeholder.svg?height=32&width=32'" 
                            :alt="user.name"
                            class="w-8 h-8 rounded-full ring-2 ring-white dark:ring-gray-800"
                          />
                          <div v-if="user.isOnline" class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-500 rounded-full ring-2 ring-white dark:ring-gray-800"></div>
                        </div>
                        <div class="flex-1">
                          <div class="flex items-center gap-2 mb-0.5">
                            <span class="font-medium text-gray-900 dark:text-white">{{ user.name }}</span>
                            <i v-if="user.isSubscriber" class="ri-verified-badge-fill text-blue-500 text-sm"></i>
                            <i v-if="user.isVip" class="ri-vip-crown-fill text-yellow-500 text-sm"></i>
                          </div>
                          <div class="text-sm text-gray-500 dark:text-gray-400">@{{ user.username }}</div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div v-else class="text-center py-12">
                    <div class="mx-auto w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-4">
                      <i class="ri-search-line text-gray-400 text-2xl"></i>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Search for users</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Type at least 2 characters to search</p>
                  </div>
                </div>
              </div>
              
              <!-- Footer with advanced options for creators -->
              <div v-if="isCreator" class="border-t border-gray-100 dark:border-gray-800 p-6">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Advanced Options</h4>
                <div class="space-y-3">
                  <label class="flex items-center gap-3 cursor-pointer group">
                    <input
                      v-model="includeSubscribers"
                      type="checkbox"
                      class="sr-only"
                    />
                    <div class="w-5 h-5 rounded border-2 transition-colors flex items-center justify-center"
                         :class="[
                           includeSubscribers 
                             ? 'bg-blue-500 border-blue-500' 
                             : 'border-gray-300 dark:border-gray-600 group-hover:border-blue-400'
                         ]">
                      <i v-if="includeSubscribers" class="ri-check-line text-white text-sm"></i>
                    </div>
                    <div>
                      <div class="text-sm font-medium text-gray-900 dark:text-white">Include Subscribers</div>
                      <div class="text-xs text-gray-500 dark:text-gray-400">Send to active subscribers</div>
                    </div>
                  </label>
                  
                  <label class="flex items-center gap-3 cursor-pointer group">
                    <input
                      v-model="includeExpiredSubscribers"
                      type="checkbox"
                      class="sr-only"
                    />
                    <div class="w-5 h-5 rounded border-2 transition-colors flex items-center justify-center"
                         :class="[
                           includeExpiredSubscribers 
                             ? 'bg-blue-500 border-blue-500' 
                             : 'border-gray-300 dark:border-gray-600 group-hover:border-blue-400'
                         ]">
                      <i v-if="includeExpiredSubscribers" class="ri-check-line text-white text-sm"></i>
                    </div>
                    <div>
                      <div class="text-sm font-medium text-gray-900 dark:text-white">Include Expired Subscribers</div>
                      <div class="text-xs text-gray-500 dark:text-gray-400">Include past subscribers</div>
                    </div>
                  </label>
                  
                  <label class="flex items-center gap-3 cursor-pointer group">
                    <input
                      v-model="includeFollowers"
                      type="checkbox"
                      class="sr-only"
                    />
                    <div class="w-5 h-5 rounded border-2 transition-colors flex items-center justify-center"
                         :class="[
                           includeFollowers 
                             ? 'bg-blue-500 border-blue-500' 
                             : 'border-gray-300 dark:border-gray-600 group-hover:border-blue-400'
                         ]">
                      <i v-if="includeFollowers" class="ri-check-line text-white text-sm"></i>
                    </div>
                    <div>
                      <div class="text-sm font-medium text-gray-900 dark:text-white">Include Followers</div>
                      <div class="text-xs text-gray-500 dark:text-gray-400">Include non-subscribing followers</div>
                    </div>
                  </label>
                </div>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, watch, nextTick, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useListStore } from '@/stores/listStore'
import { useMessagesStore } from '@/stores/messagesStore'
import { useAuthStore } from '@/stores/authStore'
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
  DialogTitle,
} from '@headlessui/vue'

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  }
})

const emit = defineEmits(['close'])
const router = useRouter()
const listStore = useListStore()
const messagesStore = useMessagesStore()
const authStore = useAuthStore()

const activeTab = ref('send')
const searchInput = ref(null)
const searchQuery = ref('')
// --- CHANGED: support multiple selected lists ---
const selectedLists = ref([]) // array of list objects
const selectedUser = ref(null)
const excludedUserIds = ref([]) // array of user IDs
const allSelectedUsers = ref([]) // array of user objects
const isRecentlyActiveSelected = ref(false)

// Advanced options for creators
const includeSubscribers = ref(false)
const includeExpiredSubscribers = ref(false)
const includeFollowers = ref(false)

// Get current user
const currentUser = computed(() => authStore.user)
const isCreator = computed(() => currentUser.value?.role === 'creator')

const tabs = computed(() => {
  if (isCreator.value) {
    return [
      { id: 'send', label: 'SEND TO' },
      { id: 'exclude', label: 'EXCLUDE' },
      { id: 'users', label: 'USERS' }
    ]
  }
  return [{ id: 'send', label: 'SEND TO' }]
})

// Computed properties for lists
const fansList = computed(() => {
  const fans = listStore.getListByName('Fans')
  return fans ? {
    uniqueId: `fans_${fans.id}`,
    id: fans.id,
    name: fans.name,
    count: fans.count || 0,
    type: 'system'
  } : null
})

// Role-based list filtering - THIS IS THE KEY CHANGE
const allLists = computed(() => {
  const userRole = currentUser.value?.role
  
  return listStore.lists
    .filter(list => {
      // For creators: show all lists EXCEPT Following
      if (userRole === 'creator') {
        return list.name !== 'Following'
      }
      // For regular users: ONLY show Following list
      else {
        return list.name === 'Following'
      }
    })
    .map(list => ({
      uniqueId: `${list.is_default ? 'default' : 'custom'}_${list.id}`,
      id: list.id,
      name: list.name,
      count: list.count || 0,
      type: list.is_default ? 'default' : 'custom',
      preview_users: list.preview_users || []
    }))
})

// Additional filtering for display
const filteredLists = computed(() => {
  return allLists.value.filter(list => shouldShowList(list))
})

// --- NEW: Merge all users from selected lists, deduplicated by user id ---
const updateAllSelectedUsers = async () => {
  const userMap = {};
  // Collect fetch promises for lists whose members are not loaded
  const fetchPromises = selectedLists.value.map(async (list) => {
    let members = listStore.getListMembers(list.id);
    if (!members || members.length === 0) {
      await listStore.fetchListMembers(list.id);
      members = listStore.getListMembers(list.id);
    }
    for (const user of members) {
      userMap[user.id] = user;
    }
  });
  await Promise.all(fetchPromises); // Wait for all fetches to complete
  allSelectedUsers.value = Object.values(userMap);
}

// --- CHANGED: isSelected for multiple lists ---
const isSelected = (list) => {
  return selectedLists.value.some(l => l.uniqueId === list.uniqueId)
}

// Helper methods for the new UI
const shouldShowList = (list) => {
  // Additional logic to determine if a list should be shown
  // Could include checks for user permissions, list availability, etc.
  return true
}

const getSubtitleText = () => {
  const userRole = currentUser.value?.role
  if (userRole === 'creator') {
    return 'Send messages to your audience'
  } else {
    return 'Message creators'
  }
}

const getTabIcon = (tabId) => {
  const icons = {
    send: 'ri-send-plane-2-line',
    exclude: 'ri-user-forbid-line', 
    users: 'ri-user-search-line'
  }
  return icons[tabId] || 'ri-circle-line'
}

const formatNumber = (num) => {
  if (num >= 1000000) {
    return `${(num / 1000000).toFixed(1)}M`
  }
  if (num >= 1000) {
    return `${(num / 1000).toFixed(1)}k`
  }
  return num.toString()
}

const getListDescription = (list) => {
  const descriptions = {
    'Followers': 'Users who follow you',
    'Following': 'Creators you can message',
    'VIP': 'Your VIP supporters',
    'Favorites': 'Your favorite supporters'
  }
  return descriptions[list.name] || 'Custom user list'
}

const selectRecentlyActive = () => {
  if (!isCreator.value) {
    return
  }
  isRecentlyActiveSelected.value = !isRecentlyActiveSelected.value
  if (isRecentlyActiveSelected.value) {
    selectedLists.value = []
    selectedUser.value = null
    // Here you would typically fetch recently active users
    // For now, we'll just set it as selected
  }
}

const clearSearch = () => {
  searchQuery.value = ''
  messagesStore.clearSearchResults()
}

// --- CHANGED: selectList for multiple selection ---
const selectList = async (list) => {
  if (!isCreator.value) {
    alert('Mass messaging is only available for creators.')
    return
  }
  const idx = selectedLists.value.findIndex(l => l.uniqueId === list.uniqueId)
  if (idx === -1) {
    selectedLists.value.push(list)
  } else {
    selectedLists.value.splice(idx, 1)
  }
  selectedUser.value = null
  await updateAllSelectedUsers()
  // Remove excluded users that are not in the new selection
  const validUserIds = allSelectedUsers.value.map(u => u.id)
  excludedUserIds.value = excludedUserIds.value.filter(id => validUserIds.includes(id))
}

const selectUser = (user) => {
  selectedUser.value = user
  selectedLists.value = []
  allSelectedUsers.value = []
  excludedUserIds.value = []
}

// Debounce search
let searchTimeout = null
const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(async () => {
    if (searchQuery.value.length >= 2) {
      await messagesStore.searchUsers(searchQuery.value)
    } else {
      messagesStore.clearSearchResults()
    }
  }, 300)
}

const close = () => {
  searchQuery.value = ''
  selectedLists.value = []
  selectedUser.value = null
  allSelectedUsers.value = []
  excludedUserIds.value = []
  isRecentlyActiveSelected.value = false
  includeSubscribers.value = false
  includeExpiredSubscribers.value = false
  includeFollowers.value = false
  messagesStore.clearSearchResults()
  emit('close')
}

const handleNext = async () => {
  if (selectedUser.value) {
    // Single user - start individual conversation
    try {
      const result = await messagesStore.getOrCreateConversation(selectedUser.value.id)
      if (result.success && result.conversation) {
        router.push({ name: 'single-message', params: { id: selectedUser.value.id } })
      } else {
        console.error('Failed to start conversation:', result.error)
      }
    } catch (error) {
      console.error('Failed to start conversation:', error)
    }
    close()
    return
  }

  // Multiple recipients - go to mass message composer
  if (selectedLists.value.length > 0 || isRecentlyActiveSelected.value) {
    if (!isCreator.value) {
      alert('Mass messaging is only available for creators.')
      return
    }
    const recipients = prepareRecipientData()
    
    if (recipients.totalCount === 0) {
      // Show warning - no users to send to
      console.warn('No recipients selected for mass message')
      alert('Please select at least one recipient to send a mass message')
      return
    }
    
    console.log('Navigating to mass message composer with recipients:', {
      totalCount: recipients.totalCount,
      lists: recipients.lists?.length || 0,
      users: recipients.users?.length || 0,
      excluded: recipients.excludedUsers?.length || 0
    })

    // Store recipient data in sessionStorage for the composer
    const composerData = {
      recipients: recipients,
      settings: {
        includeSubscribers: includeSubscribers.value,
        includeExpiredSubscribers: includeExpiredSubscribers.value,
        includeFollowers: includeFollowers.value
      },
      timestamp: Date.now() // For cleanup
    }
    
    sessionStorage.setItem('massMessageComposerData', JSON.stringify(composerData))
    
    // Navigate to mass message composer
    router.push({ name: 'mass-message-compose' })
    close()
    return
  }

  // If neither is selected, do nothing or show a warning
}

// Helper function to prepare recipient data for the composer
const prepareRecipientData = () => {
  const finalUsers = allSelectedUsers.value.filter(user => !excludedUserIds.value.includes(user.id))
  
  return {
    users: finalUsers,
    lists: selectedLists.value,
    excludedUsers: excludedUserIds.value,
    isRecentlyActive: isRecentlyActiveSelected.value,
    totalCount: finalUsers.length,
    summary: {
      listsCount: selectedLists.value.length,
      excludedCount: excludedUserIds.value.length,
      finalCount: finalUsers.length
    }
  }
}

// --- NEW: Computed property for enabling NEXT button ---
const canProceed = computed(() => {
  // Single user selected
  if (selectedUser.value) return true;
  
  // Recently active selected
  if (isRecentlyActiveSelected.value) return true;
  
  // Lists selected with users after exclusions
  if (selectedLists.value.length > 0) {
    const usersToSend = allSelectedUsers.value.filter(user => !excludedUserIds.value.includes(user.id));
    return usersToSend.length > 0;
  }
  
  return false;
});

// Fetch lists when component mounts
onMounted(async () => {
  try {
    await listStore.fetchLists()
  } catch (error) {
    console.error('Failed to fetch lists:', error)
  }
})

watch(() => props.isOpen, (newValue) => {
  if (newValue) {
    activeTab.value = 'send'
    nextTick(() => {
      if (searchInput.value) {
        searchInput.value.focus()
      }
    })
  }
})
</script>
