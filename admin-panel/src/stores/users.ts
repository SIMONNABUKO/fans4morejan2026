import { defineStore } from 'pinia'
import { ref, Ref } from 'vue'
import axios from '@/plugins/axios'
import type { User } from '@/types/store'

export interface UsersState {
  users: User[]
  currentUser: User | null
  isLoading: boolean
  error: string | null
  totalUsers: number
  currentPage: number
  perPage: number
}

interface UserCreateData {
  name: string
  email: string
  password: string
  role?: string
}

interface UserUpdateData {
  name?: string
  email?: string
  role?: string
}

export const useUsersStore = defineStore('users', {
  state: (): UsersState => ({
    users: [],
    currentUser: null,
    isLoading: false,
    error: null,
    totalUsers: 0,
    currentPage: 1,
    perPage: 10
  }),

  actions: {
    async fetchUsers() {
      this.isLoading = true
      try {
        const response = await axios.get<{ data: User[], total: number }>('/admin/users', {
          params: { page: this.currentPage, per_page: this.perPage }
        })
        this.users = response.data.data
        this.totalUsers = response.data.total
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to fetch users'
        throw error
      } finally {
        this.isLoading = false
      }
    },

    setPage(page: number) {
      this.currentPage = page
      return this.fetchUsers()
    },

    async fetchUser(id: number) {
      this.isLoading = true
      try {
        const response = await axios.get<User>(`/admin/users/show/${id}`)
        this.currentUser = response.data
        return response.data
      } catch (e) {
        this.error = e instanceof Error ? e.message : 'Failed to fetch user'
        throw this.error
      } finally {
        this.isLoading = false
      }
    },

    async createUser(userData: UserCreateData) {
      this.isLoading = true
      try {
        const response = await axios.post<User>('/admin/users/create', userData)
        this.users = [response.data, ...this.users]
        this.totalUsers++
        return response.data
      } catch (e) {
        this.error = e instanceof Error ? e.message : 'Failed to create user'
        throw this.error
      } finally {
        this.isLoading = false
      }
    },

    async updateUser(id: number, userData: UserUpdateData) {
      this.isLoading = true
      try {
        const response = await axios.put<User>(`/admin/users/update/${id}`, userData)
        const index = this.users.findIndex(user => user.id === id)
        if (index !== -1) {
          this.users[index] = response.data
        }
        if (this.currentUser?.id === id) {
          this.currentUser = response.data
        }
        return response.data
      } catch (e) {
        this.error = e instanceof Error ? e.message : 'Failed to update user'
        throw this.error
      } finally {
        this.isLoading = false
      }
    },

    async deleteUser(id: number) {
      this.isLoading = true
      try {
        await axios.delete(`/admin/users/delete/${id}`)
        this.users = this.users.filter(user => user.id !== id)
        this.totalUsers--
        if (this.currentUser?.id === id) {
          this.currentUser = null
        }
      } catch (e) {
        this.error = e instanceof Error ? e.message : 'Failed to delete user'
        throw this.error
      } finally {
        this.isLoading = false
      }
    }
  }
}) 