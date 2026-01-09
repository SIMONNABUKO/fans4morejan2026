import type { Post, PostState, PostFilters, PostStats } from '@/types/store'

export interface PostStore {
  state: PostState
  posts: Post[]
  postStats: PostStats
  loading: boolean
  error: string | null
  totalPosts: number
  currentPage: number
  perPage: number
  filters: PostFilters
  fetchPostStats: () => Promise<void>
  fetchPosts: () => Promise<void>
  updatePostStatus: (postId: number, status: string, moderationNote?: string) => Promise<Post>
  setFilters: (filters: Partial<PostFilters>) => Promise<void>
  setPage: (page: number) => Promise<void>
}

declare module '@/stores/post' {
  export const usePostStore: () => PostStore
} 