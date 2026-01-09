import { DefineComponent } from 'vue'
import type { Post } from '@/types/post'

declare const PostModerationComponent: DefineComponent<{
  post: Post
}, {}, {
  moderate: (event: { post: Post; action: string }) => void
  view: (post: Post) => void
}>

export default PostModerationComponent 