"use client"

import { ref } from "vue"

// Simple toast notification system
const toasts = ref([])
let toastId = 0

export function useToast() {
  const addToast = (message, type, duration = 3000) => {
    const id = toastId++
    const toast = { id, message, type, duration }
    toasts.value.push(toast)

    setTimeout(() => {
      removeToast(id)
    }, duration)

    return id
  }

  const removeToast = (id) => {
    const index = toasts.value.findIndex((toast) => toast.id === id)
    if (index !== -1) {
      toasts.value.splice(index, 1)
    }
  }

  return {
    toasts,
    success: (message, duration) => addToast(message, "success", duration),
    error: (message, duration) => addToast(message, "error", duration),
    info: (message, duration) => addToast(message, "info", duration),
    warning: (message, duration) => addToast(message, "warning", duration),
    removeToast,
  }
}

