<template>
  <Dialog as="div" class="relative z-50" @close="closeModal">
    <TransitionChild
      as="template"
      enter="duration-300 ease-out"
      enter-from="opacity-0"
      enter-to="opacity-100"
      leave="duration-200 ease-in"
      leave-from="opacity-100"
      leave-to="opacity-0"
    >
      <DialogOverlay class="fixed inset-0 bg-black/50" />
    </TransitionChild>

    <div class="fixed inset-0 overflow-y-auto">
      <div class="flex min-h-full items-center justify-center p-4">
        <TransitionChild
          as="template"
          enter="duration-300 ease-out"
          enter-from="opacity-0 scale-95"
          enter-to="opacity-100 scale-100"
          leave="duration-200 ease-in"
          leave-from="opacity-100 scale-100"
          leave-to="opacity-0 scale-95"
        >
          <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-lg bg-surface-light dark:bg-surface-dark p-6 text-left align-middle shadow-xl transition-all">
            <div class="flex justify-between items-center mb-4">
              <DialogTitle as="h3" class="text-lg font-medium">
                Add a new payment Method
              </DialogTitle>
              <button 
                @click="closeModal"
                class="text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary"
              >
                <i class="ri-close-line text-xl"></i>
              </button>
            </div>

            <p class="text-sm text-text-light-secondary dark:text-text-dark-secondary mb-6">
              Select Media / Fans4more is fully compliant with Payment Card Industry Data Security Standards.
            </p>

            <form @submit.prevent="handleSubmit" class="space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <input
                    type="text"
                    placeholder="First/Given Name"
                    v-model="formData.firstName"
                    class="w-full px-3 py-2 bg-background-light dark:bg-background-dark rounded border border-border-light dark:border-border-dark focus:outline-none focus:border-primary-light dark:focus:border-primary-dark"
                  />
                </div>
                <div>
                  <input
                    type="text"
                    placeholder="Last Name"
                    v-model="formData.lastName"
                    class="w-full px-3 py-2 bg-background-light dark:bg-background-dark rounded border border-border-light dark:border-border-dark focus:outline-none focus:border-primary-light dark:focus:border-primary-dark"
                  />
                </div>
              </div>

              <input
                type="text"
                placeholder="Address"
                v-model="formData.address"
                class="w-full px-3 py-2 bg-background-light dark:bg-background-dark rounded border border-border-light dark:border-border-dark focus:outline-none focus:border-primary-light dark:focus:border-primary-dark"
              />

              <input
                type="text"
                placeholder="City"
                v-model="formData.city"
                class="w-full px-3 py-2 bg-background-light dark:bg-background-dark rounded border border-border-light dark:border-border-dark focus:outline-none focus:border-primary-light dark:focus:border-primary-dark"
              />

              <div class="grid grid-cols-2 gap-4">
                <select
                  v-model="formData.country"
                  class="w-full px-3 py-2 bg-background-light dark:bg-background-dark rounded border border-border-light dark:border-border-dark focus:outline-none focus:border-primary-light dark:focus:border-primary-dark"
                >
                  <option value="US">United States of America (the)</option>
                </select>

                <select
                  v-model="formData.state"
                  class="w-full px-3 py-2 bg-background-light dark:bg-background-dark rounded border border-border-light dark:border-border-dark focus:outline-none focus:border-primary-light dark:focus:border-primary-dark"
                >
                  <option value="">Please Select</option>
                  <!-- Add US states here -->
                </select>
              </div>

              <input
                type="text"
                placeholder="Zip Code"
                v-model="formData.zipCode"
                class="w-full px-3 py-2 bg-background-light dark:bg-background-dark rounded border border-border-light dark:border-border-dark focus:outline-none focus:border-primary-light dark:focus:border-primary-dark"
              />

              <input
                type="text"
                placeholder="Card Number"
                v-model="formData.cardNumber"
                class="w-full px-3 py-2 bg-background-light dark:bg-background-dark rounded border border-border-light dark:border-border-dark focus:outline-none focus:border-primary-light dark:focus:border-primary-dark"
              />

              <input
                type="text"
                placeholder="CVV/CVC"
                v-model="formData.cvv"
                class="w-full px-3 py-2 bg-background-light dark:bg-background-dark rounded border border-border-light dark:border-border-dark focus:outline-none focus:border-primary-light dark:focus:border-primary-dark"
              />

              <div class="grid grid-cols-2 gap-4">
                <select
                  v-model="formData.expMonth"
                  class="w-full px-3 py-2 bg-background-light dark:bg-background-dark rounded border border-border-light dark:border-border-dark focus:outline-none focus:border-primary-light dark:focus:border-primary-dark"
                >
                  <option value="">Card Exp Month</option>
                  <option v-for="month in 12" :key="month" :value="month">
                    {{ month.toString().padStart(2, '0') }}
                  </option>
                </select>

                <select
                  v-model="formData.expYear"
                  class="w-full px-3 py-2 bg-background-light dark:bg-background-dark rounded border border-border-light dark:border-border-dark focus:outline-none focus:border-primary-light dark:focus:border-primary-dark"
                >
                  <option value="">Card Exp Year</option>
                  <option v-for="year in 10" :key="year" :value="currentYear + year">
                    {{ currentYear + year }}
                  </option>
                </select>
              </div>

              <div class="flex items-center justify-between mt-6">
                <div class="flex gap-2">
                  <img src="/mc.svg" alt="Mastercard" class="h-6" />
                  <img src="/visa.png" alt="Visa" class="h-6" />
                  <img src="/discover.png" alt="Discover" class="h-6" />
                </div>
                <button
                  type="submit"
                  class="px-4 py-2 bg-primary-light dark:bg-primary-dark text-white rounded hover:opacity-90"
                >
                  Add Card
                </button>
              </div>
            </form>

            <p class="text-sm text-text-light-secondary dark:text-text-dark-secondary mt-4">
              Fans4more will make a one-time charge up to $0.10 to verify the card. This and all following charges show as <span class="text-red-500">Select Media</span> or <span class="text-red-500">FANS4MORE</span>.
            </p>
          </DialogPanel>
        </TransitionChild>
      </div>
    </div>
  </Dialog>
</template>

<script setup>
import { ref, computed } from 'vue'
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  DialogOverlay,
  TransitionChild,
} from '@headlessui/vue'

const emit = defineEmits(['close'])

const currentYear = new Date().getFullYear()

const formData = ref({
  firstName: '',
  lastName: '',
  address: '',
  city: '',
  country: 'US',
  state: '',
  zipCode: '',
  cardNumber: '',
  cvv: '',
  expMonth: '',
  expYear: ''
})

const closeModal = () => {
  emit('close')
}

const handleSubmit = () => {
  // Handle form submission
  console.log('Form submitted:', formData.value)
  closeModal()
}
</script>