import { createLazyImageLoader } from '@/utils/performance'

// Create a single lazy image loader instance
const lazyImageLoader = createLazyImageLoader()

export const lazyLoad = {
  mounted(el, binding) {
    // Set up lazy loading for images
    if (el.tagName === 'IMG') {
      const src = binding.value || el.dataset.src
      const placeholder = el.dataset.placeholder || '/placeholder.jpg'
      
      if (src) {
        // Set placeholder initially
        el.src = placeholder
        el.dataset.src = src
        el.classList.add('lazy-loading')
        
        // Start observing
        lazyImageLoader.observe(el)
      }
    }
  },
  
  updated(el, binding) {
    // Handle updates to the src
    if (el.tagName === 'IMG') {
      const newSrc = binding.value || el.dataset.src
      const currentSrc = el.dataset.src
      
      if (newSrc && newSrc !== currentSrc) {
        el.dataset.src = newSrc
        el.classList.add('lazy-loading')
        el.classList.remove('lazy-loaded', 'lazy-error')
        lazyImageLoader.observe(el)
      }
    }
  },
  
  unmounted(el) {
    // Clean up when element is removed
    if (el.tagName === 'IMG') {
      lazyImageLoader.unobserve(el)
    }
  }
}

// Register directive globally
export default {
  install(app) {
    app.directive('lazy', lazyLoad)
  }
} 