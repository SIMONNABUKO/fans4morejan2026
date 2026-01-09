<template>
  <TransitionRoot appear :show="isOpen" as="template">
    <Dialog as="div" @close="handleClose" class="relative z-50">
      <TransitionChild
        as="template"
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-2">
          <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-from="opacity-0 scale-95 translate-y-4"
            enter-to="opacity-100 scale-100 translate-y-0"
            leave="duration-200 ease-in"
            leave-from="opacity-100 scale-100 translate-y-0"
            leave-to="opacity-0 scale-95 translate-y-4"
          >
            <DialogPanel class="w-full h-full max-w-none transform overflow-hidden bg-white/90 dark:bg-gray-900/90 backdrop-blur-xl border border-white/20 dark:border-gray-700/50 p-6 text-left align-middle shadow-2xl transition-all animate-scaleIn">
              <!-- Enhanced Header -->
              <DialogTitle as="div" class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                  <div class="w-12 h-12 rounded-full bg-primary-light dark:bg-primary-dark flex items-center justify-center shadow-lg">
                    <i class="ri-lock-unlock-line text-white text-xl"></i>
                  </div>
                  <div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                      {{ $t('unlock_bundle') }}
                    </h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Choose your preferred access method</p>
                  </div>
                </div>
                <button 
                  @click="handleClose"
                  class="p-2 rounded-full bg-gray-100/80 dark:bg-gray-700/80 hover:bg-gray-200/80 dark:hover:bg-gray-600/80 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all duration-200 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                >
                  <i class="ri-close-line text-xl"></i>
                </button>
              </DialogTitle>

              <!-- Enhanced Loading state -->
              <div v-if="isProcessing" class="flex flex-col items-center justify-center py-12">
                <div class="w-16 h-16 border-4 border-gray-200/30 border-t-primary-light dark:border-t-primary-dark rounded-full animate-spin mb-6"></div>
                <p class="text-gray-500 dark:text-gray-400 text-lg font-medium">Processing your request...</p>
                <p class="text-gray-400 dark:text-gray-500 text-sm mt-2">Please wait while we set up your access</p>
              </div>

              <div v-else class="space-y-8">
                <div class="mb-8">
                  <p class="text-gray-600 dark:text-gray-300 text-lg leading-relaxed mb-6">
                    {{ $t('choose_option_unlock') }}
                  </p>
                  
                  <!-- Enhanced Permission Options -->
                  <div class="space-y-6">
                    <div 
                      v-for="option in allPermissionOptions" 
                      :key="option.id"
                      class="relative p-6 rounded-2xl border border-white/20 dark:border-gray-700/50 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm transition-all duration-300 hover:bg-white/70 dark:hover:bg-gray-800/70 hover:shadow-lg"
                      :class="{
                        'border-primary-light dark:border-primary-dark bg-primary-light/10 dark:bg-primary-dark/10 shadow-lg': 
                          option.id === recommendedOption?.id
                      }"
                    >
                      <!-- Enhanced Recommended badge -->
                      <div 
                        v-if="option.id === recommendedOption?.id"
                        class="absolute -top-3 left-6 bg-primary-light dark:bg-primary-dark text-white text-xs px-4 py-2 rounded-full font-semibold shadow-lg"
                      >
                        <i class="ri-star-fill mr-1"></i>
                        {{ $t('recommended') }}
                      </div>
                      
                      <!-- Enhanced Requirements list -->
                      <div class="space-y-4">
                        <div 
                          v-for="(req, reqIndex) in option.requirements" 
                          :key="`${option.id}-req-${reqIndex}`"
                          class="flex items-center gap-3 p-3 rounded-xl bg-white/60 dark:bg-gray-700/60 border border-white/20 dark:border-gray-600/50"
                        >
                          <div class="w-8 h-8 rounded-full flex items-center justify-center"
                               :class="req.met ? 'bg-green-500/20' : 'bg-gray-200/60 dark:bg-gray-600/60'">
                            <i 
                              :class="[
                                req.met ? 'ri-checkbox-circle-fill text-green-500' : 'ri-checkbox-blank-circle-line text-gray-400 dark:text-gray-500',
                                'text-lg'
                              ]"
                            ></i>
                          </div>
                          <span 
                            :class="[
                              req.met ? 'text-green-600 dark:text-green-400 font-semibold' : 'text-gray-700 dark:text-gray-300',
                              'text-base'
                            ]"
                          >
                            {{ req.label }}
                          </span>
                        </div>
                      </div>
                      
                      <!-- Enhanced Action buttons -->
                      <div class="mt-6 flex flex-wrap gap-3">
                        <!-- Enhanced Subscribe button -->
                        <button 
                          v-if="option.requirements.some(r => r.type === 'subscription' && !r.met)"
                          @click="fetchTiers"
                          class="px-6 py-3 bg-primary-light dark:bg-primary-dark hover:bg-primary-light/90 dark:hover:bg-primary-dark/90 text-white rounded-xl font-semibold transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark"
                        >
                          <i class="ri-vip-crown-line mr-2"></i>
                          {{ $t('subscribe_to') }} {{ creatorName || $t('the_creator') }}
                        </button>
                        
                        <!-- Enhanced Follow button -->
                        <button 
                          v-if="option.requirements.some(r => r.type === 'follow' && !r.met)"
                          @click="handleFollow"
                          class="px-6 py-3 bg-primary-light dark:bg-primary-dark hover:bg-primary-light/90 dark:hover:bg-primary-dark/90 text-white rounded-xl font-semibold transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark"
                        >
                          <i class="ri-user-add-line mr-2"></i>
                          {{ $t('follow') }} {{ creatorName || $t('the_creator') }}
                        </button>
                        
                        <!-- Enhanced Pay button -->
                        <button 
                          v-if="option.requirements.some(r => r.type === 'payment' && !r.met)"
                          @click="handlePaymentWithAmount(option.price)"
                          class="px-6 py-3 bg-primary-light dark:bg-primary-dark hover:bg-primary-light/90 dark:hover:bg-primary-dark/90 text-white rounded-xl font-semibold transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark"
                        >
                          <i class="ri-money-dollar-circle-line mr-2"></i>
                          {{ $t('pay') }} ${{ option.price.toFixed(2) }}
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Enhanced Subscription Options Section -->
                <div v-if="showSubscriptionOptions && showSubscriptionDetails" class="mb-8">
                  <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full bg-primary-light dark:bg-primary-dark flex items-center justify-center">
                        <i class="ri-vip-crown-line text-white text-lg"></i>
                      </div>
                      <h4 class="text-xl font-bold text-gray-900 dark:text-white">Available Subscription Tiers</h4>
                    </div>
                    <button 
                      @click="showSubscriptionDetails = false"
                      class="px-4 py-2 rounded-xl bg-gray-100/60 dark:bg-gray-700/60 hover:bg-gray-200/60 dark:hover:bg-gray-600/60 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all duration-200 flex items-center gap-2"
                    >
                      <i class="ri-arrow-left-line"></i>
                      Back
                    </button>
                  </div>
                  
                  <div class="space-y-6">
                    <div 
                      v-for="tier in availableTiers" 
                      :key="tier.id"
                      class="space-y-4"
                    >
                      <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center"
                             :style="{ backgroundColor: tier.color_code }">
                          <i class="ri-vip-crown-line text-white text-sm"></i>
                        </div>
                        <div class="font-bold text-xl text-gray-900 dark:text-white">
                          {{ tier.title }}
                        </div>
                      </div>
                      
                      <!-- Enhanced Main subscription option -->
                      <button 
                        class="w-full px-6 py-4 text-left rounded-2xl text-white transition-all duration-200 hover:scale-[1.02] shadow-lg hover:shadow-xl"
                        :style="{
                          backgroundColor: tier.color_code,
                          '--hover-color': adjustColor(tier.color_code, -10),
                        }"
                        @click.prevent="subscribeTier(tier, 1)"
                        @mouseenter="e => e.target.style.backgroundColor = adjustColor(tier.color_code, -10)"
                        @mouseleave="e => e.target.style.backgroundColor = tier.color_code"
                      >
                        <div class="flex justify-between items-center">
                          <div class="flex items-center gap-3">
                            <i class="ri-vip-crown-line text-xl"></i>
                            <span class="font-semibold text-lg">{{ tier.title }} 1 Month</span>
                          </div>
                          <span class="font-bold text-xl">${{ tier.base_price }}</span>
                        </div>
                      </button>

                      <!-- Enhanced Additional Plans Section -->
                      <div v-if="hasAdditionalPlans(tier)" class="space-y-3">
                        <button 
                          @click.prevent="toggleAdditionalPlans(tier.id)"
                          class="w-full flex items-center justify-between px-4 py-3 rounded-xl bg-gray-100/60 dark:bg-gray-700/60 hover:bg-gray-200/60 dark:hover:bg-gray-600/60 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all duration-200"
                        >
                          <span class="flex items-center gap-2 font-medium">
                            <i class="ri-time-line"></i>
                            Additional Plans
                          </span>
                          <i :class="[
                            expandedTiers.has(tier.id) ? 'ri-arrow-up-s-line' : 'ri-arrow-down-s-line',
                            'text-xl transition-transform duration-200'
                          ]"></i>
                        </button>

                        <!-- Enhanced Additional Plans Content -->
                        <div v-if="expandedTiers.has(tier.id)" class="space-y-3 pl-4">
                          <button 
                            v-for="plan in getAdditionalPlans(tier)"
                            :key="plan.duration"
                            class="w-full px-6 py-4 text-left rounded-2xl text-white transition-all duration-200 hover:scale-[1.02] shadow-lg hover:shadow-xl"
                            :style="{
                              backgroundColor: tier.color_code,
                              '--hover-color': adjustColor(tier.color_code, -10),
                            }"
                            @click.prevent="subscribeTier(tier, plan.duration)"
                            @mouseenter="e => e.target.style.backgroundColor = adjustColor(tier.color_code, -10)"
                            @mouseleave="e => e.target.style.backgroundColor = tier.color_code"
                          >
                            <div class="flex justify-between items-center">
                              <div class="flex items-center gap-3">
                                <i class="ri-time-line text-lg"></i>
                                <span class="font-semibold text-lg">{{ tier.title }} {{ plan.duration }} Months</span>
                              </div>
                              <div class="flex items-center gap-3">
                                <span class="font-bold text-xl">${{ plan.price }}</span>
                                <span class="text-sm bg-white/20 px-3 py-1 rounded-full font-semibold">
                                  {{ calculateDiscount(tier, plan.duration) }}% Off!
                                </span>
                              </div>
                            </div>
                          </button>
                        </div>
                      </div>
                    </div>
                    
                    <!-- Enhanced message if all tiers are already subscribed -->
                    <div v-if="enabledTiers.length > 0 && availableTiers.length === 0" class="text-center py-8">
                      <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                        <i class="ri-checkbox-circle-fill text-green-500 text-2xl"></i>
                      </div>
                      <p class="text-gray-600 dark:text-gray-300 text-lg font-medium">
                        {{ $t('already_subscribed_all_tiers') }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'
import { useSubscriptionStore } from '@/stores/subscriptionStore'
import { useToast } from 'vue-toastification'
import { useTrackingLink } from '@/composables/useTrackingLink'

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  },
  creatorName: {
    type: String,
    default: ''
  },
  requiredPermissions: {
    type: Array,
    default: () => []
  },
  postOwnerId: {
    type: String,
    required: true
  },
  postId: {
    type: String,
    required: true
  },
  entityType: {
    type: String,
    default: 'App\\Models\\Post'
  },
  messageId: {
    type: String,
    default: null
  }
})

