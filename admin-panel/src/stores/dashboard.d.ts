export interface DashboardStats {
  totalUsers: number
  activePosts: number
  totalComments: number
  revenue: number
}

export interface ActivityItem {
  id: number
  type: 'user_registration' | 'subscription' | 'post_published' | 'other'
  title: string
  description: string
  timestamp: string
}

export interface DashboardStore {
  stats: DashboardStats | null
  recentActivity: ActivityItem[]
  isLoading: boolean
  error: string | null
  fetchDashboardStats: () => Promise<void>
  fetchRecentActivity: () => Promise<void>
}

declare module '@/stores/dashboard' {
  export const useDashboardStore: () => DashboardStore
} 