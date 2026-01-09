<template>
  <div 
    v-if="preview"
    class="flex-shrink-0 relative group"
    draggable="true"
    @dragstart="startDrag"
    @dragover.prevent
    @dragenter.prevent
    @drop="onDrop"
  >
    <div class="flex w-[75vw] max-w-2xl">
      <!-- Media container -->
      <div class="flex-1 h-48 relative">
        <img 
          v-if="isImage"
          :src="preview.url" 
          :alt="'Upload preview ' + preview.id" 
          class="w-full h-full object-cover rounded-l-lg border-2 border-r-0 border-primary-dark"
        />
        <video
          v-else
          :src="preview.url"
          class="w-full h-full object-cover rounded-l-lg border-2 border-r-0 border-primary-dark"
          controls
        ></video>
      </div>
      
      <!-- Right control panel -->
      <div class="w-18 bg-[#1a1b1f] border-2 border-l-0 border-primary-dark rounded-r-lg flex flex-col">
        <!-- Top controls -->
        <div class="p-2 flex flex-row justify-end gap-2">
          <button class="p-1.5 rounded-full hover:bg-surface-dark/80 text-text-dark-primary hover:text-primary-dark">
            <i class="ri-drag-move-line text-lg"></i>
          </button>
          <div class="relative">
            <button 
              @click="toggleSettings"
              class="p-1.5 rounded-full hover:bg-surface-dark/80 text-text-dark-primary hover:text-primary-dark"
            >
              <i class="ri-settings-3-line text-lg"></i>
            </button>
            
            <!-- Settings Popover -->
            <div 
              v-if="isSettingsOpen"
              class="absolute right-0 mt-2 w-32 bg-[#1a1b1f] rounded-lg shadow-lg border border-border-dark overflow-hidden z-10"
            >
              <button 
                @click="handleRemove"
                class="w-full px-4 py-2 flex items-center gap-2 text-sm text-text-dark-primary hover:bg-surface-dark/80"
              >
                <i class="ri-delete-bin-line"></i>
                Remove
              </button>
            </div>
          </div>
        </div>
        
        <!-- Preview controls -->
        <div class="flex-1 relative">
          <div v-if="preview.previewVersions && preview.previewVersions.length > 0" 
               class="absolute inset-0">
            <img 
              v-if="isImage"
              :src="preview.previewVersions[preview.previewVersions.length - 1].url" 
              alt="Preview version"
              class="w-full h-full object-cover"
            />
            <video
              v-else
              :src="preview.previewVersions[preview.previewVersions.length - 1].url"
              class="w-full h-full object-cover"
              controls
            ></video>
            <div class="absolute inset-0 bg-black/50 flex flex-col items-center justify-center gap-1">
              <span class="text-white text-sm">Preview</span>
              <button 
                v-if="isImage"
                @click="openEditor(preview.previewVersions[preview.previewVersions.length - 1])"
                class="text-white hover:text-primary-dark transition-colors"
              >
                <i class="ri-edit-line text-lg"></i>
              </button>
            </div>
          </div>
          <div v-else class="h-full flex flex-col items-center justify-center text-center p-2">
            <span class="text-text-dark-primary text-xs mb-2">Add Free Preview</span>
            <div class="relative">
              <button 
                @click="toggleFreePreviewMenu"
                class="p-1.5 rounded-full hover:bg-surface-dark/80 text-text-dark-primary hover:text-primary-dark"
              >
                <i class="ri-add-line text-xl"></i>
              </button>

              <!-- Free Preview Menu -->
              <div 
                v-if="isFreePreviewMenuOpen"
                class="absolute bottom-full mb-2 right-0 w-40 bg-[#1a1b1f] rounded-lg shadow-lg border border-border-dark overflow-hidden z-10"
              >
                <button 
                  v-if="isImage"
                  @click="handleClone"
                  class="w-full px-4 py-2 flex items-center gap-2 text-sm text-text-dark-primary hover:bg-surface-dark/80"
                >
                  <i class="ri-file-copy-line"></i>
                  Clone
                </button>
                <label 
                  class="w-full px-4 py-2 flex items-center gap-2 text-sm text-text-dark-primary hover:bg-surface-dark/80 cursor-pointer"
                >
                  <i class="ri-upload-2-line"></i>
                  Upload New
                  <input 
                    type="file" 
                    accept="image/*,video/*" 
                    class="hidden" 
                    @change="handleUploadNew"
                  >
                </label>
                <button 
                  @click="handleFromVault"
                  class="w-full px-4 py-2 flex items-center gap-2 text-sm text-text-dark-primary hover:bg-surface-dark/80"
                >
                  <i class="ri-download-line"></i>
                  From Vault
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Image Editor -->
    <ImageEditor
      v-if="isEditorOpen && isImage"
      :key="currentEditingVersion?.id || preview.url"
      :is-open="isEditorOpen"
      :image-url="currentEditingVersion?.url || preview.url"
      @close="closeEditor"
      @save="handleEditorSave"
    />
  </div>
