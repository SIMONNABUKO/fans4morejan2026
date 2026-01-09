import { createRouter, createWebHistory } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'
import LoginView from '@/views/auth/LoginView.vue'
import TierManagementView from '@/views/creator/TierManagementView.vue'
import UsersView from '@/views/users/UsersView.vue'
import UserDetailsView from '@/views/users/UserDetailsView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: LoginView,
      meta: { requiresAuth: false }
    },
    {
      path: '/',
      component: MainLayout,
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'dashboard',
          component: () => import('@/views/DashboardView.vue')
        },
        {
          path: 'users',
          name: 'users',
          component: UsersView,
          meta: { requiresAuth: true }
        },
        {
          path: 'users/:id',
          name: 'user-details',
          component: UserDetailsView,
          meta: { requiresAuth: true }
        },
        {
          path: 'creator/applications',
          name: 'creator-applications',
          component: () => import('@/views/creator-applications/CreatorApplicationsView.vue'),
          meta: {
            requiresAuth: true,
            title: 'Creator Applications'
          }
        },
        {
          path: 'content',
          name: 'content',
          component: () => import('@/views/content/ContentView.vue')
        },
        {
          path: 'media',
          name: 'media',
          component: () => import('@/views/media/MediaView.vue')
        },
        {
          path: 'financial',
          name: 'financial',
          children: [
            {
              path: 'platform-wallet',
              name: 'platform-wallet',
              component: () => import('@/views/financial/PlatformWalletView.vue'),
              meta: {
                title: 'Platform Wallet'
              }
            },
            {
              path: 'transactions',
              name: 'transactions',
              component: () => import('@/views/financial/TransactionView.vue')
            }
          ]
        },
        {
          path: 'settings',
          name: 'settings',
          component: () => import('@/views/settings/SettingsView.vue')
        },
        {
          path: 'creator/tiers',
          name: 'creator-tiers',
          component: TierManagementView,
          meta: {
            requiresAuth: true,
            title: 'Creator Tiers'
          }
        },
        {
          path: 'messages',
          name: 'messages',
          component: () => import('@/views/messages/MessageMonitoringView.vue'),
          meta: {
            title: 'Message Monitoring',
            requiresAuth: true
          }
        }
      ]
    }
  ]
})

// Navigation guard
router.beforeEach(async (to, from, next) => {
  const { useAuthStore } = await import('@/stores/auth')
  const authStore = useAuthStore()
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)

  // Check if user is authenticated
  const isAuthenticated = authStore.isAuthenticated

  if (requiresAuth && !isAuthenticated) {
    // Redirect to login if route requires auth and user is not authenticated
    next({ name: 'login', query: { redirect: to.fullPath } })
  } else if (to.name === 'login' && isAuthenticated) {
    // Redirect to dashboard if user is already authenticated and trying to access login
    next({ name: 'dashboard' })
  } else {
    next()
  }
})

export default router
