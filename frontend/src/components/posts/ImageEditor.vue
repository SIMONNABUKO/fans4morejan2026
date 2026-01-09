<template>
  <TransitionRoot appear :show="isOpen" as="template">
    <Dialog as="div" @close="handleClose" class="relative z-50">
      <TransitionChild
        as="template"
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/80" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
          <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="duration-200 ease-in"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <DialogPanel class="w-full max-w-5xl transform overflow-hidden rounded-2xl bg-background-light dark:bg-background-dark shadow-xl transition-all">
              <div class="flex flex-col h-screen">
                <DialogTitle class="text-lg font-medium p-4 bg-surface-light dark:bg-surface-dark text-text-light-primary dark:text-text-dark-primary">
                  Edit Media
                </DialogTitle>

                <!-- Toolbar -->
                <div class="flex gap-6 p-4 bg-surface-light dark:bg-surface-dark justify-center">
                  <div v-for="tool in tools" 
                       :key="tool.name"
                       class="flex flex-col items-center gap-1"
                  >
                    <button 
                      @click="selectTool(tool.name)"
                      :class="[
                        'p-2 rounded-lg transition-colors',
                        currentTool === tool.name ? 'bg-surface-light-hover dark:bg-surface-dark-hover' : 'hover:bg-surface-light-hover dark:hover:bg-surface-dark-hover'
                      ]"
                    >
                      <i :class="tool.icon" class="text-text-light-primary dark:text-text-dark-primary text-xl"></i>
                    </button>
                    <span class="text-text-light-secondary dark:text-text-dark-secondary text-xs">{{ tool.label }}</span>
                  </div>
                </div>

                <!-- Canvas Container -->
                <div class="relative flex-grow overflow-hidden flex items-center justify-center bg-background-light dark:bg-background-dark" @click="hideTextTool">
                  <div class="relative">
                    <canvas 
                      ref="canvas"
                      class="max-w-full max-h-full"
                      @mousedown="startDrawing"
                      @mousemove="draw"
                      @mouseup="stopDrawing"
                      @mouseleave="stopDrawing"
                      @touchstart="startDrawing"
                      @touchmove="draw"
                      @touchend="stopDrawing"
                    ></canvas>

                    <!-- Blur Frame -->
                    <div
                      v-if="showBlurFrame"
                      ref="blurFrame"
                      class="absolute border-2 border-text-dark-primary cursor-move"
                      :style="{
                        left: `${framePosition.x}px`,
                        top: `${framePosition.y}px`,
                        width: `${frameSize.width}px`,
                        height: `${frameSize.height}px`,
                        cursor: isDragging ? 'grabbing' : 'grab'
                      }"
                      @mousedown.prevent="startFrameDrag"
                      @touchstart.prevent="startFrameDrag"
                    >
                      <!-- Resize Handles -->
                      <template v-for="handle in resizeHandles" :key="handle.position">
                        <div
                          class="absolute w-3 h-3 bg-text-dark-primary rounded-full"
                          :style="{
                            ...handle.style,
                            cursor: handle.cursor
                          }"
                          @mousedown.stop.prevent="startResize(handle.position, $event)"
                          @touchstart.stop.prevent="startResize(handle.position, $event)"
                        ></div>
                      </template>
                    </div>

                    <!-- Text Frames -->
                    <div v-for="(textItem, index) in textItems" :key="index">
                      <div
                        class="absolute border-2 border-text-dark-primary cursor-move"
                        :style="{
                          left: `${textItem.x}px`,
                          top: `${textItem.y}px`,
                          cursor: textItem.isDragging ? 'grabbing' : 'grab'
                        }"
                        @mousedown.prevent="startTextDrag(index, $event)"
                        @touchstart.prevent="handleTextTouchStart(index, $event)"
                      >
                        <div class="p-2 bg-background-dark bg-opacity-50 text-text-dark-primary">
                          {{ textItem.text }}
                        </div>
                        <button
                          @click.stop="editText(index)"
                          @touchend.prevent="editText(index)"
                          class="absolute top-0 right-0 -mt-2 -mr-2 bg-text-dark-primary text-text-light-primary rounded-full p-1"
                        >
                          <i class="ri-edit-line"></i>
                        </button>
                        <button
                          @click.stop="deleteText(index)"
                          @touchend.prevent="deleteText(index)"
                          class="absolute top-0 right-6 -mt-2 -mr-2 bg-text-dark-primary text-text-light-primary rounded-full p-1"
                        >
                          <i class="ri-delete-bin-line"></i>
                        </button>
                      </div>
                    </div>

                    <!-- Emoji Frames -->
                    <div
                      v-for="(emojiItem, index) in emojiItems"
                      :key="index"
                      class="absolute cursor-move group"
                      :style="{
                        left: `${emojiItem.x}px`,
                        top: `${emojiItem.y}px`,
                        fontSize: `${emojiItem.size}px`,
                      }"
                      @mousedown.prevent="startEmojiDrag(index, $event)"
                      @touchstart.prevent="startEmojiDrag(index, $event)"
                    >
                      <div class="relative inline-block">
                        <span>{{ emojiItem.emoji }}</span>
                        <div class="absolute -top-8 left-1/2 -translate-x-1/2 flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                          <button
                            @click.stop="deleteEmoji(index)"
                            @touchend.prevent="deleteEmoji(index)"
                            class="bg-background-dark/50 text-text-dark-primary rounded-full p-1 text-xs"
                          >
                            <i class="ri-close-line"></i>
                          </button>
                          <i class="ri-drag-move-fill text-text-dark-primary text-sm bg-background-dark/50 rounded-full p-1"></i>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Tool Options -->
                  <div 
                    v-if="showToolOptions"
                    class="absolute top-4 right-4 bg-surface-light dark:bg-surface-dark rounded-lg shadow-lg p-2"
                    @click.stop
                  >
                    <div v-if="currentTool === 'text'" class="space-y-2">
                      <input 
                        type="text"
                        v-model="textInput"
                        placeholder="Enter text..."
                        class="w-full px-2 py-1 bg-background-light dark:bg-background-dark text-text-light-primary dark:text-text-dark-primary rounded border border-border-light dark:border-border-dark"
                      />
                      <input 
                        type="color"
                        v-model="textColor"
                        class="block w-full h-8"
                      />
                      <input 
                        type="range"
                        v-model="textSize"
                        min="12"
                        max="72"
                        class="w-full"
                      />
                      <button 
                        @click="addText"
                        class="w-full px-4 py-2 bg-primary-light dark:bg-primary-dark text-text-light-primary dark:text-text-light-primary rounded hover:bg-primary-light-hover dark:hover:bg-primary-dark-hover transition-colors"
                      >
                        Add Text
                      </button>
                    </div>
                    <div v-if="currentTool === 'emoji'">
                      <EmojiPicker @select="addEmoji" />
                    </div>
                  </div>
                  
                  <!-- Apply Button -->
                  <button 
                    @click="applyChanges"
                    class="absolute bottom-4 left-1/2 transform -translate-x-1/2 px-4 py-2 bg-primary-light dark:bg-primary-dark text-text-light-primary dark:text-text-light-primary rounded hover:bg-primary-light-hover dark:hover:bg-primary-dark-hover transition-colors"
                  >
                    Apply Changes
                  </button>
                  
                </div>

                <!-- Blur Intensity Slider -->
                <div 
                  v-if="currentTool.includes('blur')" 
                  class="p-4 bg-surface-light dark:bg-surface-dark"
                >
                  <div class="relative w-full flex items-center justify-center h-12">
                    <div class="w-full h-1 bg-text-dark-primary rounded-full">
                      <div 
                        class="absolute top-0 left-0 h-1 bg-primary-light dark:bg-primary-dark rounded-full" 
                        :style="{ width: `${(blurRadius / 20) * 100}%` }"
                      ></div>
                      <div 
                        class="absolute top-1/2 -translate-y-1/2 w-6 h-6 bg-primary-light dark:bg-primary-dark rounded-full shadow-md cursor-pointer"
                        :style="{ left: `calc(${(blurRadius / 20) * 100}% - 12px)` }"
                        @mousedown="startSliderDrag"
                        @touchstart="startSliderDrag"
                      ></div>
                    </div>
                  </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3 p-4 bg-surface-light dark:bg-surface-dark border-t border-border-light dark:border-border-dark">
                  <button 
                    @click="handleClose"
                    class="px-4 py-2 text-text-light-primary dark:text-text-dark-primary hover:text-text-light-primary/80 dark:hover:text-text-dark-primary/80 transition-colors"
                  >
                    Discard
                  </button>
                  <button 
                    @click="handleSave"
                    class="px-4 py-2 bg-primary-light dark:bg-primary-dark text-white rounded hover:bg-primary-light-hover dark:hover:bg-primary-dark-hover transition-colors"
                  >
                    Save Changes
                  </button>
                </div>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, onMounted, watch, nextTick, onUnmounted } from 'vue'
