// Performance monitoring and optimization utilities

class PerformanceMonitor {
  constructor() {
    this.metrics = new Map()
    this.observers = []
    this.initObservers()
  }

  // Initialize performance observers
  initObservers() {
    // Navigation timing
    if ('PerformanceObserver' in window) {
      const navigationObserver = new PerformanceObserver((list) => {
        list.getEntries().forEach((entry) => {
          if (entry.entryType === 'navigation') {
            this.recordMetric('navigation', {
              loadTime: entry.loadEventEnd - entry.loadEventStart,
              domContentLoaded: entry.domContentLoadedEventEnd - entry.domContentLoadedEventStart,
              firstPaint: entry.responseEnd - entry.fetchStart,
              totalTime: entry.loadEventEnd - entry.fetchStart
            })
          }
        })
      })
      
      navigationObserver.observe({ entryTypes: ['navigation'] })
      this.observers.push(navigationObserver)

      // Resource timing
      const resourceObserver = new PerformanceObserver((list) => {
        list.getEntries().forEach((entry) => {
          if (entry.entryType === 'resource') {
            this.recordMetric('resource', {
              name: entry.name,
              duration: entry.duration,
              size: entry.transferSize,
              type: entry.initiatorType
            })
          }
        })
      })
      
      resourceObserver.observe({ entryTypes: ['resource'] })
      this.observers.push(resourceObserver)

      // Long tasks
      const longTaskObserver = new PerformanceObserver((list) => {
        list.getEntries().forEach((entry) => {
          if (entry.entryType === 'longtask') {
            this.recordMetric('longtask', {
              duration: entry.duration,
              startTime: entry.startTime
            })
          }
        })
      })
      
      longTaskObserver.observe({ entryTypes: ['longtask'] })
      this.observers.push(longTaskObserver)
    }
  }

  // Record performance metric
  recordMetric(type, data) {
    if (!this.metrics.has(type)) {
      this.metrics.set(type, [])
    }
    this.metrics.get(type).push({
      ...data,
      timestamp: Date.now()
    })
  }

  // Get performance metrics
  getMetrics(type) {
    return this.metrics.get(type) || []
  }

  // Get average load time
  getAverageLoadTime() {
    const navigationMetrics = this.getMetrics('navigation')
    if (navigationMetrics.length === 0) return 0
    
    const totalTime = navigationMetrics.reduce((sum, metric) => sum + metric.loadTime, 0)
    return totalTime / navigationMetrics.length
  }

  // Get slowest resources
  getSlowestResources(limit = 10) {
    const resourceMetrics = this.getMetrics('resource')
    return resourceMetrics
      .sort((a, b) => b.duration - a.duration)
      .slice(0, limit)
  }

  // Get largest resources
  getLargestResources(limit = 10) {
    const resourceMetrics = this.getMetrics('resource')
    return resourceMetrics
      .sort((a, b) => b.size - a.size)
      .slice(0, limit)
  }

  // Measure function execution time
  async measureFunction(name, fn) {
    const start = performance.now()
    try {
      const result = await fn()
      const duration = performance.now() - start
      this.recordMetric('function', { name, duration, success: true })
      return result
    } catch (error) {
      const duration = performance.now() - start
      this.recordMetric('function', { name, duration, success: false, error: error.message })
      throw error
    }
  }

  // Measure component render time
  measureComponentRender(componentName, renderFn) {
    return this.measureFunction(`component:${componentName}`, renderFn)
  }

  // Get performance report
  getReport() {
    return {
      averageLoadTime: this.getAverageLoadTime(),
      slowestResources: this.getSlowestResources(),
      largestResources: this.getLargestResources(),
      longTasks: this.getMetrics('longtask'),
      functionMetrics: this.getMetrics('function')
    }
  }

  // Cleanup observers
  destroy() {
    this.observers.forEach(observer => observer.disconnect())
    this.observers = []
  }
}

// Image lazy loading utility
export const createLazyImageLoader = () => {
  const imageCache = new Map()
  const observerOptions = {
    root: null,
    rootMargin: '50px',
    threshold: 0.1
  }

  const imageObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const img = entry.target
        const src = img.dataset.src
        const placeholder = img.dataset.placeholder
        
        if (src && !imageCache.has(src)) {
          // Load image
          const newImg = new Image()
          newImg.onload = () => {
            img.src = src
            img.classList.remove('lazy-loading')
            img.classList.add('lazy-loaded')
            imageCache.set(src, true)
          }
          newImg.onerror = () => {
            img.classList.remove('lazy-loading')
            img.classList.add('lazy-error')
            if (placeholder) {
              img.src = placeholder
            }
          }
          newImg.src = src
        } else if (imageCache.has(src)) {
          img.src = src
          img.classList.remove('lazy-loading')
          img.classList.add('lazy-loaded')
        }
        
        imageObserver.unobserve(img)
      }
    })
  }, observerOptions)

  return {
    observe: (img) => {
      img.classList.add('lazy-loading')
      imageObserver.observe(img)
    },
    unobserve: (img) => {
      imageObserver.unobserve(img)
    },
    destroy: () => {
      imageObserver.disconnect()
    }
  }
}

// Virtual scrolling utility
export const createVirtualScroller = (items, itemHeight, containerHeight) => {
  const visibleCount = Math.ceil(containerHeight / itemHeight)
  const buffer = Math.ceil(visibleCount / 2)
  
  return {
    getVisibleItems: (scrollTop) => {
      const startIndex = Math.floor(scrollTop / itemHeight)
      const endIndex = Math.min(startIndex + visibleCount + buffer, items.length)
      const start = Math.max(0, startIndex - buffer)
      
      return {
        items: items.slice(start, endIndex),
        startIndex: start,
        endIndex,
        totalHeight: items.length * itemHeight,
        offsetY: start * itemHeight
      }
    }
  }
}

// Request batching utility
export class RequestBatcher {
  constructor(delay = 100) {
    this.pending = new Map()
    this.delay = delay
  }

  batch(key, request) {
    if (this.pending.has(key)) {
      return this.pending.get(key)
    }

    const promise = new Promise((resolve, reject) => {
      setTimeout(() => {
        request()
          .then(resolve)
          .catch(reject)
          .finally(() => {
            this.pending.delete(key)
          })
      }, this.delay)
    })

    this.pending.set(key, promise)
    return promise
  }

  clear() {
    this.pending.clear()
  }
}

// Debounce utility
export const debounce = (func, wait) => {
  let timeout
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout)
      func(...args)
    }
    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
  }
}

// Throttle utility
export const throttle = (func, limit) => {
  let inThrottle
  return function executedFunction(...args) {
    if (!inThrottle) {
      func.apply(this, args)
      inThrottle = true
      setTimeout(() => inThrottle = false, limit)
    }
  }
}

// Create global performance monitor instance
export const performanceMonitor = new PerformanceMonitor()

// Export utilities
export { PerformanceMonitor } 