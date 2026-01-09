import type { AxiosInstance } from 'axios'

declare module '@vue/runtime-core' {
  interface ComponentCustomProperties {
    $axios: AxiosInstance
  }
}

declare module '@/plugins/axios' {
  const axios: AxiosInstance
  export default axios
} 