import EmojiPicker from '../EmojiPicker.vue'
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'

const props = defineProps({
  isOpen: Boolean,
  imageUrl: {
    type: String,
    required: true
  }
})

const emit = defineEmits(['close', 'save'])

const canvas = ref(null)
const ctx = ref(null)
const originalImage = ref(null)
const isDrawing = ref(false)
const currentTool = ref('')
const showToolOptions = ref(false)

// Tool-specific states
const textInput = ref('')
const textColor = ref('#ffffff')
const textSize = ref(24)
const blurRadius = ref(4) // Default to 20% (4 on 0-20 scale)
const pixelSize = ref(8)

// Blur frame states
const showBlurFrame = ref(false)
const isDragging = ref(false)
const isResizing = ref(false)
const currentResizeHandle = ref(null)
const blurFrame = ref(null)

const framePosition = ref({ x: 0, y: 0 })
const frameSize = ref({ width: 200, height: 200 })
const dragStart = ref({ x: 0, y: 0 })

const tools = [
  { name: 'text', label: 'Text', icon: 'ri-text' },
  { name: 'blur', label: 'Blur', icon: 'ri-drop-line' },
  { name: 'blur2', label: 'Blur 2', icon: 'ri-drop-line' },
  { name: 'pixelate', label: 'Pixelate', icon: 'ri-drop-line' },
  { name: 'emoji', label: 'Emoji', icon: 'ri-emotion-line' }
]