const emit = defineEmits(['close', 'follow', 'subscribe', 'pay'])



// Debug logging for props
console.log('🔍 UnlockBundleModal: Props received:', {
  isOpen: props.isOpen,
  creatorName: props.creatorName,
  requiredPermissions: props.requiredPermissions,
  postOwnerId: props.postOwnerId,
  postId: props.postId,
  entityType: props.entityType,
  messageId: props.messageId
})

const subscriptionStore = useSubscriptionStore()
const toast = useToast()
const { trackSubscription, trackPurchase } = useTrackingLink()

const expandedTiers = ref(new Set())
const isProcessing = ref(false)
const userSubscriptions = ref([])
const showSubscriptionDetails = ref(false)

// Debug the required permissions
const debugRequiredPermissions = () => {
  console.log('Required permissions:', props.requiredPermissions)
  if (props.requiredPermissions && Array.isArray(props.requiredPermissions)) {
    props.requiredPermissions.forEach((group, i) => {
      if (Array.isArray(group)) {
        group.forEach((perm, j) => {
          console.log(`Permission [${i}][${j}]:`, perm)
        })
      }
    })
  }
}

// Computed properties to determine what to show based on permission sets
const permissionSets = computed(() => {
  if (!props.requiredPermissions || !Array.isArray(props.requiredPermissions)) {
    console.error('Invalid requiredPermissions:', props.requiredPermissions)
    return []
  }
  
  // Filter out any permission sets where all permissions are already met
  return props.requiredPermissions.filter(permissionSet => {
    if (!Array.isArray(permissionSet)) return false
    
    // Only include sets where at least one permission is not met
    return permissionSet.some(p => !p.is_met)
  })
})

