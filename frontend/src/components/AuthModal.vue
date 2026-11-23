<script setup>
import { reactive, ref, watch } from 'vue'
import { AlertCircle, Eye, EyeOff, Lock, Mail, Mountain, User as UserIcon, X } from 'lucide-vue-next'
import { useAuth } from '../composables/useAuth'

const {
  authModalOpen,
  authModalTab,
  authModalNotice,
  isLoading,
  login,
  register,
  closeAuthModal,
} = useAuth()

const formError = ref('')
const showPassword = ref(false)

const loginForm = reactive({
  email: '',
  password: '',
})

const registerForm = reactive({
  name: '',
  email: '',
  password: '',
  passwordConfirmation: '',
  role: 'renter',
})

const roleOptions = [
  { id: 'renter', label: 'Rent equipment', hint: 'Book skis & boards' },
  { id: 'owner', label: 'Rent it out', hint: 'Run rental points' },
]

// Clear errors and reset passwords when tab switches
watch(authModalTab, () => {
  formError.value = ''
  showPassword.value = false
})

watch(authModalOpen, (isOpen) => {
  if (isOpen) {
    formError.value = ''
    showPassword.value = false
  }
})

function switchTab(tab) {
  authModalTab.value = tab
  formError.value = ''
}

async function handleLogin() {
  formError.value = ''

  if (!loginForm.email.trim()) {
    formError.value = 'Please enter your email address.'
    return
  }
  if (!loginForm.password) {
    formError.value = 'Please enter your password.'
    return
  }

  try {
    await login({
      email: loginForm.email.trim(),
      password: loginForm.password,
    })
    loginForm.password = ''
  } catch (err) {
    formError.value = err.message || 'Login failed. Please check your credentials.'
  }
}

// Seeded demo users (see DemoAccountsSeeder). Hide with VITE_SHOW_DEMO_ACCOUNTS=false.
const showDemoAccounts = import.meta.env.VITE_SHOW_DEMO_ACCOUNTS !== 'false'
const demoAccounts = [
  { label: 'Renter', hint: 'Anna Huber', email: 'renter@example.com' },
  { label: 'Owner', hint: 'Tirol Ski Rent', email: 'owner@example.com' },
]

async function loginAsDemo(account) {
  loginForm.email = account.email
  loginForm.password = 'password'
  await handleLogin()
}

async function handleRegister() {
  formError.value = ''

  if (!registerForm.name.trim()) {
    formError.value = 'Please enter your full name.'
    return
  }
  if (!registerForm.email.trim()) {
    formError.value = 'Please enter your email address.'
    return
  }
  if (!registerForm.password || registerForm.password.length < 6) {
    formError.value = 'Password must be at least 6 characters.'
    return
  }
  if (registerForm.password !== registerForm.passwordConfirmation) {
    formError.value = 'Passwords do not match.'
    return
  }

  try {
    await register({
      name: registerForm.name.trim(),
      email: registerForm.email.trim(),
      password: registerForm.password,
      role: registerForm.role,
    })
    registerForm.password = ''
    registerForm.passwordConfirmation = ''
  } catch (err) {
    formError.value = err.message || 'Registration failed. Please try again.'
  }
}
</script>