const resizeHandles = [
  { position: 'nw', style: { top: '-6px', left: '-6px' }, cursor: 'nw-resize' },
  { position: 'n', style: { top: '-6px', left: '50%', transform: 'translateX(-50%)' }, cursor: 'n-resize' },
  { position: 'ne', style: { top: '-6px', right: '-6px' }, cursor: 'ne-resize' },
  { position: 'w', style: { top: '50%', left: '-6px', transform: 'translateY(-50%)' }, cursor: 'w-resize' },
  { position: 'e', style: { top: '50%', right: '-6px', transform: 'translateY(-50%)' }, cursor: 'e-resize' },
  { position: 'sw', style: { bottom: '-6px', left: '-6px' }, cursor: 'sw-resize' },
  { position: 's', style: { bottom: '-6px', left: '50%', transform: 'translateX(-50%)' }, cursor: 's-resize' },
  { position: 'se', style: { bottom: '-6px', right: '-6px' }, cursor: 'se-resize' }
]

// Mouse event handlers for document
const onMouseMove = (event) => {
  const clientX = event.clientX || (event.touches && event.touches[0].clientX)
  const clientY = event.clientY || (event.touches && event.touches[0].clientY)
  if (isDragging.value) {
    dragFrame({ clientX, clientY })
  } else if (isResizing.value) {
    resize({ clientX, clientY })
  } else if (activeTextIndex.value !== null) {
    dragText({ clientX, clientY })
  } else if (activeEmojiIndex.value !== null) {
    dragEmoji({ clientX, clientY })
  }
}