</template>

<script setup>
import { ref, computed, onUnmounted, onMounted, watch } from 'vue';
import { useUploadStore } from '@/stores/uploadStore';
import ImageEditor from './ImageEditor.vue';

const props = defineProps({
  preview: {
    type: Object,
    required: true,
    validator: (value) => {
      return value && typeof value.url === 'string' && value.id !== undefined;
    }
  },
  index: {
    type: Number,
    required: true
  }
});

const emit = defineEmits(['dragStart', 'drop', 'dragMove', 'settings', 'addFreePreview', 'editor-saved']);
const isSettingsOpen = ref(false);
const isFreePreviewMenuOpen = ref(false);
const isEditorOpen = ref(false);
const currentEditingVersion = ref(null);
const uploadStore = useUploadStore();

const isImage = computed(() => {
  if (props.preview.file) {
    return props.preview.file.type.startsWith('image/');
  }
  // Fallback to checking URL if file object is not available
  const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
  const fileExtension = props.preview.url.split('.').pop().toLowerCase();
  return imageExtensions.includes(fileExtension);
});

const toggleSettings = (event) => {
  event.stopPropagation();
  isSettingsOpen.value = !isSettingsOpen.value;
};

const toggleFreePreviewMenu = (event) => {
  event.stopPropagation();
  isFreePreviewMenuOpen.value = !isFreePreviewMenuOpen.value;
  isSettingsOpen.value = false;
};

const startDrag = (event) => {
  if (!props.preview) return;
  emit('dragStart', { event, index: props.index });
};

const onDrop = (event) => {
  if (!props.preview) return;
  emit('drop', { event, index: props.index });
};

const handleRemove = () => {
  if (!props.preview?.id) return;
  uploadStore.removeMedia(props.preview.id);
  isSettingsOpen.value = false;
};

const handleClone = () => {
  if (!isImage.value) return;
  isEditorOpen.value = true;
  currentEditingVersion.value = null;
  isFreePreviewMenuOpen.value = false;
};

const closeEditor = () => {
  isEditorOpen.value = false;
  currentEditingVersion.value = null;
};

const handleEditorSave = async (imageData) => {
  try {
    let file;
    if (imageData instanceof Blob || imageData instanceof File) {
      file = imageData;
    } else {
      const response = await fetch(imageData);
      const blob = await response.blob();
      file = new File([blob], `edited-${props.preview.id}-${Date.now()}.png`, { type: 'image/png' });
    }
    
    uploadStore.addPreviewVersion(props.preview.id, file);
    closeEditor();
    if (currentEditingVersion.value && currentEditingVersion.value.url.startsWith('blob:')) {
      URL.revokeObjectURL(currentEditingVersion.value.url);
    }
    // Emit event to parent so it can react (e.g., focus modal, etc.)
    emit('editor-saved', { previewId: props.preview.id });
  } catch (error) {
    console.error('Error saving edited image:', error);
  }
};

const handleUploadNew = (event) => {
  const files = Array.from(event.target.files || []);
  if (files.length > 0) {
    const file = files[0];
    const isNewImage = file.type.startsWith('image/');
    if (isNewImage) {
      const imageUrl = URL.createObjectURL(file);
      currentEditingVersion.value = { url: imageUrl, file: file };
      isEditorOpen.value = true;
    } else {
      // For videos, pass the file directly without creating an object URL
      uploadStore.addPreviewVersion(props.preview.id, file);
    }
  }
  event.target.value = ''; // Reset input
  isFreePreviewMenuOpen.value = false;
};

const handleFromVault = () => {
  console.log('From vault clicked');
  // Implement vault functionality here
  isFreePreviewMenuOpen.value = false;
};

const openEditor = (version) => {
  console.log('[MediaPreviewItem] openEditor called', { version, stack: new Error().stack });
  if (!isEditorOpen.value) {
    isEditorOpen.value = true;
    currentEditingVersion.value = version;
  }
};

// Close menus when clicking outside
const handleClickOutside = (event) => {
  if (!event.target.closest('.relative')) {
    isSettingsOpen.value = false;
    isFreePreviewMenuOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});

watch(isEditorOpen, (newValue) => {
  if (newValue && isImage.value) {
    console.log('[MediaPreviewItem] ImageEditor opened');
  }
});
</script>

