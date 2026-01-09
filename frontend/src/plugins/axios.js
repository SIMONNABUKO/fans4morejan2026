import axios from 'axios'

const axiosInstance = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true
})

// Add request interceptor to include auth token
axiosInstance.interceptors.request.use(config => {
  const token = localStorage.getItem("auth_token")
  if (token) {
    config.headers = {
      ...config.headers,
      "Authorization": `Bearer ${token}`
    }
  }
  return config
})

// Add response interceptor for error handling
axiosInstance.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      // Handle unauthorized access - redirect to auth page, not login
      window.location.href = '/auth'
    }
    return Promise.reject(error)
  }
)

export default axiosInstance 