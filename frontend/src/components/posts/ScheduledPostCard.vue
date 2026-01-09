<template>
  <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 flex flex-col gap-2 border border-gray-200 dark:border-gray-700">
    <PostGrid
      v-if="post.media && post.media.length > 0"
      :media="post.media"
      :author="post.user"
      :description="post.content"
      :user-has-permission="post.user_has_permission"
      :total-media-count="post.media.length"
      :required-permissions="post.required_permissions"
    />
    <div class="flex items-center justify-between mt-2">
      <div class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
        <span>
          Will send on {{ formattedDate }}
        </span>
        <button @click="openDateEdit" class="hover:text-blue-500" title="Edit scheduled date">
          <i class="ri-edit-line"></i>
        </button>
      </div>
      <button @click="confirmDelete" class="p-2 rounded hover:bg-red-50 dark:hover:bg-red-900" title="Delete post">
        <i class="ri-delete-bin-line text-red-500"></i>
      </button>
    </div>
    <div class="text-base text-gray-900 dark:text-gray-100 mb-2">
      {{ post.content }}
    </div>
    <div class="flex gap-2 mt-2">
      <button @click="openEdit" class="px-3 py-1 rounded bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
        Edit Post
      </button>
    </div>
    <div v-if="error" class="text-red-500 text-sm mt-2">{{ error }}</div>
    <div v-if="editLoading" class="text-blue-500 text-sm mt-2">Loading post...</div>
    <!-- Date Edit Modal -->
    <DateEditModal v-if="showDateEdit" :post="post" @close="showDateEdit = false" @updated="onDateUpdated" />
    <!-- Edit Post Modal -->
    <MobilePostModal 
      v-if="showEdit && editPostData" 
      :is-open="showEdit" 
      :post="editPostData" 
      :edit-mode="true" 
      @close="showEdit = false" 
      @updated="onPostUpdated" 
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import DateEditModal from './DateEditModal.vue';
import MobilePostModal from './MobilePostModal.vue';
import PostGrid from '@/components/user/PostGrid.vue';
import axiosInstance from '@/axios';
import { useMediaUploadStore } from '@/stores/mediaUploadStore';

const props = defineProps({
  post: { type: Object, required: true }
});

const emit = defineEmits(['deleted', 'updated']);

const showDateEdit = ref(false);
const showEdit = ref(false);
const isSaving = ref(false);
const error = ref(null);
const editLoading = ref(false);
const editPostData = ref(null);

const formattedDate = computed(() => {
  if (!props.post.scheduled_for) return '';
  return new Date(props.post.scheduled_for).toLocaleString(undefined, {
    weekday: 'long', year: 'numeric', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit'
  });
});

function openDateEdit() {
  showDateEdit.value = true;
}
async function openEdit() {
  editLoading.value = true;
  error.value = null;
  try {
    // Fetch the latest post data including all relationships
    const response = await axiosInstance.get(`/posts/${props.post.id}`);
    const postData = response.data.data || response.data;

    // Set the editPostData for the modal
    editPostData.value = postData;

    // Initialize the mediaUploadStore with the post data and correct context
    const mediaStore = useMediaUploadStore();
    const contextId = `edit-post-${postData.id}`;
    mediaStore.initializeFromPost(postData, contextId);
    mediaStore.setContext(contextId);

    // Open the edit modal
    showEdit.value = true;
  } catch (err) {
    error.value = 'Failed to load post data.';
    console.error(err);
  } finally {
    editLoading.value = false;
  }
}
function confirmDelete() {
  if (confirm('Are you sure you want to delete this scheduled post?')) {
    emit('deleted', props.post.id);
  }
}
function onDateUpdated(newDate) {
  emit('updated', { ...props.post, scheduled_for: newDate });
  showDateEdit.value = false;
}
async function onPostUpdated(updatedPost) {
  isSaving.value = true;
  error.value = null;
  try {
    const mediaStore = useMediaUploadStore();
    const response = await mediaStore.submitPost();
    emit('updated', response.data || response);
    showEdit.value = false;
  } catch (err) {
    error.value = 'Failed to update post.';
    console.error(err);
  } finally {
    isSaving.value = false;
  }
}
</script> 