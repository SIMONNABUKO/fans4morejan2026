<template>
  <TransitionRoot as="template" :show="isOpen">
    <Dialog as="div" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm" @close="emit('close')">
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <DialogOverlay class="fixed inset-0 bg-black/60 backdrop-blur-sm" />
      </TransitionChild>
      
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0 scale-95 translate-y-4"
        enter-to="opacity-100 scale-100 translate-y-0"
        leave="ease-in duration-200"
        leave-from="opacity-100 scale-100 translate-y-0"
        leave-to="opacity-0 scale-95 translate-y-4"
      >
        <div class="relative w-full h-full max-w-none mx-auto bg-white/90 dark:bg-gray-900/90 backdrop-blur-xl border border-white/20 dark:border-gray-700/50 shadow-2xl flex flex-col animate-scaleIn">
          <!-- Enhanced Close Button -->
          <button 
            @click="emit('close')" 
            class="absolute top-4 right-4 p-2 rounded-full bg-gray-100/80 dark:bg-gray-700/80 hover:bg-gray-200/80 dark:hover:bg-gray-600/80 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all duration-200 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-blue-500/50"
          >
            <span class="sr-only">Close</span>
            <i class="ri-close-line text-2xl"></i>
          </button>
          
          <div class="flex-1 flex flex-col p-0">
            <!-- Enhanced Header -->
            <div class="px-8 pt-8 pb-6 border-b border-white/20 dark:border-gray-700/50">
              <h2 class="text-2xl font-bold text-left text-gray-900 dark:text-white">Reply to Post</h2>
              <p class="text-gray-500 dark:text-gray-400 mt-2">Share your thoughts with the community</p>
            </div>
            
            <!-- Enhanced Comment Section -->
            <div class="px-8 pt-6 flex items-center gap-4">
              <div class="relative">
                <div class="w-14 h-14 rounded-full overflow-hidden ring-4 ring-white/20 dark:ring-gray-700/30 shadow-lg">
                  <img 
                    :src="currentUser.avatar" 
                    :alt="currentUser.username" 
                    class="w-full h-full object-cover transition-transform duration-200 hover:scale-110"
                  />
                </div>
                <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-gradient-to-r from-green-400 to-emerald-500 rounded-full border-3 border-white dark:border-gray-900 shadow-lg"></div>
              </div>
              <div class="flex-1">
                <span class="font-bold text-lg text-gray-900 dark:text-white">{{ currentUser.username }}</span>
                <p class="text-sm text-gray-500 dark:text-gray-400">Replying to this post</p>
              </div>
            </div>
            
            <div class="px-8 pt-4">
              <textarea 
                v-if="userHasPermission" 
                v-model="comment" 
                rows="4" 
                class="w-full p-4 border border-white/20 dark:border-gray-700/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 mt-2 resize-none transition-all duration-200 focus:bg-white/80 dark:focus:bg-gray-800/80" 
                placeholder="Write your comment..." 
                :disabled="commentStore.loading[post.id]"
              ></textarea>
              
              <!-- Enhanced Action Icons -->
              <div v-if="userHasPermission" class="flex items-center justify-between mt-6">
                <div class="flex items-center gap-4">
                  <button 
                    @click.prevent 
                    title="Attach Media" 
                    class="p-3 rounded-xl bg-gray-100/60 dark:bg-gray-700/60 hover:bg-gray-200/60 dark:hover:bg-gray-600/60 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all duration-200 hover:scale-110"
                  >
                    <i class="ri-image-2-line text-xl"></i>
                  </button>
                  <button 
                    @click.prevent 
                    title="Schedule Comment Date" 
                    class="p-3 rounded-xl bg-gray-100/60 dark:bg-gray-700/60 hover:bg-gray-200/60 dark:hover:bg-gray-600/60 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all duration-200 hover:scale-110"
                  >
                    <i class="ri-calendar-line text-xl"></i>
                  </button>
                  <button 
                    @click.prevent 
                    title="Set Delete/Hide Date" 
                    class="p-3 rounded-xl bg-gray-100/60 dark:bg-gray-700/60 hover:bg-gray-200/60 dark:hover:bg-gray-600/60 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all duration-200 hover:scale-110"
                  >
                    <i class="ri-calendar-close-line text-xl"></i>
                  </button>
                </div>
                
                <button 
                  @click="submitComment" 
                  :disabled="commentStore.loading[post.id] || !comment.trim()" 
                  class="px-6 py-3 bg-primary-light dark:bg-primary-dark hover:bg-primary-light/90 dark:hover:bg-primary-dark/90 text-white rounded-xl disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 font-semibold transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl"
                >
                  <div v-if="commentStore.loading[post.id]" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                  <span v-else>Post Comment</span>
                </button>
              </div>
            </div>
            
            <!-- Enhanced Restriction/Permissions Message -->
            <div v-if="!userHasPermission" class="text-center py-12 px-8">
              <div class="mb-6">
                                  <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-primary-light dark:bg-primary-dark flex items-center justify-center">
                    <i class="ri-lock-line text-white text-2xl"></i>
                  </div>
                <div class="font-bold text-xl text-gray-900 dark:text-white mb-2">Comments Restricted</div>
                <div class="text-gray-500 dark:text-gray-400">This post has replies restricted to specific permissions</div>
              </div>
              
              <div v-if="post.allowed_permissions && post.allowed_permissions.length" class="flex flex-col items-center gap-3 mt-6">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Required permissions:</span>
                <div class="flex flex-wrap justify-center gap-2">
                  <span 
                    v-for="perm in post.allowed_permissions" 
                    :key="perm" 
                    class="inline-block bg-gradient-to-r from-blue-500 to-indigo-600 text-white px-4 py-2 rounded-full text-sm font-semibold shadow-lg"
                  >
                    {{ perm }}
                  </span>
                </div>
              </div>
              <div v-else class="text-sm mt-4 text-gray-500 dark:text-gray-400">
                You must be subscribed or have permission to comment on this post.
              </div>
            </div>
          </div>
        </div>
      </TransitionChild>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Dialog, DialogOverlay, TransitionRoot, TransitionChild } from '@headlessui/vue'
