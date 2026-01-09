<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark">
    <!-- Mobile View -->
    <div class="lg:hidden">
      <!-- Mobile Header -->
      <header class="bg-surface-light dark:bg-surface-dark shadow-sm">
        <div class="px-4 py-4 flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-br from-primary-light to-primary-dark rounded-xl flex items-center justify-center">
              <img :src="logoUrl" alt="Fans4More" class="w-6 h-6 object-contain" />
            </div>
            <div>
              <h1 class="text-xl font-bold text-text-light-primary dark:text-text-dark-primary">Fans4More</h1>
              <p class="text-xs text-text-light-secondary dark:text-text-dark-secondary">Connect with creators</p>
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
            <button 
              @click="showLoginModal = true"
              class="text-sm font-medium text-primary-light dark:text-primary-dark hover:text-gradient-blue-to dark:hover:text-gradient-blue-from"
            >
              Sign In
            </button>
          </div>
        </div>
      </header>

      <!-- Mobile Hero Section -->
      <section class="px-4 py-8 text-center">
        <div class="max-w-sm mx-auto">
          <h2 class="text-2xl font-bold text-text-light-primary dark:text-text-dark-primary mb-3">
            Unlock Exclusive Content
          </h2>
          <p class="text-text-light-secondary dark:text-text-dark-secondary mb-8">
            Connect with your favorite creators and unlock exclusive content
          </p>
          
          <!-- CTA Buttons -->
          <div class="space-y-3 mb-6">
            <button
              @click="showRegisterModal = true"
              class="w-full py-4 bg-gradient-to-r from-primary-light to-primary-dark text-background-light rounded-2xl font-semibold hover:shadow-lg hover:shadow-primary-light/25 transition-all duration-200"
            >
              Get Started Free
            </button>
            <button
              @click="showLoginModal = true"
              class="w-full py-4 bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark text-text-light-primary dark:text-text-dark-primary rounded-2xl font-semibold hover:bg-glassmorphism-light-secondary dark:hover:bg-glassmorphism-dark-secondary transition-colors"
            >
              I have an account
            </button>
          </div>

          <!-- Social Login -->
          <div class="space-y-3">
            <div class="relative">
              <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-border-light dark:border-border-dark"></div>
              </div>
              <div class="relative flex justify-center text-sm">
                <span class="px-4 bg-background-light dark:bg-background-dark text-text-light-secondary dark:text-text-dark-secondary">
                  Or continue with
                </span>
              </div>
            </div>
            
            <div class="grid grid-cols-3 gap-3">
              <button class="py-3 bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl flex items-center justify-center hover:bg-glassmorphism-light-secondary dark:hover:bg-glassmorphism-dark-secondary transition-colors">
                <i class="ri-google-fill text-xl text-error-light"></i>
              </button>
              <button class="py-3 bg-info-light text-background-light rounded-xl flex items-center justify-center hover:bg-info-dark transition-colors">
                <i class="ri-twitter-fill text-xl"></i>
              </button>
              <button class="py-3 bg-ui-highlight text-background-light rounded-xl flex items-center justify-center hover:bg-gradient-purple-to transition-colors">
                <i class="ri-twitch-fill text-xl"></i>
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- Mobile Features -->
      <section class="px-4 py-8 bg-surface-light dark:bg-surface-dark">
        <div class="max-w-sm mx-auto text-center">
          <h3 class="text-lg font-semibold text-text-light-primary dark:text-text-dark-primary mb-6">
            Why Choose Fans4More?
          </h3>
          <div class="space-y-6">
            <div class="flex items-start space-x-4">
              <div class="w-10 h-10 bg-ui-highlight/10 dark:bg-ui-highlight/20 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="ri-user-heart-line text-ui-highlight"></i>
              </div>
              <div class="text-left">
                <h4 class="font-semibold text-text-light-primary dark:text-text-dark-primary">Connect Directly</h4>
                <p class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Build meaningful relationships with your favorite creators</p>
              </div>
            </div>
            <div class="flex items-start space-x-4">
              <div class="w-10 h-10 bg-primary-light/10 dark:bg-primary-dark/20 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="ri-lock-line text-primary-light dark:text-primary-dark"></i>
              </div>
              <div class="text-left">
                <h4 class="font-semibold text-text-light-primary dark:text-text-dark-primary">Exclusive Content</h4>
                <p class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Access premium content you won't find anywhere else</p>
              </div>
            </div>
            <div class="flex items-start space-x-4">
              <div class="w-10 h-10 bg-success-light/10 dark:bg-success-dark/20 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="ri-shield-check-line text-success-light dark:text-success-dark"></i>
              </div>
              <div class="text-left">
                <h4 class="font-semibold text-text-light-primary dark:text-text-dark-primary">Safe & Secure</h4>
                <p class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Your privacy and security are our top priority</p>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Desktop View -->
    <div class="hidden lg:flex min-h-screen">
      <!-- Left Side - Hero & Content -->
      <div class="flex-1 flex flex-col">
        <!-- Desktop Header -->
        <header class="bg-surface-light dark:bg-surface-dark border-b border-border-light dark:border-border-dark">
          <div class="px-8 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="w-12 h-12 bg-gradient-to-br from-primary-light to-primary-dark rounded-2xl flex items-center justify-center">
                <img :src="logoUrl" alt="Fans4More" class="w-8 h-8 object-contain" />
              </div>
              <div>
                <h1 class="text-2xl font-bold text-text-light-primary dark:text-text-dark-primary">Fans4More</h1>
                <p class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Connect with creators</p>
              </div>
            </div>
            
            <!-- Quick Login Form -->
            <div class="flex items-center space-x-4">
              <div class="flex items-center space-x-3">
                <input
                  v-model="loginForm.login"
                  type="text"
                  placeholder="Email or username"
                  class="px-4 py-2 border border-border-light dark:border-border-dark rounded-lg text-sm bg-surface-light dark:bg-surface-dark focus:ring-2 focus:ring-primary-light focus:border-primary-light transition-all w-48"
                />
                <input
                  v-model="loginForm.password"
                  type="password"
                  placeholder="Password"
                  class="px-4 py-2 border border-border-light dark:border-border-dark rounded-lg text-sm bg-surface-light dark:bg-surface-dark focus:ring-2 focus:ring-primary-light focus:border-primary-light transition-all w-32"
                />
                <button
                  @click="handleInlineLogin"
                  class="px-6 py-2 bg-gradient-to-r from-primary-light to-primary-dark text-background-light rounded-lg text-sm font-medium hover:shadow-lg transition-all duration-200"
                >
                  Sign In
                </button>
              </div>
              <!-- Theme Toggle -->
              <button 
                @click="toggleTheme"
                class="p-2 text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary hover:bg-glassmorphism-light-tertiary dark:hover:bg-glassmorphism-dark-tertiary rounded-lg transition-all duration-200"
              >
                <i :class="theme === 'dark' ? 'ri-sun-line' : 'ri-moon-line'" class="text-xl"></i>
              </button>
              <button
                type="button"
                @click="openForgotModal"
                class="text-sm text-primary-light dark:text-primary-dark hover:text-gradient-blue-to dark:hover:text-gradient-blue-from"
              >
                {{ t('forgot_password') }}
              </button>
            </div>
          </div>
        </header>

        <!-- Hero Content -->
        <div class="flex-1 flex items-center justify-center px-8 py-12">
          <div class="max-w-2xl text-center">
            <h2 class="text-5xl font-bold text-text-light-primary dark:text-text-dark-primary mb-6">
              Connect with Your
              <span class="bg-gradient-to-r from-primary-light to-primary-dark bg-clip-text text-transparent">
                Favorite Creators
              </span>
            </h2>
            <p class="text-xl text-text-light-secondary dark:text-text-dark-secondary mb-12 leading-relaxed">
              Discover exclusive content, build meaningful connections, and support the creators you love on our secure platform.
            </p>

            <!-- Stats -->
            <div class="flex items-center justify-center space-x-12 mb-12 text-text-light-secondary dark:text-text-dark-secondary">
              <div class="text-center">
                <div class="text-2xl font-bold text-text-light-primary dark:text-text-dark-primary">10K+</div>
                <div class="text-sm">Creators</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-text-light-primary dark:text-text-dark-primary">50K+</div>
                <div class="text-sm">Content</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-text-light-primary dark:text-text-dark-primary">1M+</div>
                <div class="text-sm">Members</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Side - Auth Panel -->
      <div class="w-96 bg-surface-light dark:bg-surface-dark border-l border-border-light dark:border-border-dark flex flex-col">
        <div class="flex-1 flex items-center justify-center p-8">
          <div class="w-full max-w-sm">
            <div class="text-center mb-8">
              <h3 class="text-2xl font-bold text-text-light-primary dark:text-text-dark-primary mb-2">
                Join Fans4More
              </h3>
              <p class="text-text-light-secondary dark:text-text-dark-secondary">
                Start your journey today
              </p>
            </div>

            <!-- Primary Actions -->
            <div class="space-y-4 mb-8">
              <button
                @click="showRegisterModal = true"
                class="w-full py-4 bg-gradient-to-r from-primary-light to-primary-dark text-background-light rounded-2xl font-semibold hover:shadow-lg hover:shadow-primary-light/25 transition-all duration-200 flex items-center justify-center space-x-2"
              >
                <i class="ri-user-add-line"></i>
                <span>Create Account</span>
              </button>
              <button
                @click="showLoginModal = true"
                class="w-full py-4 bg-glassmorphism-light-secondary dark:bg-glassmorphism-dark-secondary text-text-light-primary dark:text-text-dark-primary rounded-2xl font-semibold hover:bg-glassmorphism-light-primary dark:hover:bg-glassmorphism-dark-primary transition-colors flex items-center justify-center space-x-2"
              >
                <i class="ri-login-box-line"></i>
                <span>Sign In</span>
              </button>
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

    <!-- Register Modal -->
    <TransitionRoot appear :show="showRegisterModal" as="template">
      <Dialog as="div" @close="showRegisterModal = false" class="relative z-50">
        <TransitionChild
          enter="duration-300 ease-out"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="duration-200 ease-in"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-background-dark/60 backdrop-blur-sm" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4">
            <TransitionChild
              enter="duration-300 ease-out"
              enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100"
              leave="duration-200 ease-in"
              leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95"
            >
              <DialogPanel class="w-full max-w-lg bg-surface-light dark:bg-surface-dark rounded-3xl shadow-2xl border border-border-light dark:border-border-dark overflow-hidden">
                <!-- Header with gradient -->
                <div class="bg-gradient-to-r from-primary-light to-primary-dark px-8 py-6 text-background-light">
                  <div class="flex justify-between items-start">
                    <div>
                      <h2 class="text-2xl font-bold">Create Your Account</h2>
                      <p class="text-background-light/80 mt-1">Join thousands of creators and fans</p>
                    </div>
                    <button 
                      @click="showRegisterModal = false" 
                      class="text-background-light/80 hover:text-background-light p-2 rounded-xl hover:bg-background-light/10 transition-colors"
                    >
                      <i class="ri-close-line text-xl"></i>
                    </button>
                  </div>
                </div>

                <!-- Form Content -->
                <div class="px-8 py-6">
                  <!-- Register Form -->
                  <form @submit.prevent="handleRegister" class="space-y-5">
                    <div>
                      <label class="block text-sm font-semibold text-text-light-primary dark:text-text-dark-primary mb-2">
                        <i class="ri-user-line mr-2 text-primary-light dark:text-primary-dark"></i>Full Name
                      </label>
                      <input
                        v-model="registerForm.name"
                        type="text"
                        placeholder="John Doe"
                        class="w-full px-4 py-3 border border-border-light dark:border-border-dark rounded-xl focus:ring-2 focus:ring-primary-light focus:border-primary-light bg-surface-light dark:bg-background-dark text-text-light-primary dark:text-text-dark-primary transition-all placeholder-text-light-secondary dark:placeholder-text-dark-secondary"
                        required
                      />
                      <p v-if="registerErrors.name" class="text-error-light text-xs mt-1 flex items-center">
                        <i class="ri-error-warning-line mr-1"></i>{{ registerErrors.name[0] }}
                      </p>
                    </div>

                    <div>
                      <label class="block text-sm font-semibold text-text-light-primary dark:text-text-dark-primary mb-2">
                        <i class="ri-at-line mr-2 text-primary-light dark:text-primary-dark"></i>Username
                      </label>
                      <input
                        v-model="registerForm.username"
                        type="text"
                        placeholder="johndoe"
                        class="w-full px-4 py-3 border border-border-light dark:border-border-dark rounded-xl focus:ring-2 focus:ring-primary-light focus:border-primary-light bg-surface-light dark:bg-background-dark text-text-light-primary dark:text-text-dark-primary transition-all placeholder-text-light-secondary dark:placeholder-text-dark-secondary"
                        required
                      />
                      <p v-if="registerErrors.username" class="text-error-light text-xs mt-1 flex items-center">
                        <i class="ri-error-warning-line mr-1"></i>{{ registerErrors.username[0] }}
                      </p>
                    </div>

                    <div>
                      <label class="block text-sm font-semibold text-text-light-primary dark:text-text-dark-primary mb-2">
                        <i class="ri-mail-line mr-2 text-primary-light dark:text-primary-dark"></i>Email Address
                      </label>
                      <input
                        v-model="registerForm.email"
                        type="email"
                        placeholder="john@example.com"
                        class="w-full px-4 py-3 border border-border-light dark:border-border-dark rounded-xl focus:ring-2 focus:ring-primary-light focus:border-primary-light bg-surface-light dark:bg-background-dark text-text-light-primary dark:text-text-dark-primary transition-all placeholder-text-light-secondary dark:placeholder-text-dark-secondary"
                        required
                      />
                      <p v-if="registerErrors.email" class="text-error-light text-xs mt-1 flex items-center">
                        <i class="ri-error-warning-line mr-1"></i>{{ registerErrors.email[0] }}
                      </p>
                    </div>

                    <div>
                      <label class="block text-sm font-semibold text-text-light-primary dark:text-text-dark-primary mb-2">
                        <i class="ri-lock-line mr-2 text-primary-light dark:text-primary-dark"></i>Password
                      </label>
                      <input
                        v-model="registerForm.password"
                        type="password"
                        placeholder="••••••••"
                        class="w-full px-4 py-3 border border-border-light dark:border-border-dark rounded-xl focus:ring-2 focus:ring-primary-light focus:border-primary-light bg-surface-light dark:bg-background-dark text-text-light-primary dark:text-text-dark-primary transition-all placeholder-text-light-secondary dark:placeholder-text-dark-secondary"
                        required
                      />
                      <p v-if="registerErrors.password" class="text-error-light text-xs mt-1 flex items-center">
                        <i class="ri-error-warning-line mr-1"></i>{{ registerErrors.password[0] }}
                      </p>
                    </div>

                    <div>
                      <label class="block text-sm font-semibold text-text-light-primary dark:text-text-dark-primary mb-2">
                        <i class="ri-lock-2-line mr-2 text-primary-light dark:text-primary-dark"></i>Confirm Password
                      </label>
                      <input
                        v-model="registerForm.password_confirmation"
                        type="password"
                        placeholder="••••••••"
                        class="w-full px-4 py-3 border border-border-light dark:border-border-dark rounded-xl focus:ring-2 focus:ring-primary-light focus:border-primary-light bg-surface-light dark:bg-background-dark text-text-light-primary dark:text-text-dark-primary transition-all placeholder-text-light-secondary dark:placeholder-text-dark-secondary"
                        required
                      />
                    </div>

                    <div class="hidden">
                      <input v-model="registerForm.referral_code" type="text" class="text-text-light-primary dark:text-text-dark-primary" />
                    </div>

                    <!-- Terms Agreement -->
                    <div class="flex items-start space-x-3 p-4 bg-glassmorphism-light-tertiary dark:bg-glassmorphism-dark-tertiary rounded-xl">
                      <input 
                        type="checkbox" 
                        id="terms" 
                        class="mt-1 rounded border-border-light dark:border-border-dark text-primary-light focus:ring-primary-light" 
                        required
                      />
                                             <label for="terms" class="text-sm text-text-light-secondary dark:text-text-dark-secondary leading-relaxed">
                         I agree to the 
                         <router-link to="/terms" class="text-primary-light dark:text-primary-dark hover:underline font-medium">Terms of Service</router-link> 
                         and 
                         <router-link to="/privacy" class="text-primary-light dark:text-primary-dark hover:underline font-medium">Privacy Policy</router-link>. 
                         I confirm that I am 18 years or older.
                       </label>
                    </div>

                    <button
                      type="submit"
                      :disabled="!isRegisterFormValid || authStore.loading"
                      class="w-full py-4 bg-gradient-to-r from-primary-light to-primary-dark text-background-light rounded-xl font-semibold hover:shadow-lg hover:shadow-primary-light/25 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center space-x-2"
                    >
                      <span v-if="authStore.loading" class="flex items-center space-x-2">
                        <i class="ri-loader-4-line animate-spin"></i>
                        <span>Creating Account...</span>
                      </span>
                      <span v-else class="flex items-center space-x-2">
                        <i class="ri-user-add-line"></i>
                        <span>Create My Account</span>
                      </span>
                    </button>

                    <p v-if="registerErrors.general" class="text-error-light text-sm text-center flex items-center justify-center space-x-2">
                      <i class="ri-error-warning-line"></i>
                      <span>{{ registerErrors.general[0] }}</span>
                    </p>
                  </form>

                  <!-- Divider -->
                  <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                      <div class="w-full border-t border-border-light dark:border-border-dark"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                      <span class="px-4 bg-surface-light dark:bg-surface-dark text-text-light-secondary dark:text-text-dark-secondary">
                        Already have an account?
                      </span>
                    </div>
                  </div>

                  <!-- Login Link -->
                  <button 
                    @click="showRegisterModal = false; showLoginModal = true" 
                    class="w-full py-3 bg-glassmorphism-light-secondary dark:bg-glassmorphism-dark-secondary text-text-light-primary dark:text-text-dark-primary rounded-xl font-medium hover:bg-glassmorphism-light-primary dark:hover:bg-glassmorphism-dark-primary transition-colors flex items-center justify-center space-x-2"
                  >
                    <i class="ri-login-box-line"></i>
                    <span>Sign In Instead</span>
                  </button>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Login Modal -->
    <TransitionRoot appear :show="showLoginModal" as="template">
      <Dialog as="div" @close="showLoginModal = false" class="relative z-50">
        <TransitionChild
          enter="duration-300 ease-out"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="duration-200 ease-in"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-background-dark/60 backdrop-blur-sm" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4">
            <TransitionChild
              enter="duration-300 ease-out"
              enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100"
              leave="duration-200 ease-in"
              leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95"
            >
              <DialogPanel class="w-full max-w-md bg-surface-light dark:bg-surface-dark rounded-3xl shadow-2xl border border-border-light dark:border-border-dark overflow-hidden">
                <!-- Header with gradient -->
                <div class="bg-gradient-to-r from-primary-light to-primary-dark px-8 py-6 text-background-light">
                  <div class="flex justify-between items-start">
                    <div>
                      <h2 class="text-2xl font-bold">Welcome Back!</h2>
                      <p class="text-background-light/80 mt-1">Sign in to continue your journey</p>
                    </div>
                    <button 
                      @click="showLoginModal = false" 
                      class="text-background-light/80 hover:text-background-light p-2 rounded-xl hover:bg-background-light/10 transition-colors"
                    >
                      <i class="ri-close-line text-xl"></i>
                    </button>
                  </div>
                </div>

                <!-- Form Content -->
                <div class="px-8 py-6">
                  <!-- Login Form -->
                  <form @submit.prevent="handleLogin" class="space-y-5">
                    <div>
                      <label class="block text-sm font-semibold text-text-light-primary dark:text-text-dark-primary mb-2">
                        <i class="ri-user-line mr-2 text-primary-light dark:text-primary-dark"></i>Email or Username
                      </label>
                      <input
                        v-model="loginForm.login"
                        type="text"
                        placeholder="Enter your email or username"
                        class="w-full px-4 py-3 border border-border-light dark:border-border-dark rounded-xl focus:ring-2 focus:ring-primary-light focus:border-primary-light bg-surface-light dark:bg-background-dark text-text-light-primary dark:text-text-dark-primary transition-all placeholder-text-light-secondary dark:placeholder-text-dark-secondary"
                        required
                      />
                    </div>

                    <div>
                      <label class="block text-sm font-semibold text-text-light-primary dark:text-text-dark-primary mb-2">
                        <i class="ri-lock-line mr-2 text-primary-light dark:text-primary-dark"></i>Password
                      </label>
                      <input
                        v-model="loginForm.password"
                        type="password"
                        placeholder="Enter your password"
                        class="w-full px-4 py-3 border border-border-light dark:border-border-dark rounded-xl focus:ring-2 focus:ring-primary-light focus:border-primary-light bg-surface-light dark:bg-background-dark text-text-light-primary dark:text-text-dark-primary transition-all placeholder-text-light-secondary dark:placeholder-text-dark-secondary"
                        required
                      />
                    </div>

                    <div class="flex justify-between items-center">
                      <label class="flex items-center">
                        <input 
                          type="checkbox" 
                          class="rounded border-border-light dark:border-border-dark text-primary-light focus:ring-primary-light" 
                        />
                        <span class="ml-2 text-sm text-text-light-secondary dark:text-text-dark-secondary">Remember me</span>
                      </label>
                      <button
                        type="button"
                        @click="openForgotModal"
                        class="text-sm text-primary-light dark:text-primary-dark hover:underline font-medium"
                      >
                        {{ t('forgot_password') }}
                      </button>
                    </div>

                    <button
                      type="submit"
                      :disabled="authStore.loading"
                      class="w-full py-4 bg-gradient-to-r from-primary-light to-primary-dark text-background-light rounded-xl font-semibold hover:shadow-lg hover:shadow-primary-light/25 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center space-x-2"
                    >
                      <span v-if="authStore.loading" class="flex items-center space-x-2">
                        <i class="ri-loader-4-line animate-spin"></i>
                        <span>Signing in...</span>
                      </span>
                      <span v-else class="flex items-center space-x-2">
                        <i class="ri-login-box-line"></i>
                        <span>Sign In</span>
                      </span>
                    </button>

                    <p v-if="loginError" class="text-error-light text-sm text-center flex items-center justify-center space-x-2">
                      <i class="ri-error-warning-line"></i>
                      <span>{{ loginError }}</span>
                    </p>
                  </form>

                  <!-- Social Login -->
                  <div class="mt-6">
                    <div class="relative">
                      <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-border-light dark:border-border-dark"></div>
                      </div>
                      <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-surface-light dark:bg-surface-dark text-text-light-secondary dark:text-text-dark-secondary">
                          Or continue with
                        </span>
                      </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3 mt-4">
                      <button class="py-3 bg-surface-light dark:bg-background-dark border border-border-light dark:border-border-dark text-text-light-primary dark:text-text-dark-primary rounded-xl flex items-center justify-center hover:bg-glassmorphism-light-secondary dark:hover:bg-glassmorphism-dark-secondary transition-colors">
                        <i class="ri-google-fill text-xl text-error-light"></i>
                      </button>
                      <button class="py-3 bg-info-light text-background-light rounded-xl flex items-center justify-center hover:bg-info-dark transition-colors">
                        <i class="ri-twitter-fill text-xl"></i>
                      </button>
                      <button class="py-3 bg-ui-highlight text-background-light rounded-xl flex items-center justify-center hover:bg-gradient-purple-to transition-colors">
                        <i class="ri-twitch-fill text-xl"></i>
                      </button>
                    </div>
                  </div>

                  <!-- Divider -->
                  <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                      <div class="w-full border-t border-border-light dark:border-border-dark"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                      <span class="px-4 bg-surface-light dark:bg-surface-dark text-text-light-secondary dark:text-text-dark-secondary">
                        New to Fans4More?
                      </span>
                    </div>
                  </div>

                  <!-- Register Link -->
                  <button 
                    @click="showLoginModal = false; showRegisterModal = true" 
                    class="w-full py-3 bg-glassmorphism-light-secondary dark:bg-glassmorphism-dark-secondary text-text-light-primary dark:text-text-dark-primary rounded-xl font-medium hover:bg-glassmorphism-light-primary dark:hover:bg-glassmorphism-dark-primary transition-colors flex items-center justify-center space-x-2"
                  >
                    <i class="ri-user-add-line"></i>
                    <span>Create Account</span>
                  </button>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Forgot Password Modal -->
    <TransitionRoot appear :show="showForgotModal" as="template">
      <Dialog as="div" @close="showForgotModal = false" class="relative z-50">
        <TransitionChild
          enter="duration-300 ease-out"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="duration-200 ease-in"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-background-dark/60 backdrop-blur-sm" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4">
            <TransitionChild
              enter="duration-300 ease-out"
              enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100"
              leave="duration-200 ease-in"
              leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95"
            >
              <DialogPanel class="w-full max-w-md bg-surface-light dark:bg-surface-dark rounded-3xl shadow-2xl border border-border-light dark:border-border-dark overflow-hidden">
                <div class="bg-gradient-to-r from-primary-light to-primary-dark px-8 py-6 text-background-light">
                  <div class="flex justify-between items-start">
                    <div>
                      <h2 class="text-2xl font-bold">Reset your password</h2>
                      <p class="text-background-light/80 mt-1">We’ll send you a reset link</p>
                    </div>
                    <button 
                      @click="showForgotModal = false" 
                      class="text-background-light/80 hover:text-background-light p-2 rounded-xl hover:bg-background-light/10 transition-colors"
                    >
                      <i class="ri-close-line text-xl"></i>
                    </button>
                  </div>
                </div>

                <div class="px-8 py-6">
                  <form @submit.prevent="handleForgotPassword" class="space-y-5">
                    <div>
                      <label class="block text-sm font-semibold text-text-light-primary dark:text-text-dark-primary mb-2">
                        Email address
                      </label>
                      <input
                        v-model="forgotEmail"
                        type="email"
                        placeholder="you@example.com"
                        class="w-full px-4 py-3 border border-border-light dark:border-border-dark rounded-xl focus:ring-2 focus:ring-primary-light focus:border-primary-light bg-surface-light dark:bg-background-dark text-text-light-primary dark:text-text-dark-primary transition-all placeholder-text-light-secondary dark:placeholder-text-dark-secondary"
                        required
                      />
                    </div>

                    <button
                      type="submit"
                      :disabled="authStore.loading"
                      class="w-full py-4 bg-gradient-to-r from-primary-light to-primary-dark text-background-light rounded-xl font-semibold hover:shadow-lg hover:shadow-primary-light/25 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center space-x-2"
                    >
                      <span v-if="authStore.loading" class="flex items-center space-x-2">
                        <i class="ri-loader-4-line animate-spin"></i>
                        <span>Sending...</span>
                      </span>
                      <span v-else class="flex items-center space-x-2">
                        <i class="ri-mail-send-line"></i>
                        <span>Send reset link</span>
                      </span>
                    </button>

                    <p v-if="forgotSuccess" class="text-success-light text-sm text-center">
                      {{ forgotSuccess }}
                    </p>
                    <p v-if="forgotError" class="text-error-light text-sm text-center flex items-center justify-center space-x-2">
                      <i class="ri-error-warning-line"></i>
                      <span>{{ forgotError }}</span>
                    </p>
                  </form>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Reset Password Modal -->
    <TransitionRoot appear :show="showResetModal" as="template">
      <Dialog as="div" @close="showResetModal = false" class="relative z-50">
        <TransitionChild
          enter="duration-300 ease-out"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="duration-200 ease-in"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-background-dark/60 backdrop-blur-sm" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4">
            <TransitionChild
              enter="duration-300 ease-out"
              enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100"
              leave="duration-200 ease-in"
              leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95"
            >
              <DialogPanel class="w-full max-w-md bg-surface-light dark:bg-surface-dark rounded-3xl shadow-2xl border border-border-light dark:border-border-dark overflow-hidden">
                <div class="bg-gradient-to-r from-primary-light to-primary-dark px-8 py-6 text-background-light">
                  <div class="flex justify-between items-start">
                    <div>
                      <h2 class="text-2xl font-bold">Create a new password</h2>
                      <p class="text-background-light/80 mt-1">Choose a strong password</p>
                    </div>
                    <button 
                      @click="showResetModal = false" 
                      class="text-background-light/80 hover:text-background-light p-2 rounded-xl hover:bg-background-light/10 transition-colors"
                    >
                      <i class="ri-close-line text-xl"></i>
                    </button>
                  </div>
                </div>

                <div class="px-8 py-6">
                  <form @submit.prevent="handleResetPassword" class="space-y-5">
                    <div>
                      <label class="block text-sm font-semibold text-text-light-primary dark:text-text-dark-primary mb-2">
                        Email address
                      </label>
                      <input
                        v-model="resetForm.email"
                        type="email"
                        class="w-full px-4 py-3 border border-border-light dark:border-border-dark rounded-xl focus:ring-2 focus:ring-primary-light focus:border-primary-light bg-surface-light dark:bg-background-dark text-text-light-primary dark:text-text-dark-primary transition-all placeholder-text-light-secondary dark:placeholder-text-dark-secondary"
                        required
                      />
                    </div>

                    <div>
                      <label class="block text-sm font-semibold text-text-light-primary dark:text-text-dark-primary mb-2">
                        New password
                      </label>
                      <input
                        v-model="resetForm.password"
                        type="password"
                        class="w-full px-4 py-3 border border-border-light dark:border-border-dark rounded-xl focus:ring-2 focus:ring-primary-light focus:border-primary-light bg-surface-light dark:bg-background-dark text-text-light-primary dark:text-text-dark-primary transition-all placeholder-text-light-secondary dark:placeholder-text-dark-secondary"
                        required
                      />
                    </div>

                    <div>
                      <label class="block text-sm font-semibold text-text-light-primary dark:text-text-dark-primary mb-2">
                        Confirm password
                      </label>
                      <input
                        v-model="resetForm.password_confirmation"
                        type="password"
                        class="w-full px-4 py-3 border border-border-light dark:border-border-dark rounded-xl focus:ring-2 focus:ring-primary-light focus:border-primary-light bg-surface-light dark:bg-background-dark text-text-light-primary dark:text-text-dark-primary transition-all placeholder-text-light-secondary dark:placeholder-text-dark-secondary"
                        required
                      />
                    </div>

                    <button
                      type="submit"
                      :disabled="authStore.loading"
                      class="w-full py-4 bg-gradient-to-r from-primary-light to-primary-dark text-background-light rounded-xl font-semibold hover:shadow-lg hover:shadow-primary-light/25 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center space-x-2"
                    >
                      <span v-if="authStore.loading" class="flex items-center space-x-2">
                        <i class="ri-loader-4-line animate-spin"></i>
                        <span>Resetting...</span>
                      </span>
                      <span v-else class="flex items-center space-x-2">
                        <i class="ri-lock-line"></i>
                        <span>Reset password</span>
                      </span>
                    </button>

                    <p v-if="resetSuccess" class="text-success-light text-sm text-center">
                      {{ resetSuccess }}
                    </p>
                    <p v-if="resetError" class="text-error-light text-sm text-center flex items-center justify-center space-x-2">
                      <i class="ri-error-warning-line"></i>
                      <span>{{ resetError }}</span>
                    </p>
                  </form>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { useRouter } from 'vue-router'
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
} from '@headlessui/vue'
import { useI18n } from 'vue-i18n'
import { useTrackingLink } from '@/composables/useTrackingLink'
import { useTheme } from '@/composables/useTheme'
import Footer from '@/components/Footer.vue'
import logoUrl from '@/assets/logo.png'

