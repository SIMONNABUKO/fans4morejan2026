import { defineStore } from 'pinia';
import axios from '@/plugins/axios';
import type { AuthResponse, User as UserType } from '@/types/store';

interface LoginCredentials {
  email: string
  password: string
  remember?: boolean
}

interface AuthState {
  token: string | null
  user: UserType | null
  loading: boolean
  error: string | null
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    token: localStorage.getItem('auth_token') || null,
    user: JSON.parse(localStorage.getItem('auth_user') || 'null'),
    loading: false,
    error: null
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdmin: (state) => state.user?.role === 'admin'
  },

  actions: {
    async login(credentials: LoginCredentials) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.post<AuthResponse>('/admin/login', credentials);
        this.setAuthData(response.data);
        return response.data;
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to login';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async logout() {
      try {
        await axios.post('/admin/logout');
      } finally {
        this.clearAuth();
      }
    },

    clearAuth() {
      this.token = null;
      this.user = null;
      localStorage.removeItem('auth_token');
      localStorage.removeItem('auth_user');
      delete axios.defaults.headers.common['Authorization'];
    },

    setAuthData({ token, user }: AuthResponse) {
      this.token = token;
      this.user = user;
      localStorage.setItem('auth_token', token);
      localStorage.setItem('auth_user', JSON.stringify(user));
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    },

    async fetchUser(): Promise<UserType | null> {
      try {
        const response = await axios.get('/admin/me');
        this.user = response.data;
        localStorage.setItem('auth_user', JSON.stringify(response.data));
        return response.data;
      } catch (error) {
        this.clearAuth();
        throw error;
      }
    },

    initialize(): void {
      const token = localStorage.getItem('auth_token');
      const userStr = localStorage.getItem('auth_user');
      
      if (token && userStr) {
        this.token = token;
        this.user = JSON.parse(userStr);
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
      } else {
        this.clearAuth();
      }
    },

    async checkAuth(): Promise<boolean> {
      if (!this.token) {
        this.clearAuth();
        return false;
      }

      this.loading = true;
      try {
        const response = await axios.get<UserType>('/admin/user');
        this.user = response.data;
        localStorage.setItem('auth_user', JSON.stringify(response.data));
        return true;
      } catch (error) {
        this.clearAuth();
        return false;
      } finally {
        this.loading = false;
      }
    }
  }
});