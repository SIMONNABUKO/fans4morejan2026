<template>
<div class="min-h-screen bg-gradient-to-b from-background-light via-gray-50 to-background-light dark:from-background-dark dark:via-gray-900 dark:to-background-dark">
  <!-- Modern Header -->
  <header class="sticky top-0 z-20 bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-lg border-b border-border-light dark:border-border-dark shadow-sm">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between pt-6 pb-8">
        <!-- Left Side: Navigation and Title -->
        <div class="flex items-center gap-4">
          <router-link 
            to="/become-creator" 
            class="p-2 rounded-lg bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/20 dark:border-gray-700/50 text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary hover:bg-white/70 dark:hover:bg-gray-800/70 transition-all duration-200 hover:scale-105 shadow-sm hover:shadow-md"
          >
            <i class="ri-arrow-left-line text-lg"></i>
          </router-link>
          
          <div class="flex flex-col gap-1.5">
            <h1 class="text-2xl font-bold text-text-light-primary dark:text-text-dark-primary leading-tight">
              {{ isUpdate ? t('update_creator_application') : t('verified_user_application') }}
            </h1>
            <p class="text-text-light-secondary dark:text-text-dark-secondary text-sm">
              {{ isUpdate ? t('update_application_info') : t('verification_info') }}
            </p>
          </div>
        </div>
        
        <!-- Right Side: Application Status -->
        <div v-if="applicationStatus" class="hidden md:flex items-center gap-4">
          <div class="px-4 py-2 rounded-full font-semibold text-sm"
               :class="{
                 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400': applicationStatus === 'pending',
                 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400': applicationStatus === 'approved',
                 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400': applicationStatus === 'rejected'
               }">
            {{ applicationStatus.charAt(0).toUpperCase() + applicationStatus.slice(1) }}
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Application Status Banner -->
    <div v-if="applicationStatus" class="mb-8">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border overflow-hidden animate-slideDown"
           :class="{
             'border-yellow-200 dark:border-yellow-800': applicationStatus === 'pending',
             'border-green-200 dark:border-green-800': applicationStatus === 'approved',
             'border-red-200 dark:border-red-800': applicationStatus === 'rejected'
           }">
        <div class="p-6"
             :class="{
               'bg-gradient-to-r from-yellow-50 to-amber-50 dark:from-yellow-900/20 dark:to-amber-900/20': applicationStatus === 'pending',
               'bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20': applicationStatus === 'approved',
               'bg-gradient-to-r from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20': applicationStatus === 'rejected'
             }">
          <div class="flex items-start gap-4">
            <div class="flex-shrink-0">
              <div class="w-14 h-14 rounded-2xl flex items-center justify-center shadow-lg"
                   :class="{
                     'bg-yellow-500': applicationStatus === 'pending',
                     'bg-green-500': applicationStatus === 'approved',
                     'bg-red-500': applicationStatus === 'rejected'
                   }">
                <i v-if="applicationStatus === 'pending'" class="ri-time-line text-white text-2xl"></i>
                <i v-if="applicationStatus === 'approved'" class="ri-checkbox-circle-fill text-white text-2xl"></i>
                <i v-if="applicationStatus === 'rejected'" class="ri-close-circle-fill text-white text-2xl"></i>
              </div>
            </div>
            <div class="flex-1">
              <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                {{ t('application_status', { status: applicationStatus.charAt(0).toUpperCase() + applicationStatus.slice(1) }) }}
              </h3>
              <div v-if="applicationFeedback" class="text-gray-700 dark:text-gray-300 leading-relaxed">
                <p>{{ t('feedback', { feedback: applicationFeedback }) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-8">
      <!-- Personal Information -->
      <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden transform hover:shadow-xl transition-shadow duration-300">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/10 dark:to-emerald-900/10">
          <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-gradient-to-br from-green-500 to-emerald-500 rounded-2xl flex items-center justify-center shadow-lg">
              <i class="ri-user-settings-line text-white text-2xl"></i>
            </div>
            <div>
              <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-1">{{ t('personal_information') }}</h2>
              <p class="text-sm text-gray-600 dark:text-gray-400">{{ t('account_settings') }}</p>
            </div>
          </div>
        </div>
        
        <div class="p-8">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="transform hover:scale-[1.02] transition-transform duration-200">
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ t('first_name') }}</label>
              <input
                v-model="form.firstName"
                type="text"
                :placeholder="t('first_name')"
                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border-2 border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200"
                :class="{ 'border-red-500 focus:ring-red-500': !form.firstName && formSubmitted }"
                required
              />
              <span v-if="!form.firstName && formSubmitted" class="text-red-500 text-sm mt-1 flex items-center gap-1">
                <i class="ri-error-warning-line"></i>
                {{ t('first_name_required') }}
              </span>
            </div>
            
            <div class="transform hover:scale-[1.02] transition-transform duration-200">
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ t('last_name') }}</label>
              <input
                v-model="form.lastName"
                type="text"
                :placeholder="t('last_name')"
                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border-2 border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200"
                :class="{ 'border-red-500 focus:ring-red-500': !form.lastName && formSubmitted }"
                required
              />
              <span v-if="!form.lastName && formSubmitted" class="text-red-500 text-sm mt-1 flex items-center gap-1">
                <i class="ri-error-warning-line"></i>
                {{ t('last_name_required') }}
              </span>
            </div>
            
            <div class="transform hover:scale-[1.02] transition-transform duration-200">
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ t('birthday') }}</label>
              <input
                v-model="form.birthday"
                type="date"
                :placeholder="t('birthday')"
                :max="maxDate"
                :min="minDate"
                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border-2 border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200"
                :class="{ 'border-red-500 focus:ring-red-500': (birthdayError || (!form.birthday && formSubmitted)) }"
                @change="validateAge"
                required
              />
              <span v-if="birthdayError" class="text-red-500 text-sm mt-1 flex items-center gap-1">
                <i class="ri-error-warning-line"></i>
                {{ birthdayError }}
              </span>
              <span v-else-if="!form.birthday && formSubmitted" class="text-red-500 text-sm mt-1 flex items-center gap-1">
                <i class="ri-error-warning-line"></i>
                {{ t('birthday_required') }}
              </span>
            </div>
            
            <div class="transform hover:scale-[1.02] transition-transform duration-200">
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ t('address') }}</label>
              <input
                v-model="form.address"
                type="text"
                :placeholder="t('address')"
                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border-2 border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200"
                required
              />
            </div>
            
            <div class="transform hover:scale-[1.02] transition-transform duration-200">
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ t('city') }}</label>
              <input
                v-model="form.city"
                type="text"
                :placeholder="t('city')"
                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border-2 border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200"
                required
              />
            </div>
            
            <div class="transform hover:scale-[1.02] transition-transform duration-200">
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ t('country') }}</label>
              <select
                v-model="form.country"
                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border-2 border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200"
                required
              >
                <option value="">{{ t('country') }}</option>
                <option value="US">United States</option>
              </select>
            </div>
            
            <div class="transform hover:scale-[1.02] transition-transform duration-200">
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">State/Province</label>
              <input
                v-model="form.state"
                type="text"
                placeholder="State/province"
                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border-2 border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200"
                required
              />
            </div>
            
            <div class="transform hover:scale-[1.02] transition-transform duration-200">
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Zip Code</label>
              <input
                v-model="form.zipCode"
                type="text"
                placeholder="Zip code/ Postal code"
                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border-2 border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200"
                :class="{ 'border-red-500 focus:ring-red-500': !form.zipCode && formSubmitted }"
                required
              />
              <span v-if="!form.zipCode && formSubmitted" class="text-red-500 text-sm mt-1 flex items-center gap-1">
                <i class="ri-error-warning-line"></i>
                Zip code is required
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Documentation -->
      <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden transform hover:shadow-xl transition-shadow duration-300">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/10 dark:to-pink-900/10">
          <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center shadow-lg">
              <i class="ri-file-shield-line text-white text-2xl"></i>
            </div>
            <div>
              <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-1">{{ t('document_verification') }}</h2>
              <p class="text-sm text-gray-600 dark:text-gray-400">
                In order to process your application, we require the following files from you:
              </p>
            </div>
          </div>
        </div>
        
        <div class="p-8">
          <!-- Requirements List -->
          <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl p-6 mb-8 border border-blue-200 dark:border-blue-700">
            <h3 class="font-bold text-blue-900 dark:text-blue-100 mb-4 flex items-center gap-2">
              <i class="ri-clipboard-line text-xl"></i>
              Required Documents:
            </h3>
            <ul class="space-y-3 text-sm text-blue-800 dark:text-blue-200">
              <li class="flex items-start gap-3">
                <i class="ri-checkbox-circle-fill text-blue-600 dark:text-blue-400 text-lg mt-0.5"></i>
                <span>A picture of the front of your state ID document</span>
              </li>
              <li class="flex items-start gap-3">
                <i class="ri-checkbox-circle-fill text-blue-600 dark:text-blue-400 text-lg mt-0.5"></i>
                <span>A picture of the back of your state ID document</span>
              </li>
            </ul>
          </div>

          <!-- Rules -->
          <div class="mb-8 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/10 dark:to-emerald-900/10 rounded-2xl p-6 border border-green-200 dark:border-green-700">
            <h3 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <i class="ri-file-check-line text-green-600 dark:text-green-400"></i>
              Rules for Fast Approval
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                <div class="flex items-center gap-2">
                  <i class="ri-checkbox-circle-fill text-green-600 dark:text-green-400"></i>
                  <span>Image must be clear</span>
                </div>
                <div class="flex items-center gap-2">
                  <i class="ri-checkbox-circle-fill text-green-600 dark:text-green-400"></i>
                  <span>Your ID must be fully in frame</span>
                </div>
                <div class="flex items-center gap-2">
                  <i class="ri-checkbox-circle-fill text-green-600 dark:text-green-400"></i>
                  <span>Must be in color</span>
                </div>
                <div class="flex items-center gap-2">
                  <i class="ri-checkbox-circle-fill text-green-600 dark:text-green-400"></i>
                  <span>Text must be clearly visible</span>
                </div>
                <div class="flex items-center gap-2">
                  <i class="ri-checkbox-circle-fill text-green-600 dark:text-green-400"></i>
                  <span>Background must be minimal</span>
                </div>
              </div>
              <div class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                <div class="flex items-center gap-2">
                  <i class="ri-checkbox-circle-fill text-green-600 dark:text-green-400"></i>
                  <span>Image should not be edited, resized or rotated</span>
                </div>
                <div class="flex items-center gap-2">
                  <i class="ri-checkbox-circle-fill text-green-600 dark:text-green-400"></i>
                  <span>Image files must be .png or .jpg</span>
                </div>
                <div class="flex items-center gap-2">
                  <i class="ri-checkbox-circle-fill text-green-600 dark:text-green-400"></i>
                  <span>Must be under 7MB in size</span>
                </div>
                <div class="flex items-center gap-2">
                  <i class="ri-checkbox-circle-fill text-green-600 dark:text-green-400"></i>
                  <span>ID must be valid and not expired</span>
                </div>
                <div class="flex items-center gap-2">
                  <i class="ri-checkbox-circle-fill text-green-600 dark:text-green-400"></i>
                  <span>Facial verification is required</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Document Upload -->
          <div class="space-y-6">
            <div class="transform hover:scale-[1.02] transition-transform duration-200">
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 flex items-center gap-2">
                <i class="ri-file-list-line text-purple-600 dark:text-purple-400"></i>
                {{ t('select_document_type') }}
              </label>
              <select
                v-model="form.documentType"
                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border-2 border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200"
                :class="{ 'border-red-500 focus:ring-red-500': !form.documentType && formSubmitted }"
                required
              >
                <option value="">{{ t('select_document_type') }}</option>
                <option value="driving_license">{{ t('driving_license') }}</option>
                <option value="state_id">{{ t('state_id') }}</option>
              </select>
              <span v-if="!form.documentType && formSubmitted" class="text-red-500 text-sm mt-1 flex items-center gap-1">
                <i class="ri-error-warning-line"></i>
                {{ t('document_type_required') }}
              </span>
            </div>
            
            <!-- File Upload Sections -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Front ID -->
              <div class="group bg-gradient-to-br from-purple-50 to-blue-50 dark:from-purple-900/10 dark:to-blue-900/10 rounded-2xl p-6 border-2 border-dashed border-purple-300 dark:border-purple-700 hover:border-purple-500 dark:hover:border-purple-500 transition-all duration-300 hover:shadow-lg">
                <div class="text-center">
                  <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-blue-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <i class="ri-camera-line text-white text-2xl"></i>
                  </div>
                  <h4 class="font-bold text-gray-900 dark:text-white mb-2">Front of ID</h4>
                  <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Upload the front side of your document</p>
                  <label class="cursor-pointer">
                    <input
                      type="file"
                      accept="image/png,image/jpeg"
                      class="hidden"
                      @change="handleFileUpload($event, 'frontId')"
                      :required="!isUpdate && !form.frontId"
                    />
                    <div class="px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white rounded-xl font-semibold transition-all duration-200 hover:scale-105 shadow-lg">
                      Choose File
                    </div>
                  </label>
                  <div v-if="form.frontId" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full text-sm font-medium">
                    <i class="ri-checkbox-circle-fill"></i>
                    File uploaded successfully
                  </div>
                </div>
              </div>

              <!-- Back ID -->
              <div class="group bg-gradient-to-br from-purple-50 to-blue-50 dark:from-purple-900/10 dark:to-blue-900/10 rounded-2xl p-6 border-2 border-dashed border-purple-300 dark:border-purple-700 hover:border-purple-500 dark:hover:border-purple-500 transition-all duration-300 hover:shadow-lg">
                <div class="text-center">
                  <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-blue-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <i class="ri-camera-line text-white text-2xl"></i>
                  </div>
                  <h4 class="font-bold text-gray-900 dark:text-white mb-2">Back of ID</h4>
                  <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Upload the back side of your document</p>
                  <label class="cursor-pointer">
                    <input
                      type="file"
                      accept="image/png,image/jpeg"
                      class="hidden"
                      @change="handleFileUpload($event, 'backId')"
                      :required="!isUpdate && !form.backId"
                    />
                    <div class="px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white rounded-xl font-semibold transition-all duration-200 hover:scale-105 shadow-lg">
                      Choose File
                    </div>
                  </label>
                  <div v-if="form.backId" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full text-sm font-medium">
                    <i class="ri-checkbox-circle-fill"></i>
                    File uploaded successfully
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Terms of Service Agreement -->
      <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/10 dark:to-indigo-900/10 rounded-2xl p-6 border border-blue-200 dark:border-blue-700">
        <label class="flex items-start gap-3 cursor-pointer">
          <input
            type="checkbox"
            v-model="form.agreedToTerms"
            class="mt-1 w-5 h-5 rounded border-2 border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200"
            :class="{ 'border-red-500': !form.agreedToTerms && formSubmitted }"
          />
          <div class="flex-1">
            <p class="text-sm text-gray-700 dark:text-gray-300 font-medium">
              I agree to the <a href="/terms" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">Terms of Service</a> and confirm that all information provided is accurate.
            </p>
            <span v-if="!form.agreedToTerms && formSubmitted" class="text-red-500 text-sm mt-1 flex items-center gap-1">
              <i class="ri-error-warning-line"></i>
              You must agree to the terms of service
            </span>
          </div>
        </label>
      </div>

      <!-- Submit Button -->
      <div class="flex justify-center">
        <button
          type="submit"
          :disabled="submitting"
          class="group px-8 py-4 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white rounded-xl font-bold text-lg transition-all duration-300 focus:ring-4 focus:ring-blue-500/50 disabled:opacity-50 disabled:cursor-not-allowed shadow-xl hover:shadow-2xl hover:scale-105"
        >
          <div class="flex items-center gap-3">
            <i v-if="submitting" class="ri-loader-4-line animate-spin text-xl"></i>
            <i v-else class="ri-send-plane-fill text-xl group-hover:translate-x-1 transition-transform"></i>
            {{ submitting ? t('submitting') : (isUpdate ? t('update_application') : t('submit_application')) }}
          </div>
        </button>
      </div>
    </form>
  </div>