const onMouseUp = () => {
  if (isDragging.value || isResizing.value) {
    stopFrameDrag()
    stopResize()
  }
  stopTextDrag()
  stopEmojiDrag()
}

onMounted(() => {
  document.addEventListener('mousemove', onMouseMove)
  document.addEventListener('mouseup', onMouseUp)
  document.addEventListener('touchmove', onMouseMove, { passive: false })
  document.addEventListener('touchend', onMouseUp)
  
  if (props.isOpen) {
    nextTick(() => initCanvas())
  }
})

onUnmounted(() => {
  document.removeEventListener('mousemove', onMouseMove)
  document.removeEventListener('mouseup', onMouseUp)
  document.removeEventListener('touchmove', onMouseMove)
  document.removeEventListener('touchend', onMouseUp)
})

const initCanvas = async () => {
  if (!canvas.value) {
    console.error('Canvas element not found')
    return
  }

  ctx.value = canvas.value.getContext('2d')

  try {
    originalImage.value = new Image()
    originalImage.value.crossOrigin = 'anonymous'
    
    await new Promise((resolve, reject) => {
      originalImage.value.onload = resolve
      originalImage.value.onerror = reject
      originalImage.value.src = props.imageUrl
    })

    let width = originalImage.value.width
    let height = originalImage.value.height

    width = Math.max(1, Math.floor(width))
    height = Math.max(1, Math.floor(height))

    const maxWidth = window.innerWidth * 0.9
    const maxHeight = window.innerHeight * 0.7

    if (width > maxWidth || height > maxHeight) {
      const ratio = Math.min(maxWidth / width, maxHeight / height)
      width = Math.floor(width * ratio)
      height = Math.floor(height * ratio)
    }

    if (width <= 0 || height <= 0) {
      console.error('Invalid image dimensions:', width, height)
      return
    }

    canvas.value.width = width
    canvas.value.height = height

    // Initialize frame position at center
    const defaultSize = Math.min(200, width / 2, height / 2)
    frameSize.value = { width: defaultSize, height: defaultSize }
    framePosition.value = {
      x: (width - defaultSize) / 2,
      y: (height - defaultSize) / 2
    }

    // Draw original image
    ctx.value.clearRect(0, 0, width, height)
    ctx.value.drawImage(originalImage.value, 0, 0, width, height)

    // No tool selected by default

  } catch (error) {
    console.error('Error loading image:', error)
  }
}

const selectTool = (tool) => {
  if (!canvas.value || canvas.value.width === 0 || canvas.value.height === 0) {
    console.error('Canvas is not properly initialized')
    return
  }
  
  // Apply current changes before switching tools
  if (currentTool.value) {
    applyChanges()
  }
  
  currentTool.value = tool
  showToolOptions.value = ['text', 'blur', 'blur2', 'pixelate', 'emoji'].includes(tool)
  showBlurFrame.value = tool === 'blur' || tool === 'blur2' || tool === 'pixelate'

  const canvasWidth = canvas.value.width
  const canvasHeight = canvas.value.height

  if (tool === 'blur2') {
    frameSize.value = { width: canvasWidth, height: canvasHeight }
    framePosition.value = { x: 0, y: 0 }
  } else if (tool === 'blur' || tool === 'pixelate') {
    const defaultSize = Math.min(200, canvasWidth / 2, canvasHeight / 2)
    frameSize.value = { 
      width: Math.max(1, defaultSize), 
      height: Math.max(1, defaultSize) 
    }
    framePosition.value = {
      x: (canvasWidth - frameSize.value.width) / 2,
      y: (canvasHeight - frameSize.value.height) / 2
    }
  }

  // Ensure frame size is never zero
  frameSize.value.width = Math.max(1, frameSize.value.width)
  frameSize.value.height = Math.max(1, frameSize.value.height)

  // Reset canvas to the current saved state
  ctx.value.clearRect(0, 0, canvasWidth, canvasHeight)
  ctx.value.drawImage(originalImage.value, 0, 0, canvasWidth, canvasHeight)

  // Apply effect immediately when selecting blur or pixelate
  if (['blur', 'blur2', 'pixelate'].includes(tool)) {
    nextTick(() => applyEffect())
  } else if (tool === '') {
    // If no tool is selected, just draw the original image
    ctx.value.clearRect(0, 0, canvas.value.width, canvas.value.height)
    ctx.value.drawImage(originalImage.value, 0, 0, canvas.value.width, canvas.value.height)
  }
}

