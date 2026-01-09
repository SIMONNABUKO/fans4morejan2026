<template>
  <div class="p-4 flex items-center gap-4">
    <div class="relative flex-1">
      <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"></i>
      <input
        type="text"
        :placeholder="t('search_timeline')"
        class="w-full pl-10 pr-4 py-2 bg-accent rounded-full text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary"
      />
    </div>
    <button class="text-primary hover:text-primary/80 transition-colors">
      <i class="ri-refresh-line text-xl"></i>
    </button>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
</script>

<style scoped>
/* Enhanced glassmorphism effects */
.backdrop-blur-sm {
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}

/* Smooth transitions for all interactive elements */
* {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Enhanced focus states */
input:focus {
  transform: translateY(-1px);
}

/* Enhanced hover effects */
button:hover {
  transform: translateY(-1px);
}
</style>