const authStore = useAuthStore()
const router = useRouter()
const { t } = useI18n()
const { initTrackingLink, trackSignup } = useTrackingLink()
const { theme, setTheme } = useTheme()

const showRegisterModal = ref(false)
const showLoginModal = ref(false)
const showForgotModal = ref(false)
const showResetModal = ref(false)

const registerForm = ref({
  name: '',
  username: '',
  email: '',
  password: '',
  password_confirmation: '',
  referral_code: '',
})

const loginForm = ref({
  login: '',
  password: '',
})

const forgotEmail = ref('')
const forgotError = ref('')
const forgotSuccess = ref('')

const resetForm = ref({
  email: '',
  token: '',
  password: '',
  password_confirmation: '',
})
const resetError = ref('')
const resetSuccess = ref('')

const registerErrors = ref({})
const loginError = ref('')

const isRegisterFormValid = computed(() => {
  return (
    registerForm.value.name &&
    registerForm.value.username &&
    registerForm.value.email &&
    registerForm.value.password &&
    registerForm.value.password_confirmation &&
    registerForm.value.password === registerForm.value.password_confirmation
  )
})

const handleRegister = async () => {
  if (!isRegisterFormValid.value) {
    return
  }

  const result = await authStore.register(registerForm.value)
  if (result.success) {
    showRegisterModal.value = false
    await trackSignup('register')
    router.push('/')
  } else {
    registerErrors.value = result.errors
  }
}