const startFrameDrag = (event) => {
  isDragging.value = true
  const rect = canvas.value.getBoundingClientRect()
  const clientX = event.clientX || (event.touches && event.touches[0].clientX)
  const clientY = event.clientY || (event.touches && event.touches[0].clientY)
  dragStart.value = {
    x: clientX - rect.left - framePosition.value.x,
    y: clientY - rect.top - framePosition.value.y
  }
}

const dragFrame = (event) => {
  if (!isDragging.value) return

  const rect = canvas.value.getBoundingClientRect()
  const clientX = event.clientX || (event.touches && event.touches[0].clientX)
  const clientY = event.clientY || (event.touches && event.touches[0].clientY)
  const x = clientX - rect.left - dragStart.value.x
  const y = clientY - rect.top - dragStart.value.y

  // Constrain to canvas bounds
  framePosition.value = {
    x: Math.max(0, Math.min(canvas.value.width - frameSize.value.width, x)),
    y: Math.max(0, Math.min(canvas.value.height - frameSize.value.height, y))
  }
  applyEffect()
}

const stopFrameDrag = () => {
  if (isDragging.value) {
    isDragging.value = false
    applyEffect()
  }
}

const startResize = (handle, event) => {
  event.stopPropagation()
  isResizing.value = true
  currentResizeHandle.value = handle
  const rect = canvas.value.getBoundingClientRect()
  const clientX = event.clientX || (event.touches && event.touches[0].clientX)
  const clientY = event.clientY || (event.touches && event.touches[0].clientY)
  dragStart.value = {
    x: clientX - rect.left,
    y: clientY - rect.top,
    width: frameSize.value.width,
    height: frameSize.value.height,
    left: framePosition.value.x,
    top: framePosition.value.y
  }
}

const resize = (event) => {
  if (!isResizing.value) return

  const rect = canvas.value.getBoundingClientRect()
  const clientX = event.clientX || (event.touches && event.touches[0].clientX)
  const clientY = event.clientY || (event.touches && event.touches[0].clientY)
  const x = clientX - rect.left
  const y = clientY - rect.top
  const dx = x - dragStart.value.x
  const dy = y - dragStart.value.y

  const handle = currentResizeHandle.value
  const newSize = { ...frameSize.value }
  const newPosition = { ...framePosition.value }

  if (handle.includes('w')) {
    newSize.width = Math.max(50, dragStart.value.width - dx)
    newPosition.x = dragStart.value.left + dx
  }
  if (handle.includes('e')) {
    newSize.width = Math.max(50, dragStart.value.width + dx)
  }
  if (handle.includes('n')) {
    newSize.height = Math.max(50, dragStart.value.height - dy)
    newPosition.y = dragStart.value.top + dy
  }
  if (handle.includes('s')) {
    newSize.height = Math.max(50, dragStart.value.height + dy)
  }

  // Constrain to canvas bounds
  if (newPosition.x >= 0 && newPosition.x + newSize.width <= canvas.value.width) {
    frameSize.value.width = newSize.width
    framePosition.value.x = newPosition.x
  }
  if (newPosition.y >= 0 && newPosition.y + newSize.height <= canvas.value.height) {
    frameSize.value.height = newSize.height
    framePosition.value.y = newPosition.y
  }

  applyEffect()
}

const stopResize = () => {
  if (isResizing.value) {
    isResizing.value = false
    currentResizeHandle.value = null
    applyEffect()
  }
}