</div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { useCreatorApplicationStore } from '@/stores/creatorApplicationStore'
import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toastification'

const { t } = useI18n()
const toast = useToast()

const authStore = useAuthStore()
const creatorApplicationStore = useCreatorApplicationStore()

const form = ref({
  firstName: '',
  lastName: '',
  birthday: '',
  address: '',
  city: '',
  country: '',
  state: '',
  zipCode: '',
  documentType: '',
  agreedToTerms: false,
  frontId: null,
  backId: null,
  previews: {
    frontId: null,
    backId: null
  }
})

const formSubmitted = ref(false)
const isUpdate = ref(false)
const applicationStatus = ref(null)
const applicationFeedback = ref(null)
const birthdayError = ref('')
const submitting = ref(false)

// Computed properties for date constraints
const maxDate = computed(() => {
  const today = new Date()
  return today.toISOString().split('T')[0]
})

const minDate = computed(() => {
  const today = new Date()
  const minDateObj = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate())
  return minDateObj.toISOString().split('T')[0]
})

const calculateAge = (birthday) => {
  const birthDate = new Date(birthday)
  const today = new Date()
  let age = today.getFullYear() - birthDate.getFullYear()
  const monthDiff = today.getMonth() - birthDate.getMonth()
  
  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
    age--
  }
  
  return age
}

