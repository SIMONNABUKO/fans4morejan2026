import { defineStore } from 'pinia'
import axios from '@/plugins/axios'
import type { User, UserFormData, UserState } from '@/types/store'

export const useUserStore = defineStore('user', {
  state: (): UserState => ({
    users: [] as any[],
    loading: false,
    error: null as string | null,
    totalUsers: 0,
    currentPage: 1,
    perPage: 10
  }),

  actions: {
    async fetchUsers() {
      try {
        this.loading = true
        const response = await axios.get('/admin/users')
        this.users = response.data.data
        this.totalUsers = this.users.length
        this.error = null
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to fetch users'
        throw error
      } finally {
        this.loading = false
      }
    },

    async createUser(data: UserFormData) {
      this.loading = true
      this.error = null
      try {
        const response = await axios.post('/users', data)
        this.users.push(response.data)
        await this.fetchUsers()
        return response.data
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to create user'
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateUser(id: number, userData: UserFormData) {
      try {
        this.loading = true
        const response = await axios.put(`/admin/users/${id}`, userData)
        const updatedUser = response.data.data
        const index = this.users.findIndex(user => user.id === id)
        if (index !== -1) {
          this.users[index] = updatedUser
        }
        this.error = null
        return updatedUser
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to update user'
        throw error
      } finally {
        this.loading = false
      }
    },

    async deleteUser(id: number) {
      try {
        this.loading = true
        await axios.delete(`/admin/users/${id}`)
        this.users = this.users.filter(user => user.id !== id)
        this.totalUsers = this.users.length
        this.error = null
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to delete user'
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateUserStatus(userId: number, status: string) {
      this.loading = true
      this.error = null
      try {
        await axios.patch(`/admin/users/${userId}/status`, { status })
        await this.fetchUsers()
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to update user status'
        throw error
      } finally {
        this.loading = false
      }
    },

    setPage(page: number) {
      this.currentPage = page
      return this.fetchUsers()
    }
  }
})