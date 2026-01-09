<template>
  <div class="bg-background-light dark:bg-background-dark border-b border-border-light dark:border-border-dark sticky top-0 z-20 backdrop-blur-lg w-full">
    <div class="flex items-center justify-between px-4 py-3 w-full" :style="{ maxWidth: `${viewportWidth}px` }">
      <!-- Scrollable Filter Buttons -->
      <div class="flex-1 overflow-hidden min-w-0">
        <div 
          ref="scrollContainer"
          class="flex gap-2 overflow-x-auto scrollbar-hide"
          @scroll="handleScroll"
        >
          <button
            v-for="filter in feedFilterStore.enabledFilters"
            :key="filter.id"
            :class="[
              'px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap flex-shrink-0 transition-all duration-200',
              feedFilterStore.activeFilter === filter.id
                ? 'bg-primary-light dark:bg-primary-dark text-white shadow-lg'
                : 'bg-surface-light dark:bg-surface-dark text-text-light-secondary dark:text-text-dark-secondary hover:bg-gray-100 dark:hover:bg-gray-700'
            ]"
            @click="handleFilterClick(filter.id)"
          >
            {{ t(filter.label) }}
          </button>
        </div>
      </div>
      
      <!-- Action Buttons -->
      <div class="flex items-center gap-2 ml-3 flex-shrink-0 min-w-fit">
        <button
          @click="refreshFeed"
          class="p-2 text-text-light-secondary dark:text-text-dark-secondary hover:text-primary-light dark:hover:text-primary-dark bg-surface-light dark:bg-surface-dark hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors"
          :class="{ 'animate-spin': isRefreshing }"
        >
          <i class="ri-refresh-line text-lg"></i>
        </button>
        
        <button
          @click="openEditModal"
          class="p-2 text-text-light-secondary dark:text-text-dark-secondary hover:text-primary-light dark:hover:text-primary-dark bg-surface-light dark:bg-surface-dark hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors"
        >
          <i class="ri-edit-line text-lg"></i>
        </button>
      </div>
    </div>

    <!-- Feed Filter Edit Modal -->
    <FeedFilterEditModal
      :is-open="isEditModalOpen"
      @close="closeEditModal"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useFeedFilterStore } from '@/stores/feedFilterStore';
import { useFeedStore } from '@/stores/feedStore';
import FeedFilterEditModal from '@/components/modals/FeedFilterEditModal.vue';

const { t } = useI18n();
const router = useRouter();
const feedFilterStore = useFeedFilterStore();
const feedStore = useFeedStore();

const isEditModalOpen = ref(false);
const isRefreshing = ref(false);
const scrollContainer = ref(null);
const viewportWidth = ref(window.innerWidth);

// Update viewport width function
const updateViewportWidth = () => {
  viewportWidth.value = window.innerWidth;
};

const handleFilterClick = async (filterId) => {
  console.log('🎯 Filter clicked:', filterId);
  
  if (filterId === 'forYou') {
    console.log('🎯 Navigating to previews');
    router.push({ name: 'previews' });
  } else {
    console.log('🎯 Setting active filter:', filterId);
    await feedFilterStore.setActiveFilter(filterId);
    console.log('🎯 Active filter set, now refreshing feed');
    
    await refreshFeedForFilter(filterId);
    console.log('🎯 Feed refresh completed');
  }
};

const refreshFeedForFilter = async (filterId) => {
  console.log('🔄 refreshFeedForFilter called with:', filterId);
  
  try {
    feedStore.clearPosts();
    
    if (filterId === 'all') {
      console.log('🔄 Fetching regular feed');
      await feedStore.fetchFeed();
    } else if (filterId.startsWith('list_')) {
      const listId = filterId.replace('list_', '');
      console.log('🔄 Fetching filtered feed for list ID:', listId);
      await feedStore.fetchFilteredFeed(filterId);
    } else {
      console.log('🔄 Fetching regular feed (fallback)');
      await feedStore.fetchFeed();
    }
  } catch (error) {
    console.error('🔄 Error refreshing feed for filter:', error);
  }
};

const refreshFeed = async () => {
  isRefreshing.value = true;
  try {
    await refreshFeedForFilter(feedFilterStore.activeFilter);
  } catch (error) {
    console.error('Error refreshing feed:', error);
  } finally {
    isRefreshing.value = false;
  }
};

const openEditModal = () => {
  isEditModalOpen.value = true;
};

const closeEditModal = () => {
  isEditModalOpen.value = false;
};

const handleScroll = () => {
  // Simple scroll handler for future enhancements
};

onMounted(async () => {
  try {
    await feedFilterStore.fetchUserFilterPreferences();
    
    // Update viewport width on resize
    window.addEventListener('resize', updateViewportWidth);
    

  } catch (error) {
    console.error('Error loading feed filter preferences:', error);
  }
});

// Clean up event listener
import { onBeforeUnmount } from 'vue';
onBeforeUnmount(() => {
  window.removeEventListener('resize', updateViewportWidth);
});
</script>

<style scoped>
/* Hide scrollbar for webkit browsers */
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

/* Hide scrollbar for Firefox */
.scrollbar-hide {
  scrollbar-width: none;
  -ms-overflow-style: none;
}

/* Smooth scrolling */
.scrollbar-hide {
  -webkit-overflow-scrolling: touch;
  scroll-behavior: smooth;
  max-width: 100%;
  box-sizing: border-box;
}

/* Ensure proper flex behavior */
.flex-1 {
  min-width: 0;
  max-width: 100%;
}
</style>

