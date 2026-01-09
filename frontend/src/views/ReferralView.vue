<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark">
    <!-- Modern Header -->
    <header class="sticky top-0 z-20 bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-lg border-b border-border-light dark:border-border-dark shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 py-4">
          <!-- Left Side: Navigation and Title -->
          <div class="flex items-center gap-3">
            <router-link 
              to="/dashboard" 
              class="p-1.5 rounded-lg bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/20 dark:border-gray-700/50 text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary hover:bg-white/70 dark:hover:bg-gray-800/70 transition-all duration-200"
            >
              <i class="ri-arrow-left-line text-base"></i>
            </router-link>
            
            <div class="flex flex-col">
              <h1 class="text-xl font-semibold text-text-light-primary dark:text-text-dark-primary leading-tight">
                {{ t('referral_program') }}
              </h1>
              <p class="text-text-light-secondary dark:text-text-dark-secondary text-xs mt-0.5">
                Earn money by referring friends and creators
              </p>
            </div>
          </div>
          
          <!-- Right Side: Quick Stats -->
          <div class="hidden md:flex items-center gap-4">
            <div class="text-right">
              <div class="text-xs text-text-light-secondary dark:text-text-dark-secondary">Total Earnings</div>
              <div class="text-sm font-semibold text-green-600 dark:text-green-400">${{ formatCurrency(referralStore.totalEarnings || 0) }}</div>
            </div>
            <div class="text-right">
              <div class="text-xs text-text-light-secondary dark:text-text-dark-secondary">Total Referrals</div>
              <div class="text-sm font-semibold text-primary-light dark:text-primary-dark">{{ referralStore.totalClaims || 0 }}</div>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
      <!-- Error Message -->
      <div v-if="referralStore.error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
            <i class="ri-error-warning-line text-red-600 dark:text-red-400 text-sm"></i>
          </div>
          <div>
            <h3 class="text-sm font-medium text-red-900 dark:text-red-100">Error</h3>
            <p class="text-xs text-red-600 dark:text-red-400">{{ referralStore.error }}</p>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="referralStore.loading" class="flex justify-center items-center py-12">
        <div class="flex flex-col items-center gap-3">
          <div class="animate-spin rounded-full h-8 w-8 border-3 border-primary-light dark:border-primary-dark border-t-transparent"></div>
          <p class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Loading referral data...</p>
        </div>
      </div>

      <!-- Content -->
      <div v-else class="space-y-6">
        <!-- Statistics Section -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-6 h-6 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
              <i class="ri-bar-chart-line text-blue-600 dark:text-blue-400 text-sm"></i>
            </div>
            <h2 class="text-lg font-semibold text-text-light-primary dark:text-text-dark-primary">{{ t('your_statistics') }}</h2>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Total Earnings Card (Clickable) -->
            <div 
              @click="openEarningsModal"
              class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 p-4 rounded-lg border border-green-200 dark:border-green-800 cursor-pointer hover:shadow-md transition-all duration-200 hover:scale-[1.02]"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                    <i class="ri-money-dollar-circle-line text-white text-sm"></i>
                  </div>
                  <div>
                    <div class="text-xs text-green-600 dark:text-green-400 font-medium">{{ t('total_earnings') }}</div>
                    <div class="text-lg font-bold text-green-700 dark:text-green-300">${{ formatCurrency(referralStore.totalEarnings || 0) }}</div>
                  </div>
                </div>
                <div class="text-green-600 dark:text-green-400">
                  <i class="ri-arrow-right-s-line text-sm"></i>
                </div>
              </div>
            </div>
            
            <!-- Total Referrals Card (Clickable) -->
            <div 
              @click="openReferredUsersModal"
              class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800 cursor-pointer hover:shadow-md transition-all duration-200 hover:scale-[1.02]"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                    <i class="ri-user-add-line text-white text-sm"></i>
                  </div>
                  <div>
                    <div class="text-xs text-blue-600 dark:text-blue-400 font-medium">{{ t('total_referrals') }}</div>
                    <div class="text-lg font-bold text-blue-700 dark:text-blue-300">{{ referralStore.totalClaims || 0 }}</div>
                  </div>
                </div>
                <div class="text-blue-600 dark:text-blue-400">
                  <i class="ri-arrow-right-s-line text-sm"></i>
                </div>
              </div>
            </div>
            
            <!-- Commission Rate Card -->
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 p-4 rounded-lg border border-purple-200 dark:border-purple-800">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center">
                  <i class="ri-percent-line text-white text-sm"></i>
                </div>
                <div>
                  <div class="text-xs text-purple-600 dark:text-purple-400 font-medium">Commission Rate</div>
                  <div class="text-lg font-bold text-purple-700 dark:text-purple-300">10%</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- User Referral Section -->
        <div v-if="isUser || isAdmin" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
          <!-- Header with better visual hierarchy -->
          <div class="flex items-start justify-between mb-6">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                <i class="ri-user-line text-green-600 dark:text-green-400 text-lg"></i>
              </div>
              <div>
                <h2 class="text-lg font-semibold text-text-light-primary dark:text-text-dark-primary mb-1">{{ t('user_referral_link') }}</h2>
                <p class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Share this link to earn rewards from user referrals</p>
              </div>
            </div>
          </div>

          <!-- Action buttons in a separate row -->
          <div class="flex items-center gap-3 mb-4">
            <button
              v-if="isEditingCode !== 'user'"
              @click="startEditingCode('user')"
              class="px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-200 flex items-center gap-2"
            >
              <i class="ri-edit-line text-sm"></i>
              {{ t('edit') }}
            </button>
            <button
              @click="generateNewCode('user')"
              :disabled="isGeneratingCode"
              class="px-3 py-2 text-sm bg-primary-light dark:bg-primary-dark text-white rounded-lg hover:bg-primary-light/90 dark:hover:bg-primary-dark/90 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
            >
              <i v-if="isGeneratingCode" class="ri-loader-4-line animate-spin text-sm"></i>
              <i v-else class="ri-refresh-line text-sm"></i>
              {{ isGeneratingCode ? t('generating') : t('new_code') }}
            </button>
          </div>

          <!-- Edit Mode with better styling -->
          <div v-if="isEditingCode === 'user'" class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
            <div class="flex items-center gap-3">
              <input
                v-model="newReferralCode"
                type="text"
                class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:border-transparent"
                :placeholder="t('enter_new_referral_code')"
              />
              <button
                @click="saveReferralCode('user')"
                :disabled="isGeneratingCode"
                class="px-4 py-2 text-sm bg-green-600 dark:bg-green-500 text-white rounded-lg hover:bg-green-700 dark:hover:bg-green-600 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
              >
                <i class="ri-save-line text-sm"></i>
                {{ t('save') }}
              </button>
              <button
                @click="cancelEditing"
                class="px-4 py-2 text-sm bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 transition-all duration-200 flex items-center gap-2"
              >
                {{ t('cancel') }}
              </button>
            </div>
          </div>

          <!-- Display Mode with improved styling -->
          <div v-else class="space-y-3">
            <div class="flex items-center justify-between">
              <label class="text-sm font-medium text-text-light-primary dark:text-text-dark-primary">Your Referral Link</label>
              <button
                @click="copyToClipboard(userReferralLink)"
                class="text-xs text-primary-light dark:text-primary-dark hover:text-primary-light/80 dark:hover:text-primary-dark/80 font-medium flex items-center gap-1"
              >
                <i class="ri-file-copy-line text-sm"></i>
                Copy
              </button>
            </div>
            <div class="relative">
              <input
                type="text"
                :value="userReferralLink"
                readonly
                class="w-full px-4 py-3 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none font-mono"
              />
              <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
              </div>
            </div>
            <p class="text-xs text-text-light-secondary dark:text-text-dark-secondary">
              Share this link with friends to earn rewards when they join and make purchases
            </p>
          </div>
        </div>

        <!-- Creator Referral Section -->
        <div v-if="isCreator || isAdmin" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
          <!-- Header with better visual hierarchy -->
          <div class="flex items-start justify-between mb-6">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                <i class="ri-vip-crown-line text-purple-600 dark:text-purple-400 text-lg"></i>
              </div>
              <div>
                <h2 class="text-lg font-semibold text-text-light-primary dark:text-text-dark-primary mb-1">{{ t('creator_referral_link') }}</h2>
                <p class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Share this link to earn commissions from creator referrals</p>
              </div>
            </div>
          </div>

          <!-- Action buttons in a separate row -->
          <div class="flex items-center gap-3 mb-4">
            <button
              v-if="isEditingCode !== 'creator'"
              @click="startEditingCode('creator')"
              class="px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-200 flex items-center gap-2"
            >
              <i class="ri-edit-line text-sm"></i>
              {{ t('edit') }}
            </button>
            <button
              @click="generateNewCode('creator')"
              :disabled="isGeneratingCode"
              class="px-3 py-2 text-sm bg-primary-light dark:bg-primary-dark text-white rounded-lg hover:bg-primary-light/90 dark:hover:bg-primary-dark/90 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
            >
              <i v-if="isGeneratingCode" class="ri-loader-4-line animate-spin text-sm"></i>
              <i v-else class="ri-refresh-line text-sm"></i>
              {{ isGeneratingCode ? t('generating') : t('new_code') }}
            </button>
          </div>

          <!-- Edit Mode with better styling -->
          <div v-if="isEditingCode === 'creator'" class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
            <div class="flex items-center gap-3">
              <input
                v-model="newReferralCode"
                type="text"
                class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:border-transparent"
                :placeholder="t('enter_new_referral_code')"
              />
              <button
                @click="saveReferralCode('creator')"
                :disabled="isGeneratingCode"
                class="px-4 py-2 text-sm bg-green-600 dark:bg-green-500 text-white rounded-lg hover:bg-green-700 dark:hover:bg-green-600 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
              >
                <i class="ri-save-line text-sm"></i>
                {{ t('save') }}
              </button>
              <button
                @click="cancelEditing"
                class="px-4 py-2 text-sm bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 transition-all duration-200 flex items-center gap-2"
              >
                {{ t('cancel') }}
              </button>
            </div>
          </div>

          <!-- Display Mode with improved styling -->
          <div v-else class="space-y-3">
            <div class="flex items-center justify-between">
              <label class="text-sm font-medium text-text-light-primary dark:text-text-dark-primary">Your Referral Link</label>
              <button
                @click="copyToClipboard(creatorReferralLink)"
                class="text-xs text-primary-light dark:text-primary-dark hover:text-primary-light/80 dark:hover:text-primary-dark/80 font-medium flex items-center gap-1"
              >
                <i class="ri-file-copy-line text-sm"></i>
                Copy
              </button>
            </div>
            <div class="relative">
              <input
                type="text"
                :value="creatorReferralLink"
                readonly
                class="w-full px-4 py-3 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none font-mono"
              />
              <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
              </div>
            </div>
            <p class="text-xs text-text-light-secondary dark:text-text-dark-secondary">
              Share this link with other creators to earn 10% commission on their earnings
            </p>
          </div>
        </div>

        <!-- How It Works Section -->
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-6 border border-blue-200 dark:border-blue-800">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-6 h-6 bg-blue-500 rounded-lg flex items-center justify-center">
              <i class="ri-question-line text-white text-sm"></i>
            </div>
            <h2 class="text-lg font-semibold text-blue-900 dark:text-blue-100">How It Works</h2>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="text-center">
              <div class="w-10 h-10 bg-blue-100 dark:bg-blue-800 rounded-full flex items-center justify-center mx-auto mb-3">
                <span class="text-blue-600 dark:text-blue-400 font-bold text-sm">1</span>
              </div>
              <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-1">Share Your Link</h3>
              <p class="text-xs text-blue-700 dark:text-blue-200">Share your referral link with friends and followers</p>
            </div>
            
            <div class="text-center">
              <div class="w-10 h-10 bg-blue-100 dark:bg-blue-800 rounded-full flex items-center justify-center mx-auto mb-3">
                <span class="text-blue-600 dark:text-blue-400 font-bold text-sm">2</span>
              </div>
              <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-1">They Sign Up</h3>
              <p class="text-xs text-blue-700 dark:text-blue-200">When they sign up using your link, you get credit</p>
            </div>
            
            <div class="text-center">
              <div class="w-10 h-10 bg-blue-100 dark:bg-blue-800 rounded-full flex items-center justify-center mx-auto mb-3">
                <span class="text-blue-600 dark:text-blue-400 font-bold text-sm">3</span>
              </div>
              <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-1">Earn Money</h3>
              <p class="text-xs text-blue-700 dark:text-blue-200">Earn commission on their purchases and subscriptions</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Referred Users Modal -->
    <TransitionRoot appear :show="showReferredUsersModal" as="template">
      <Dialog as="div" @close="showReferredUsersModal = false" class="relative z-50">
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="ease-in duration-200"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4">
            <TransitionChild
              as="template"
              enter="ease-out duration-300"
              enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100"
              leave="ease-in duration-200"
              leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95"
            >
              <DialogPanel class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-lg w-full max-h-[80vh] overflow-hidden">
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                  <div class="flex items-center gap-2">
                    <div class="w-6 h-6 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                      <i class="ri-user-add-line text-blue-600 dark:text-blue-400 text-sm"></i>
                    </div>
                    <DialogTitle class="text-lg font-semibold text-gray-900 dark:text-white">Referred Users</DialogTitle>
                  </div>
                  <button
                    @click="showReferredUsersModal = false"
                    class="p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200"
                  >
                    <i class="ri-close-line text-lg"></i>
                  </button>
                </div>
                
                <div class="p-4 overflow-y-auto max-h-[60vh]">
                  <div v-if="loading" class="flex justify-center items-center py-6">
                    <div class="animate-spin rounded-full h-6 w-6 border-3 border-blue-600 border-t-transparent"></div>
                  </div>
                  
                  <div v-else-if="error" class="text-center py-6">
                    <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mx-auto mb-3">
                      <i class="ri-error-warning-line text-red-600 dark:text-red-400 text-lg"></i>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Error Loading Users</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ error }}</p>
                  </div>
                  
                  <div v-else-if="referredUsers.length === 0" class="text-center py-6">
                    <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-3">
                      <i class="ri-user-add-line text-lg text-gray-400 dark:text-gray-500"></i>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">No Referred Users Yet</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Start sharing your referral link to see your referred users here</p>
                  </div>
                  
                  <div v-else class="space-y-3">
                    <div 
                      v-for="referral in referredUsers" 
                      :key="referral.id"
                      class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-all duration-200"
                    >
                      <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full flex items-center justify-center">
                          <span class="text-white font-semibold text-xs">{{ referral.referred?.username?.charAt(0).toUpperCase() || 'U' }}</span>
                        </div>
                        <div>
                          <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ referral.referred?.username || 'Unknown User' }}</h4>
                          <p class="text-xs text-gray-500 dark:text-gray-400">{{ referral.referred?.email || 'No email' }}</p>
                        </div>
                      </div>
                      <div class="text-right">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Joined</div>
                        <div class="text-xs font-medium text-gray-900 dark:text-white">{{ formatDate(referral.created_at) }}</div>
                      </div>
                    </div>
                  </div>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Earnings Breakdown Modal -->
    <TransitionRoot appear :show="showEarningsModal" as="template">
      <Dialog as="div" @close="showEarningsModal = false" class="relative z-50">
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="ease-in duration-200"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4">
            <TransitionChild
              as="template"
              enter="ease-out duration-300"
              enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100"
              leave="ease-in duration-200"
              leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95"
            >
              <DialogPanel class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-2xl w-full max-h-[80vh] overflow-hidden">
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                  <div class="flex items-center gap-2">
                    <div class="w-6 h-6 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                      <i class="ri-money-dollar-circle-line text-green-600 dark:text-green-400 text-sm"></i>
                    </div>
                    <DialogTitle class="text-lg font-semibold text-gray-900 dark:text-white">Earnings Breakdown</DialogTitle>
                  </div>
                  <button
                    @click="showEarningsModal = false"
                    class="p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200"
                  >
                    <i class="ri-close-line text-lg"></i>
                  </button>
                </div>
                
                <div class="p-4 overflow-y-auto max-h-[60vh]">
                  <!-- Summary Section -->
                  <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                    <div class="flex items-center justify-between">
                      <div>
                        <h3 class="text-sm font-semibold text-green-900 dark:text-green-100">Total Earnings</h3>
                        <p class="text-xs text-green-700 dark:text-green-300">From all your referrals</p>
                      </div>
                      <div class="text-right">
                        <div class="text-2xl font-bold text-green-700 dark:text-green-300">${{ formatCurrency(totalEarningsAmount) }}</div>
                        <div class="text-xs text-green-600 dark:text-green-400">{{ earningsData.length }} transactions</div>
                      </div>
                    </div>
                  </div>

                  <div v-if="earningsLoading" class="flex justify-center items-center py-6">
                    <div class="animate-spin rounded-full h-6 w-6 border-3 border-green-600 border-t-transparent"></div>
                  </div>
                  
                  <div v-else-if="earningsError" class="text-center py-6">
                    <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mx-auto mb-3">
                      <i class="ri-error-warning-line text-red-600 dark:text-red-400 text-lg"></i>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Error Loading Earnings</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ earningsError }}</p>
                  </div>
                  
                  <div v-else-if="earningsData.length === 0" class="text-center py-6">
                    <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-3">
                      <i class="ri-money-dollar-circle-line text-lg text-gray-400 dark:text-gray-500"></i>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">No Earnings Yet</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Your referred users haven't made any purchases yet</p>
                  </div>
                  
                  <div v-else class="space-y-3">
                    <div 
                      v-for="earning in earningsData" 
                      :key="earning.id"
                      class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-all duration-200"
                    >
                      <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-gradient-to-br from-green-400 to-blue-500 rounded-full flex items-center justify-center">
                          <span class="text-white font-semibold text-xs">{{ earning.referral?.referred?.username?.charAt(0).toUpperCase() || 'U' }}</span>
                        </div>
                        <div>
                          <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ earning.referral?.referred?.username || 'Unknown User' }}</h4>
                          <p class="text-xs text-gray-500 dark:text-gray-400">{{ earning.transaction?.description || 'Referral commission' }}</p>
                        </div>
                      </div>
                      <div class="text-right">
                        <div class="text-sm font-bold text-green-600 dark:text-green-400">${{ formatCurrency(earning.amount) }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ formatDate(earning.created_at) }}</div>
                      </div>
                    </div>
                  </div>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useReferralStore } from '@/stores/referralStore'
