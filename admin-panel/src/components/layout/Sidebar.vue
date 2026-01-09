<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'

// Props and emits
const props = defineProps<{
  isOpen: boolean
}>()

const emit = defineEmits<{
  (e: 'toggle'): void
}>()

const router = useRouter()
const route = useRoute()

// Track which menus are expanded
const expandedMenus = ref<Set<string>>(new Set(['Creator', 'Financial', 'Communication']))

// Close sidebar on mobile after navigation
const handleNavigation = async (path: string) => {
  await router.push(path)
  emit('toggle') // Always emit toggle event, parent component will handle mobile check
}

// Toggle submenu expansion
const toggleSubmenu = (menuName: string, event: Event) => {
  event.stopPropagation() // Prevent event from bubbling up
  if (expandedMenus.value.has(menuName)) {
    expandedMenus.value.delete(menuName)
  } else {
    expandedMenus.value.add(menuName)
  }
}

// Check if a route is active
const isActive = (path: string) => {
  if (path === '/') {
    return route.path === path
  }
  return route.path.startsWith(path)
}

// Navigation data - updated to match the image
const navigation = [
  { 
    name: 'Dashboard', 
    path: '/', 
    icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'
  },
  { 
    name: 'Users', 
    path: '/users', 
    icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'
  },
  { 
    name: 'Creator', 
    path: '/creator', 
    icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
    submenu: [
      { name: 'Applications', path: '/creator/applications' },
      { name: 'Tier Management', path: '/creator/tiers' }
    ]
  },
  { 
    name: 'Content', 
    path: '/content', 
    icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
  },
  { 
    name: 'Media', 
    path: '/media', 
    icon: 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'
  },
  { 
    name: 'Financial', 
    path: '/financial', 
    icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    submenu: [
      { name: 'Platform Wallet', path: '/financial/platform-wallet' },
      { name: 'Transactions', path: '/financial/transactions' }
    ]
  },
  { 
    name: 'Settings', 
    path: '/settings', 
    icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'
  },
  { 
    name: 'Communication', 
    path: '/communication', 
    icon: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
    submenu: [
      { name: 'Messages', path: '/communication/messages' }
    ]
  }
]

// Ensure useRoute is called only once
let currentRoutePath: string;

onMounted(() => {
  currentRoutePath = route.path;
});

// Check if a subroute is active
const isSubActive = (path: string) => {
  return currentRoutePath?.startsWith(path) || false;
}
</script>

<template>
  <aside
    class="sidebar"
    :class="{ 'sidebar-open': isOpen, 'sidebar-closed': !isOpen }"
  >
    <!-- Header -->
    <div class="sidebar-header">
      <h1 class="sidebar-title">Admin Panel</h1>
    </div>
    
    <!-- Divider -->
    <div class="sidebar-divider"></div>

    <!-- Navigation -->
    <div class="sidebar-nav-container">
      <nav class="sidebar-nav">
        <ul class="sidebar-menu">
          <!-- Menu Items -->
          <li v-for="item in navigation" :key="item.name" class="sidebar-menu-item">
            <!-- Main menu item -->
            <a 
              :href="item.path"
              @click.prevent="item.submenu ? toggleSubmenu(item.name, $event) : handleNavigation(item.path)"
              class="sidebar-link"
              :class="{ 
                'active': item.path === '/' ? route.path === '/' : isActive(item.path),
                'has-submenu': item.submenu
              }"
            >
              <!-- Icon -->
              <svg 
                class="sidebar-icon" 
                xmlns="http://www.w3.org/2000/svg" 
                fill="none" 
                viewBox="0 0 24 24" 
                stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
              </svg>
              
              <!-- Menu Text -->
              <span class="sidebar-text">{{ item.name }}</span>
            </a>

            <!-- Submenu -->
            <ul 
              v-if="item.submenu" 
              class="sidebar-submenu"
              :class="{ 'expanded': expandedMenus.has(item.name) }"
            >
              <li v-for="subItem in item.submenu" :key="subItem.name" class="sidebar-submenu-item">
                <a 
                  :href="subItem.path"
                  @click.prevent="handleNavigation(subItem.path)"
                  class="sidebar-sublink"
                  :class="{ 'active': isSubActive(subItem.path) }"
                >
                  {{ subItem.name }}
                </a>
              </li>
            </ul>
          </li>
        </ul>
      </nav>
    </div>
  </aside>
</template>

<style scoped>
/* Base sidebar styles */
.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  width: 260px;
  display: flex;
  flex-direction: column;
  background-color: white;
  z-index: 50;
  transform: translateX(-100%);
  transition: transform 0.3s ease-in-out;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
}

.sidebar-open {
  transform: translateX(0);
}

.sidebar-closed {
  transform: translateX(-100%);
}

/* Header styles */
.sidebar-header {
  padding: 1.25rem;
}

.sidebar-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1F2937;
}

/* Divider */
.sidebar-divider {
  height: 1px;
  background-color: #E5E7EB;
  margin: 0;
}

/* Navigation container - enables scrolling */
.sidebar-nav-container {
  flex: 1;
  overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: rgba(156, 163, 175, 0.3) transparent;
}

.sidebar-nav-container::-webkit-scrollbar {
  width: 4px;
}

.sidebar-nav-container::-webkit-scrollbar-track {
  background: transparent;
}

.sidebar-nav-container::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.3);
  border-radius: 4px;
}

.sidebar-nav-container::-webkit-scrollbar-thumb:hover {
  background: rgba(156, 163, 175, 0.5);
}

/* Navigation styles */
.sidebar-nav {
  padding: 0.5rem 0;
}

.sidebar-menu {
  list-style: none;
  padding: 0;
  margin: 0;
}

.sidebar-menu-item {
  margin-bottom: 0.25rem;
}

/* Link styles */
.sidebar-link {
  display: flex;
  align-items: center;
  padding: 0.75rem 1rem;
  text-decoration: none;
  color: #111827;
  font-weight: 500;
  cursor: pointer;
  width: 100%;
  position: relative;
}

.sidebar-link:hover {
  background-color: #F3F4F6;
}

.sidebar-link.active {
  background-color: #EEF2FF;
  color: #4F46E5;
}

/* Icon styles */
.sidebar-icon {
  width: 1.5rem;
  height: 1.5rem;
  margin-right: 0.75rem;
  color: #6B7280;
  flex-shrink: 0;
}

.sidebar-link.active .sidebar-icon {
  color: #4F46E5;
}

/* Menu text */
.sidebar-text {
  flex-grow: 1;
}

/* Submenu styles */
.sidebar-submenu {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.3s ease-in-out;
  list-style: none;
  padding: 0;
  margin: 0;
}

.sidebar-submenu.expanded {
  max-height: 500px; /* Large enough to fit all submenu items */
}

.sidebar-submenu-item {
  margin: 0;
}

.sidebar-sublink {
  display: block;
  padding: 0.5rem 0.75rem 0.5rem 3.25rem;
  text-decoration: none;
  color: #4B5563;
  font-weight: 400;
  transition: background-color 0.2s;
}

.sidebar-sublink:hover {
  background-color: #F3F4F6;
}

.sidebar-sublink.active {
  background-color: #EEF2FF;
  color: #4F46E5;
}
</style>