const applyEffect = () => {
  if (!canvas.value || !ctx.value || !originalImage.value) {
    console.error('Canvas, context, or original image is not available')
    return
  }

  const { width: canvasWidth, height: canvasHeight } = canvas.value
  if (canvasWidth === 0 || canvasHeight === 0) {
    console.error('Canvas has zero width or height')
    return
  }

  // Reset canvas to original image
  ctx.value.clearRect(0, 0, canvasWidth, canvasHeight)
  ctx.value.drawImage(originalImage.value, 0, 0, canvasWidth, canvasHeight)

  // If no tool is selected, just return after drawing the original image
  if (!currentTool.value) {
    return
  }

  const { x, y } = framePosition.value
  const { width, height } = frameSize.value

  if (width <= 0 || height <= 0) {
    console.error('Invalid frame size:', width, height)
    return
  }

  if (x < 0 || y < 0 || x + width > canvasWidth || y + height > canvasHeight) {
    console.error('Frame is outside canvas bounds')
    return
  }

  if (currentTool.value === 'blur') {
    if (width > 0 && height > 0) {
      const tempCanvas = document.createElement('canvas');
      tempCanvas.width = width;
      tempCanvas.height = height;
      const tempCtx = tempCanvas.getContext('2d');
      
      tempCtx.drawImage(canvas.value, x, y, width, height, 0, 0, width, height);
      tempCtx.filter = `blur(${blurRadius.value}px)`;
      tempCtx.drawImage(tempCanvas, 0, 0);
      
      ctx.value.drawImage(tempCanvas, 0, 0, width, height, x, y, width, height);
    }
  } else if (currentTool.value === 'blur2') {
    ctx.value.filter = `blur(${blurRadius.value}px)`
    
    const tempCanvas = document.createElement('canvas')
    tempCanvas.width = canvas.value.width
    tempCanvas.height = canvas.value.height
    const tempCtx = tempCanvas.getContext('2d')
    
    tempCtx.drawImage(canvas.value, 0, 0)
    ctx.value.filter = `blur(${blurRadius.value}px)`
    ctx.value.drawImage(canvas.value, 0, 0)
    
    ctx.value.filter = 'none'
    ctx.value.globalCompositeOperation = 'source-atop'
    ctx.value.drawImage(tempCanvas, x, y, width, height, x, y, width, height)
    ctx.value.globalCompositeOperation = 'source-over'
    ctx.value.filter = 'none'
  } else if (currentTool.value === 'pixelate') {
    const pixelSize = 8
    const tempCanvas = document.createElement('canvas')
    tempCanvas.width = width
    tempCanvas.height = height
    const tempCtx = tempCanvas.getContext('2d')

    if (width > 0 && height > 0) {
      tempCtx.drawImage(canvas.value, x, y, width, height, 0, 0, width, height)

      tempCtx.imageSmoothingEnabled = false
      const scaleFactor = pixelSize
      const smallWidth = Math.max(1, Math.floor(width / scaleFactor))
      const smallHeight = Math.max(1, Math.floor(height / scaleFactor))
      
      tempCtx.drawImage(tempCanvas, 
        0, 0, width, height,
        0, 0, smallWidth, smallHeight
      )
      tempCtx.drawImage(tempCanvas,
        0, 0, smallWidth, smallHeight,
        0, 0, width, height
      )

      ctx.value.drawImage(tempCanvas, 0, 0, width, height, x, y, width, height)
    }
  }

  // Draw text items
  textItems.value.forEach(item => {
    ctx.value.font = `${item.size}px sans-serif`
    ctx.value.fillStyle = item.color
    ctx.value.fillText(item.text, item.x, item.y)
  })

  // Draw emoji items
  emojiItems.value.forEach(item => {
    ctx.value.font = `${item.size}px sans-serif`
    ctx.value.textBaseline = 'middle'
    ctx.value.textAlign = 'center'
    ctx.value.fillText(item.emoji, item.x + item.size / 2, item.y + item.size / 2)
  })
}

const startDrawing = (event) => {
  if (currentTool.value === 'text' && textInput.value) {
    const rect = canvas.value.getBoundingClientRect()
    const x = event.clientX - rect.left
    const y = event.clientY - rect.top

    ctx.value.font = `${textSize.value}px sans-serif`
    ctx.value.fillStyle = textColor.value
    ctx.value.fillText(textInput.value, x, y)
    addText()
  }
}

