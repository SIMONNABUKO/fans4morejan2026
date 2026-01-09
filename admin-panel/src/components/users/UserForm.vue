<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useUserStore } from '@/stores/user'
import type { UserFormData } from '@/types/store'

const props = defineProps<{
  userId?: number
  editMode?: boolean
}>()

const emit = defineEmits<{
  (e: 'success'): void
  (e: 'cancel'): void
}>()

const userStore = useUserStore()
const loading = ref(false)

const formData = ref<UserFormData>({
  username: '',
  display_name: '',
  email: '',
  password: '',
  role: 'user',
  status: 'active'
})

onMounted(async () => {
  if (props.editMode && props.userId) {
    const user = userStore.users.find(u => u.id === props.userId)
    if (user) {
      formData.value = {
        username: user.username,
        display_name: user.display_name || '',
        email: user.email,
        password: '',
        role: user.role,
        status: user.status || 'active'
      }
    }
  }
})

const handleSubmit = async () => {
  try {
    loading.value = true
    if (props.editMode && props.userId) {
      // If password is empty in edit mode, remove it from the request
      const updateData = { ...formData.value }
      if (!updateData.password) {
        delete updateData.password
      }
      await userStore.updateUser(props.userId, updateData)
    } else {
      await userStore.createUser(formData.value)
    }
    emit('success')
  } catch (error) {
    console.error('Failed to submit user form:', error)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <form @submit.prevent="handleSubmit" class="space-y-4">
    <div class="form-group">
      <label for="username" class="block text-sm font-medium mb-1">Username</label>
      <input
        id="username"
        v-model="formData.username"
        type="text"
        class="form-input w-full"
        required
      />
    </div>

    <div class="form-group">
      <label for="display_name" class="block text-sm font-medium mb-1">Display Name</label>
      <input
        id="display_name"
        v-model="formData.display_name"
        type="text"
        class="form-input w-full"
        required
      />
    </div>

    <div class="form-group">
      <label for="email" class="block text-sm font-medium mb-1">Email</label>
      <input
        id="email"
        v-model="formData.email"
        type="email"
        class="form-input w-full"
        required
      />
    </div>

    <div class="form-group">
      <label for="password" class="block text-sm font-medium mb-1">
        {{ editMode ? 'New Password (leave empty to keep current)' : 'Password' }}
      </label>
      <input
        id="password"
        v-model="formData.password"
        type="password"
        class="form-input w-full"
        :required="!editMode"
      />
    </div>

    <div class="form-group">
      <label for="role" class="block text-sm font-medium mb-1">Role</label>
      <select
        id="role"
        v-model="formData.role"
        class="form-select w-full"
        required
      >
        <option value="user">User</option>
        <option value="admin">Admin</option>
        <option value="moderator">Moderator</option>
      </select>
    </div>

    <div class="form-group">
      <label for="status" class="block text-sm font-medium mb-1">Status</label>
      <select
        id="status"
        v-model="formData.status"
        class="form-select w-full"
        required
      >
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
        <option value="banned">Banned</option>
      </select>
    </div>

    <div class="flex justify-end space-x-2">
      <button
        type="button"
        @click="$emit('cancel')"
        class="btn btn-secondary"
      >
        Cancel
      </button>
      <button
        type="submit"
        class="btn btn-primary"
        :disabled="loading"
      >
        {{ editMode ? 'Update' : 'Create' }} User
      </button>
    </div>
  </form>
</template> 