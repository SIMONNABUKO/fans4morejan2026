<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
    <div class="bg-white dark:bg-gray-900 rounded-lg shadow-lg p-6 w-full max-w-lg">
      <h2 class="text-lg font-semibold mb-4">Edit Post</h2>
      <textarea
        v-model="content"
        rows="5"
        class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 mb-4 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100"
        placeholder="Edit your post content..."
      ></textarea>
      <div class="flex justify-end gap-2">
        <button @click="$emit('close')" class="px-3 py-1 rounded bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition">Cancel</button>
        <button @click="submit" class="px-3 py-1 rounded bg-blue-500 text-white hover:bg-blue-600 transition">Save</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
const props = defineProps({
  post: { type: Object, required: true }
});
const emit = defineEmits(['close', 'updated']);

const content = ref('');

watch(
  () => props.post.content,
  (val) => {
    content.value = val || '';
  },
  { immediate: true }
);

function submit() {
  if (!content.value.trim()) return;
  emit('updated', { ...props.post, content: content.value });
  emit('close');
}
</script> 