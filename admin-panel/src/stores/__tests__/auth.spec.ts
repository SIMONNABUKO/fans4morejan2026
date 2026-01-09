import { setActivePinia, createPinia } from 'pinia';
import { useAuthStore } from '../auth';
import axios from '@/plugins/axios';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import type { User, AuthResponse } from '@/types/store';
import type { AxiosResponse } from 'axios';

describe('Auth Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia());
    vi.clearAllMocks();
    localStorage.clear();
  });

  const mockUser: User = {
    id: 1,
    name: 'Test User',
    display_name: 'Test User',
    email: 'test@example.com',
    state_id: null,
    username: 'testuser',
    handle: 'testuser',
    bio: null,
    avatar: '',
    cover_photo: '',
    role: 'admin',
    support_count: null,
    facebook: null,
    twitter: null,
    instagram: null,
    linkedin: null,
    media_watermark: null,
    confirmed_at: null,
    email_verified_at: null,
    google_id: null,
    can_be_followed: true,
    is_online: false,
    is_suspended: false,
    is_banned: false,
    country_name: null,
    country_code: null,
    region_name: null,
    city_name: null,
    ip_address: null,
    api_token: null,
    last_seen_at: null,
    terms_accepted: true,
    has_2fa: false,
    deleted_at: null,
    created_at: '2023-01-01',
    updated_at: '2023-01-01',
    total_likes_received: 0,
    total_video_uploads: 0,
    total_image_uploads: 0,
    followers_count: 0,
    status: 'active'
  };

  const mockAuthResponse: AuthResponse = {
    token: 'test-token',
    user: mockUser
  };

  it('initializes with correct initial state', () => {
    const store = useAuthStore();
    expect(store.token).toBeNull();
    expect(store.user).toBeNull();
    expect(store.loading).toBe(false);
    expect(store.error).toBeNull();
  });

  it('handles successful login', async () => {
    const axiosResponse: AxiosResponse<AuthResponse> = {
      data: mockAuthResponse,
      status: 200,
      statusText: 'OK',
      headers: {},
      config: {} as any
    };
    const postSpy = vi.spyOn(axios, 'post').mockResolvedValueOnce(axiosResponse);

    const store = useAuthStore();
    const result = await store.login({ email: 'test@example.com', password: 'password' });

    expect(postSpy).toHaveBeenCalledWith('/admin/login', {
      email: 'test@example.com',
      password: 'password'
    });
    expect(result).toEqual(mockAuthResponse);
    expect(store.token).toBe(mockAuthResponse.token);
    expect(store.user).toEqual(mockAuthResponse.user);
    expect(localStorage.getItem('auth_token')).toBe(mockAuthResponse.token);
    expect(localStorage.getItem('auth_user')).toBe(JSON.stringify(mockAuthResponse.user));
  });

  it('handles login failure', async () => {
    const errorMessage = 'Invalid credentials';
    const postSpy = vi.spyOn(axios, 'post').mockRejectedValueOnce({
      response: { data: { message: errorMessage } }
    });

    const store = useAuthStore();
    await expect(store.login({ 
      email: 'test@example.com', 
      password: 'wrong' 
    })).rejects.toEqual({
      response: { data: { message: errorMessage } }
    });

    expect(store.error).toBe(errorMessage);
    expect(store.token).toBeNull();
    expect(store.user).toBeNull();
  });

  it('handles logout', async () => {
    const store = useAuthStore();
    store.token = mockAuthResponse.token;
    store.user = mockAuthResponse.user;
    localStorage.setItem('auth_token', mockAuthResponse.token);
    localStorage.setItem('auth_user', JSON.stringify(mockAuthResponse.user));

    const logoutResponse: AxiosResponse = {
      data: {},
      status: 200,
      statusText: 'OK',
      headers: {},
      config: {} as any
    };
    const postSpy = vi.spyOn(axios, 'post').mockResolvedValueOnce(logoutResponse);
    await store.logout();

    expect(postSpy).toHaveBeenCalledWith('/admin/logout');
    expect(store.token).toBeNull();
    expect(store.user).toBeNull();
    expect(localStorage.getItem('auth_token')).toBeNull();
    expect(localStorage.getItem('auth_user')).toBeNull();
  });

  describe('checkAuth', () => {
    it('validates existing token', async () => {
      const store = useAuthStore();
      store.token = mockAuthResponse.token;
      const userResponse: AxiosResponse<User> = {
        data: mockUser,
        status: 200,
        statusText: 'OK',
        headers: {},
        config: {} as any
      };
      const getSpy = vi.spyOn(axios, 'get').mockResolvedValueOnce(userResponse);

      const result = await store.checkAuth();

      expect(result).toBe(true);
      expect(store.user).toEqual(mockUser);
      expect(localStorage.getItem('auth_user')).toBe(JSON.stringify(mockUser));
    });

    it('handles invalid token', async () => {
      const store = useAuthStore();
      store.token = 'invalid-token';
      const getSpy = vi.spyOn(axios, 'get').mockRejectedValueOnce({
        response: { status: 401 }
      });

      const result = await store.checkAuth();

      expect(result).toBe(false);
      expect(store.token).toBeNull();
      expect(store.user).toBeNull();
      expect(localStorage.getItem('auth_token')).toBeNull();
      expect(localStorage.getItem('auth_user')).toBeNull();
    });
  });
}); 