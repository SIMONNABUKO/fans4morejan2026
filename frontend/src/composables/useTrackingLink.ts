import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axiosInstance from '@/axios'

export function useTrackingLink() {
  const route = useRoute()
  const trackingLinkId = ref<string | null>(null)

  // Initialize tracking link ID from URL or localStorage
  const initTrackingLink = () => {
    console.log('=== Tracking Link Initialization ===')
    console.log('Current URL:', window.location.href)
    
    // Parse URL directly to get tracking_link_id
    const url = new URL(window.location.href)
    const urlTrackingId = url.searchParams.get('tracking_link_id')
    console.log('Tracking ID from URL params:', urlTrackingId)
    
    if (urlTrackingId) {
      console.log('Found tracking ID in URL, saving to localStorage...')
      trackingLinkId.value = urlTrackingId
      localStorage.setItem('tracking_link_id', urlTrackingId)
      console.log('Saved to localStorage:', localStorage.getItem('tracking_link_id'))
    } else {
      const storedId = localStorage.getItem('tracking_link_id')
      console.log('No tracking ID in URL, checking localStorage:', storedId)
      trackingLinkId.value = storedId
    }
    
    console.log('Final trackingLinkId value:', trackingLinkId.value)
    console.log('=== End Tracking Link Initialization ===')
  }

  // Initialize on mount
  onMounted(() => {
    console.log('=== Tracking Link Component Mounted ===')
    initTrackingLink()
  })

  // Track a signup action
  const trackSignup = async (method: 'register' | 'login') => {
    console.log('=== Tracking Signup ===')
    console.log('Current tracking ID:', trackingLinkId.value)
    console.log('Method:', method)
    
    if (!trackingLinkId.value) {
      console.log('No tracking ID found, skipping signup tracking')
      return
    }

    try {
      console.log('Sending signup tracking request...')
      await axiosInstance.post(`/tracking-links/${trackingLinkId.value}/actions`, {
        action_type: 'signup',
        action_data: { method }
      })
      console.log('Signup tracking successful')
    } catch (error) {
      console.error('Failed to track signup:', error)
    }
  }

  // Track a subscription action
  const trackSubscription = async (tierId: number, duration: number) => {
    console.log('=== Tracking Subscription ===')
    console.log('Current tracking ID:', trackingLinkId.value)
    console.log('Tier ID:', tierId)
    console.log('Duration:', duration)
    
    if (!trackingLinkId.value) {
      console.log('No tracking ID found, skipping subscription tracking')
      return
    }

    try {
      console.log('Sending subscription tracking request...')
      await axiosInstance.post(`/tracking-links/${trackingLinkId.value}/actions`, {
        action_type: 'subscription',
        action_data: { tier_id: tierId, duration }
      })
      console.log('Subscription tracking successful')
    } catch (error) {
      console.error('Failed to track subscription:', error)
    }
  }

  // Track a purchase action
  const trackPurchase = async (entityId: number, entityType: string, amount: number) => {
    console.log('=== Tracking Purchase ===')
    console.log('Current tracking ID:', trackingLinkId.value)
    console.log('Entity ID:', entityId)
    console.log('Entity Type:', entityType)
    console.log('Amount:', amount)
    
    if (!trackingLinkId.value) {
      console.log('No tracking ID found, skipping purchase tracking')
      return
    }

    try {
      console.log('Sending purchase tracking request...')
      await axiosInstance.post(`/tracking-links/${trackingLinkId.value}/actions`, {
        action_type: 'purchase',
        action_data: { 
          entity_id: entityId,
          entity_type: entityType,
          amount
        }
      })
      console.log('Purchase tracking successful')
    } catch (error) {
      console.error('Failed to track purchase:', error)
    }
  }

  // Clear tracking link ID
  const clearTrackingLink = () => {
    console.log('=== Clearing Tracking Link ===')
    console.log('Current tracking ID:', trackingLinkId.value)
    trackingLinkId.value = null
    localStorage.removeItem('tracking_link_id')
    console.log('Tracking ID cleared from localStorage:', localStorage.getItem('tracking_link_id'))
  }

  return {
    trackingLinkId,
    initTrackingLink,
    trackSignup,
    trackSubscription,
    trackPurchase,
    clearTrackingLink
  }
} 
