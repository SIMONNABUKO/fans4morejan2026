export interface Post {
  id: number;
  title: string;
  content: string;
  processed_content: string;
  status: string;
  moderation_note?: string;
  user: {
    id: number;
    name: string;
    username: string;
    avatar?: string;
  };
  media: {
    id: number;
    type: string;
    url: string;
    previews: {
      id: number;
      url: string;
    }[];
  }[];
  stats: {
    total_likes: number;
    total_views: number;
    total_bookmarks: number;
    total_comments: number;
    total_tips: number;
    total_tip_amount: number;
    is_liked: boolean;
    is_bookmarked: boolean;
  };
  permission_sets: {
    id: number;
    type: string;
    value: string;
  }[];
  purchases: {
    id: number;
    user_id: number;
    created_at: string;
  }[];
  tagged_users: {
    id: number;
    username: string;
    name: string;
    status: string;
  }[];
  created_at: string;
  updated_at: string;
}

export interface PostStats {
  total: number;
  pending: number;
  published: number;
  rejected: number;
  reported: number;
}

export interface PostFilters {
  status: string;
  search: string;
}

export interface PostPagination {
  currentPage: number;
  totalPages: number;
  perPage: number;
  totalItems: number;
}

export interface PostState {
  posts: Post[];
  postStats: PostStats;
  loading: boolean;
  statsLoading: boolean;
  error: string | null;
  totalPosts: number;
  currentPage: number;
  perPage: number;
  filters: PostFilters;
  pagination: PostPagination;
  filteredPosts: Post[];
}

export interface PostStoreActions {
  fetchPostStats(): Promise<void>;
  fetchPosts(): Promise<void>;
  updatePostStatus(postId: number, status: string, moderationNote?: string): Promise<Post>;
  setFilters(filters: Partial<PostFilters>): Promise<void>;
  setPage(page: number): Promise<void>;
  reviewReport(postId: number): Promise<void>;
  moderatePost(params: { post: Post; action: string }): Promise<void>;
  updateFilters(filters: Partial<PostFilters>): void;
}

export interface PostStoreState {
  posts: Post[];
  postStats: PostStats;
  loading: boolean;
  statsLoading: boolean;
  error: string | null;
  totalPosts: number;
  currentPage: number;
  perPage: number;
  filters: PostFilters;
  pagination: PostPagination;
  filteredPosts: Post[];
} 