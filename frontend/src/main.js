import { createApp } from "vue"
import { createPinia } from "pinia"
import App from "./App.vue"
import router from "./router"
import "./assets/app.css"
import "remixicon/fonts/remixicon.css"
import axiosInstance from "./axios"

// Core imports only - defer heavy dependencies
import { useAuthStore } from "./stores/authStore"
import { useSettingsStore } from "./stores/settingsStore"

// Import lazy loading directive
import lazyLoadDirective from './directives/lazyLoad'

// Load i18n synchronously since it's required by components
const loadI18n = async () => {
  const { createI18n } = await import('vue-i18n')
  
  // Only load the current locale and fallback
  const savedLocale = localStorage.getItem('locale') || 'en'
  const fallbackLocale = 'en'
  
  const [currentLocale, fallbackLocaleData] = await Promise.all([
    import(`./locales/${savedLocale}.json`),
    import(`./locales/${fallbackLocale}.json`)
  ])
  
  const messages = {
    [savedLocale]: currentLocale.default,
    [fallbackLocale]: fallbackLocaleData.default
  }
  
  return createI18n({
    legacy: false,
    locale: savedLocale,
    fallbackLocale,
    messages
  })
}

// Lazy load heavy dependencies
const loadHeavyDependencies = async () => {
  // Lazy load Pusher and WebSocket service
  const [Pusher, websocketService] = await Promise.all([
    import("pusher-js"),
    import("./services/websocketService")
  ])
  
  // Initialize WebSocket after app is mounted
  websocketService.default.initialize()
}

// Lazy load toast notification
const loadToastPlugin = async () => {
  const [ToastPlugin, toastCSS] = await Promise.all([
    import('vue-toast-notification'),
    import('vue-toast-notification/dist/theme-sugar.css')
  ])
  return ToastPlugin.default
}

// Load vue-toastification plugin
const loadToastificationPlugin = async () => {
  const [ToastPlugin, toastCSS] = await Promise.all([
    import('vue-toastification'),
    import('vue-toastification/dist/index.css')
  ])
  return ToastPlugin.default
}

// Lazy load date picker
const loadDatePicker = async () => {
  const [VueDatePicker, datePickerCSS] = await Promise.all([
    import('@vuepic/vue-datepicker'),
    import('@vuepic/vue-datepicker/dist/main.css')
  ])
  return VueDatePicker.default
}

// Initialize app with i18n
const initializeApp = async () => {
  try {
    // Load i18n first since it's required by components
    const i18n = await loadI18n()
    
    // Create and configure app
    const app = createApp(App)
    const pinia = createPinia()

    app.use(pinia)
    app.use(router)
    app.use(i18n) // Register i18n immediately

    // Register lazy loading directive
    app.use(lazyLoadDirective)

    // Use the custom axios instance globally
    app.config.globalProperties.$axios = axiosInstance

    // Load and register toast plugins before mounting
    const [toastPlugin, toastificationPlugin, datePicker] = await Promise.all([
      loadToastPlugin(),
      loadToastificationPlugin(),
      loadDatePicker()
    ])
    
    // Register remaining plugins
    app.use(toastPlugin)
    app.use(toastificationPlugin)
    app.component('VueDatePicker', datePicker)

    // Mount the app
    app.mount("#app")
    
    // Load heavy dependencies
    await loadHeavyDependencies()
    
    // Initialize stores
    const authStore = useAuthStore()
    const settingsStore = useSettingsStore()
    
    // Check authentication status
    await authStore.checkAuth()
    
    // Initialize settings only if user is authenticated
    if (authStore.isAuthenticated) {
      await settingsStore.initializeSettings()
    }
    
    // Preload other locales in background
    setTimeout(() => {
      import('./locales/fr.json')
      import('./locales/es.json')
      import('./locales/de.json')
      import('./locales/pt.json')
      import('./locales/tr.json')
      import('./locales/ko.json')
    }, 2000)
    
  } catch (error) {
    console.error('Error initializing app:', error)
  }
}

// Start initialization immediately
initializeApp()