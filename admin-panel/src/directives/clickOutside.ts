import type { DirectiveBinding } from 'vue'

interface ExtendedHTMLElement extends HTMLElement {
  clickOutsideEvent?: (event: Event) => void
}

export const clickOutside = {
  mounted(el: ExtendedHTMLElement, binding: DirectiveBinding) {
    el.clickOutsideEvent = (event: Event) => {
      if (!(el === event.target || el.contains(event.target as Node))) {
        binding.value(event)
      }
    }
    document.addEventListener('click', el.clickOutsideEvent)
  },
  unmounted(el: ExtendedHTMLElement) {
    if (el.clickOutsideEvent) {
      document.removeEventListener('click', el.clickOutsideEvent)
    }
  }
} 