const validateAge = () => {
  if (!form.value.birthday) {
    birthdayError.value = t('birthday_required')
    return false
  }
  
  // Check if date is within allowed range
  const selectedDate = new Date(form.value.birthday)
  const minDateObj = new Date(minDate.value)
  const maxDateObj = new Date(maxDate.value)
  
  if (selectedDate < minDateObj || selectedDate > maxDateObj) {
    birthdayError.value = 'You must be at least 18 years old to apply'
    return false
  }
  
  const age = calculateAge(form.value.birthday)
  if (age < 18) {
    birthdayError.value = 'You must be at least 18 years old to apply'
    return false
  }
  
  birthdayError.value = ''
  return true
}

const isFormValid = computed(() => {
  // For updates, only validate fields that are required for all submissions
  const baseValidation = 
    form.value.firstName &&
    form.value.lastName &&
    form.value.birthday &&
    validateAge() &&
    form.value.address &&
    form.value.city &&
    form.value.country &&
    form.value.state &&
    form.value.zipCode &&
    form.value.documentType &&
    form.value.agreedToTerms

  // For new applications, also validate required files
  if (!isUpdate.value) {
    return baseValidation &&
      form.value.frontId &&
      form.value.backId
  }

  return baseValidation
})

onMounted(async () => {
  try {
    // Fetch existing application data
    const existingApplication = await creatorApplicationStore.getApplicationByUserId()
    
    if (existingApplication) {
      isUpdate.value = true
      applicationStatus.value = existingApplication.status
      applicationFeedback.value = existingApplication.feedback
      
      // Map the server response to our form fields
      form.value = {
        firstName: existingApplication.first_name,
        lastName: existingApplication.last_name,
        birthday: existingApplication.birthday ? new Date(existingApplication.birthday).toISOString().split('T')[0] : '',
        address: existingApplication.address,
        city: existingApplication.city,
        country: existingApplication.country,
        state: existingApplication.state,
        zipCode: existingApplication.zip_code,
        documentType: existingApplication.document_type,
        agreedToTerms: true, // Assuming they agreed when they first submitted
        frontId: existingApplication.front_id,
        backId: existingApplication.back_id,
        previews: {
          frontId: existingApplication.front_id,
          backId: existingApplication.back_id
        }
      }
    } else {
      // Pre-fill the form with user data if available
      if (authStore.user) {
        form.value.firstName = authStore.user.firstName || ''
        form.value.lastName = authStore.user.lastName || ''
        form.value.birthday = authStore.user.birthday ? new Date(authStore.user.birthday).toISOString().split('T')[0] : ''
        form.value.address = authStore.user.address || ''
        form.value.city = authStore.user.city || ''
        form.value.country = authStore.user.country || ''
        form.value.state = authStore.user.state || ''
        form.value.zipCode = authStore.user.zipCode || ''
      }
      
      // Set default birthday to 18 years ago if not already set
      if (!form.value.birthday) {
        const today = new Date()
        const defaultDate = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate())
        form.value.birthday = defaultDate.toISOString().split('T')[0]
      }
    }
  } catch (error) {
    console.error('Error fetching application:', error)
    // Handle the error (e.g., show an error message to the user)
  }
})