const draw = (event) => {
  if (isDragging.value) {
    dragFrame(event)
  } else if (isResizing.value) {
    resize(event)
  }
}

const stopDrawing = () => {
  stopFrameDrag()
  stopResize()
}

const handleClose = () => {
  emit('close')
}

const handleSave = () => {
  // Apply all current changes
  applyChanges()
  
  const imageData = canvas.value.toDataURL('image/jpeg', 0.9)
  emit('save', imageData)
  handleClose()
}

watch([() => props.isOpen, () => props.imageUrl], async ([newIsOpen, newImageUrl]) => {
  if (newIsOpen && newImageUrl) {
    await nextTick()
    await initCanvas()
  }
})

onMounted(() => {
  if (props.isOpen) {
    nextTick(() => initCanvas())
  }
})

const setBlurRadius = (level) => {
  blurRadius.value = level * 4
  applyEffect()
}

const startSliderDrag = (event) => {
  event.preventDefault()
  const sliderTrack = event.target.parentElement
  const updateSlider = (e) => {
    const rect = sliderTrack.getBoundingClientRect()
    const x = (e.clientX || e.touches[0].clientX) - rect.left
    const percentage = Math.max(0, Math.min(1, x / rect.width))
    blurRadius.value = Math.round(percentage * 20)
    applyEffect()
  }

  const stopDrag = () => {
    document.removeEventListener('mousemove', updateSlider)
    document.removeEventListener('touchmove', updateSlider)
    document.removeEventListener('mouseup', stopDrag)
    document.removeEventListener('touchend', stopDrag)
  }

  document.addEventListener('mousemove', updateSlider)
  document.addEventListener('touchmove', updateSlider)
  document.addEventListener('mouseup', stopDrag)
  document.addEventListener('touchend', stopDrag)
}

const textItems = ref([])
const activeTextIndex = ref(null)
const emojiItems = ref([])
const activeEmojiIndex = ref(null)

const addText = () => {
  if (textInput.value.trim()) {
    textItems.value.push({
      text: textInput.value,
      x: canvas.value.width / 2,
      y: canvas.value.height / 2,
      color: textColor.value,
      size: textSize.value,
      isDragging: false
    })
    textInput.value = ''
    applyEffect()
    showToolOptions.value = false
    currentTool.value = ''
  }
}

const handleTextTouchStart = (index, event) => {
  event.preventDefault();
  startTextDrag(index, event);
};

const startTextDrag = (index, event) => {
  event.preventDefault();
  activeTextIndex.value = index;
  textItems.value[index].isDragging = true;
  const rect = canvas.value.getBoundingClientRect();
  const clientX = event.touches ? event.touches[0].clientX : event.clientX;
  const clientY = event.touches ? event.touches[0].clientY : event.clientY;
  dragStart.value = {
    x: clientX - rect.left - textItems.value[index].x,
    y: clientY - rect.top - textItems.value[index].y
  };

  document.addEventListener('mousemove', dragText);
  document.addEventListener('touchmove', dragText, { passive: false });
  document.addEventListener('mouseup', stopTextDrag);
  document.addEventListener('touchend', stopTextDrag);
};

const dragText = (event) => {
  if (activeTextIndex.value === null) return;
  const textItem = textItems.value[activeTextIndex.value];
  if (!textItem.isDragging) return;

  let clientX, clientY;

  if (event.touches) {
    clientX = event.touches[0].clientX;
    clientY = event.touches[0].clientY;
  } else {
    clientX = event.clientX;
    clientY = event.clientY;
  }

  const rect = canvas.value.getBoundingClientRect();
  const x = clientX- rect.left - dragStart.value.x;
  const y = clientY - rect.top - dragStart.value.y;

  textItem.x = Math.max(0, Math.min(canvas.value.width - 100, x));
  textItem.y = Math.max(0, Math.min(canvas.value.height - 30, y));
  applyEffect();
};

