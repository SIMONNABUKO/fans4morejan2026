import axiosBase from 'axios'
import { useAuthStore } from '@/stores/auth'

// Create axios instance with base configuration
const axiosInstance = axiosBase.create({
  baseURL: import.meta.env.VITE_API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Flag to prevent multiple token renewal attempts
let isRefreshing = false
let failedQueue: any[] = []

const processQueue = (error: any | null, token: string | null = null) => {
  failedQueue.forEach(prom => {
    if (error) {
      prom.reject(error)
    } else {
      prom.resolve(token)
    }
  })
  failedQueue = []
}

// Request interceptor
axiosInstance.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    
    // Log request details
    console.log('🚀 Request:', {
      url: config.url,
      method: config.method?.toUpperCase(),
      headers: config.headers,
      data: config.data
    })

    // Add token if available
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
      console.log('🔑 Token added to request:', `Bearer ${token.substring(0, 10)}...`)
    } else {
      console.log('⚠️ No token found in localStorage')
    }

    return config
  },
  (error) => {
    console.error('❌ Request error:', error)
    return Promise.reject(error)
  }
)

// Response interceptor
axiosInstance.interceptors.response.use(
  (response) => {
    // Log successful response
    console.log('✅ Response:', {
      url: response.config.url,
      status: response.status,
      statusText: response.statusText,
      data: response.data
    })
    return response
  },
  async (error) => {
    const originalRequest = error.config

    // Log error response
    console.error('❌ Response error:', {
      url: error.config?.url,
      status: error.response?.status,
      statusText: error.response?.statusText,
      data: error.response?.data
    })

    // If the error is 401 and it's not a token renewal request
    if (error.response?.status === 401 && !originalRequest._retry && !originalRequest.url.includes('/admin/renew-token')) {
      if (isRefreshing) {
        // If token refresh is in progress, add request to queue
        return new Promise((resolve, reject) => {
          failedQueue.push({ resolve, reject })
        })
          .then((token) => {
            originalRequest.headers.Authorization = `Bearer ${token}`
            return axiosInstance(originalRequest)
          })
          .catch((err) => Promise.reject(err))
      }

      originalRequest._retry = true
      isRefreshing = true

      try {
        // Attempt to renew token
        const response = await axiosInstance.post('/admin/renew-token')
        const { token } = response.data

        // Update token in localStorage and axios headers
        localStorage.setItem('token', token)
        axiosInstance.defaults.headers.common['Authorization'] = `Bearer ${token}`
        originalRequest.headers.Authorization = `Bearer ${token}`

        // Process queued requests
        processQueue(null, token)

        // Retry the original request
        return axiosInstance(originalRequest)
      } catch (renewError) {
        // If token renewal fails, use auth store to logout
        processQueue(renewError, null)
        const authStore = useAuthStore()
        await authStore.logout()
        return Promise.reject(renewError)
      } finally {
        isRefreshing = false
      }
    }

    return Promise.reject(error)
  }
)

export default axiosInstance 