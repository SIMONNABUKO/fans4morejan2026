import axios from "axios"

const baseURL = import.meta.env.VITE_API_URL
console.log('API Base URL:', baseURL)

const axiosInstance = axios.create({
  baseURL: baseURL.endsWith('/api') ? baseURL : `${baseURL}/api`,
  headers: {
    "Content-Type": "application/json",
    "Accept": "application/json",
  },
  withCredentials: true
})

axiosInstance.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem("auth_token")
    console.log('🔐 Auth token from localStorage:', token ? `${token.substring(0, 20)}...` : 'NO TOKEN')
    
    if (token) {
      // Ensure Authorization header is set regardless of Content-Type
      config.headers = {
        ...config.headers,
        "Authorization": `Bearer ${token}`
      }
    }
    
    console.log('📡 Making request to:', config.baseURL + config.url)
    console.log('📡 Request method:', config.method?.toUpperCase())
    console.log('📡 Request params:', config.params)
    console.log('📡 Request headers:', config.headers)
    
    return config
  },
  (error) => {
    console.error('📡 Request interceptor error:', error)
    return Promise.reject(error)
  },
)

// Add response interceptor for error handling
axiosInstance.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      localStorage.removeItem("auth_token")
      // Handle unauthorized access - redirect to auth page, but only if not already there
      const currentPath = window.location.pathname
      if (currentPath !== '/auth' && !currentPath.startsWith('/verify-age')) {
        window.location.href = '/auth'
      }
    }
    return Promise.reject(error)
  }
)

export default axiosInstance