// Find the best permission set for the user (one with fewest unmet permissions or lowest price)
const bestPermissionSet = computed(() => {
  if (permissionSets.value.length === 0) return null
  
  // First, try to find a set where the user only needs to pay (already subscribed/following)
  const paymentOnlySets = permissionSets.value.filter(set => {
    // Check if all non-payment permissions are met
    const nonPaymentPermissions = set.filter(p => p.type !== 'add_price')
    const allNonPaymentMet = nonPaymentPermissions.every(p => p.is_met)
    
    // And there's only a payment permission left unmet
    const paymentPermissions = set.filter(p => p.type === 'add_price' && !p.is_met)
    
    return allNonPaymentMet && paymentPermissions.length > 0
  })
  
  if (paymentOnlySets.length > 0) {
    // Find the set with the lowest payment amount
    return paymentOnlySets.reduce((best, current) => {
      const bestPrice = parseFloat(best.find(p => p.type === 'add_price' && !p.is_met)?.value || '9999')
      const currentPrice = parseFloat(current.find(p => p.type === 'add_price' && !p.is_met)?.value || '9999')
      return currentPrice < bestPrice ? current : best
    }, paymentOnlySets[0])
  }
  
  // If no payment-only sets, find the set with the fewest unmet permissions
  return permissionSets.value.reduce((best, current) => {
    const bestUnmetCount = best.filter(p => !p.is_met).length
    const currentUnmetCount = current.filter(p => !p.is_met).length
    return currentUnmetCount < bestUnmetCount ? current : best
  }, permissionSets.value[0])
})

