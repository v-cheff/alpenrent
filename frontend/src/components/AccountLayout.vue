<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { ArrowLeft, LogOut } from 'lucide-vue-next'
import AuthModal from './AuthModal.vue'
import { useAuth } from '../composables/useAuth.js'

/**
 * Shell for the personal pages. Each page belongs to one role:
 * "renter" → /my-bookings, "owner" → /my-equipment.
 * The default slot renders only for a signed-in user with that role; `ready` fires then.
 */
const props = defineProps({
  title: { type: String, required: true },
  role: { type: String, required: true, validator: (value) => ['renter', 'owner'].includes(value) },
  signInText: { type: String, required: true },
  icon: { type: [Object, Function], required: true },
})

const emit = defineEmits(['ready'])

const { user, isAuthenticated, accountPage, checkAuth, openLogin, logout } = useAuth()
const authChecked = ref(false)

const hasRole = computed(() => isAuthenticated.value && (user.value?.role ?? 'renter') === props.role)
const roleName = computed(() => (props.role === 'owner' ? 'rental point owner' : 'renter'))

watch(
  () => authChecked.value && hasRole.value,
  (ready) => {
    if (ready) emit('ready')
  },
)

function signIn() {
  openLogin(`Sign in with a ${roleName.value} account to continue.`)
}

async function signOut() {
  await logout()
  window.location.href = '/'
}

onMounted(async () => {
  await checkAuth()
  authChecked.value = true
})
</script>

<template>
  <div class="min-h-screen bg-slate-100 text-slate-900">
    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur">
      <div class="mx-auto flex max-w-[1600px] flex-wrap items-center justify-between gap-4 px-5 py-5 lg:px-8">
        <div class="flex items-center gap-4">
          <a
            href="/"
            class="flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-600 transition hover:bg-slate-100"
            aria-label="Back to catalog"
            title="Back to catalog"
          >
            <ArrowLeft class="h-5 w-5" />
          </a>
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-cyan-600">Alpenrent</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ title }}</h1>
          </div>
        </div>

        <div v-if="isAuthenticated" class="flex flex-wrap items-center gap-3">
          <div class="flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 py-1.5 pl-2 pr-3">
            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-cyan-600 text-xs font-bold text-white">
              {{ user?.name ? user.name.charAt(0).toUpperCase() : 'U' }}
            </span>
            <span class="max-w-[160px] truncate text-sm font-medium text-slate-700">{{ user?.name }}</span>
            <button
              type="button"
              class="rounded-full p-1 text-slate-400 transition hover:bg-slate-200 hover:text-slate-700"
              aria-label="Log out"
              title="Log out"
              @click="signOut"
            >
              <LogOut class="h-4 w-4" />
            </button>
          </div>

          <slot v-if="hasRole" name="actions" />
        </div>
      </div>
    </header>

    <main class="mx-auto max-w-[1600px] px-5 py-8 lg:px-8">
      <p v-if="!authChecked" class="text-sm text-slate-500">Loading…</p>

      <section
        v-else-if="!isAuthenticated || !hasRole"
        class="mx-auto mt-10 max-w-lg rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-sm"
      >
        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-700">
          <component :is="icon" class="h-7 w-7" />
        </span>

        <template v-if="!isAuthenticated">
          <h2 class="mt-4 text-xl font-bold text-slate-900">{{ title }}</h2>
          <p class="mt-2 text-sm text-slate-500">{{ signInText }}</p>
          <button
            type="button"
            class="mt-6 rounded-full bg-cyan-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-600/20 transition hover:bg-cyan-500"
            @click="signIn"
          >
            Sign in or register
          </button>
        </template>

        <template v-else>
          <h2 class="mt-4 text-xl font-bold text-slate-900">This page is for {{ roleName }}s</h2>
          <p class="mt-2 text-sm text-slate-500">
            You are signed in as {{ user?.role === 'owner' ? 'a rental point owner' : 'a renter' }}.
            Your personal page is "{{ accountPage.label }}".
          </p>
          <a
            :href="accountPage.href"
            class="mt-6 inline-block rounded-full bg-cyan-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-600/20 transition hover:bg-cyan-500"
          >
            Go to {{ accountPage.label }}
          </a>
        </template>
      </section>

      <slot v-else />
    </main>

    <AuthModal />
  </div>
</template>
