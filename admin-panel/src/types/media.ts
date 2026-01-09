export interface MediaPreview {
  id: number;
  url: string;
}

export interface Media {
  id: number;
  user_id: number;
  mediable_id: number;
  mediable_type: string;
  type: string;
  url: string;
  full_url: string;
  status: string;
  created_at: string;
  updated_at: string;
  user: {
    id: number;
    name: string;
    username: string;
    avatar?: string;
  };
  previews: MediaPreview[];
  stats: {
    total_likes: number;
    total_views: number;
    total_bookmarks: number;
  };
}

export interface MediaFilters {
  type: string;
  user_id?: number;
  date_range?: {
    start: string;
    end: string;
  };
  status?: string;
}

export interface MediaState {
  media: Media[];
  loading: boolean;
  error: string | null;
  totalMedia: number;
  currentPage: number;
  perPage: number;
  filters: MediaFilters;
  selectedMedia: Media | null;
}

export interface MediaStoreActions {
  fetchMedia(): Promise<void>;
  updateMediaStatus(mediaId: number, status: string): Promise<void>;
  deleteMedia(mediaId: number): Promise<void>;
  setFilters(filters: MediaFilters): void;
  setPage(page: number): void;
  selectMedia(media: Media | null): void;
} 