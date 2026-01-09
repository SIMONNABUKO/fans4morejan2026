<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-gray-900 dark:via-slate-900 dark:to-indigo-950">
    <!-- Modern Header with Glass Effect -->
    <header class="sticky top-0 z-20 bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl border-b border-gray-200/50 dark:border-gray-700/50 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-24 py-6">
          <!-- Enhanced Left Side: Title with Icon -->
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg">
              <i class="ri-link text-white text-xl"></i>
            </div>
            <div class="flex flex-col">
              <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 dark:from-white dark:to-gray-300 bg-clip-text text-transparent leading-tight">
                {{ $t('tracking_links') }}
              </h1>
              <p class="text-gray-600 dark:text-gray-400 text-sm mt-0.5 font-medium">
                {{ $t('manage_and_monitor') }}
              </p>
            </div>
          </div>
          
          <!-- Enhanced Right Side: Create Button -->
          <button
            @click="openCreateModal"
            class="group flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-2xl font-semibold transition-all duration-300 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 hover:scale-105 shadow-lg hover:shadow-xl"
          >
            <i class="ri-add-circle-line text-xl group-hover:rotate-90 transition-transform duration-300"></i>
            {{ $t('create_new') }}
          </button>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Enhanced Loading State -->
      <div v-if="trackingLinksStore.loading" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
        <div v-for="i in 6" :key="i" class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-3xl shadow-sm border border-gray-200/50 dark:border-gray-700/50 p-8">
          <div class="flex justify-between items-start mb-8">
            <div class="flex items-center gap-4">
              <div class="w-14 h-14 bg-gray-200 dark:bg-gray-700 rounded-2xl animate-pulse"></div>
              <div>
                <div class="h-6 w-32 bg-gray-200 dark:bg-gray-700 rounded-lg mb-2 animate-pulse"></div>
                <div class="h-4 w-20 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
              </div>
            </div>
          </div>
          <div class="bg-gradient-to-br from-gray-100 to-blue-100 dark:from-gray-700 dark:to-blue-900/20 rounded-2xl p-6 space-y-6">
            <div class="grid grid-cols-2 gap-4">
              <div v-for="j in 2" :key="j" class="text-center p-4 rounded-xl bg-white/70 dark:bg-gray-800/70">
                <div class="h-8 w-16 mx-auto mb-2 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
                <div class="h-4 w-12 mx-auto bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Enhanced Error State -->
      <div v-else-if="trackingLinksStore.error" class="bg-gradient-to-r from-red-50 to-pink-50 dark:from-red-900/20 dark:to-pink-900/20 border border-red-200 dark:border-red-700/50 rounded-3xl p-8 mb-8 flex items-start gap-6">
        <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-pink-500 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg">
          <i class="ri-error-warning-line text-white text-xl"></i>
        </div>
        <div>
          <p class="font-bold text-red-900 dark:text-red-100 mb-2 text-lg">{{ $t('error') }}</p>
          <p class="text-red-700 dark:text-red-300">{{ trackingLinksStore.error }}</p>
        </div>
      </div>

      <!-- Enhanced Empty State -->
      <div v-else-if="!trackingLinksStore.hasTrackingLinks" class="text-center py-20 px-4">
        <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-3xl shadow-lg border border-gray-200/50 dark:border-gray-700/50 p-16 max-w-lg mx-auto">
          <div class="w-24 h-24 bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-500 rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-xl">
            <i class="ri-link text-4xl text-white"></i>
          </div>
          <h3 class="text-2xl font-bold mb-4 text-gray-900 dark:text-white">
            {{ $t('no_tracking_links') }}
          </h3>
          <p class="text-gray-600 dark:text-gray-400 mb-10 text-lg leading-relaxed">
            {{ $t('create_tracking_link_description') }}
          </p>
          <button
            @click="openCreateModal"
            class="group flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-2xl font-semibold transition-all duration-300 mx-auto hover:scale-105 shadow-lg hover:shadow-xl"
          >
            <i class="ri-add-circle-line text-xl group-hover:rotate-90 transition-transform duration-300"></i>
            {{ $t('create_new') }}
          </button>
        </div>
      </div>

      <!-- Enhanced Tracking Links Grid -->
      <div v-else class="space-y-12">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
          <div
            v-for="link in trackingLinksStore.trackingLinks"
            :key="link.id"
            class="group bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-3xl shadow-sm border border-gray-200/50 dark:border-gray-700/50 transition-all duration-500 hover:shadow-2xl hover:scale-[1.02] hover:bg-white/90 dark:hover:bg-gray-800/90 overflow-hidden"
          >
            <!-- Enhanced Link Header -->
            <div class="p-8 border-b border-gray-200/50 dark:border-gray-700/50">
              <div class="flex justify-between items-start">
                <div class="flex items-center gap-4">
                  <div class="relative">
                    <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-2xl flex items-center justify-center shadow-lg">
                      <img
                        :src="getAvatarUrl(link)"
                        :alt="link.creator?.display_name || link.creator?.username"
                        class="w-12 h-12 rounded-xl object-cover"
                      />
                    </div>
                    <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-500 rounded-full border-2 border-white dark:border-gray-800"></div>
                  </div>
                  <div>
                    <h3 class="font-bold text-gray-900 dark:text-white text-xl mb-1">
                      {{ link.name }}
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">
                      @{{ link.creator?.username }}
                    </p>
                  </div>
                </div>
                <div class="flex items-center gap-2">
                  <button
                    @click="copyTrackingLinkUrl(getTrackingLinkUrl(link))"
                    class="p-3 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-xl transition-all duration-200"
                    :title="$t('copy_link')"
                  >
                    <i class="ri-file-copy-line text-lg"></i>
                  </button>
                  <button
                    @click="confirmAndDeleteLink(link.id)"
                    class="p-3 text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-all duration-200"
                    :title="$t('delete')"
                  >
                    <i class="ri-delete-bin-line text-lg"></i>
                  </button>
                </div>
              </div>
            </div>

            <!-- Enhanced Link Stats -->
            <div class="p-8">
              <div class="bg-gradient-to-br from-gray-50 via-blue-50 to-indigo-50 dark:from-gray-700 dark:via-blue-900/20 dark:to-indigo-900/20 rounded-2xl p-6 space-y-6 mb-6">
                <!-- Primary Stats Row -->
                <div class="grid grid-cols-2 gap-4">
                  <button 
                    @click="openEventModal(link, 'click')"
                    class="group text-center p-6 rounded-2xl bg-white/80 dark:bg-gray-800/80 hover:bg-white dark:hover:bg-gray-800 transition-all duration-300 hover:scale-105 shadow-sm hover:shadow-lg"
                  >
                    <p class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent mb-2">
                      {{ link.clicks_count || 0 }}
                    </p>
                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                      {{ $t('clicks') }}
                    </p>
                  </button>
                  <button 
                    @click="openActionModal(link, 'signup')"
                    class="group text-center p-6 rounded-2xl bg-white/80 dark:bg-gray-800/80 hover:bg-white dark:hover:bg-gray-800 transition-all duration-300 hover:scale-105 shadow-sm hover:shadow-lg"
                  >
                    <p class="text-4xl font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent mb-2">
                      {{ link.signups_count || 0 }}
                    </p>
                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                      {{ $t('signups') }}
                    </p>
                  </button>
                </div>
                
                <!-- Secondary Stats Row -->
                <div class="grid grid-cols-3 gap-3">
                  <div class="text-center p-4 rounded-xl bg-white/60 dark:bg-gray-800/60">
                    <p class="text-2xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                      {{ link.subscriptions_count || 0 }}
                    </p>
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">
                      {{ $t('subscriptions') }}
                    </p>
                  </div>
                  <div class="text-center p-4 rounded-xl bg-white/60 dark:bg-gray-800/60">
                    <p class="text-2xl font-bold bg-gradient-to-r from-orange-600 to-red-600 bg-clip-text text-transparent">
                      {{ link.purchases_count || 0 }}
                    </p>
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">
                      {{ $t('purchases') }}
                    </p>
                  </div>
                  <div class="text-center p-4 rounded-xl bg-white/60 dark:bg-gray-800/60">
                    <p class="text-2xl font-bold bg-gradient-to-r from-pink-600 to-rose-600 bg-clip-text text-transparent">
                      {{ link.tips_count || 0 }}
                    </p>
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">
                      {{ $t('tips') }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Enhanced Tracking Link URL -->
              <div class="mb-6">
                <div class="flex items-center gap-3 p-4 bg-gray-100/80 dark:bg-gray-700/80 rounded-2xl border border-gray-200/50 dark:border-gray-600/50">
                  <i class="ri-link text-gray-500 dark:text-gray-400 text-lg"></i>
                  <span class="text-sm text-gray-700 dark:text-gray-300 truncate font-mono">
                    {{ getTrackingLinkUrl(link) }}
                  </span>
                </div>
              </div>

              <!-- Enhanced Action Buttons -->
              <div class="flex gap-3">
                <button
                  @click="openStatsModal(link)"
                  class="flex-1 flex items-center justify-center gap-2 py-3 px-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl text-sm font-semibold transition-all duration-200 hover:scale-105 shadow-lg"
                >
                  <i class="ri-bar-chart-line"></i>
                  {{ $t('view_stats') }}
                </button>
                <button
                  @click="toggleLinkStatus(link)"
                  :class="[
                    'flex items-center justify-center gap-2 py-3 px-6 rounded-xl text-sm font-semibold transition-all duration-200 hover:scale-105 shadow-lg',
                    link.is_active 
                      ? 'bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white'
                      : 'bg-gradient-to-r from-gray-600 to-slate-600 hover:from-gray-700 hover:to-slate-700 text-white'
                  ]"
                >
                  <i :class="link.is_active ? 'ri-toggle-fill' : 'ri-toggle-line'"></i>
                  {{ link.is_active ? $t('active') : $t('inactive') }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Completely redesigned Summary Stats with better spacing and layout -->
        <div class="bg-gradient-to-br from-white/90 via-blue-50/90 to-indigo-50/90 dark:from-gray-800/90 dark:via-gray-700/90 dark:to-indigo-900/50 backdrop-blur-xl rounded-4xl shadow-2xl border border-white/30 dark:border-gray-600/30 p-16 relative overflow-hidden">
          <!-- Background decoration -->
          <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 via-transparent to-indigo-500/5 pointer-events-none"></div>
          <div class="absolute top-0 right-0 w-96 h-96 bg-gradient-to-br from-blue-400/10 to-indigo-400/10 rounded-full blur-3xl pointer-events-none"></div>
          <div class="absolute bottom-0 left-0 w-72 h-72 bg-gradient-to-tr from-purple-400/10 to-pink-400/10 rounded-full blur-3xl pointer-events-none"></div>
          
          <div class="relative z-10">
            <!-- Enhanced Header with more space -->
            <div class="text-center mb-20">
              <div class="inline-flex items-center gap-6 mb-8">
                <div class="w-20 h-20 bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600 rounded-4xl flex items-center justify-center mx-auto mb-8 shadow-2xl">
                  <i class="ri-bar-chart-box-line text-white text-3xl"></i>
                </div>
                <h2 class="text-5xl font-black bg-gradient-to-r from-gray-900 via-indigo-900 to-purple-900 dark:from-white dark:via-blue-100 dark:to-purple-100 bg-clip-text text-transparent">
                  Performance Overview
                </h2>
              </div>
              <p class="text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto leading-relaxed">
                Track your success with comprehensive analytics and insights across all your tracking links
              </p>
            </div>
            
            <!-- Redesigned Stats Grid with much better spacing and responsive layout -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-12 mb-16">
              <!-- Total Links Card -->
              <div class="group relative">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-4xl blur-2xl opacity-20 group-hover:opacity-30 transition-opacity duration-700"></div>
                <div class="relative bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm rounded-4xl p-10 border border-white/60 dark:border-gray-600/60 shadow-2xl hover:shadow-3xl transition-all duration-700 hover:scale-110 hover:-translate-y-4 cursor-pointer">
                  <div class="text-center">
                    <div class="w-24 h-24 bg-gradient-to-br from-blue-500 via-blue-600 to-indigo-600 rounded-4xl flex items-center justify-center mx-auto mb-8 shadow-2xl group-hover:scale-125 group-hover:rotate-6 transition-all duration-700">
                      <i class="ri-link text-white text-4xl"></i>
                    </div>
                    <div class="space-y-4">
                      <p class="text-6xl font-black bg-gradient-to-br from-blue-600 to-indigo-700 bg-clip-text text-transparent leading-none">
                        {{ trackingLinksStore.trackingLinks.length }}
                      </p>
                      <p class="text-base font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">
                        Total Links
                      </p>
                      <div class="w-16 h-1.5 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full mx-auto"></div>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Total Clicks Card -->
              <div class="group relative">
                <div class="absolute inset-0 bg-gradient-to-br from-green-500 to-emerald-600 rounded-4xl blur-2xl opacity-20 group-hover:opacity-30 transition-opacity duration-700"></div>
                <div class="relative bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm rounded-4xl p-10 border border-white/60 dark:border-gray-600/60 shadow-2xl hover:shadow-3xl transition-all duration-700 hover:scale-110 hover:-translate-y-4 cursor-pointer">
                  <div class="text-center">
                    <div class="w-24 h-24 bg-gradient-to-br from-green-500 via-green-600 to-emerald-600 rounded-4xl flex items-center justify-center mx-auto mb-8 shadow-2xl group-hover:scale-125 group-hover:rotate-6 transition-all duration-700">
                      <i class="ri-cursor-line text-white text-4xl"></i>
                    </div>
                    <div class="space-y-4">
                      <p class="text-6xl font-black bg-gradient-to-br from-green-600 to-emerald-700 bg-clip-text text-transparent leading-none">
                        {{ totalClicks }}
                      </p>
                      <p class="text-base font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">
                        Total Clicks
                      </p>
                      <div class="w-16 h-1.5 bg-gradient-to-r from-green-500 to-emerald-600 rounded-full mx-auto"></div>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Total Signups Card -->
              <div class="group relative">
                <div class="absolute inset-0 bg-gradient-to-br from-purple-500 to-pink-600 rounded-4xl blur-2xl opacity-20 group-hover:opacity-30 transition-opacity duration-700"></div>
                <div class="relative bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm rounded-4xl p-10 border border-white/60 dark:border-gray-600/60 shadow-2xl hover:shadow-3xl transition-all duration-700 hover:scale-110 hover:-translate-y-4 cursor-pointer">
                  <div class="text-center">
                    <div class="w-24 h-24 bg-gradient-to-br from-purple-500 via-purple-600 to-pink-600 rounded-4xl flex items-center justify-center mx-auto mb-8 shadow-2xl group-hover:scale-125 group-hover:rotate-6 transition-all duration-700">
                      <i class="ri-user-add-line text-white text-4xl"></i>
                    </div>
                    <div class="space-y-4">
                      <p class="text-6xl font-black bg-gradient-to-br from-purple-600 to-pink-700 bg-clip-text text-transparent leading-none">
                        {{ totalSignups }}
                      </p>
                      <p class="text-base font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">
                        Total Signups
                      </p>
                      <div class="w-16 h-1.5 bg-gradient-to-r from-purple-500 to-pink-600 rounded-full mx-auto"></div>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Conversion Rate Card -->
              <div class="group relative">
                <div class="absolute inset-0 bg-gradient-to-br from-orange-500 to-red-600 rounded-4xl blur-2xl opacity-20 group-hover:opacity-30 transition-opacity duration-700"></div>
                <div class="relative bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm rounded-4xl p-10 border border-white/60 dark:border-gray-600/60 shadow-2xl hover:shadow-3xl transition-all duration-700 hover:scale-110 hover:-translate-y-4 cursor-pointer">
                  <div class="text-center">
                    <div class="w-24 h-24 bg-gradient-to-br from-orange-500 via-orange-600 to-red-600 rounded-4xl flex items-center justify-center mx-auto mb-8 shadow-2xl group-hover:scale-125 group-hover:rotate-6 transition-all duration-700">
                      <i class="ri-line-chart-line text-white text-4xl"></i>
                    </div>
                    <div class="space-y-4">
                      <p class="text-6xl font-black bg-gradient-to-br from-orange-600 to-red-700 bg-clip-text text-transparent leading-none">
                        {{ conversionRate }}<span class="text-3xl">%</span>
                      </p>
                      <p class="text-base font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">
                        Conversion Rate
                      </p>
                      <div class="w-16 h-1.5 bg-gradient-to-r from-orange-500 to-red-600 rounded-full mx-auto"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Enhanced Bottom Insights with better spacing -->
            <div class="pt-12 border-t border-gray-200/50 dark:border-gray-600/50">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-12 text-center">
                <div class="space-y-4">
                  <p class="text-4xl font-black text-gray-900 dark:text-white">
                    {{ trackingLinksStore.trackingLinks.filter(link => link.is_active).length }}
                  </p>
                  <p class="text-lg text-gray-600 dark:text-gray-400 font-semibold">Active Links</p>
                </div>
                <div class="space-y-4">
                  <p class="text-4xl font-black text-gray-900 dark:text-white">
                    {{ totalClicks > 0 ? Math.round(totalClicks / trackingLinksStore.trackingLinks.length) : 0 }}
                  </p>
                  <p class="text-lg text-gray-600 dark:text-gray-400 font-semibold">Avg. Clicks per Link</p>
                </div>
                <div class="space-y-4">
                  <p class="text-4xl font-black text-gray-900 dark:text-white">
                    {{ totalSignups > 0 ? Math.round(totalSignups / trackingLinksStore.trackingLinks.length) : 0 }}
                  </p>
                  <p class="text-lg text-gray-600 dark:text-gray-400 font-semibold">Avg. Signups per Link</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Enhanced Create Tracking Link Modal -->
    <TransitionRoot as="template" :show="showCreateModal">
      <Dialog as="div" class="relative z-50" @close="closeCreateModal">
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="ease-in duration-200"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4 text-center">
            <TransitionChild
              as="template"
              enter="ease-out duration-300"
              enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100"
              leave="ease-in duration-200"
              leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95"
            >
              <DialogPanel class="w-full max-w-lg transform overflow-hidden rounded-3xl bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl p-8 text-left align-middle shadow-2xl transition-all border border-gray-200/50 dark:border-gray-700/50">
                <div class="flex items-center gap-4 mb-8">
                  <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="ri-add-circle-line text-white text-xl"></i>
                  </div>
                  <DialogTitle as="h3" class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $t('create_tracking_link') }}
                  </DialogTitle>
                </div>
                
                <form @submit.prevent="createTrackingLink" class="space-y-6">
                  <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                      {{ $t('link_name') }}
                    </label>
                    <input
                      id="name"
                      v-model="newLink.name"
                      type="text"
                      required
                      class="w-full px-4 py-4 border border-gray-300 dark:border-gray-600 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white/80 dark:bg-gray-700/80 text-gray-900 dark:text-white font-medium transition-all duration-200"
                      :placeholder="$t('enter_link_name')"
                    />
                  </div>
                  
                  <div>
                    <label for="description" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                      {{ $t('description') }} ({{ $t('optional') }})
                    </label>
                    <textarea
                      id="description"
                      v-model="newLink.description"
                      rows="4"
                      class="w-full px-4 py-4 border border-gray-300 dark:border-gray-600 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white/80 dark:bg-gray-700/80 text-gray-900 dark:text-white font-medium transition-all duration-200 resize-none"
                      :placeholder="$t('enter_description')"
                    ></textarea>
                  </div>
                  
                  <div class="flex justify-end gap-4 pt-6">
                    <button
                      type="button"
                      @click="closeCreateModal"
                      class="px-6 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-200 hover:scale-105"
                    >
                      {{ $t('cancel') }}
                    </button>
                    <button
                      type="submit"
                      :disabled="!newLink.name.trim() || isCreating"
                      class="px-8 py-3 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 rounded-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 hover:scale-105 shadow-lg"
                    >
                      <i v-if="isCreating" class="ri-loader-4-line animate-spin"></i>
                      {{ isCreating ? $t('creating') : $t('create') }}
                    </button>
                  </div>
                </form>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useTrackingLinksStore } from '@/stores/trackingLinksStore'