const showSubscriptionOptions = computed(() => {
  if (!bestPermissionSet.value) return false
  
  // Check if the best permission set has an unmet subscription requirement
  return bestPermissionSet.value.some(p => p.type === 'subscribed_all_tiers' && !p.is_met)
})

const showFollowOption = computed(() => {
  if (!bestPermissionSet.value) return false
  
  // Check if the best permission set has an unmet follow requirement
  return bestPermissionSet.value.some(p => p.type === 'following' && !p.is_met)
})

const showPaymentOption = computed(() => {
  if (!bestPermissionSet.value) return false
  
  // Check if the best permission set has an unmet payment requirement
  return bestPermissionSet.value.some(p => p.type === 'add_price' && !p.is_met)
})

// Updated requiredAmount computed property to get the payment amount from the best permission set
const requiredAmount = computed(() => {
  if (!bestPermissionSet.value) return '0.00'
  
  // Find the payment permission in the best set
  const paymentPermission = bestPermissionSet.value.find(p => p.type === 'add_price' && !p.is_met)
  return paymentPermission ? paymentPermission.value || '0.00' : '0.00'
})

// Format the required amount for display
const displayRequiredAmount = computed(() => {
  const amount = parseFloat(requiredAmount.value);
  return amount ? amount.toFixed(2) : '0.00';
})

const enabledTiers = computed(() => subscriptionStore.enabledTiers);

// Filter out tiers that the user is already subscribed to
const availableTiers = computed(() => {
  if (!enabledTiers.value || !userSubscriptions.value) {
    return enabledTiers.value || [];
  }
  
  // Filter out tiers that the user is already subscribed to
  return enabledTiers.value.filter(tier => {
    const isSubscribed = userSubscriptions.value.some(
      sub => sub.tier_id === tier.id && sub.status === 'completed'
    );
    return !isSubscribed;
  });
})

const getAdditionalPlans = (tier) => {
  return [
    { duration: 2, price: tier.two_month_price },
    { duration: 3, price: tier.three_month_price },
    { duration: 6, price: tier.six_month_price }
  ].filter(plan => plan.price > 0);
}

// Fetch tiers for the post owner
const fetchTiers = async () => {
  console.log('Fetching tiers for postOwnerId:', props.postOwnerId)
  try {
    showSubscriptionDetails.value = true;
    const { success, tiers } = await subscriptionStore.getUserActiveTiers(props.postOwnerId)
    console.log('Fetch tiers result:', success, tiers)
    
    // Also fetch user's current subscriptions
    await fetchUserSubscriptions();
  } catch (error) {
    console.error('Error fetching tiers:', error)
    toast.error('Failed to load subscription tiers')
  }
}

