declare module '@/services/api-client' {
  import { AxiosInstance } from 'axios'

  export interface ApiResponse<T = any> {
    data: T
    message?: string
    status: number
  }

  export interface ApiError {
    message: string
    errors?: Record<string, string[]>
    status: number
  }

  export interface ApiClient extends AxiosInstance {
    setAuthToken: (token: string) => void
    clearAuthToken: () => void
  }

  const apiClient: ApiClient
  export default apiClient
} 