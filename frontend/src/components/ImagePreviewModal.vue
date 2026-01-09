<template>
  <Teleport to="body">
    <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80">
      <div class="relative bg-white dark:bg-gray-900 rounded-lg shadow-lg max-w-lg w-full p-4 flex flex-col items-center">
        <button @click="$emit('close')" class="absolute top-2 right-2 text-gray-700 dark:text-gray-200 hover:text-primary-light dark:hover:text-primary-dark">
          <i class="ri-close-line text-2xl"></i>
        </button>
        <img :src="preview.url" :alt="'Preview ' + preview.id" class="max-h-[70vh] w-auto rounded-lg mb-4" />
        <div class="flex items-center gap-3 mt-2">
          <img v-if="preview.user && preview.user.avatar" :src="preview.user.avatar" :alt="preview.user.username" class="w-10 h-10 rounded-full border border-primary-light" />
          <div>
            <div class="font-semibold text-gray-900 dark:text-white">{{ preview.user?.username }}</div>
            <button v-if="preview.user" class="text-xs text-primary-light dark:text-primary-dark underline" @click="$router.push({ name: 'user-profile', params: { id: preview.user.id } })">
              View Profile
            </button>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
const props = defineProps({
  preview: {
    type: Object,
    required: true
  },
  isOpen: {
    type: Boolean,
    required: true
  }
})
</script> 