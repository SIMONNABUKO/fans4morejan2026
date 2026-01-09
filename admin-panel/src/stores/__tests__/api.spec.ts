/// <reference types="vitest" />
import { describe, it, expect, beforeEach, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { AxiosHeaders } from 'axios'
import type { AxiosInstance, InternalAxiosRequestConfig, AxiosRequestConfig, AxiosResponse } from 'axios'
import type { Mock } from 'vitest'
import axios from '@/plugins/axios'
import { localStorageMock } from '@/test/setup'

// Create a mock axios instance
const mockAxios = {
  get: vi.fn(),
  post: vi.fn(),
  patch: vi.fn(),
  put: vi.fn(),
  delete: vi.fn(),
  create: vi.fn().mockReturnThis(),
  defaults: {
    baseURL: import.meta.env.VITE_API_URL,
    headers: new AxiosHeaders({
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    })
  },
  interceptors: {
    request: {
      use: vi.fn((callback) => {
        mockAxios._requestInterceptor = callback
      })
    },
    response: {
      use: vi.fn((_, errorCallback) => {
        mockAxios._responseInterceptor = errorCallback
      })
    }
  },
  _requestInterceptor: null as null | ((config: InternalAxiosRequestConfig) => InternalAxiosRequestConfig),
  _responseInterceptor: null as null | ((error: any) => any)
}

// Import stores
import { useUserStore } from '@/stores/user'
import { useDashboardStore } from '@/stores/dashboard'
import { usePostStore } from '@/stores/post'

const TEST_TOKEN = 'test-auth-token'

describe('API Request Format Tests', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
    localStorageMock.clear()
    
    // Type assertion to access mock functions
    const mockGetItem = localStorageMock.getItem as Mock
    mockGetItem.mockImplementation((key: string) => {
      if (key === 'auth_token') return TEST_TOKEN
      return null
    })

    // Mock axios methods
    Object.assign(axios, mockAxios)

    // Set up request interceptor
    mockAxios.interceptors.request.use((config: InternalAxiosRequestConfig) => {
      const token = localStorageMock.getItem('auth_token')
      if (token) {
        config.headers['Authorization'] = `Bearer ${token}`
      }
      return config
    })

    // Set up response interceptor
    mockAxios.interceptors.response.use(
      (response: AxiosResponse) => response,
      (error: any) => {
        if (error.response?.status === 401) {
          localStorageMock.removeItem('auth_token')
        }
        return Promise.reject(error)
      }
    )

    // Mock successful responses
    mockAxios.get.mockImplementation(async (url, config = {}) => {
      const baseConfig = {
        ...config,
        headers: new AxiosHeaders({
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        })
      } as InternalAxiosRequestConfig

      // Apply request interceptor
      const interceptedConfig = mockAxios._requestInterceptor ? 
        await mockAxios._requestInterceptor(baseConfig) :
        baseConfig

      return {
        data: {
          data: [],
          total: 0
        },
        config: interceptedConfig,
        headers: interceptedConfig.headers,
        status: 200,
        statusText: 'OK'
      }
    })

    mockAxios.patch.mockImplementation(async (url, data, config = {}) => {
      const baseConfig = {
        ...config,
        headers: new AxiosHeaders({
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        })
      } as InternalAxiosRequestConfig

      // Apply request interceptor
      const interceptedConfig = mockAxios._requestInterceptor ? 
        await mockAxios._requestInterceptor(baseConfig) :
        baseConfig

      return {
        data,
        config: interceptedConfig,
        headers: interceptedConfig.headers,
        status: 200,
        statusText: 'OK'
      }
    })
  })

  describe('Authentication Headers', () => {
    it('should add auth token to request headers', async () => {
      const userStore = useUserStore()
      await userStore.fetchUsers()

      // Get the mock call arguments
      const [url, config] = mockAxios.get.mock.calls[0]
      expect(url).toBe('/admin/users')
      expect(config).toEqual({
        params: { page: 1, per_page: 10 }
      })

      // Get the mock result to check headers
      const result = await mockAxios.get.mock.results[0].value
      expect(result.headers['Authorization']).toBe(`Bearer ${TEST_TOKEN}`)
      expect(result.headers['Content-Type']).toBe('application/json')
      expect(result.headers['Accept']).toBe('application/json')
    })

    it('should not add auth token when not logged in', async () => {
      const mockGetItem = localStorageMock.getItem as Mock
      mockGetItem.mockReturnValue(null)
      
      const userStore = useUserStore()
      await userStore.fetchUsers()

      // Get the mock result to check headers
      const result = await mockAxios.get.mock.results[0].value
      expect(result.headers['Authorization']).toBeUndefined()
    })
  })

  describe('User Management Endpoints', () => {
    it('should format user list request correctly', async () => {
      const userStore = useUserStore()
      await userStore.fetchUsers()

      expect(mockAxios.get).toHaveBeenCalledWith('/admin/users', {
        params: { page: 1, per_page: 10 }
      })
    })

    it('should format user status update correctly', async () => {
      const userStore = useUserStore()
      const userId = 123
      const status = 'blocked'

      await userStore.updateUserStatus(userId, status)

      expect(mockAxios.patch).toHaveBeenCalledWith(
        `/admin/users/${userId}/status`,
        { status }
      )
    })

    it('should handle pagination parameters', async () => {
      const userStore = useUserStore()
      userStore.currentPage = 2
      userStore.perPage = 20

      await userStore.fetchUsers()

      expect(mockAxios.get).toHaveBeenCalledWith('/admin/users', {
        params: { page: 2, per_page: 20 }
      })
    })
  })

  describe('Dashboard Store', () => {
    it('fetches dashboard stats', async () => {
      const dashboardStore = useDashboardStore()
      await dashboardStore.fetchDashboardStats()
      expect(dashboardStore.stats).toBeDefined()
    })
  })

  describe('Post Management Endpoints', () => {
    it('should format post list request correctly', async () => {
      const postStore = usePostStore()
      await postStore.fetchPosts()

      expect(mockAxios.get).toHaveBeenCalledWith('/admin/posts', {
        params: {
          page: 1,
          per_page: 10,
          status: '',
          search: ''
        }
      })
    })

    it('should format post status update correctly', async () => {
      const postStore = usePostStore()
      const postId = 123
      const status = 'approved'
      const moderationNote = 'Content approved'

      await postStore.updatePostStatus(postId, status, moderationNote)

      const lastCall = mockAxios.patch.mock.calls[0]
      expect(lastCall[0]).toBe(`/admin/posts/${postId}/status`)
      expect(lastCall[1]).toEqual({ status, moderation_note: moderationNote })
    })
  })

  describe('Error Handling', () => {
    it('should handle 401 responses correctly', async () => {
      const userStore = useUserStore()
      const mockGetItem = localStorageMock.getItem as Mock
      const mockRemoveItem = localStorageMock.removeItem as Mock
      
      mockAxios.get.mockImplementationOnce(async () => {
        const error = {
          response: { status: 401 },
          config: {},
          isAxiosError: true,
          toJSON: () => ({})
        }
        if (mockAxios._responseInterceptor) {
          await mockAxios._responseInterceptor(error)
        }
        throw error
      })

      await expect(userStore.fetchUsers()).rejects.toThrow()
      expect(mockRemoveItem).toHaveBeenCalledWith('auth_token')
    })

    it('should handle network errors correctly', async () => {
      const userStore = useUserStore()
      
      mockAxios.get.mockRejectedValueOnce(new Error('Network Error'))

      try {
        await userStore.fetchUsers()
        expect('Should have thrown an error').toBeFalsy()
      } catch (error) {
        expect(userStore.error).toBe('Failed to fetch users')
      }
    })
  })
}) 