const handleFileUpload = (event, field) => {
  const file = event.target.files[0]
  if (file) {
    // Check file size (7MB limit)
    if (file.size > 7 * 1024 * 1024) {
      toast.error(t('file_size_limit'))
      event.target.value = ''
      return
    }
    
    // Check file type
    if (!['image/jpeg', 'image/png'].includes(file.type)) {
      toast.error(t('file_type_requirement'))
      event.target.value = ''
      return
    }
    
    // Create preview URL
    if (form.value.previews[field]) {
      URL.revokeObjectURL(form.value.previews[field])
    }
    form.value.previews[field] = URL.createObjectURL(file)
    form.value[field] = file
  }
}

const handleFileRemove = (field) => {
  if (form.value.previews[field]) {
    URL.revokeObjectURL(form.value.previews[field])
    form.value.previews[field] = null
  }
  form.value[field] = null
}

const handleSubmit = async () => {
  formSubmitted.value = true
  if (!isFormValid.value) {
    toast.error('Please fill in all required fields and agree to the terms of service.')
    return
  }

  const formData = new FormData()

  // Convert camelCase to snake_case and add to formData
  const snakeCaseMapping = {
    firstName: 'first_name',
    lastName: 'last_name',
    zipCode: 'zip_code',
    documentType: 'document_type',
    frontId: 'front_id',
    backId: 'back_id',
    agreedToTerms: 'agreed_to_terms'
  }

  // Validate required fields before submission
  const requiredFields = [
    'firstName',
    'lastName',
    'birthday',
    'address',
    'city',
    'country',
    'state',
    'zipCode',
    'documentType'
  ]

  const missingFields = requiredFields.filter(field => !form.value[field])
  if (missingFields.length > 0) {
    console.error('Missing required fields:', missingFields)
    toast.error(`Missing required fields: ${missingFields.join(', ')}`)
    return
  }

  // Validate required files for new applications
  if (!isUpdate.value) {
    const requiredFiles = ['frontId', 'backId']
    const missingFiles = requiredFiles.filter(field => !form.value[field])
    if (missingFiles.length > 0) {
      console.error('Missing required files:', missingFiles)
      toast.error(`Missing required files: ${missingFiles.join(', ')}`)
      return
    }
  }

  // Add text fields with snake_case names
  Object.keys(form.value).forEach(key => {
    if (key !== 'previews') {
      const snakeKey = snakeCaseMapping[key] || key
      if (typeof form.value[key] !== 'object' || form.value[key] instanceof File) {
        // For boolean values like agreedToTerms, convert to string
        const value = typeof form.value[key] === 'boolean' ? form.value[key].toString() : form.value[key]
        formData.append(snakeKey, value)
      }
    }
  })

  try {
    submitting.value = true
    const result = await creatorApplicationStore.submitApplication(formData)
    if (result.success) {
      toast.success(result.message || 'Application submitted successfully!')
      // Optionally, redirect to a success page or clear the form
    } else {
      console.error('Submission failed:', result.errors)
      let errorMessage = 'Failed to submit application. Please check the following: '
      if (result.errors) {
        const errorsList = Object.entries(result.errors).map(([field, errors]) => {
          return `${field}: ${errors.join(', ')}`
        })
        errorMessage += errorsList.join('; ')
      }
      toast.error(errorMessage)
    }
  } catch (error) {
    console.error('Error submitting form:', error)
    if (error.response?.data?.errors) {
      const errorsList = Object.entries(error.response.data.errors).map(([field, errors]) => {
        return `${field}: ${errors.join(', ')}`
      })
      toast.error('Validation failed: ' + errorsList.join('; '))
    } else {
      toast.error('An unexpected error occurred. Please try again.')
    }
  } finally {
    submitting.value = false
  }
}

// Cleanup preview URLs when component is unmounted
onMounted(() => {
  onUnmounted(() => {
    Object.values(form.value.previews).forEach(previewUrl => {
      if (previewUrl) {
        URL.revokeObjectURL(previewUrl)
      }
    })
  })
})
</script>

<style scoped>
@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-slideDown {
  animation: slideDown 0.5s ease-out;
}
</style>
