// Centralized store management with lazy loading
import { useAuthStore } from './authStore'
import { useSettingsStore } from './settingsStore'
import { useAgeVerificationStore } from './ageVerificationStore'

// Core stores that are always loaded
export { useAuthStore, useSettingsStore, useAgeVerificationStore }

// Lazy-loaded stores for better performance
export const useMessagesStore = () => import(/* webpackChunkName: "messaging" */ './messagesStore.js')
export const useMessagesFilterStore = () => import(/* webpackChunkName: "messaging" */ './messagesFilterStore.js')
export const useMessageSettingsStore = () => import(/* webpackChunkName: "messaging" */ './messageSettingsStore.js')

export const useFeedStore = () => import(/* webpackChunkName: "content-management" */ './feedStore.js')
export const useFeedFilterStore = () => import(/* webpackChunkName: "content-management" */ './feedFilterStore.js')
export const usePostOptionsStore = () => import(/* webpackChunkName: "content-management" */ './postOptionsStore.js')
export const useScheduledPostsStore = () => import(/* webpackChunkName: "content-management" */ './scheduledPostsStore.js')
export const useBookmarkStore = () => import(/* webpackChunkName: "content-management" */ './bookmarkStore.js')

export const useSubscriptionStore = () => import(/* webpackChunkName: "creator-dashboard" */ './subscriptionStore.js')
export const useSubscribersStore = () => import(/* webpackChunkName: "creator-dashboard" */ './subscribersStore.js')
export const useSupportersStore = () => import(/* webpackChunkName: "creator-dashboard" */ './supportersStore.js')
export const useCreatorSettingsStore = () => import(/* webpackChunkName: "creator-dashboard" */ './creatorSettingsStore.js')

export const useStatisticsStore = () => import(/* webpackChunkName: "analytics" */ './statisticsStore.js')
export const useAnalyticsStore = () => import(/* webpackChunkName: "analytics" */ './analyticsStore.js')
export const useEarningsStore = () => import(/* webpackChunkName: "analytics" */ './earningsStore.js')

export const useMediaUploadStore = () => import(/* webpackChunkName: "media" */ './mediaUploadStore.js')
export const useUploadStore = () => import(/* webpackChunkName: "media" */ './uploadStore.js')
export const useVaultStore = () => import(/* webpackChunkName: "media" */ './vaultStore.js')

export const useUserProfileStore = () => import(/* webpackChunkName: "settings" */ './userProfileStore.js')

// Other stores
export const useNotificationsStore = () => import('./notificationsStore.js')
export const useReferralStore = () => import('./referralStore.js')
export const useTrackingLinksStore = () => import('./trackingLinksStore.js')
export const useListStore = () => import('./listStore.js')
export const useFollowStore = () => import('./followStore.js')
export const useCreatorApplicationStore = () => import('./creatorApplicationStore.js')
export const useCommentStore = () => import('./commentStore.js')
export const useReachStore = () => import('./reachStore.js')
export const useTopFansStore = () => import('./topFansStore.js')
export const useWalletStore = () => import('./walletStore.js')

// Store cache for performance
const storeCache = new Map()
const CACHE_TIMEOUT = 5 * 60 * 1000 // 5 minutes

// Helper function to get cached store instance
export const getCachedStore = async (storeName, storeLoader) => {
  const cacheKey = `store:${storeName}`
  const cached = storeCache.get(cacheKey)
  
  if (cached && Date.now() - cached.timestamp < CACHE_TIMEOUT) {
    return cached.instance
  }
  
  const module = await storeLoader()
  const instance = module.default ? module.default() : module()
  
  storeCache.set(cacheKey, {
    instance,
    timestamp: Date.now()
  })
  
  return instance
}

// Preload critical stores
export const preloadCriticalStores = async () => {
  const criticalStores = [
    { name: 'messages', loader: useMessagesStore },
    { name: 'feed', loader: useFeedStore },
    { name: 'notifications', loader: useNotificationsStore }
  ]
  
  await Promise.all(
    criticalStores.map(store => getCachedStore(store.name, store.loader))
  )
}

// Clear store cache
export const clearStoreCache = () => {
  storeCache.clear()
} 