// Fetch user's current subscriptions
const fetchUserSubscriptions = async () => {
  try {
    // Assuming there's a method in the subscription store to get user subscriptions
    // If not, you'll need to add this method to your store
    const result = await subscriptionStore.fetchUserSubscriptions();
    userSubscriptions.value = result.data || [];
    console.log('User subscriptions:', userSubscriptions.value);
  } catch (error) {
    console.error('Error fetching user subscriptions:', error);
    // Don't show an error toast here, as this is a background operation
  }
}

// Handle subscription
const subscribeTier = async (tier, duration) => {
  try {
    isProcessing.value = true;
    
    // Check if user is already subscribed to this tier
    const isAlreadySubscribed = userSubscriptions.value.some(
      sub => sub.tier_id === tier.id && sub.status === 'completed'
    );
    
    if (isAlreadySubscribed) {
      toast.info("You are already subscribed to this tier");
      isProcessing.value = false;
      return;
    }
    
    // Log the data being sent to the server
    console.log('Subscribing to tier:', {
      tierId: tier.id,
      duration: duration
    });

    const result = await subscriptionStore.subscribeTier(tier.id, duration)
    console.log('Subscription result:', result);
    
    if (result.success) {
      // Track the subscription
      await trackSubscription(tier.id, duration)
      toast.success(result.message || "Payment processed successfully");
      // Emit the subscribe event to refresh content
      emit('subscribe', { tierId: tier.id, duration, postOwnerId: props.postOwnerId });
      // Refresh the page to update permission checks
      setTimeout(() => {
        window.location.reload();
      }, 1500);
      handleClose();
    } else {
      // Handle specific error cases
      if (result.error === 'insufficient_balance') {
        toast.error("Insufficient wallet balance. Please add funds to your wallet.");
      } else {
        toast.error(result.error || 'Failed to process subscription');
      }
    }
  } catch (error) {
    console.error('Error in subscribeTier:', error);
    toast.error('Failed to process subscription');
  } finally {
    isProcessing.value = false;
  }
}

// Handle follow
const handleFollow = () => {
  emit('follow', props.postOwnerId)
  handleClose()
}

// Updated handlePayment to use the post ID from props and handle different entity types
const handlePayment = async () => {
  console.log('handlePayment method called');
  try {
    isProcessing.value = true;
    
    const amount = parseFloat(requiredAmount.value)
    console.log('Amount:', amount);
    if (!amount) {
      toast.error(`Invalid amount`)
      isProcessing.value = false;
      return;
    }
    
    // Try to get the post ID from props first
    let entityId = props.postId;
    console.log('Entity ID from props:', entityId);
    
    // If entity ID is not provided directly, try to extract it from the required permissions
    if (!entityId) {
      console.log('Entity ID not provided directly, trying to extract from permissions');
      
      // Look through all permission groups for a payment requirement that might have an entity ID
      props.requiredPermissions.forEach((permissionGroup, groupIndex) => {
        if (Array.isArray(permissionGroup)) {
          permissionGroup.forEach((p, permIndex) => {
            console.log(`Examining permission [${groupIndex}][${permIndex}]:`, p);
            
            // Check for entity_id in various places
            if (p.type === 'add_price' && !p.is_met) {
              // Check direct post_id property
              if (p.post_id) {
                entityId = p.post_id;
                console.log('Found entity ID in permission.post_id:', entityId);
              }
              
              // Check data property
              if (p.data && p.data.post_id) {
                entityId = p.data.post_id;
                console.log('Found entity ID in permission.data.post_id:', entityId);
              }
              
              // Check entity_id property
              if (p.entity_id) {
                entityId = p.entity_id;
                console.log('Found potential entity ID in permission.entity_id:', entityId);
              }
              
              // Check context property
              if (p.context && p.context.post_id) {
                entityId = p.context.post_id;
                console.log('Found entity ID in permission.context.post_id:', entityId);
              }
            }
          });
        }
      });
      
      // If we still don't have an entity ID, try to get it from the current URL
      if (!entityId) {
        console.log('Trying to extract entity ID from URL:', window.location.pathname);
        const urlParts = window.location.pathname.split('/');
        console.log('URL parts:', urlParts);
        
        // Try to find a numeric ID in the URL
        for (let i = urlParts.length - 1; i >= 0; i--) {
          if (/^\d+$/.test(urlParts[i])) {
            entityId = urlParts[i];
            console.log('Found numeric ID in URL part:', entityId);
            break;
          }
        }
      }
    }
    
    // Check if we have an entity ID
    console.log('Final Entity ID:', entityId);
    if (!entityId) {
      toast.error('Could not determine entity ID for purchase');
      isProcessing.value = false;
      return;
    }
    
    console.log('Processing purchase:', {
      entityId: entityId,
      entityType: props.entityType,
      receiverId: props.postOwnerId,
      amount: amount
    });
    
    // Use the appropriate store method based on entity type
    let result;
    if (props.entityType === 'App\\Models\\Message') {
      console.log('Calling subscriptionStore.purchaseMessage');
      result = await subscriptionStore.purchaseMessage(entityId, props.postOwnerId, amount);
    } else {
      console.log('Calling subscriptionStore.purchasePost');
      result = await subscriptionStore.purchasePost(entityId, props.postOwnerId, amount);
    }
    
    console.log('Purchase result:', result);
    
    if (result.success) {
      toast.success('Payment processed successfully');
      
      // Check if redirect is required for external payment
      if (result.data?.redirect_required && result.data?.payment_url) {
        window.open(result.data.payment_url, "_blank");
        toast.info("Please complete your payment on the payment page");
      } else {
        // Payment was processed immediately (wallet or test mode)
        toast.success("Content unlocked successfully!");
        
        // Refresh the page to update permission checks
        setTimeout(() => {
          window.location.reload();
        }, 1500);
      }
      
      handleClose();
    } else {
      // Handle specific error cases
      if (result.error === 'insufficient_balance') {
        toast.error("Insufficient wallet balance. Please add funds to your wallet.");
      } else {
        toast.error(result.message || 'Payment failed');
      }
    }
  } catch (error) {
    console.error('Error in handlePayment:', error);
    toast.error('Failed to process payment');
  } finally {
    isProcessing.value = false;
  }
}

