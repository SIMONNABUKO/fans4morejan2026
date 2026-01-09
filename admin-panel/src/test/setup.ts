import { beforeEach, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import type { Mock } from 'vitest'

// Mock localStorage
export const localStorageMock = {
  getItem: vi.fn(),
  setItem: vi.fn(),
  removeItem: vi.fn(),
  clear: vi.fn(),
  length: 0,
  key: vi.fn(),
  [Symbol.iterator]: vi.fn()
} as unknown as Storage & { [key: string]: Mock }

// Set up global localStorage
global.localStorage = localStorageMock

// Setup Pinia for testing
beforeEach(() => {
  setActivePinia(createPinia())
  vi.clearAllMocks()
  localStorageMock.clear()
}) 