import { useAuthStore } from '@/stores/authStore'
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'
import axiosInstance from '@/axios'

const { t } = useI18n()
const referralStore = useReferralStore()
const authStore = useAuthStore()

// User type checks
const isUser = computed(() => authStore.user?.role === 'user')
const isCreator = computed(() => authStore.user?.role === 'creator')
const isAdmin = computed(() => authStore.user?.role === 'admin')

// Referral data
const userReferralLink = computed(() => {
  const baseUrl = 'https://fans4more.com/auth?ref='
  return baseUrl + (referralStore.userReferralCode || '')
})

const creatorReferralLink = computed(() => {
  const baseUrl = 'https://fans4more.com/auth?ref='
  return baseUrl + (referralStore.creatorReferralCode || '')
})

// Edit mode state
const isEditingCode = ref(null)
const newReferralCode = ref('')
const isGeneratingCode = ref(false)

// Modal state
const showReferredUsersModal = ref(false)
const referredUsers = ref([])
const loading = ref(false)
const error = ref(null)

// Earnings modal state
const showEarningsModal = ref(false)
const earningsLoading = ref(false)
const earningsError = ref(null)
const earningsData = ref([])
const totalEarningsAmount = ref(0)

// Format currency
const formatCurrency = (amount) => {
  return parseFloat(amount || 0).toFixed(2)
}