// New method to handle payment with a specific amount
const handlePaymentWithAmount = async (amount) => {
  try {
    isProcessing.value = true;
    
    // Get the entity ID based on the type
    const entityId = props.entityType === 'App\\Models\\Message' 
      ? props.messageId 
      : props.postId;
    
    if (!entityId) {
      toast.error('Could not determine entity ID for purchase');
      isProcessing.value = false;
      return;
    }
    
    // Use the appropriate store method based on entity type
    let result;
    if (props.entityType === 'App\\Models\\Message') {
      result = await subscriptionStore.purchaseMessage(entityId, props.postOwnerId, amount);
    } else {
      result = await subscriptionStore.purchasePost(entityId, props.postOwnerId, amount);
    }
    
    if (result.success) {
      // Track the purchase
      await trackPurchase(entityId, props.entityType, amount);
      
      // Check if redirect is required for external payment
      if (result.data?.redirect_required && result.data?.payment_url) {
        window.open(result.data.payment_url, "_blank");
        toast.info("Please complete your payment on the payment page");
      } else {
        // Payment was processed immediately (wallet or test mode)
        toast.success("Content unlocked successfully!");
        
        // Refresh the page to update permission checks
        setTimeout(() => {
          window.location.reload();
        }, 1500);
      }
      
      handleClose();
    } else {
      // Handle specific error cases
      if (result.error === 'insufficient_balance') {
        toast.error("Insufficient wallet balance. Please add funds to your wallet.");
      } else {
        toast.error(result.message || 'Payment failed');
      }
    }
  } catch (error) {
    console.error('Error in handlePaymentWithAmount:', error);
    toast.error('Failed to process payment');
  } finally {
    isProcessing.value = false;
  }
}

const handleClose = () => {
  emit('close')
}

const adjustColor = (hex, percent) => {
  hex = hex.replace('#', '')
  let r = parseInt(hex.substring(0, 2), 16)
  let g = parseInt(hex.substring(2, 4), 16)
  let b = parseInt(hex.substring(4, 6), 16)
  r = Math.max(0, Math.min(255, r + (r * percent / 100)))
  g = Math.max(0, Math.min(255, g + (g * percent / 100)))
  b = Math.max(0, Math.min(255, b + (b * percent / 100)))
  return '#' + 
    Math.round(r).toString(16).padStart(2, '0') +
    Math.round(g).toString(16).padStart(2, '0') +
    Math.round(b).toString(16).padStart(2, '0')
}

const hasAdditionalPlans = (tier) => {
  return tier.two_month_price > 0 || tier.three_month_price > 0 || tier.six_month_price > 0
}

const toggleAdditionalPlans = (tierId) => {
  if (expandedTiers.value.has(tierId)) {
    expandedTiers.value.delete(tierId)
  } else {
    expandedTiers.value.add(tierId)
  }
}

