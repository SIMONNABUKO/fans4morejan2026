import { computed } from 'vue'
import { useSettingsStore } from '@/stores/settingsStore'

export function useBlur() {
  const settingsStore = useSettingsStore()

  // Get blur level from settings (using sensitive content blur for both posts and messages)
  const blurLevel = computed(() => settingsStore.privacyAndSecurity.sensitiveContentLevel || 0)

  // Blur intensity mapping
  const blurIntensities = {
    0: 'none',
    1: 'light',
    2: 'medium', 
    3: 'strong'
  }

  // CSS blur values for each level
  const blurValues = {
    0: '0px',
    1: '4px',
    2: '8px',
    3: '16px'
  }

  // Get CSS blur value
  const getBlurValue = computed(() => {
    return blurValues[blurLevel.value] || '0px'
  })

  // Check if content should be blurred
  const shouldBlurContent = computed(() => {
    return blurLevel.value > 0
  })

  // Get blur class for content (works for both posts and messages)
  const getBlurClass = computed(() => {
    if (!shouldBlurContent.value) return ''
    return `blur-content-${blurLevel.value}`
  })

  // Update blur settings
  const updateBlur = async (level) => {
    try {
      await settingsStore.updateSettings('privacyAndSecurity', {
        sensitiveContentLevel: level
      })
    } catch (error) {
      console.error('Error updating blur settings:', error)
      throw error
    }
  }

  // Check if content is sensitive (placeholder - this would be determined by backend)
  const isSensitiveContent = (content) => {
    // This is a placeholder. In a real implementation, this would be determined by:
    // 1. Backend API response indicating content sensitivity
    // 2. User's own content sensitivity settings
    // 3. AI/ML detection of sensitive content
    // 4. Manual tagging by content creators
    
    // For now, we'll return false as the backend should handle this
    return false
  }

  return {
    // State
    blurLevel,
    shouldBlurContent,
    
    // Computed values
    getBlurValue,
    getBlurClass,
    
    // Methods
    updateBlur,
    isSensitiveContent,
    
    // Constants
    blurIntensities,
    blurValues
  }
} 