import { DefineComponent } from 'vue'
import type { PostFilters } from '@/types/post'

declare const PostFiltersComponent: DefineComponent<{
  initialFilters: PostFilters
}, {}, {
  'update:filters': (filters: PostFilters) => void
  apply: () => void
}>

export default PostFiltersComponent 