import { defineProps, defineEmits } from 'vue'
import { useCommentStore } from '@/stores/commentStore'
import { useToast } from 'vue-toastification'

const props = defineProps({
  isOpen: Boolean,
  post: Object,
  userHasPermission: Boolean,
  currentUser: {
    type: Object,
    required: true
  }
})
const emit = defineEmits(['close', 'commentSubmitted'])

const comment = ref('')
const commentStore = useCommentStore()
const toast = useToast ? useToast() : null

watch(() => props.isOpen, (val) => {
  if (!val) comment.value = ''
})

const submitComment = async () => {
  if (!comment.value.trim()) return
  try {
    const res = await commentStore.addComment(props.post.id, comment.value)
    if (res.success) {
      comment.value = ''
      if (toast) toast.success(res.message)
      else alert(res.message)
      emit('commentSubmitted', res.comment)
      emit('close')
    } else {
      if (toast) toast.error(res.error)
      else alert(res.error)
    }
  } catch (e) {
    if (toast) toast.error('Failed to add comment')
    else alert('Failed to add comment')
  }
}
</script>

<style scoped>
/* Enhanced animations */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideInFromBottom {
  from {
    opacity: 0;
    transform: translateY(100%);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes scaleIn {
  from {
    opacity: 0;
    transform: scale(0.9);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
}

/* Apply animations to elements */
.animate-fadeInUp {
  animation: fadeInUp 0.6s ease-out;
}

.animate-slideInFromBottom {
  animation: slideInFromBottom 0.5s ease-out;
}

.animate-scaleIn {
  animation: scaleIn 0.4s ease-out;
}

.animate-pulse {
  animation: pulse 2s infinite;
}

/* Enhanced focus states */
button:focus-visible,
textarea:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
  border-radius: 0.5rem;
}

/* Smooth transitions for all interactive elements */
* {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Enhanced scrollbar styling */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.5);
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: rgba(156, 163, 175, 0.7);
}

/* Dark mode scrollbar */
.dark ::-webkit-scrollbar-thumb {
  background: rgba(75, 85, 99, 0.5);
}

.dark ::-webkit-scrollbar-thumb:hover {
  background: rgba(75, 85, 99, 0.7);
}

/* Glassmorphism effects */
.glass {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.dark .glass {
  background: rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

/* Enhanced button hover effects */
.btn-hover {
  position: relative;
  overflow: hidden;
}

.btn-hover::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.btn-hover:hover::before {
  left: 100%;
}

/* Enhanced loading spinner */
.spinner {
  border: 2px solid rgba(156, 163, 175, 0.3);
  border-top: 2px solid #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Enhanced tooltip */
.tooltip {
  position: relative;
}

.tooltip::before {
  content: attr(data-tooltip);
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(0, 0, 0, 0.9);
  color: white;
  padding: 0.5rem 0.75rem;
  border-radius: 0.5rem;
  font-size: 0.75rem;
  white-space: nowrap;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.2s;
  z-index: 10;
}

.tooltip:hover::before {
  opacity: 1;
}

/* Enhanced focus ring for accessibility */
.focus-ring:focus {
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
}

/* Enhanced gradient text */
.gradient-text {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Enhanced shadow effects */
.shadow-soft {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.shadow-medium {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.shadow-strong {
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Enhanced modal card hover effects */
.modal-card {
  position: relative;
  overflow: hidden;
}

.modal-card::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
  opacity: 0;
  transition: opacity 0.3s;
}

.modal-card:hover::after {
  opacity: 1;
}

/* Enhanced verified badge */
.verified-badge {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: white;
  font-weight: 600;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

/* Enhanced action buttons */
.action-button {
  position: relative;
  overflow: hidden;
}

.action-button::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.action-button:hover::before {
  left: 100%;
}

/* Enhanced disabled state */
.disabled-button {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none !important;
}

.disabled-button:hover {
  transform: none !important;
  box-shadow: none !important;
}

/* Enhanced textarea styling */
.modern-textarea {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  transition: all 0.3s ease;
}

.modern-textarea:focus {
  background: rgba(255, 255, 255, 0.2);
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.dark .modern-textarea {
  background: rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.dark .modern-textarea:focus {
  background: rgba(0, 0, 0, 0.2);
  border-color: #3b82f6;
}

/* Enhanced permission badges */
.permission-badge {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: white;
  font-weight: 600;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.permission-badge:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.2);
}

/* Enhanced close button */
.close-button {
  position: relative;
  overflow: hidden;
}

.close-button::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.close-button:hover::before {
  left: 100%;
}

/* Enhanced post button */
.post-button {
  position: relative;
  overflow: hidden;
}

.post-button::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.post-button:hover::before {
  left: 100%;
}

/* Enhanced restriction icon */
.restriction-icon {
  background: linear-gradient(135deg, #f97316, #dc2626);
  box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.2);
  animation: pulse 2s infinite;
}

/* Enhanced user avatar */
.user-avatar {
  position: relative;
  overflow: hidden;
}

.user-avatar::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
  opacity: 0;
  transition: opacity 0.3s;
}

.user-avatar:hover::after {
  opacity: 1;
}

/* Enhanced action icons */
.action-icon {
  position: relative;
  overflow: hidden;
}

.action-icon::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.action-icon:hover::before {
  left: 100%;
}

/* Enhanced modal backdrop */
.modal-backdrop {
  backdrop-filter: blur(8px);
  background: rgba(0, 0, 0, 0.6);
}

/* Enhanced modal content */
.modal-content {
  backdrop-filter: blur(20px);
  background: rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.dark .modal-content {
  background: rgba(0, 0, 0, 0.9);
  border: 1px solid rgba(255, 255, 255, 0.1);
}
</style> 