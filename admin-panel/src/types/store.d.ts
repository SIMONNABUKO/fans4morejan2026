export interface User {
  id: number
  name: string
  email: string
  role: string
  createdAt: string
  updatedAt: string
  lastLoginAt: string | null
  emailVerified: boolean
  twoFactorEnabled: boolean
  avatar?: string
}

export interface AuthState {
  user: User | null
  token: string | null
  loading: boolean
  error: string | null
}

export interface LoginCredentials {
  login: string
  password: string
  remember?: boolean
}

export interface AuthResponse {
  token: string
  user: User
}

export interface PostStats {
  total: number
  pending: number
  published: number
  rejected: number
  reported: number
}

export interface Post {
  id: number
  title: string
  content: string
  status: 'pending' | 'published' | 'rejected' | 'reported'
  created_at: string
  updated_at: string
  user: User
}

export interface PostFilters {
  status: string
  search: string
}

export interface PostState {
  posts: Post[]
  postStats: PostStats
  loading: boolean
  error: string | null
  totalPosts: number
  currentPage: number
  perPage: number
  filters: PostFilters
}

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

declare module '@/stores/auth' {
  export interface AuthStore {
    state: AuthState
    getters: {
      isAuthenticated: () => boolean
      isAdmin: () => boolean
      userProfile: () => User | null
    }
    actions: {
      login: (credentials: LoginCredentials) => Promise<AuthResponse>
      logout: () => Promise<void>
      fetchUser: () => Promise<User | null>
      setAuthData: (data: AuthResponse) => void
      clearAuthData: () => void
      init: () => Promise<void>
      checkAuth: () => Promise<boolean>
    }
  }

  export const useAuthStore: () => AuthStore
} 