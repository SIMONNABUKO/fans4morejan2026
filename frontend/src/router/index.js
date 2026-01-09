import { createRouter, createWebHistory } from "vue-router"
import MainLayout from "@/layouts/MainLayout.vue"
import { useAuthStore } from "@/stores/authStore"
import { useAgeVerificationStore } from "@/stores/ageVerificationStore"
import axiosInstance from '@/axios'
import AuthView from "../views/AuthView.vue"

// Lazy-loaded components with chunk names for better code splitting
const HomeView = () => import(/* webpackChunkName: "content-management" */ "../views/HomeView.vue")
const AgeVerificationView = () => import("../views/AgeVerificationView.vue")
const PreviewsView = () => import("../views/PreviewsView.vue")
const ProfileView = () => import("../views/ProfileView.vue")
const MediaCollectionView = () => import(/* webpackChunkName: "media" */ "../views/MediaCollectionView.vue")
const AlbumView = () => import(/* webpackChunkName: "media" */ "../views/AlbumView.vue")
const SubscriptionsView = () => import(/* webpackChunkName: "creator-dashboard" */ "../views/SubscriptionsView.vue")
const CreatorDashboardView = () => import(/* webpackChunkName: "creator-dashboard" */ "../views/CreatorDashboardView.vue")
const PlansView = () => import(/* webpackChunkName: "creator-dashboard" */ "../views/PlansView.vue")
const SubscribersView = () => import(/* webpackChunkName: "creator-dashboard" */ "../views/SubscribersView.vue")
const ManagementView = () => import("../views/ManagementView.vue")
const UploadsView = () => import(/* webpackChunkName: "media" */ "../views/UploadsView.vue")
const ListsView = () => import("../views/ListsView.vue")
const MessagesView = () => import(/* webpackChunkName: "messaging" */ "../views/MessagesView.vue")
const SingleMessageView = () => import(/* webpackChunkName: "messaging" */ "../views/SingleMessageView.vue")
const MassMessageComposerView = () => import(/* webpackChunkName: "messaging" */ "../views/MassMessageComposerView.vue")
const PayoutView = () => import("../views/PayoutView.vue")
const FollowersView = () => import("../views/FollowersView.vue")
const VaultView = () => import(/* webpackChunkName: "media" */ "../views/VaultView.vue")
const AlbumDetailView = () => import(/* webpackChunkName: "media" */ "../views/AlbumDetailView.vue")
const BecomeCreatorView = () => import("../views/BecomeCreatorView.vue")
const CreatorApplication = () => import("../views/CreatorApplication.vue")
const TermsOfService = () => import("../views/TOSView.vue")
const PrivacyView = () => import("../views/PrivacyView.vue")
const SingleListView = () => import("../views/SingleListView.vue")
const MessagesSettingsView = () => import(/* webpackChunkName: "messaging" */ "../views/MessagesSettingsView.vue")
const Notifications = ()=> import("../views/Notifications.vue")
const EarningStatistics = ()=> import(/* webpackChunkName: "analytics" */ "../views/EarningStatisticsView.vue")
const ReferralView = () => import('../views/ReferralView.vue')
const TrackingLinksView = () => import("../views/TrackingLinksView.vue")
const SearchView = () => import("../views/SearchView.vue")
const ManagementAccessView = () => import("../views/ManagementAccessView.vue")

// Settings Views
const SettingsView = () => import(/* webpackChunkName: "settings" */ "../views/SettingsView.vue")
const AccountSettingsView = () => import(/* webpackChunkName: "settings" */ "../views/settings/AccountSettingsView.vue")
const PrivacySettingsView = () => import(/* webpackChunkName: "settings" */ "../views/settings/PrivacySettingsView.vue")
const SessionManagementView = () => import(/* webpackChunkName: "settings" */ "../views/settings/SessionManagementView.vue")
const PaymentsSettingsView = () => import(/* webpackChunkName: "settings" */ "../views/settings/PaymentsSettingsView.vue")
const DisplaySettingsView = () => import(/* webpackChunkName: "settings" */ "../views/settings/DisplaySettingsView.vue")
const NotificationSettingsView = () => import(/* webpackChunkName: "settings" */ "../views/settings/NotificationSettingsView.vue")
const ConnectionsSettingsView = () => import(/* webpackChunkName: "settings" */ "../views/settings/ConnectionsSettingsView.vue")
const AboutView = () => import(/* webpackChunkName: "settings" */ "../views/settings/AboutView.vue")