<template>
  <Teleport to="body">
    <div
      v-if="authModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-md transition-all"
    >
      <!-- Backdrop -->
      <button
        type="button"
        class="fixed inset-0 bg-slateink/60 transition-opacity"
        aria-label="Close dialog"
        @click="closeAuthModal"
      />

      <!-- Dialog Card -->
      <div
        role="dialog"
        aria-modal="true"
        class="relative z-10 w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-slate-900/10 transition-all sm:my-8"
      >
        <!-- Header -->
        <div class="relative bg-gradient-to-br from-slateink to-slate-800 px-6 py-6 text-white sm:px-8">
          <button
            type="button"
            class="absolute right-4 top-4 rounded-full p-2 text-white/70 transition hover:bg-white/10 hover:text-white"
            aria-label="Close"
            @click="closeAuthModal"
          >
            <X class="h-5 w-5" />
          </button>

          <div class="flex items-center gap-2.5">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/15 ring-1 ring-white/20">
              <Mountain class="h-5 w-5 text-white" />
            </span>
            <div>
              <p class="text-xs font-semibold uppercase tracking-widest text-alpine-300">AlpenRent</p>
              <h2 class="text-lg font-bold">My Account</h2>
            </div>
          </div>

          <!-- Notification if prompted by booking -->
          <div
            v-if="authModalNotice"
            class="mt-4 flex items-start gap-2.5 rounded-2xl bg-white/10 p-3 text-xs leading-relaxed text-white ring-1 ring-white/20 backdrop-blur-sm"
          >
            <Lock class="mt-0.5 h-4 w-4 shrink-0 text-alpine-300" />
            <span>{{ authModalNotice }}</span>
          </div>

          <!-- Tab Navigation -->
          <div class="mt-5 flex rounded-2xl bg-white/10 p-1 ring-1 ring-white/15">
            <button
              type="button"
              class="flex-1 rounded-xl py-2 text-xs font-bold transition"
              :class="
                authModalTab === 'login'
                  ? 'bg-white text-slateink shadow-sm'
                  : 'text-white/80 hover:text-white'
              "
              @click="switchTab('login')"
            >
              Sign in
            </button>
            <button
              type="button"
              class="flex-1 rounded-xl py-2 text-xs font-bold transition"
              :class="
                authModalTab === 'register'
                  ? 'bg-white text-slateink shadow-sm'
                  : 'text-white/80 hover:text-white'
              "
              @click="switchTab('register')"
            >
              Register
            </button>
          </div>
        </div>

        <!-- Body -->
        <div class="p-6 sm:p-8">
          <!-- Error banner -->
          <div
            v-if="formError"
            class="mb-5 flex items-start gap-2.5 rounded-2xl bg-rose-50 p-3.5 text-xs font-medium text-rose-700 ring-1 ring-rose-200"
            role="alert"
          >
            <AlertCircle class="mt-0.5 h-4 w-4 shrink-0 text-rose-500" />
            <span class="flex-1">{{ formError }}</span>
          </div>

          <!-- LOGIN FORM -->
          <form v-if="authModalTab === 'login'" class="space-y-4" @submit.prevent="handleLogin">
            <div>
              <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">
                Email
              </label>
              <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                  <Mail class="h-4 w-4" />
                </span>
                <input
                  v-model="loginForm.email"
                  type="email"
                  required
                  placeholder="name@example.com"
                  autocomplete="email"
                  class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm text-slateink transition placeholder:text-slate-400 focus:border-alpine-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-alpine-100"
                />
              </div>
            </div>

            <div>
              <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">
                Password
              </label>
              <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                  <Lock class="h-4 w-4" />
                </span>
                <input
                  v-model="loginForm.password"
                  :type="showPassword ? 'text' : 'password'"
                  required
                  placeholder="••••••••"
                  autocomplete="current-password"
                  class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-10 text-sm text-slateink transition placeholder:text-slate-400 focus:border-alpine-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-alpine-100"
                />
                <button
                  type="button"
                  class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600"
                  tabindex="-1"
                  @click="showPassword = !showPassword"
                >
                  <EyeOff v-if="showPassword" class="h-4 w-4" />
                  <Eye v-else class="h-4 w-4" />
                </button>
              </div>
            </div>

            <button
              type="submit"
              :disabled="isLoading"
              class="mt-2 flex w-full items-center justify-center gap-2 rounded-2xl bg-alpine-600 py-3.5 text-sm font-bold text-white shadow-lg shadow-alpine-600/25 transition hover:bg-alpine-700 active:scale-[0.99] disabled:opacity-60"
            >
              <svg
                v-if="isLoading"
                class="h-4 w-4 animate-spin text-white"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path
                  class="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                ></path>
              </svg>
              <span>{{ isLoading ? 'Signing in...' : 'Sign in' }}</span>
            </button>

            <div v-if="showDemoAccounts" class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-3">
              <p class="text-center text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                Try a demo account
              </p>
              <div class="mt-2 grid grid-cols-2 gap-2">
                <button
                  v-for="account in demoAccounts"
                  :key="account.email"
                  type="button"
                  :disabled="isLoading"
                  class="rounded-xl bg-white px-3 py-2 text-left ring-1 ring-slate-200 transition hover:ring-alpine-400 disabled:opacity-60"
                  @click="loginAsDemo(account)"
                >
                  <span class="block text-sm font-bold text-slateink">{{ account.label }}</span>
                  <span class="block truncate text-xs text-slate-500">{{ account.hint }}</span>
                </button>
              </div>
            </div>

            <div class="pt-2 text-center text-xs text-slate-500">
              Don't have an account?
              <button
                type="button"
                class="font-bold text-alpine-700 hover:underline ml-1"
                @click="switchTab('register')"
              >
                Register
              </button>
            </div>
          </form>

          <!-- REGISTER FORM -->
          <form v-else class="space-y-4" @submit.prevent="handleRegister">
            <fieldset>
              <legend class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">
                I want to
              </legend>
              <div class="grid grid-cols-2 gap-2">
                <label
                  v-for="option in roleOptions"
                  :key="option.id"
                  class="cursor-pointer rounded-2xl border p-3 transition"
                  :class="registerForm.role === option.id
                    ? 'border-alpine-600 bg-alpine-50 ring-4 ring-alpine-100'
                    : 'border-slate-200 bg-slate-50 hover:border-slate-300'"
                >
                  <input v-model="registerForm.role" type="radio" name="role" :value="option.id" class="sr-only" />
                  <span class="block text-sm font-bold text-slateink">{{ option.label }}</span>
                  <span class="mt-0.5 block text-xs text-slate-500">{{ option.hint }}</span>
                </label>
              </div>
            </fieldset>

            <div>
              <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">
                Full name
              </label>
              <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                  <UserIcon class="h-4 w-4" />
                </span>
                <input
                  v-model="registerForm.name"
                  type="text"
                  required
                  placeholder="John Doe"
                  autocomplete="name"
                  class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm text-slateink transition placeholder:text-slate-400 focus:border-alpine-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-alpine-100"
                />
              </div>
            </div>

            <div>
              <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">
                Email
              </label>
              <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                  <Mail class="h-4 w-4" />
                </span>
                <input
                  v-model="registerForm.email"
                  type="email"
                  required
                  placeholder="name@example.com"
                  autocomplete="email"
                  class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm text-slateink transition placeholder:text-slate-400 focus:border-alpine-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-alpine-100"
                />
              </div>
            </div>

            <div>
              <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">
                Password
              </label>
              <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                  <Lock class="h-4 w-4" />
                </span>
                <input
                  v-model="registerForm.password"
                  :type="showPassword ? 'text' : 'password'"
                  required
                  minlength="6"
                  placeholder="At least 6 characters"
                  autocomplete="new-password"
                  class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-10 text-sm text-slateink transition placeholder:text-slate-400 focus:border-alpine-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-alpine-100"
                />
                <button
                  type="button"
                  class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600"
                  tabindex="-1"
                  @click="showPassword = !showPassword"
                >
                  <EyeOff v-if="showPassword" class="h-4 w-4" />
                  <Eye v-else class="h-4 w-4" />
                </button>
              </div>
            </div>

            <div>
              <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">
                Confirm password
              </label>
              <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                  <Lock class="h-4 w-4" />
                </span>
                <input
                  v-model="registerForm.passwordConfirmation"
                  :type="showPassword ? 'text' : 'password'"
                  required
                  minlength="6"
                  placeholder="Repeat your password"
                  autocomplete="new-password"
                  class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm text-slateink transition placeholder:text-slate-400 focus:border-alpine-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-alpine-100"
                />
              </div>
            </div>

            <button
              type="submit"
              :disabled="isLoading"
              class="mt-2 flex w-full items-center justify-center gap-2 rounded-2xl bg-alpine-600 py-3.5 text-sm font-bold text-white shadow-lg shadow-alpine-600/25 transition hover:bg-alpine-700 active:scale-[0.99] disabled:opacity-60"
            >
              <svg
                v-if="isLoading"
                class="h-4 w-4 animate-spin text-white"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path
                  class="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                ></path>
              </svg>
              <span>{{ isLoading ? 'Creating account...' : 'Create account' }}</span>
            </button>

            <div class="pt-2 text-center text-xs text-slate-500">
              Already have an account?
              <button
                type="button"
                class="font-bold text-alpine-700 hover:underline ml-1"
                @click="switchTab('login')"
              >
                Sign in
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </Teleport>
</template>
