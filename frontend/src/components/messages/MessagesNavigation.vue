<template>
  <div class="flex items-center justify-between gap-2">
    <!-- Scrollable Filter Buttons Container -->
    <div class="flex-1 min-w-0 relative overflow-hidden">
      <!-- Gradient overlay to indicate scrollability on the right -->
      <div class="absolute right-0 top-0 bottom-2 w-8 bg-gradient-to-l from-white via-white/80 dark:from-gray-900 dark:via-gray-900/80 to-transparent pointer-events-none z-10"></div>
      
      <div class="flex items-center gap-2 sm:gap-3 overflow-x-auto scrollbar-hide mobile-scroll pb-2 pr-10">
        <button
          v-for="filter in messagesFilterStore.enabledFilters"
          :key="filter.id"
          :class="[
            'px-3 py-1.5 sm:px-4 sm:py-2 rounded-full transition-colors whitespace-nowrap flex-shrink-0 text-sm sm:text-base touch-friendly',
            messagesFilterStore.activeFilter === filter.id
              ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-medium'
              : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'
          ]"
          @click="handleFilterClick(filter.id)"
        >
          {{ t(filter.label) }}
        </button>
      </div>
    </div>
    
    <!-- Action Buttons (Fixed on the right) -->
    <div class="flex items-center gap-1 flex-shrink-0">
      <button
        @click="refreshConversations"
        class="p-1.5 sm:p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full transition-colors touch-friendly"
        :class="{ 'animate-spin': isRefreshing }"
      >
        <i class="ri-refresh-line text-lg sm:text-xl"></i>
      </button>
      <button
        @click="openEditModal"
        class="p-1.5 sm:p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full transition-colors touch-friendly"
      >
        <i class="ri-edit-line text-lg sm:text-xl"></i>
      </button>
    </div>

    <!-- Messages Filter Edit Modal -->
    <MessagesFilterEditModal
      :is-open="isEditModalOpen"
      @close="closeEditModal"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useMessagesFilterStore } from '@/stores/messagesFilterStore';
import MessagesFilterEditModal from '@/components/modals/MessagesFilterEditModal.vue';

const emit = defineEmits(['refresh', 'filter-change']);

const { t } = useI18n();
const messagesFilterStore = useMessagesFilterStore();

const isEditModalOpen = ref(false);
const isRefreshing = ref(false);

const handleFilterClick = async (filterId) => {
  console.log('🎯 Messages Filter clicked:', filterId);
  console.log('🎯 Filter type:', typeof filterId);
  
  console.log('🎯 Setting active messages filter:', filterId);
  await messagesFilterStore.setActiveFilter(filterId);
  console.log('🎯 Active messages filter set, emitting filter-change');
  
  // Emit filter change event to parent component
  emit('filter-change', filterId);
};

const refreshConversations = async () => {
  isRefreshing.value = true;
  try {
    emit('refresh');
  } finally {
    setTimeout(() => {
      isRefreshing.value = false;
    }, 1000);
  }
};

const openEditModal = () => {
  isEditModalOpen.value = true;
};

const closeEditModal = () => {
  isEditModalOpen.value = false;
};
</script> 