const stopTextDrag = () => {
  if (activeTextIndex.value !== null && textItems.value[activeTextIndex.value]) {
    textItems.value[activeTextIndex.value].isDragging = false;
  }
  activeTextIndex.value = null;
  applyEffect();
  
  document.removeEventListener('mousemove', dragText);
  document.removeEventListener('touchmove', dragText);
  document.removeEventListener('mouseup', stopTextDrag);
  document.removeEventListener('touchend', stopTextDrag);
};

const editText = (index) => {
  if (activeTextIndex.value !== null) {
    stopTextDrag();
  }
  const textItem = textItems.value[index]
  textInput.value = textItem.text
  textColor.value = textItem.color
  textSize.value = textItem.size
  showToolOptions.value = true
  currentTool.value = 'text'
}

const deleteText = (index) => {
  textItems.value.splice(index, 1)
  applyEffect()
}

const addEmoji = (emojiData) => {
  const newEmoji = {
    emoji: emojiData.i,
    x: Math.random() * (canvas.value.width - 48),
    y: Math.random() * (canvas.value.height - 48),
    size: 48,
    isDragging: false
  }
  emojiItems.value.push(newEmoji)
  applyEffect()
  showToolOptions.value = false
  currentTool.value = ''
}

const startEmojiDrag = (index, event) => {
  event.preventDefault();
  activeEmojiIndex.value = index;
  emojiItems.value[index].isDragging = true;
  const rect = canvas.value.getBoundingClientRect();
  const clientX = event.touches ? event.touches[0].clientX : event.clientX;
  const clientY = event.touches ? event.touches[0].clientY : event.clientY;
  dragStart.value = {
    x: clientX - rect.left - emojiItems.value[index].x,
    y: clientY - rect.top - emojiItems.value[index].y
  };

  document.addEventListener('mousemove', dragEmoji);
  document.addEventListener('touchmove', dragEmoji, { passive: false });
  document.addEventListener('mouseup', stopEmojiDrag);
  document.addEventListener('touchend', stopEmojiDrag);
};

const dragEmoji = (event) => {
  if (activeEmojiIndex.value === null) return;
  const emojiItem = emojiItems.value[activeEmojiIndex.value];
  if (!emojiItem.isDragging) return;

  let clientX, clientY;

  if (event.touches) {
    clientX = event.touches[0].clientX;
    clientY = event.touches[0].clientY;
  } else {
    clientX = event.clientX;
    clientY = event.clientY;
  }

  const rect = canvas.value.getBoundingClientRect();
  const x = clientX - rect.left - dragStart.value.x;
  const y = clientY - rect.top - dragStart.value.y;

  emojiItem.x = Math.max(0, Math.min(canvas.value.width - emojiItem.size, x));
  emojiItem.y = Math.max(0, Math.min(canvas.value.height - emojiItem.size, y));
  applyEffect();
};

const stopEmojiDrag = () => {
  if (activeEmojiIndex.value !== null && emojiItems.value[activeEmojiIndex.value]) {
    emojiItems.value[activeEmojiIndex.value].isDragging = false;
  }
  activeEmojiIndex.value = null;
  applyEffect();
  
  document.removeEventListener('mousemove', dragEmoji);
  document.removeEventListener('touchmove', dragEmoji);
  document.removeEventListener('mouseup', stopEmojiDrag);
  document.removeEventListener('touchend', stopEmojiDrag);
};

const deleteEmoji = (index) => {
  emojiItems.value.splice(index, 1)
  applyEffect()
}

const hideTextTool = () => {
  if (currentTool.value !== 'text') {
    showToolOptions.value = false
  }
}

const applyChanges = () => {
  if (currentTool.value) {
    applyEffect()
    // Save the current canvas state as the new base image
    const imageData = canvas.value.toDataURL('image/png')
    const img = new Image()
    img.onload = () => {
      originalImage.value = img
    }
    img.src = imageData
  }
}
</script>

<style scoped>
.resize-handle {
  position: absolute;
  width: 10px;
  height: 10px;
  background: white;
  border-radius: 50%;
}
</style>
