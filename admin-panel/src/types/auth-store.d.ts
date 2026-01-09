import { StoreDefinition } from 'pinia'
import type { AuthStore } from './store'

interface User {
  id: number
  name: string
  email: string
  avatar?: string
  role: string
  createdAt: string
  updatedAt: string
  lastLoginAt: string | null
  emailVerified: boolean
  twoFactorEnabled: boolean
}

interface AuthState {
  user: User | null
  token: string | null
  loading: boolean
  error: string | null
  isAuthenticated: boolean
  lastActivity: string | null
  sessionExpiry: string | null
}

interface LoginCredentials {
  email: string
  password: string
  remember?: boolean
}

interface AuthStore extends AuthState {
  login(credentials: LoginCredentials): Promise<void>
  logout(): Promise<void>
  checkAuth(): Promise<boolean>
  refreshToken(): Promise<void>
  updateProfile(data: Partial<User>): Promise<User>
  clearError(): void
}

declare module 'pinia' {
  interface StoreDefinition {
    auth: AuthStore
  }
}

declare module '@/stores/auth' {
  const store: AuthStore
  export default store
} 