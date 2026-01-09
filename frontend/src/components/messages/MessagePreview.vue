<template>
<div v-if="conversation && conversation.user">
  <button 
    class="w-full px-4 py-4 flex items-center gap-4 hover:bg-surface-light-hover dark:hover:bg-surface-dark-hover transition-colors group"
    @click="navigateToMessage"
  >
    <!-- Avatar -->
    <div class="relative">
      <div v-if="conversation.user.avatar" class="w-12 h-12 rounded-full overflow-hidden ring-2 ring-border-light dark:ring-border-dark">
        <img 
          :src="conversation.user.avatar" 
          :alt="conversation.user.username"
          class="w-full h-full object-cover"
        />
      </div>
      <div 
        v-else 
        class="w-12 h-12 rounded-full bg-surface-light dark:bg-surface-dark flex items-center justify-center ring-2 ring-border-light dark:ring-border-dark"
      >
        <i class="ri-user-3-line text-xl text-text-light-secondary dark:text-text-dark-secondary"></i>
      </div>
      <!-- Online indicator -->
      <span v-if="conversation.user.isOnline" class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-background-light dark:border-background-dark shadow-sm"></span>
    </div>

    <!-- Content -->
    <div class="flex-1 min-w-0">
      <div class="flex items-center gap-2 mb-1">
        <button 
          @click.stop="router.push(`/${conversation.user.username}/posts`)"
          class="font-semibold truncate text-text-light-primary dark:text-text-dark-primary hover:text-primary-light dark:hover:text-primary-dark transition-colors cursor-pointer"
        >
          {{ conversation.user.username }}
        </button>
        <i v-if="conversation.user.isSubscriber" class="ri-verified-badge-fill text-primary-light dark:text-primary-dark text-sm"></i>
        <button 
          @click.stop="router.push(`/${conversation.user.username}/posts`)"
          class="text-text-light-secondary dark:text-text-dark-secondary text-sm hover:text-primary-light dark:hover:text-primary-dark transition-colors cursor-pointer"
        >
          @{{ conversation.user.handle }}
        </button>
        <div class="relative ml-auto">
          <button 
            class="p-2 text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary hover:bg-surface-light dark:hover:bg-surface-dark rounded-full transition-colors block md:opacity-0 md:group-hover:opacity-100 touch-manipulation"
            @click.stop="showMenu = !showMenu"
            :aria-label="t('more_options')"
          >
            <i class="ri-more-fill text-sm"></i>
          </button>
          <UserActionsMenu 
            :is-open="showMenu"
            :user-id="conversation.user.id"
            @close="showMenu = false"
            @action="handleMenuAction"
            @show-list-modal="showListModal"
          />
        </div>
      </div>
      <p class="text-sm text-text-light-secondary dark:text-text-dark-secondary truncate leading-relaxed">
        {{ lastMessageContent }}
      </p>
    </div>

    <!-- Timestamp -->
    <div class="flex flex-col items-end gap-1">
      <span class="text-xs font-medium text-text-light-tertiary dark:text-text-dark-tertiary">
        {{ formattedTime }}
      </span>
      <!-- Unread indicator -->
      <span v-if="conversation.unreadCount > 0" class="w-2 h-2 bg-primary-light dark:bg-primary-dark rounded-full"></span>
    </div>
  </button>

  <!-- List Selection Modal -->
  <ListSelectionModal
    :is-open="showListSelectionModal"
    :user-id="conversation.user.id"
    @close="showListSelectionModal = false"
    @user-added="handleUserAddedToList"
  />
</div>
<div v-else class="w-full px-3 py-2 text-text-light-secondary dark:text-text-dark-secondary">
  {{ t('invalid_conversation_data') }}
</div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { format } from 'date-fns';
import UserActionsMenu from './UserActionsMenu.vue';
import ListSelectionModal from '../modals/ListSelectionModal.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const props = defineProps({
  conversation: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['user-blocked', 'user-added-to-list']);

const router = useRouter();
const showMenu = ref(false);
const showListSelectionModal = ref(false);

const lastMessageContent = computed(() => {
  return props.conversation.lastMessage?.content || t('no_messages_yet');
});

const formattedTime = computed(() => {
  if (props.conversation.lastMessage?.created_at) {
    return format(new Date(props.conversation.lastMessage.created_at), 'h:mm a');
  }
  return '';
});

const navigateToMessage = () => {
  if (props.conversation.user && props.conversation.user.id) {
    router.push(`/messages/${props.conversation.user.id}`);
  } else {
    console.error('Invalid user object:', props.conversation.user);
  }
};

const handleMenuAction = (action) => {
  console.log('Menu action:', action);
  
  // Handle block action - refresh conversations to remove blocked user
  if (action === 'block') {
    // Emit an event to parent to refresh conversations
    emit('user-blocked', conversation.user.id);
  }
};

const showListModal = (userId) => {
  showListSelectionModal.value = true;
};

const handleUserAddedToList = (listData) => {
  console.log('User added to list:', listData);
  emit('user-added-to-list', listData);
};
</script>