const calculateDiscount = (tier, duration) => {
  const basePrice = tier.base_price
  let discountedPrice
  switch (duration) {
    case 2:
      discountedPrice = tier.two_month_price
      break
    case 3:
      discountedPrice = tier.three_month_price
      break
    case 6:
      discountedPrice = tier.six_month_price
      break
    default:
      return 0
  }
  const fullPrice = basePrice * duration
  return Math.round((1 - (discountedPrice / fullPrice)) * 100)
}

// Method to get all available permission options for the user
const allPermissionOptions = computed(() => {
  if (!props.requiredPermissions || !Array.isArray(props.requiredPermissions)) {
    return []
  }
  
  // Process each permission set to create user-friendly options
  return props.requiredPermissions.map((set, index) => {
    if (!Array.isArray(set)) return null
    
    // Skip sets where all permissions are already met
    if (set.every(p => p.is_met)) return null
    
    const option = {
      id: `option-${index}`,
      requirements: [],
      price: null,
      allMet: set.every(p => p.is_met),
      isRecommended: false
    }
    
    // Process each permission in the set
    set.forEach(p => {
      if (p.type === 'subscribed_all_tiers') {
        option.requirements.push({
          type: 'subscription',
          met: p.is_met,
          label: p.is_met ? 'Already subscribed' : 'Subscribe to any tier'
        })
      } else if (p.type === 'following') {
        option.requirements.push({
          type: 'follow',
          met: p.is_met,
          label: p.is_met ? 'Already following' : 'Follow creator'
        })
      } else if (p.type === 'add_price') {
        option.price = parseFloat(p.value || '0')
        option.requirements.push({
          type: 'payment',
          met: p.is_met,
          label: p.is_met ? 'Already paid' : `Pay $${parseFloat(p.value || '0').toFixed(2)}`
        })
      }
    })
    
    return option
  }).filter(Boolean) // Remove null entries
})

// Determine which option is recommended (lowest price or fewest steps)
const recommendedOption = computed(() => {
  if (allPermissionOptions.value.length === 0) return null
  
  // First, check if any options only require payment (all other requirements met)
  const paymentOnlyOptions = allPermissionOptions.value.filter(option => {
    const unmetRequirements = option.requirements.filter(req => !req.met)
    return unmetRequirements.length === 1 && unmetRequirements[0].type === 'payment'
  })
  
  if (paymentOnlyOptions.length > 0) {
    // Find the option with the lowest price
    return paymentOnlyOptions.reduce((best, current) => {
      return (current.price < best.price) ? current : best
    }, paymentOnlyOptions[0])
  }
  
  // If no payment-only options, find the option with the fewest unmet requirements
  return allPermissionOptions.value.reduce((best, current) => {
    const bestUnmetCount = best.requirements.filter(req => !req.met).length
    const currentUnmetCount = current.requirements.filter(req => !req.met).length
    
    if (currentUnmetCount < bestUnmetCount) return current
    
    // If same number of unmet requirements, prefer the one with lower price
    if (currentUnmetCount === bestUnmetCount && current.price < best.price) return current
    
    return best
  }, allPermissionOptions.value[0])
})

onMounted(() => {
  console.log('UnlockBundleModal mounted')
  console.log('Props:', props)
  debugRequiredPermissions()
  if (showSubscriptionOptions.value) {
    console.log('Calling fetchTiers on mount')
    fetchTiers()
  }
})

// Add a watcher for isOpen prop
watch(() => props.isOpen, (newValue) => {
  console.log('isOpen changed:', newValue)
  if (newValue) {
    debugRequiredPermissions()
    if (showSubscriptionOptions.value) {
      console.log('Calling fetchTiers on isOpen change')
      fetchTiers()
    }
  }
})

// Add a watcher for requiredPermissions
watch(() => props.requiredPermissions, (newValue) => {
  console.log('requiredPermissions changed:', newValue)
  debugRequiredPermissions()
}, { deep: true })

// Add a watcher for enabledTiers
watch(enabledTiers, (newValue) => {
  console.log('enabledTiers changed:', newValue)
})
</script>