// Format date
const formatDate = (dateString) => {
  if (!dateString) return 'Unknown'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Copy to clipboard
const copyToClipboard = async (text) => {
  try {
    await navigator.clipboard.writeText(text)
    // You could add a toast notification here
  } catch (err) {
    console.error('Failed to copy text: ', err)
  }
}

// Start editing code
const startEditingCode = (type) => {
  isEditingCode.value = type
  newReferralCode.value = type === 'user' ? referralStore.userReferralCode : referralStore.creatorReferralCode
}

// Cancel editing
const cancelEditing = () => {
  isEditingCode.value = null
  newReferralCode.value = ''
}

// Generate new code
const generateNewCode = async (type) => {
  isGeneratingCode.value = true
  try {
    if (type === 'user') {
      await referralStore.generateUserReferralCode()
    } else {
      await referralStore.generateCreatorReferralCode()
    }
    isEditingCode.value = null
  } catch (err) {
    console.error('Error generating code:', err)
  } finally {
    isGeneratingCode.value = false
  }
}

// Save referral code
const saveReferralCode = async (type) => {
  if (!newReferralCode.value.trim()) return
  
  isGeneratingCode.value = true
  try {
    if (type === 'user') {
      await referralStore.updateUserReferralCode(newReferralCode.value)
    } else {
      await referralStore.updateCreatorReferralCode(newReferralCode.value)
    }
    isEditingCode.value = null
    newReferralCode.value = ''
  } catch (err) {
    console.error('Error updating code:', err)
  } finally {
    isGeneratingCode.value = false
  }
}

// Fetch referred users
const fetchReferredUsers = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await axiosInstance.get('/referrals/referred-users')
    referredUsers.value = response.data.data.data || []
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load referred users'
    console.error('Error fetching referred users:', err)
  } finally {
    loading.value = false
  }
}

// Open modal and fetch data
const openReferredUsersModal = async () => {
  showReferredUsersModal.value = true
  await fetchReferredUsers()
}

// Fetch earnings breakdown
const fetchEarningsBreakdown = async () => {
  earningsLoading.value = true
  earningsError.value = null
  try {
    const response = await axiosInstance.get('/referrals/earnings-breakdown')
    earningsData.value = response.data.data.data || []
    totalEarningsAmount.value = response.data.data.total_earnings || 0
  } catch (err) {
    earningsError.value = err.response?.data?.message || 'Failed to load earnings breakdown'
    console.error('Error fetching earnings breakdown:', err)
  } finally {
    earningsLoading.value = false
  }
}

// Open earnings modal
const openEarningsModal = async () => {
  showEarningsModal.value = true
  await fetchEarningsBreakdown()
}

// Initialize
onMounted(() => {
  referralStore.fetchReferralData()
})
</script> 