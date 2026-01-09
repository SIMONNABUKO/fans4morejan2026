import { StoreDefinition } from 'pinia'

interface User {
  id: number
  name: string
  email: string
  role: string
  createdAt: string
  updatedAt: string
  lastLoginAt: string | null
  status: 'active' | 'inactive' | 'suspended'
}

interface UserState {
  users: User[]
  currentUser: User | null
  loading: boolean
  error: string | null
  lastFetch: string | null
}

interface UserStore extends UserState {
  fetchUsers(): Promise<void>
  fetchUser(id: number): Promise<User>
  updateUser(id: number, data: Partial<User>): Promise<User>
  deleteUser(id: number): Promise<void>
  setCurrentUser(user: User | null): void
  clearError(): void
}

declare module 'pinia' {
  interface StoreDefinition {
    user: UserStore
  }
} 