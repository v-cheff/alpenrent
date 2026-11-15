import { computed, ref } from 'vue'
import {
  getCurrentUser,
  loginUser,
  logoutUser,
  registerUser,
} from '../api/auth.js'

const user = ref(null)
const token = ref(localStorage.getItem('alpenrent_token') || '')
const isLoading = ref(false)

const authModalOpen = ref(false)
const authModalTab = ref('login') // 'login' | 'register'
const authModalNotice = ref('')
const onAuthSuccessCallback = ref(null)

export function useAuth() {
  const isAuthenticated = computed(() => !!user.value)
  const isOwner = computed(() => user.value?.role === 'owner')
  const isRenter = computed(() => !!user.value && !isOwner.value)

  // Each role has its own personal page.
  const accountPage = computed(() =>
    isOwner.value
      ? { href: '/my-equipment', label: 'My rental points' }
      : { href: '/my-bookings', label: 'My bookings' },
  )

  function openLogin(notice = '', onSuccess = null) {
    authModalTab.value = 'login'
    authModalNotice.value = notice
    onAuthSuccessCallback.value = onSuccess
    authModalOpen.value = true
  }

  function openRegister(notice = '', onSuccess = null) {
    authModalTab.value = 'register'
    authModalNotice.value = notice
    onAuthSuccessCallback.value = onSuccess
    authModalOpen.value = true
  }

  function closeAuthModal() {
    authModalOpen.value = false
    authModalNotice.value = ''
    onAuthSuccessCallback.value = null
  }

  async function checkAuth() {
    if (!token.value) {
      user.value = null
      return null
    }

    isLoading.value = true
    try {
      const data = await getCurrentUser()
      user.value = data.user
      return user.value
    } catch {
      // Token expired or invalid
      token.value = ''
      user.value = null
      localStorage.removeItem('alpenrent_token')
      return null
    } finally {
      isLoading.value = false
    }
  }

  async function login({ email, password }) {
    isLoading.value = true
    try {
      const data = await loginUser({ email, password })
      token.value = data.token
      user.value = data.user
      localStorage.setItem('alpenrent_token', data.token)

      const callback = onAuthSuccessCallback.value
      closeAuthModal()

      if (typeof callback === 'function') {
        callback(data.user)
      }

      return data
    } finally {
      isLoading.value = false
    }
  }

  async function register({ name, email, password, role = 'renter' }) {
    isLoading.value = true
    try {
      const data = await registerUser({ name, email, password, role })
      token.value = data.token
      user.value = data.user
      localStorage.setItem('alpenrent_token', data.token)

      const callback = onAuthSuccessCallback.value
      closeAuthModal()

      if (typeof callback === 'function') {
        callback(data.user)
      }

      return data
    } finally {
      isLoading.value = false
    }
  }

  async function logout() {
    isLoading.value = true
    try {
      await logoutUser()
    } catch {
      // Ignore errors on logout
    } finally {
      token.value = ''
      user.value = null
      localStorage.removeItem('alpenrent_token')
      isLoading.value = false
    }
  }

  return {
    user,
    token,
    isLoading,
    isAuthenticated,
    isOwner,
    isRenter,
    accountPage,
    authModalOpen,
    authModalTab,
    authModalNotice,
    openLogin,
    openRegister,
    closeAuthModal,
    checkAuth,
    login,
    register,
    logout,
  }
}