<style scoped>
/* Enhanced animations */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideInFromBottom {
  from {
    opacity: 0;
    transform: translateY(100%);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes scaleIn {
  from {
    opacity: 0;
    transform: scale(0.9);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
}

/* Apply animations to elements */
.animate-fadeInUp {
  animation: fadeInUp 0.6s ease-out;
}

.animate-slideInFromBottom {
  animation: slideInFromBottom 0.5s ease-out;
}

.animate-scaleIn {
  animation: scaleIn 0.4s ease-out;
}

.animate-pulse {
  animation: pulse 2s infinite;
}

/* Enhanced focus states */
button:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
  border-radius: 0.5rem;
}

/* Smooth transitions for all interactive elements */
* {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Enhanced scrollbar styling */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.5);
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: rgba(156, 163, 175, 0.7);
}

/* Dark mode scrollbar */
.dark ::-webkit-scrollbar-thumb {
  background: rgba(75, 85, 99, 0.5);
}

.dark ::-webkit-scrollbar-thumb:hover {
  background: rgba(75, 85, 99, 0.7);
}

/* Glassmorphism effects */
.glass {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.dark .glass {
  background: rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

/* Enhanced button hover effects */
.btn-hover {
  position: relative;
  overflow: hidden;
}

.btn-hover::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.btn-hover:hover::before {
  left: 100%;
}

/* Enhanced loading spinner */
.spinner {
  border: 2px solid rgba(156, 163, 175, 0.3);
  border-top: 2px solid #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Enhanced tooltip */
.tooltip {
  position: relative;
}

.tooltip::before {
  content: attr(data-tooltip);
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(0, 0, 0, 0.9);
  color: white;
  padding: 0.5rem 0.75rem;
  border-radius: 0.5rem;
  font-size: 0.75rem;
  white-space: nowrap;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.2s;
  z-index: 10;
}

.tooltip:hover::before {
  opacity: 1;
}

/* Enhanced focus ring for accessibility */
.focus-ring:focus {
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
}

/* Enhanced gradient text */
.gradient-text {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Enhanced shadow effects */
.shadow-soft {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.shadow-medium {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.shadow-strong {
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Enhanced modal card hover effects */
.modal-card {
  position: relative;
  overflow: hidden;
}

.modal-card::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
  opacity: 0;
  transition: opacity 0.3s;
}

.modal-card:hover::after {
  opacity: 1;
}

/* Enhanced verified badge */
.verified-badge {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: white;
  font-weight: 600;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

/* Enhanced action buttons */
.action-button {
  position: relative;
  overflow: hidden;
}

.action-button::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.action-button:hover::before {
  left: 100%;
}

/* Enhanced disabled state */
.disabled-button {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none !important;
}

.disabled-button:hover {
  transform: none !important;
  box-shadow: none !important;
}

/* Enhanced permission option cards */
.permission-option {
  position: relative;
  overflow: hidden;
}

.permission-option::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
  opacity: 0;
  transition: opacity 0.3s;
}

.permission-option:hover::after {
  opacity: 1;
}

/* Enhanced requirement items */
.requirement-item {
  position: relative;
  overflow: hidden;
}

.requirement-item::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
  opacity: 0;
  transition: opacity 0.3s;
}

.requirement-item:hover::after {
  opacity: 1;
}

/* Enhanced subscription tier cards */
.subscription-tier {
  position: relative;
  overflow: hidden;
}

.subscription-tier::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
  opacity: 0;
  transition: opacity 0.3s;
}

.subscription-tier:hover::after {
  opacity: 1;
}

/* Enhanced close button */
.close-button {
  position: relative;
  overflow: hidden;
}

.close-button::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.close-button:hover::before {
  left: 100%;
}

/* Enhanced unlock icon */
.unlock-icon {
  background: var(--primary-light);
  box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.2);
  animation: pulse 2s infinite;
}

/* Enhanced recommended badge */
.recommended-badge {
  background: var(--primary-light);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  animation: pulse 2s infinite;
}

/* Enhanced action buttons */
.action-button {
  position: relative;
  overflow: hidden;
}

.action-button::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.action-button:hover::before {
  left: 100%;
}

/* Enhanced modal backdrop */
.modal-backdrop {
  backdrop-filter: blur(8px);
  background: rgba(0, 0, 0, 0.6);
}

/* Enhanced modal content */
.modal-content {
  backdrop-filter: blur(20px);
  background: rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.dark .modal-content {
  background: rgba(0, 0, 0, 0.9);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

/* Enhanced loading state */
.loading-state {
  background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
  animation: pulse 2s infinite;
}

.dark .loading-state {
  background: linear-gradient(135deg, #374151, #4b5563);
}

/* Enhanced success state */
.success-state {
  background: linear-gradient(135deg, #d1fae5, #a7f3d0);
  animation: pulse 2s infinite;
}

.dark .success-state {
  background: linear-gradient(135deg, #065f46, #047857);
}
</style>

