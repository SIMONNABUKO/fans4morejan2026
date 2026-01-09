import { Post, PostFilters, PostPagination, PostStats, PostStoreState, PostStoreActions } from './post'

export interface User {
  id: number
  name: string
  display_name: string | null
  email: string
  state_id: number | null
  username: string
  handle: string
  bio: string | null
  avatar: string
  cover_photo: string
  role: 'admin' | 'creator' | 'user'
  support_count: number | null
  facebook: string | null
  twitter: string | null
  instagram: string | null
  linkedin: string | null
  media_watermark: string | null
  confirmed_at: string | null
  email_verified_at: string | null
  google_id: string | null
  can_be_followed: boolean
  is_online: boolean
  is_suspended: boolean
  is_banned: boolean
  country_name: string | null
  country_code: string | null
  region_name: string | null
  city_name: string | null
  ip_address: string | null
  api_token: string | null
  last_seen_at: string | null
  terms_accepted: boolean
  has_2fa: boolean
  deleted_at: string | null
  created_at: string
  updated_at: string
  total_likes_received: number
  total_video_uploads: number
  total_image_uploads: number
  followers_count: number
  status?: string
}

export interface AuthState {
  token: string | null
  user: User | null
  loading: boolean
  error: string | null
}

export interface LoginCredentials {
  email: string
  password: string
  remember?: boolean
}

export interface AuthResponse {
  token: string
  user: User
}

export interface AuthStoreActions {
  login(credentials: LoginCredentials): Promise<AuthResponse>
  logout(): Promise<void>
  fetchUser(): Promise<User | null>
  setAuthData(data: AuthResponse): void
  clearAuthData(): void
  init(): Promise<void>
  checkAuth(): Promise<boolean>
}

export interface PostStore extends PostStoreState, PostStoreActions {
  filteredPosts: Post[]
  postStats: PostStats
}

export interface AuthStore {
  token: string | null
  user: User | null
  loading: boolean
  error: string | null
  isAuthenticated: boolean
  isAdmin: boolean
  userProfile: User | null
  login(credentials: LoginCredentials): Promise<AuthResponse>
  logout(): Promise<void>
  fetchUser(): Promise<User | null>
  setAuthData(data: AuthResponse): void
  clearAuthData(): void
  init(): Promise<void>
  checkAuth(): Promise<boolean>
}

export interface PostState {
  posts: Post[];
  loading: boolean;
  error: string | null;
  totalPosts: number;
  currentPage: number;
  perPage: number;
  filters: PostFilters;
}

export interface UserFormData {
  display_name: string
  username: string
  email: string
  password?: string
  password_confirmation?: string
  role: string
  status: string
}

export interface UserStore {
  users: User[]
  loading: boolean
  error: string | null
  totalUsers: number
  currentPage: number
  perPage: number
  fetchUsers(): Promise<void>
  createUser(userData: UserFormData): Promise<void>
  updateUser(userId: number, userData: UserFormData): Promise<void>
  deleteUser(userId: number): Promise<void>
  updateUserStatus(userId: number, status: string): Promise<void>
  setPage(page: number): Promise<void>
}

export interface UserState {
  users: User[]
  loading: boolean
  error: string | null
  totalUsers: number
  currentPage: number
  perPage: number
} 