// Direct imports
import UserProfileView from "../views/UserProfileView.vue"
import TopSupporters from "@/views/TopSupporters.vue"

// Authentication guard function
const authGuard = (to, from, next) => {
  const isVerified = localStorage.getItem("age-verified") === "true"
  const isAuthenticated = !!localStorage.getItem("auth_token")
  
  if (!isVerified) {
    next({ name: "age-verification" })
  } else if (!isAuthenticated) {
    next({ name: "auth" })
  } else {
    next()
  }
}

// Public routes (no auth required)
const publicRoutes = [
  {
    path: "/verify-age",
    name: "age-verification",
    component: AgeVerificationView,
  },
  {
    path: "/auth",
    name: "auth",
    component: AuthView,
    meta: { layout: MainLayout },
  },
  {
    path: "/management-access/:token",
    name: "management-access",
    component: ManagementAccessView,
  },
  {
    path: "/:username/posts",
    name: "userProfile",
    component: UserProfileView,
  },
]

// Main authenticated routes
const authenticatedRoutes = [
  {
    path: "/",
    name: "home",
    component: HomeView,
    beforeEnter: authGuard,
  },
  {
    path: "/posts/scheduled",
    name: "scheduled-posts",
    component: () => import("../views/ScheduledPostsView.vue"),
    beforeEnter: authGuard,
  },
  {
    path: "/previews",
    name: "previews",
    component: PreviewsView,
    beforeEnter: authGuard,
  },
  {
    path: "/:username",
    name: "profile",
    component: ProfileView,
    props: true,
    beforeEnter: authGuard,
  },
  {
    path: "/:username/followers",
    name: "followers",
    component: FollowersView,
    props: true,
    beforeEnter: authGuard,
  },
  {
    path: "/media",
    name: "media-collection",
    component: MediaCollectionView,
    beforeEnter: authGuard,
  },
  {
    path: "/media/album/:id",
    name: "album-view",
    component: AlbumView,
    props: true,
    beforeEnter: authGuard,
  },
  {
    path: "/subscriptions",
    name: "subscriptions",
    component: SubscriptionsView,
    beforeEnter: authGuard,
  },
  {
    path: "/notifications",
    name: "notifications",
    component: Notifications,
    beforeEnter: authGuard,
  },
  {
    path: "/search",
    name: "search",
    component: SearchView,
    beforeEnter: authGuard,
  },
  {
    path: "/messages",
    name: "messages",
    component: MessagesView,
    beforeEnter: authGuard,
  },
  {
    path: "/messages/:id",
    name: "single-message",
    component: SingleMessageView,
    props: true,
    beforeEnter: authGuard,
  },
  {
    path: "/messages/compose",
    name: "mass-message-compose",
    component: MassMessageComposerView,
    beforeEnter: authGuard,
  },
  {
    path: "/terms",
    name: "terms",
    component: TermsOfService,
    beforeEnter: authGuard,
  },
  {
    path: "/privacy",
    name: "privacy",
    component: PrivacyView,
    beforeEnter: authGuard,
  },
  {
    path: "/referrals",
    name: "referrals",
    component: ReferralView,
    beforeEnter: authGuard,
    meta: { layout: MainLayout },
  },
]

