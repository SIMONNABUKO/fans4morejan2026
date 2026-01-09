<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark">
    <!-- Mobile View -->
    <div class="lg:hidden">
      <!-- Mobile Header -->
      <header class="bg-surface-light dark:bg-surface-dark shadow-sm">
        <div class="px-4 py-4 flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-br from-primary-light to-primary-dark rounded-xl flex items-center justify-center">
              <img src="/fslogo.png" alt="Fans4More" class="w-6 h-6 object-contain" />
            </div>
            <div>
              <h1 class="text-xl font-bold text-text-light-primary dark:text-text-dark-primary">Fans4More</h1>
              <p class="text-xs text-text-light-secondary dark:text-text-dark-secondary">Terms & Conditions</p>
            </div>
          </div>
          <div class="flex items-center space-x-3">
            <!-- Theme Toggle -->
            <button 
              @click="toggleTheme"
              class="p-2 text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary hover:bg-glassmorphism-light-tertiary dark:hover:bg-glassmorphism-dark-tertiary rounded-xl transition-all duration-200"
            >
              <i :class="theme === 'dark' ? 'ri-sun-line' : 'ri-moon-line'" class="text-xl"></i>
            </button>
            <router-link 
              to="/auth"
              class="text-sm font-medium text-primary-light dark:text-primary-dark hover:text-gradient-blue-to dark:hover:text-gradient-blue-from"
            >
              Sign In
            </router-link>
          </div>
        </div>
      </header>

      <!-- Mobile Content -->
      <section class="px-4 py-8">
        <div class="max-w-sm mx-auto">
          <h2 class="text-2xl font-bold text-text-light-primary dark:text-text-dark-primary mb-3 text-center">
            Terms and Conditions
          </h2>
          <p class="text-text-light-secondary dark:text-text-dark-secondary mb-6 text-center">
            Last updated: {{ lastUpdated }}
          </p>
          
          <!-- Loading State -->
          <div v-if="loading" class="text-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-3 border-primary-light border-t-transparent mx-auto mb-3"></div>
            <span class="text-text-light-secondary dark:text-text-dark-secondary">Loading Terms and Conditions...</span>
          </div>

          <!-- Error State -->
          <div v-else-if="error" class="text-center py-8">
            <div class="w-16 h-16 bg-error-light/10 dark:bg-error-dark/20 rounded-full flex items-center justify-center mx-auto mb-4">
              <i class="ri-error-warning-line text-error-light dark:text-error-dark text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-text-light-primary dark:text-text-dark-primary mb-2">Error Loading Terms</h3>
            <p class="text-text-light-secondary dark:text-text-dark-secondary mb-4">{{ error }}</p>
            <button @click="manualRetry" class="px-4 py-2 bg-primary-light text-background-light rounded-lg hover:bg-primary-dark transition-colors">
              Try Again
            </button>
          </div>

          <!-- Content -->
          <div v-else-if="termsData" class="bg-surface-light dark:bg-surface-dark rounded-2xl shadow-lg border border-border-light dark:border-border-dark p-6">
            <div class="prose prose-sm max-w-none text-text-light-primary dark:text-text-dark-primary" v-html="termsData.content"></div>
          </div>
        </div>
      </section>
    </div>

    <!-- Desktop View -->
    <div class="hidden lg:flex min-h-screen">
      <!-- Left Side - Content -->
      <div class="flex-1 flex flex-col">
        <!-- Desktop Header -->
        <header class="bg-surface-light dark:bg-surface-dark border-b border-border-light dark:border-border-dark">
          <div class="px-8 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="w-12 h-12 bg-gradient-to-br from-primary-light to-primary-dark rounded-2xl flex items-center justify-center">
                <img src="/fslogo.png" alt="Fans4More" class="w-8 h-8 object-contain" />
              </div>
              <div>
                <h1 class="text-2xl font-bold text-text-light-primary dark:text-text-dark-primary">Fans4More</h1>
                <p class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Terms & Conditions</p>
              </div>
            </div>
            
            <div class="flex items-center space-x-4">
              <!-- Theme Toggle -->
              <button 
                @click="toggleTheme"
                class="p-2 text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary hover:bg-glassmorphism-light-tertiary dark:hover:bg-glassmorphism-dark-tertiary rounded-lg transition-all duration-200"
              >
                <i :class="theme === 'dark' ? 'ri-sun-line' : 'ri-moon-line'" class="text-xl"></i>
              </button>
              <router-link 
                to="/auth"
                class="text-sm text-primary-light dark:text-primary-dark hover:text-gradient-blue-to dark:hover:text-gradient-blue-from"
              >
                Sign In
              </router-link>
            </div>
          </div>
        </header>

        <!-- Desktop Content -->
        <div class="flex-1 flex items-center justify-center px-8 py-12">
          <div class="max-w-4xl w-full">
            <!-- Loading State -->
            <div v-if="loading" class="text-center py-12">
              <div class="animate-spin rounded-full h-12 w-12 border-4 border-primary-light border-t-transparent mx-auto mb-4"></div>
              <span class="text-text-light-secondary dark:text-text-dark-secondary">Loading Terms and Conditions...</span>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="text-center py-12">
              <div class="w-20 h-20 bg-error-light/10 dark:bg-error-dark/20 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="ri-error-warning-line text-error-light dark:text-error-dark text-3xl"></i>
              </div>
              <h3 class="text-xl font-semibold text-text-light-primary dark:text-text-dark-primary mb-3">Error Loading Terms</h3>
              <p class="text-text-light-secondary dark:text-text-dark-secondary mb-6">{{ error }}</p>
              <button @click="manualRetry" class="px-6 py-3 bg-primary-light text-background-light rounded-lg hover:bg-primary-dark transition-colors">
                Try Again
              </button>
            </div>

            <!-- Content -->
            <div v-else-if="termsData" class="bg-surface-light dark:bg-surface-dark rounded-3xl shadow-2xl border border-border-light dark:border-border-dark overflow-hidden">
              <!-- Header with gradient -->
              <div class="bg-gradient-to-r from-primary-light to-primary-dark px-8 py-6 text-background-light">
                <div class="flex justify-between items-start">
                  <div>
                    <h2 class="text-2xl font-bold">Terms and Conditions</h2>
                    <p class="text-background-light/80 mt-1">Last updated: {{ lastUpdated }}</p>
                  </div>
                  <div class="flex items-center gap-3">
                    <div class="px-3 py-1 bg-background-light/20 rounded-full">
                      <span class="text-xs font-medium">Document ID: {{ documentId }}</span>
                    </div>
                    <div class="px-3 py-1 bg-background-light/20 rounded-full">
                      <span class="text-xs font-medium">Version {{ version }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Content -->
              <div class="p-8">
                <!-- Important Legal Notice -->
                <div class="bg-error-light/10 dark:bg-error-dark/20 border border-error-light/20 dark:border-error-dark/30 rounded-xl p-6 mb-8">
                  <div class="flex items-start gap-3">
                    <div class="w-6 h-6 bg-error-light/20 dark:bg-error-dark/30 rounded-lg flex items-center justify-center flex-shrink-0">
                      <i class="ri-error-warning-line text-error-light dark:text-error-dark text-sm"></i>
                    </div>
                    <div>
                      <h3 class="text-lg font-semibold text-error-light dark:text-error-dark mb-2">Important Legal Notice</h3>
                      <p class="text-sm text-error-light dark:text-error-dark leading-relaxed">
                        This document contains legally binding terms and conditions. By using our platform, you agree to be bound by these terms. 
                        Please read this document carefully before proceeding. If you do not agree to these terms, you must not use our services.
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Terms Content -->
                <div class="prose prose-lg max-w-none text-text-light-primary dark:text-text-dark-primary" v-html="termsData.content"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Side - Auth Panel -->
      <div class="w-96 bg-surface-light dark:bg-surface-dark border-l border-border-light dark:border-border-dark flex flex-col">
        <div class="flex-1 flex items-center justify-center p-8">
          <div class="w-full max-w-sm text-center">
            <div class="mb-8">
              <h3 class="text-2xl font-bold text-text-light-primary dark:text-text-dark-primary mb-2">
                Join Fans4More
              </h3>
              <p class="text-text-light-secondary dark:text-text-dark-secondary">
                Start your journey today
              </p>
            </div>

            <!-- Primary Actions -->
            <div class="space-y-4 mb-8">
              <router-link
                to="/auth"
                class="w-full py-4 bg-gradient-to-r from-primary-light to-primary-dark text-background-light rounded-2xl font-semibold hover:shadow-lg hover:shadow-primary-light/25 transition-all duration-200 flex items-center justify-center space-x-2"
              >
                <i class="ri-user-add-line"></i>
                <span>Create Account</span>
              </router-link>
              <router-link
                to="/auth"
                class="w-full py-4 bg-glassmorphism-light-secondary dark:bg-glassmorphism-dark-secondary text-text-light-primary dark:text-text-dark-primary rounded-2xl font-semibold hover:bg-glassmorphism-light-primary dark:hover:bg-glassmorphism-dark-primary transition-colors flex items-center justify-center space-x-2"
              >
                <i class="ri-login-box-line"></i>
                <span>Sign In</span>
              </router-link>
            </div>

            <!-- Divider -->
            <div class="relative mb-8">
              <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-border-light dark:border-border-dark"></div>
              </div>
              <div class="relative flex justify-center text-sm">
                <span class="px-4 bg-surface-light dark:bg-surface-dark text-text-light-secondary dark:text-text-dark-secondary">
                  Or continue with
                </span>
              </div>
            </div>

            <!-- Social Login -->
            <div class="space-y-3 mb-8">
              <button class="w-full py-3 bg-surface-light dark:bg-glassmorphism-dark-secondary border border-border-light dark:border-border-dark text-text-light-primary dark:text-text-dark-primary rounded-xl flex items-center justify-center space-x-3 hover:bg-glassmorphism-light-secondary dark:hover:bg-glassmorphism-dark-primary transition-colors">
                <i class="ri-google-fill text-xl text-error-light"></i>
                <span>Continue with Google</span>
              </button>
              <button class="w-full py-3 bg-info-light text-background-light rounded-xl flex items-center justify-center space-x-3 hover:bg-info-dark transition-colors">
                <i class="ri-twitter-fill text-xl"></i>
                <span>Continue with Twitter</span>
              </button>
              <button class="w-full py-3 bg-ui-highlight text-background-light rounded-xl flex items-center justify-center space-x-3 hover:bg-gradient-purple-to transition-colors">
                <i class="ri-twitch-fill text-xl"></i>
                <span>Continue with Twitch</span>
              </button>
            </div>

            <!-- Terms -->
            <p class="text-xs text-center text-text-light-secondary dark:text-text-dark-secondary leading-relaxed">
              By joining, you agree to our 
              <router-link to="/terms" class="text-primary-light dark:text-primary-dark hover:underline">Terms</router-link> and 
              <router-link to="/privacy" class="text-primary-light dark:text-primary-dark hover:underline">Privacy Policy</router-link>.
              <br>You must be 18+ to join.
            </p>
          </div>
        </div>

        <!-- Footer -->
        <Footer />
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="max-w-4xl mx-auto px-4 py-12">
      <div class="flex justify-center items-center">
        <div class="animate-spin rounded-full h-8 w-8 border-3 border-blue-600 border-t-transparent"></div>
        <span class="ml-3 text-gray-600 dark:text-gray-400">Loading Terms and Conditions...</span>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="max-w-4xl mx-auto px-4 py-12">
      <div class="text-center">
        <div class="w-16 h-16 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
          <i class="ri-error-warning-line text-red-600 dark:text-red-400 text-2xl"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Error Loading Terms</h3>
        <p class="text-gray-600 dark:text-gray-400 mb-4">{{ error }}</p>
        <button @click="manualRetry" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
          Try Again
        </button>
      </div>
    </div>

    <!-- Content -->
    <div v-else-if="termsData" class="max-w-4xl mx-auto px-4 py-8">
      <!-- Important Legal Notice -->
      <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-6 mb-8">
        <div class="flex items-start gap-3">
          <div class="w-6 h-6 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
            <i class="ri-error-warning-line text-red-600 dark:text-red-400 text-sm"></i>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-red-900 dark:text-red-100 mb-2">Important Legal Notice</h3>
            <p class="text-sm text-red-700 dark:text-red-300 leading-relaxed">
              This document contains legally binding terms and conditions. By using our platform, you agree to be bound by these terms. 
              Please read this document carefully before proceeding. If you do not agree to these terms, you must not use our services.
            </p>
          </div>
        </div>
      </div>

      <!-- Table of Contents -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Table of Contents</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
          <a href="#section-1" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
            1. Agreement to Terms
          </a>
          <a href="#section-2" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
            2. Additional Terms
          </a>
          <a href="#section-3" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
            3. Fees
          </a>
          <a href="#section-4" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
            4. Account Setup
          </a>
          <a href="#section-5" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
            5. Legal Responsibility
          </a>
          <a href="#section-6" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
            6. Transactions
          </a>
          <a href="#section-7" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
            7. Content Terms
          </a>
          <a href="#section-8" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
            8. Co-Authored Content
          </a>
          <a href="#section-9" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
            9. Payouts
          </a>
          <a href="#section-10" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
            10. Withholding
          </a>
          <a href="#section-11" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
            11. Tax Compliance
          </a>
          <a href="#section-12" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
            12. Section 2257
          </a>
          <a href="#section-13" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
            13. Complaints Policy
          </a>
        </div>
      </div>

      <!-- Main Content -->
      <div class="space-y-8">
        <!-- Section 1: Agreement -->
        <section id="section-1" class="scroll-mt-20">
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-sm">1</span>
              </div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ termsData.title }}</h3>
            </div>
            <div class="space-y-4 text-sm leading-relaxed">
              <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                <p class="text-yellow-800 dark:text-yellow-200 font-medium">{{ termsData.disclaimer }}</p>
              </div>
              <p class="text-gray-700 dark:text-gray-300">{{ termsData.fan_definition }}</p>
              <p class="text-gray-700 dark:text-gray-300">{{ termsData.agreement_intro }}</p>
            </div>
          </div>
        </section>

        <!-- Section 2: Additional Terms -->
        <section id="section-2" class="scroll-mt-20">
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-sm">2</span>
              </div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Additional Terms</h3>
            </div>
            <div class="space-y-3">
              <p class="text-sm text-gray-700 dark:text-gray-300">{{ termsData.additional_terms }}</p>
              <ul class="space-y-2 pl-6">
                <li v-for="(term, key) in termsData.terms_list" :key="key" class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed flex items-start gap-2 break-words">
                  <span class="w-1.5 h-1.5 bg-blue-500 rounded-full mt-2 flex-shrink-0"></span>
                  <span class="whitespace-normal">{{ term }}</span>
                </li>
              </ul>
              <p class="text-sm text-gray-700 dark:text-gray-300 mt-4">{{ termsData.europe_regulation }}</p>
              <p class="text-sm text-gray-700 dark:text-gray-300">{{ termsData.fan_terms }}</p>
            </div>
          </div>
        </section>

        <!-- Section 3: Fees -->
        <section id="section-3" class="scroll-mt-20">
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-sm">3</span>
              </div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ termsData.fees_title }}</h3>
            </div>
            <div class="space-y-3">
              <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ termsData.fees_description }}</p>
            </div>
          </div>
        </section>

        <!-- Section 4: Account Setup -->
        <section id="section-4" class="scroll-mt-20">
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-sm">4</span>
              </div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ termsData.account_setup_title }}</h3>
            </div>
            <div class="space-y-3">
              <ul class="space-y-2 pl-6">
                <li v-for="(requirement, index) in termsData.account_requirements" :key="index" class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed flex items-start gap-2 break-words">
                  <span class="w-1.5 h-1.5 bg-blue-500 rounded-full mt-2 flex-shrink-0"></span>
                  <span class="whitespace-normal">{{ requirement }}</span>
                </li>
              </ul>
            </div>
          </div>
        </section>

        <!-- Section 5: Legal Responsibility -->
        <section id="section-5" class="scroll-mt-20">
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-sm">5</span>
              </div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ termsData.legal_responsibility_title }}</h3>
            </div>
            <div class="space-y-3">
              <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ termsData.legal_responsibility_description }}</p>
            </div>
          </div>
        </section>

        <!-- Section 6: Transactions -->
        <section id="section-6" class="scroll-mt-20">
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-sm">6</span>
              </div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ termsData.transactions_title }}</h3>
            </div>
            <div class="space-y-3">
              <ul class="space-y-2 pl-6">
                <li v-for="(point, index) in termsData.transaction_points" :key="index" class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed flex items-start gap-2 break-words">
                  <span class="w-1.5 h-1.5 bg-blue-500 rounded-full mt-2 flex-shrink-0"></span>
                  <span class="whitespace-normal">{{ point }}</span>
                </li>
              </ul>
            </div>
          </div>
        </section>

        <!-- Section 7: Content -->
        <section id="section-7" class="scroll-mt-20">
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-sm">7</span>
              </div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ termsData.content_title }}</h3>
            </div>
            <div class="space-y-3">
              <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ termsData.content_intro }}</p>
              <ul class="space-y-2 pl-6">
                <li v-for="(point, index) in termsData.content_points" :key="index" class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed flex items-start gap-2 break-words">
                  <span class="w-1.5 h-1.5 bg-blue-500 rounded-full mt-2 flex-shrink-0"></span>
                  <span class="whitespace-normal">{{ point }}</span>
                </li>
              </ul>
            </div>
          </div>
        </section>

        <!-- Section 8: Co-Authored Content -->
        <section id="section-8" class="scroll-mt-20">
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-sm">8</span>
              </div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ termsData.co_authored_title }}</h3>
            </div>
            <div class="space-y-3">
              <ul class="space-y-2 pl-6">
                <li v-for="(point, index) in termsData.co_authored_points" :key="index" class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed flex items-start gap-2 break-words">
                  <span class="w-1.5 h-1.5 bg-blue-500 rounded-full mt-2 flex-shrink-0"></span>
                  <span class="whitespace-normal">{{ point }}</span>
                </li>
              </ul>
            </div>
          </div>
        </section>

        <!-- Section 9: Payouts -->
        <section id="section-9" class="scroll-mt-20">
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-sm">9</span>
              </div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ termsData.payouts_title }}</h3>
            </div>
            <div class="space-y-3">
              <ul class="space-y-2 pl-6">
                <li v-for="(point, index) in termsData.payout_points" :key="index" class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed flex items-start gap-2 break-words">
                  <span class="w-1.5 h-1.5 bg-blue-500 rounded-full mt-2 flex-shrink-0"></span>
                  <span class="whitespace-normal">{{ point }}</span>
                </li>
              </ul>
            </div>
          </div>
        </section>

        <!-- Section 10: Withholding -->
        <section id="section-10" class="scroll-mt-20">
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-sm">10</span>
              </div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ termsData.withholding_title }}</h3>
            </div>
            <div class="space-y-3">
              <ul class="space-y-2 pl-6">
                <li v-for="(point, index) in termsData.withholding_points" :key="index" class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed flex items-start gap-2 break-words">
                  <span class="w-1.5 h-1.5 bg-blue-500 rounded-full mt-2 flex-shrink-0"></span>
                  <span class="whitespace-normal">{{ point }}</span>
                </li>
              </ul>
            </div>
          </div>
        </section>

        <!-- Section 11: Tax -->
        <section id="section-11" class="scroll-mt-20">
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-sm">11</span>
              </div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ termsData.tax_title }}</h3>
            </div>
            <div class="space-y-3">
              <ul class="space-y-2 pl-6">
                <li v-for="(point, index) in termsData.tax_points" :key="index" class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed flex items-start gap-2 break-words">
                  <span class="w-1.5 h-1.5 bg-blue-500 rounded-full mt-2 flex-shrink-0"></span>
                  <span class="whitespace-normal">{{ point }}</span>
                </li>
              </ul>
            </div>
          </div>
        </section>

        <!-- Section 12: Section 2257 -->
        <section id="section-12" class="scroll-mt-20">
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-sm">12</span>
              </div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ termsData.section_2257_title }}</h3>
            </div>
            <div class="space-y-4">
              <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ termsData.section_2257_content_subject }}</p>
              <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ termsData.section_2257_exemption }}</p>
              <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ termsData.section_2257_title_work }}</p>
              <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ termsData.section_2257_records }}</p>
              <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ termsData.section_2257_company }}</p>
              </div>
            </div>
          </div>
        </section>

        <!-- Section 13: Complaints -->
        <section id="section-13" class="scroll-mt-20">
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-sm">13</span>
              </div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ termsData.complaints_title }}</h3>
            </div>
            <div class="space-y-4">
              <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ termsData.complaints_introduction }}</p>
              <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ termsData.complaints_contact }}</p>
              <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ termsData.complaints_purpose }}</p>
              <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ termsData.complaints_how_to_complain }}</p>
              <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ termsData.complaints_alternative_contact }}</p>
              
              <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <h4 class="text-base font-semibold text-red-900 dark:text-red-100 mb-3">{{ termsData.complaints_illegal_content_title }}</h4>
                <p class="text-sm text-red-700 dark:text-red-300 mb-3">{{ termsData.complaints_illegal_content_intro }}</p>
                <ul class="space-y-2 pl-6">
                  <li v-for="(point, index) in termsData.complaints_illegal_content_points" :key="index" class="text-sm text-red-700 dark:text-red-300 leading-relaxed flex items-start gap-2 break-words">
                    <span class="w-1.5 h-1.5 bg-red-500 rounded-full mt-2 flex-shrink-0"></span>
                    <span class="whitespace-normal">{{ point }}</span>
                  </li>
                </ul>
              </div>
              
              <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <h4 class="text-base font-semibold text-blue-900 dark:text-blue-100 mb-3">{{ termsData.complaints_other_complaints_title }}</h4>
                <ul class="space-y-2 pl-6">
                  <li v-for="(point, index) in termsData.complaints_other_complaints_points" :key="index" class="text-sm text-blue-700 dark:text-blue-300 leading-relaxed flex items-start gap-2 break-words">
                    <span class="w-1.5 h-1.5 bg-blue-500 rounded-full mt-2 flex-shrink-0"></span>
                    <span class="whitespace-normal">{{ point }}</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </section>
      </div>

      <!-- Footer -->
      <div class="mt-12 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="text-center space-y-4">
          <div class="flex items-center justify-center gap-4 text-sm text-gray-500 dark:text-gray-400">
            <span>Document ID: {{ documentId }}</span>
            <span>•</span>
            <span>Version: {{ version }}</span>
            <span>•</span>
            <span>Last Updated: {{ lastUpdated }}</span>
          </div>
          <div class="px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg inline-block">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Legally Binding Document</span>
          </div>
        </div>
      </div>

      <!-- Need Help Section -->
      <div class="mt-8 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-6">
        <div class="flex items-start gap-3">
          <div class="w-6 h-6 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
            <i class="ri-question-line text-blue-600 dark:text-blue-400 text-sm"></i>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-2">Need Help?</h3>
            <p class="text-sm text-blue-700 dark:text-blue-300 mb-3">
              If you have any questions about these terms and conditions, please contact our support team.
            </p>
            <div class="flex items-center gap-4 text-sm">
              <span class="text-blue-700 dark:text-blue-300">
                <i class="ri-mail-line mr-1"></i>
                support@fans4more.com
              </span>
              <span class="text-blue-700 dark:text-blue-300">
                <i class="ri-customer-service-line mr-1"></i>
                Contact Support
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import axios from '@/axios'
import { useTheme } from '@/composables/useTheme'
import Footer from '@/components/Footer.vue'