import { useToast } from 'vue-toast-notification'
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'

const { t } = useI18n()
const trackingLinksStore = useTrackingLinksStore()
const toast = useToast()

// Modal states
const showCreateModal = ref(false)
const isCreating = ref(false)

// Form data
const newLink = ref({
  name: '',
  description: ''
})

// Computed properties for summary stats
const totalClicks = computed(() => {
  return trackingLinksStore.trackingLinks.reduce((total, link) => total + (link.clicks_count || 0), 0)
})

const totalSignups = computed(() => {
  return trackingLinksStore.trackingLinks.reduce((total, link) => total + (link.signups_count || 0), 0)
})

const conversionRate = computed(() => {
  if (totalClicks.value === 0) return 0
  return ((totalSignups.value / totalClicks.value) * 100).toFixed(1)
})

// Functions
const openCreateModal = () => {
  newLink.value = {
    name: '',
    description: ''
  }
  showCreateModal.value = true
}

const closeCreateModal = () => {
  showCreateModal.value = false
  newLink.value = {
    name: '',
    description: ''
  }
}

const createTrackingLink = async () => {
  if (!newLink.value.name.trim()) return
  
  isCreating.value = true
  try {
    await trackingLinksStore.createTrackingLink(newLink.value)
    closeCreateModal()
    toast.success(t('tracking_link_created'))
  } catch (error) {
    console.error('Error creating tracking link:', error)
    toast.error(t('error_creating_tracking_link'))
  } finally {
    isCreating.value = false
  }
}