// Creator routes
const creatorRoutes = [
  {
    path: "/become-creator",
    name: "become-creator",
    component: BecomeCreatorView,
    beforeEnter: authGuard,
  },
  {
    path: "/creator-application",
    name: "creator-application",
    component: CreatorApplication,
    beforeEnter: authGuard,
  },
  {
    path: "/dashboard",
    name: "creator-dashboard",
    component: CreatorDashboardView,
    beforeEnter: authGuard,
  },
  {
    path: "/dashboard/plans",
    name: "creator-plans",
    component: PlansView,
    beforeEnter: authGuard,
  },
  {
    path: "/dashboard/earnings",
    name: "earning-statistics",
    component: EarningStatistics,
    beforeEnter: authGuard,
  },
  {
    path: "/dashboard/supporters",
    name: "top-supporters",
    component: TopSupporters,
    beforeEnter: authGuard,
  },
  {
    path: "/dashboard/vault",
    name: "vault",
    component: VaultView,
    beforeEnter: authGuard,
  },
  {
    path: "/dashboard/vault/album/:id",
    name: "album-detail",
    component: AlbumDetailView,
    beforeEnter: authGuard,
  },
  {
    path: "/dashboard/subscribers",
    name: "subscribers",
    component: SubscribersView,
    beforeEnter: authGuard,
  },
  {
    path: "/dashboard/management",
    name: "management",
    component: ManagementView,
    beforeEnter: authGuard,
  },
  {
    path: "/dashboard/uploads",
    name: "uploads",
    component: UploadsView,
    beforeEnter: authGuard,
  },
  {
    path: "/dashboard/lists",
    name: "lists",
    component: ListsView,
    beforeEnter: authGuard,
  },
  {
    path: "/dashboard/lists/:id",
    name: 'single-list',
    component: SingleListView,
    props: true,
    beforeEnter: authGuard,
  },
  {
    path: '/dashboard/statistics',
    name: 'statistics',
    component: () => import('../views/StatisticsView.vue'),
    beforeEnter: authGuard
  },
  {
    path: "/dashboard/messages",
    name: "dashboard-messages",
    component: MessagesSettingsView,
    beforeEnter: authGuard,
  },
  {
    path: "/dashboard/tracking-links",
    name: "tracking-links",
    component: () => import("../views/TrackingLinksView.vue"),
    beforeEnter: authGuard,
  },
  {
    path: "/payout",
    name: "payout",
    component: PayoutView,
    beforeEnter: authGuard,
  },
  {
    path: '/wallet',
    name: 'Wallet',
    component: () => import('@/views/WalletView.vue'),
    meta: { requiresAuth: true },
  },
]

// Settings routes
const settingsRoutes = [
  {
    path: "/settings",
    name: "settings",
    component: SettingsView,
    beforeEnter: authGuard,
  },
  {
    path: "/settings/account",
    name: "settings-account",
    component: AccountSettingsView,
    beforeEnter: authGuard,
  },
  {
    path: "/settings/privacy",
    name: "settings-privacy",
    component: PrivacySettingsView,
    beforeEnter: authGuard,
  },
  {
    path: "/settings/sessions",
    name: "settings-sessions",
    component: SessionManagementView,
    beforeEnter: authGuard,
  },
  {
    path: "/settings/payments",
    name: "settings-payments",
    component: PaymentsSettingsView,
    beforeEnter: authGuard,
  },
  {
    path: "/settings/display",
    name: "settings-display",
    component: DisplaySettingsView,
    beforeEnter: authGuard,
  },
  {
    path: "/settings/notifications",
    name: "settings-notifications",
    component: NotificationSettingsView,
    beforeEnter: authGuard,
  },
  {
    path: "/settings/connections",
    name: "settings-connections",
    component: ConnectionsSettingsView,
    beforeEnter: authGuard,
  },
  {
    path: "/settings/about",
    name: "settings-about",
    component: AboutView,
    beforeEnter: authGuard,
  },
  {
    path: "/language",
    name: "language",
    component: DisplaySettingsView,
    beforeEnter: authGuard,
  },
]

// Combine all routes
const routes = [
  ...publicRoutes,
  ...authenticatedRoutes,
  ...creatorRoutes,
  ...settingsRoutes,
  {
    path: '/post/:id',
    name: 'singlePost',
    component: () => import('@/views/SinglePostView.vue'),
    meta: {
      requiresAuth: true,
      requiresAgeVerification: true
    }
  },
  {
    path: '/:username/:slug',
    name: 'tracking-link-redirect',
    component: () => import('../views/TrackingLinkRedirect.vue')
  }
]

// Create router instance
const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

// Global navigation guard
router.beforeEach((to, from, next) => {
  const isVerified = localStorage.getItem("age-verified") === "true"
  const isAuthenticated = !!localStorage.getItem("auth_token")

  // Allow direct access to age verification, auth, public profile pages, and management access
  if (to.name === "age-verification" || 
      to.name === "auth" || 
      to.name === "userProfile" || 
      to.name === "tracking-link-redirect" ||
      to.name === "management-access") {
    next()
    return
  }

  // Check age verification first
  if (!isVerified) {
    next({ name: "age-verification" })
    return
  }

  // Check authentication status
  if (!isAuthenticated) {
    next({ name: "auth" })
    return
  }

  next()
})

export default router