const { t } = useI18n()
const { theme, setTheme } = useTheme()

// Reactive data
const loading = ref(true)
const error = ref(null)
const termsData = ref(null)

// Computed properties
const lastUpdated = computed(() => {
  if (!termsData.value?.published_at) return 'Unknown'
  return new Date(termsData.value.published_at).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
})

const documentId = computed(() => termsData.value?.document_id || 'N/A')
const version = computed(() => termsData.value?.version || 'N/A')

// Methods
const fetchTerms = async () => {
  try {
    loading.value = true
    error.value = null
    
    const response = await axios.get('/terms/latest')
    
    if (response.data.success) {
      termsData.value = response.data.data
    } else {
      error.value = response.data.message || 'Failed to load terms and conditions'
    }
  } catch (err) {
    console.error('Error fetching terms:', err)
    error.value = err.response?.data?.message || 'Failed to load terms and conditions'
  } finally {
    loading.value = false
  }
}

// Manual retry function
const manualRetry = () => {
  fetchTerms()
}

const toggleTheme = () => {
  setTheme(theme.value === 'dark' ? 'light' : 'dark')
}

// Lifecycle
onMounted(() => {
  fetchTerms()
})
</script>

<style scoped>
/* Prevent text wrapping issues */
.whitespace-normal {
  white-space: normal !important;
  word-wrap: break-word !important;
  overflow-wrap: break-word !important;
}

.break-words {
  word-break: normal !important;
  word-wrap: break-word !important;
  overflow-wrap: break-word !important;
}

/* Ensure proper text flow in list items */
li {
  word-break: normal !important;
  white-space: normal !important;
}

/* Prevent unwanted line breaks */
p, span, div {
  word-break: normal !important;
  white-space: normal !important;
}
</style>