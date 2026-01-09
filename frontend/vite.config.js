import { fileURLToPath, URL } from 'node:url'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    vueDevTools(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    },
  },
  build: {
    rollupOptions: {
      output: {
        manualChunks: {
          // Core Vue ecosystem
          'vue-core': ['vue', 'vue-router', 'pinia'],
          
          // UI libraries (excluding problematic remixicon)
          'ui-vendor': ['@headlessui/vue'],
          
          // Heavy libraries
          'chart-vendor': ['chart.js'],
          'date-vendor': ['date-fns', '@vuepic/vue-datepicker'],
          'emoji-vendor': ['emoji-mart', 'vue-emoji-picker'],
          'toast-vendor': ['vue-toast-notification', 'vue-toastification'],
          
          // Feature-based chunks
          'messaging': [
            './src/stores/messagesStore.js',
            './src/stores/messagesFilterStore.js',
            './src/stores/messageSettingsStore.js',
            './src/views/MessagesView.vue',
            './src/views/SingleMessageView.vue',
            './src/views/MassMessageComposerView.vue',
            './src/views/MessagesSettingsView.vue'
          ],
          'analytics': [
            './src/stores/statisticsStore.js',
            './src/stores/analyticsStore.js',
            './src/stores/earningsStore.js',
            './src/views/StatisticsView.vue',
            './src/views/EarningStatisticsView.vue'
          ],
          'media': [
            './src/stores/mediaUploadStore.js',
            './src/stores/uploadStore.js',
            './src/stores/vaultStore.js',
            './src/views/MediaCollectionView.vue',
            './src/views/AlbumView.vue',
            './src/views/AlbumDetailView.vue',
            './src/views/VaultView.vue',
            './src/views/UploadsView.vue'
          ]
        }
      }
    },
    chunkSizeWarningLimit: 1000,
    target: 'es2015',
    minify: 'terser',
    terserOptions: {
      compress: {
        drop_console: true,
        drop_debugger: true
      }
    }
  },
  optimizeDeps: {
    include: [
      'vue',
      'vue-router',
      'pinia',
      'axios'
    ],
    exclude: [
      'chart.js',
      '@vuepic/vue-datepicker',
      'emoji-mart',
      'vue-emoji-picker'
    ]
  },
  server: {
    hmr: {
      overlay: false
    }
  }
})
