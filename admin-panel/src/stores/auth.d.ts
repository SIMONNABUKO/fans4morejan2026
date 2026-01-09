import type { AuthState, LoginCredentials, User } from '@/types/auth-store'
import type { StoreDefinition } from 'pinia'

declare module '@/stores/auth' {
  export const useAuthStore: () => StoreDefinition['auth']
}

export {
  AuthState,
  LoginCredentials,
  User
} 