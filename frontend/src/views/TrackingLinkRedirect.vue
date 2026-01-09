<template>
  <div class="flex items-center justify-center min-h-screen">
    <div class="text-center">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-light dark:border-primary-dark mx-auto"></div>
      <p class="mt-4 text-text-light-primary dark:text-text-dark-primary">
        {{ $t('redirecting') }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axiosInstance from '@/axios'
import { useToast } from 'vue-toast-notification'
import { useI18n } from 'vue-i18n'

const route = useRoute()
const router = useRouter()
const toast = useToast()
const { t } = useI18n()

onMounted(async () => {
  try {
    console.log('Recording click for tracking link:', {
      username: route.params.username,
      slug: route.params.slug
    });

    // Get referrer information
    const referrer = document.referrer;
    const referrerDomain = referrer ? new URL(referrer).hostname : null;

    // Call the backend to record the click with all necessary data
    const response = await axiosInstance.post(`/tracking-links/click`, {
      username: route.params.username,
      slug: route.params.slug,
      ip_address: null, // Will be captured by backend
      user_agent: navigator.userAgent,
      referrer_url: referrer,
      referrer_domain: referrerDomain,
      metadata: {
        screen_width: window.innerWidth,
        screen_height: window.innerHeight,
        language: navigator.language,
        platform: navigator.platform,
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone
      }
    }, {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    });

    console.log('Click recorded successfully:', response.data);
    
    // Store the tracking link ID in localStorage
    if (response.data.tracking_link_id) {
      localStorage.setItem('tracking_link_id', response.data.tracking_link_id);
    }

    // Redirect to the user's profile
    console.log('Redirecting to user profile:', route.params.username);
    router.push({ path: `/${route.params.username}/posts` });
  } catch (error) {
    console.error('Error recording click:', {
      error: error.message,
      response: error.response?.data,
      status: error.response?.status,
      headers: error.response?.headers
    });
    
    // Show more detailed error message
    const errorMessage = error.response?.data?.message || error.message;
    toast.error(t('failed_to_record_click') + ': ' + errorMessage);
    
    // Still redirect even if there's an error
    router.push({ path: `/${route.params.username}/posts` });
  }
})
</script> 