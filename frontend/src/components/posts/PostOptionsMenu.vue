<template>
  <Menu as="div" class="relative">
    <MenuButton class="p-2 rounded-full bg-gray-100/80 dark:bg-gray-700/80 hover:bg-gray-200/80 dark:hover:bg-gray-600/80 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all duration-200">
      <i class="ri-more-fill text-xl"></i>
    </MenuButton>

    <transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="transform scale-95 opacity-0"
      enter-to-class="transform scale-100 opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="transform scale-100 opacity-100"
      leave-to-class="transform scale-95 opacity-0"
    >
      <MenuItems class="absolute right-0 mt-3 w-64 origin-top-right rounded-2xl bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl border border-white/30 dark:border-gray-600/30 shadow-2xl focus:outline-none z-[9999] max-h-96 overflow-y-auto" style="transform-origin: top right;">
        <div class="py-2">
          <MenuItem v-for="(item, index) in filteredMenuItems" :key="index" v-slot="{ active }">
            <button
              @click="handleMenuItemClick(item.action)"
              :class="[
                active ? 'bg-gray-100/80 dark:bg-gray-700/80' : '',
                'group flex w-full items-center px-4 py-3 text-sm text-gray-900 dark:text-white hover:bg-gray-100/80 dark:hover:bg-gray-700/80 transition-all duration-200',
                index !== filteredMenuItems.length - 1 ? 'border-b border-gray-200/50 dark:border-gray-600/50' : ''
              ]"
            >
              <i :class="[item.icon, 'mr-3 text-lg text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors duration-200']"></i>
              <span class="font-medium">{{ item.label }} {{ item.includeUsername ? truncatedUsername : '' }}</span>
              <i v-if="item.verified" class="ri-verified-badge-fill ml-auto text-blue-500"></i>
            </button>
          </MenuItem>
        </div>
      </MenuItems>
    </transition>
  </Menu>
</template>

<script setup>
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { computed, onMounted } from 'vue'
import { useFeedStore } from '@/stores/feedStore'
import { useListStore } from '@/stores/listStore'
import { useAuthStore } from '@/stores/authStore'
import { useToast } from 'vue-toastification'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  userId: {
    type: [Number, String],
    required: true
  },
  username: {
    type: String,
    required: true
  },
  postCreatorRole: {
    type: String,
    default: 'user'
  },
  isOwnPost: {
    type: Boolean,
    default: false
  },
  postPinned: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits([
  'unfollow',
  'subscribe',
  'copyLink',
  'report',
  'vip',
  'mute',
  'block',
  'pin',
  'unpin'
])

const feedStore = useFeedStore()
const listStore = useListStore()
const authStore = useAuthStore()
const toast = useToast()
const { t } = useI18n()

onMounted(async () => {
  try {
    await listStore.fetchLists()
  } catch (error) {
    console.error('Error fetching lists:', error)
  }
})

const truncatedUsername = computed(() => {
  if (props.username.length > 5) {
    return `${props.username.slice(0, 5)}...`
  }
  return props.username
})

// Check if current user is a creator and post creator is also a creator
const shouldHideCreatorOptions = computed(() => {
  const currentUserRole = authStore.user?.role
  return currentUserRole === 'creator' && props.postCreatorRole === 'creator'
})

const allMenuItems = [
  { label: t('add_to_list'), icon: 'ri-add-line', action: 'addToList', verified: true, includeUsername: true },
  { label: t('unfollow'), icon: 'ri-user-unfollow-line', action: 'unfollow', verified: true, includeUsername: true },
  { label: t('subscribe_to'), icon: 'ri-vip-crown-line', action: 'subscribe', verified: true, includeUsername: true },
  { label: t('copy_post_link'), icon: 'ri-link', action: 'copyLink', includeUsername: false },
  { label: t('report_post'), icon: 'ri-flag-line', action: 'report', includeUsername: false },
  { label: t('vip'), icon: 'ri-vip-diamond-line', action: 'vip', verified: true, includeUsername: true },
  { label: t('mute'), icon: 'ri-volume-mute-line', action: 'mute', verified: true, includeUsername: true },
  { label: t('block'), icon: 'ri-forbid-line', action: 'block', verified: true, includeUsername: true },
]