const handleLogin = async () => {
  loginError.value = ''
  const result = await authStore.login(loginForm.value)
  if (result.success) {
    showLoginModal.value = false
    router.push('/')
    await trackSignup('login')
  } else {
    loginError.value = result.error
  }
}

const handleInlineLogin = async () => {
  loginError.value = ''
  const result = await authStore.login(loginForm.value)
  if (result.success) {
    router.push('/')
    await trackSignup('login')
  } else {
    loginError.value = result.error
  }
}

const openForgotModal = () => {
  forgotError.value = ''
  forgotSuccess.value = ''
  showForgotModal.value = true
}

const handleForgotPassword = async () => {
  forgotError.value = ''
  forgotSuccess.value = ''

  const result = await authStore.requestPasswordReset(forgotEmail.value)
  if (result.success) {
    forgotSuccess.value = result.message || 'If that email exists, we sent a reset link.'
  } else {
    forgotError.value = result.error || 'Failed to send reset link.'
  }
}

const handleResetPassword = async () => {
  resetError.value = ''
  resetSuccess.value = ''

  const result = await authStore.resetPassword(resetForm.value)
  if (result.success) {
    resetSuccess.value = result.message || 'Password reset successful.'
    showResetModal.value = false
    showLoginModal.value = true
  } else {
    resetError.value = result.error || 'Failed to reset password.'
  }
}

const toggleTheme = () => {
  setTheme(theme.value === 'dark' ? 'light' : 'dark')
}

onMounted(async () => {
  const urlParams = new URLSearchParams(window.location.search)
  const refCode = urlParams.get('ref')
  const trackingId = urlParams.get('tracking_link_id')
  const resetToken = urlParams.get('token')
  const resetEmail = urlParams.get('email')

  if (refCode) {
    registerForm.value.referral_code = refCode
  }

  if (trackingId) {
    localStorage.setItem('tracking_link_id', trackingId)
  }

  initTrackingLink()

  if (resetToken && resetEmail) {
    resetForm.value.token = resetToken
    resetForm.value.email = resetEmail
    showResetModal.value = true
  }
})
</script>