const copyTrackingLinkUrl = (url) => {
  navigator.clipboard.writeText(url).then(() => {
    toast.success(t('link_copied'))
  }).catch(() => {
    toast.error(t('failed_to_copy'))
  })
}

const getTrackingLinkUrl = (link) => {
  if (!link || !link.creator) return ''
  const baseUrl = import.meta.env.VITE_FRONTEND_URL || window.location.origin
  return `${baseUrl}/${link.creator.username}/${link.slug}`
}

const getAvatarUrl = (link) => {
  if (!link?.creator?.avatar) {
    return '/images/default-avatar.png'
  }
  return link.creator.avatar
}

const confirmAndDeleteLink = async (id) => {
  if (confirm(t('confirm_delete_tracking_link'))) {
    try {
      await trackingLinksStore.deleteTrackingLink(id)
      toast.success(t('tracking_link_deleted'))
    } catch (error) {
      console.error('Error deleting tracking link:', error)
      toast.error(t('failed_to_delete_tracking_link'))
    }
  }
}

const toggleLinkStatus = async (link) => {
  try {
    await trackingLinksStore.updateTrackingLink(link.id, {
      is_active: !link.is_active
    })
    toast.success(link.is_active ? t('link_deactivated') : t('link_activated'))
  } catch (error) {
    console.error('Error updating tracking link:', error)
    toast.error(t('error_updating_tracking_link'))
  }
}

const openStatsModal = async (link) => {
  console.log('Opening stats modal for link:', link.id)
}

const openEventModal = async (link, eventType) => {
  console.log('Opening event modal for link:', link.id, 'event type:', eventType)
}

const openActionModal = async (link, actionType) => {
  console.log('Opening action modal for link:', link.id, 'action type:', actionType)
}

// Lifecycle
onMounted(async () => {
  try {
    await trackingLinksStore.fetchTrackingLinks()
  } catch (error) {
    console.error('Error loading tracking links:', error)
    toast.error(t('error_loading_tracking_links'))
  }
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Added custom rounded-4xl utility for extra rounded corners */
.rounded-4xl {
  border-radius: 2rem;
}

/* Added custom shadow-3xl for enhanced shadows */
.shadow-3xl {
  box-shadow: 0 35px 60px -12px rgba(0, 0, 0, 0.25);
}
</style>
