<template>
<button 
  type="button"
  role="switch"
  :aria-checked="modelValue"
  @click="$emit('update:modelValue', !modelValue)"
  class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:ring-offset-2"
  :class="modelValue ? 'bg-primary-light dark:bg-primary-dark' : 'bg-surface-light dark:bg-surface-dark'"
>
  <span 
    class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
    :class="modelValue ? 'translate-x-6' : 'translate-x-1'"
  />
</button>
</template>

<script setup>
defineProps({
  modelValue: {
    type: Boolean,
    required: true
  }
})

defineEmits(['update:modelValue'])
</script>