const ownPostMenuItems = computed(() => {
  const items = [
    { label: t('copy_post_link'), icon: 'ri-link', action: 'copyLink', includeUsername: false },
    { label: t('edit_post'), icon: 'ri-edit-line', action: 'edit', includeUsername: false },
    { label: t('delete_post'), icon: 'ri-delete-bin-line', action: 'delete', includeUsername: false },
  ]
  
  // Add pin/unpin based on current state
  if (props.postPinned) {
    items.unshift({ label: t('unpin_post'), icon: 'ri-pushpin-unfill', action: 'unpin', includeUsername: false })
  } else {
    items.unshift({ label: t('pin_post'), icon: 'ri-pushpin-line', action: 'pin', includeUsername: false })
  }
  
  return items
})

const filteredMenuItems = computed(() => {
  // If it's the user's own post, show own post menu items
  if (props.isOwnPost) {
    return ownPostMenuItems.value
  }
  
  // For other users' posts, filter based on creator options
  if (shouldHideCreatorOptions.value) {
    // Filter out 'unfollow', 'subscribe_to', and 'vip' options
    return allMenuItems.filter(item => 
      !['unfollow', 'subscribe', 'vip'].includes(item.action)
    )
  }
  return allMenuItems
})

const handleMenuItemClick = async (action) => {
  try {
    switch (action) {
      case 'addToList':
        try {
          // First, let's check if user has any custom lists
          await listStore.fetchLists()
          const customLists = listStore.lists.filter(list => !list.is_default)
          
          if (customLists.length === 0) {
            // If no custom lists, create a default "Favorites" list
            const newList = await listStore.createList({
              name: 'Favorites',
              description: 'My favorite users'
            })
            // Add user to the newly created list
            const result = await listStore.addMemberToList(newList.id, props.userId)
            if (result.message) {
              toast.success('User added to Favorites list')
            } else {
              toast.error('Failed to add user to list')
            }
          } else {
            // If user has custom lists, add to the first one
            const firstList = customLists[0]
            const result = await listStore.addMemberToList(firstList.id, props.userId)
            if (result.message) {
              toast.success(`User added to ${firstList.name} list`)
            } else {
              toast.error('Failed to add user to list')
            }
          }
        } catch (error) {
          console.error('Error adding user to list:', error)
          toast.error('Failed to add user to list')
        }
        break

      case 'vip':
        try {
          const result = await listStore.addToVipList(props.userId)
          if (result.message) {
            toast.success('User added to VIP list')
            emit('vip', props.userId)
          } else {
            toast.error(result.message || 'Failed to add user to VIP list')
          }
        } catch (error) {
          toast.error('Failed to add user to VIP list')
        }
        break

      case 'mute':
        try {
          const result = await listStore.addToMutedList(props.userId)
          if (result.message) {
            toast.success('User muted successfully')
            emit('mute', props.userId)
          } else {
            toast.error(result.message || 'Failed to mute user')
          }
        } catch (error) {
          toast.error('Failed to mute user')
        }
        break

      case 'block':
        try {
          const result = await listStore.addToBlockedList(props.userId)
          if (result.message) {
            toast.success('User blocked successfully')
            emit('block', props.userId)
          } else {
            toast.error(result.message || 'Failed to block user')
          }
        } catch (error) {
          toast.error('Failed to block user')
        }
        break

      case 'unfollow':
        result = await feedStore.unfollowUser(props.userId)
        if (result.success) {
          toast.success(result.message)
          emit('unfollow', props.userId)
        } else {
          toast.error(result.error)
        }
        break

      case 'pin':
        emit('pin', props.userId)
        break

      case 'unpin':
        emit('unpin', props.userId)
        break

      case 'edit':
        emit('edit', props.userId)
        break

      case 'delete':
        emit('delete', props.userId)
        break

      default:
        emit(action, props.userId)
    }
  } catch (error) {
    console.error('Error handling menu action:', error)
    toast.error('An error occurred while processing your request')
  }
}
</script>