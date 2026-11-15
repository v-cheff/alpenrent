<script setup>
import { ref } from 'vue'
import { CalendarDays, LogOut, Menu, Mountain, Phone, Store, User as UserIcon, X } from 'lucide-vue-next'
import { useBooking } from '../composables/useBooking'
import { useAuth } from '../composables/useAuth'

const { state } = useBooking()
const { user, isAuthenticated, isOwner, accountPage, openLogin, logout } = useAuth()
const menuOpen = ref(false)

const links = [
  { href: '#catalog', label: 'Equipment' },
  { href: '#trust', label: 'Trust & VAT' },
  { href: '#reviews', label: 'Reviews' },
]

function openSizing() {
  menuOpen.value = false
  state.sizingOpen = true
}

function handleLoginClick() {
  menuOpen.value = false
  openLogin()
}

function handleLogoutClick() {
  menuOpen.value = false
  logout()
}

function go(href) {
  menuOpen.value = false
  document.querySelector(href)?.scrollIntoView({ behavior: 'smooth' })
}
</script>

<template>
  <header class="absolute inset-x-0 top-0 z-30">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
      <a href="#top" class="flex items-center gap-2 text-white">
        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/15 ring-1 ring-white/25 backdrop-blur">
          <Mountain class="h-5 w-5" />
        </span>
        <span class="text-lg font-extrabold tracking-tight">AlpenRent</span>
        <span class="hidden text-xs font-medium text-white/70 sm:inline">Tyrol</span>
      </a>

      <nav class="hidden items-center gap-8 text-sm font-medium text-white/85 md:flex">
        <a v-for="link in links" :key="link.href" :href="link.href" class="transition hover:text-white">
          {{ link.label }}
        </a>
        <button type="button" class="transition hover:text-white" @click="openSizing">
          Sizing guide
        </button>
      </nav>

      <div class="flex items-center gap-2.5">
        <!-- <a
          href="tel:+43512123456"
          class="hidden items-center gap-2 rounded-full bg-white/10 px-3 py-2 text-sm font-semibold text-white ring-1 ring-white/20 backdrop-blur transition hover:bg-white/20 sm:inline-flex"
        >
          <Phone class="h-4 w-4" />
          <span>+43 512 123 456</span>
        </a> -->

        <!-- AUTH BUTTONS (DESKTOP) -->
        <template v-if="isAuthenticated">
          <a
            :href="accountPage.href"
            class="hidden items-center gap-1.5 rounded-full bg-white/10 px-3 py-2 text-sm font-semibold text-white ring-1 ring-white/20 backdrop-blur transition hover:bg-white/20 md:inline-flex"
          >
            <component :is="isOwner ? Store : CalendarDays" class="h-4 w-4" />
            <span>{{ accountPage.label }}</span>
          </a>
          <div class="hidden items-center gap-2 rounded-full bg-white/15 py-1.5 pl-3 pr-2 text-sm font-semibold text-white ring-1 ring-white/25 backdrop-blur md:flex">
            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-alpine-600 text-xs font-extrabold text-white">
              {{ user?.name ? user.name.charAt(0).toUpperCase() : 'U' }}
            </span>
            <span class="max-w-[120px] truncate text-xs">{{ user?.name }}</span>
            <button
              type="button"
              class="rounded-full p-1 text-white/80 transition hover:bg-white/20 hover:text-white"
              title="Log out"
              aria-label="Log out"
              @click="handleLogoutClick"
            >
              <LogOut class="h-3.5 w-3.5" />
            </button>
          </div>
        </template>
        <template v-else>
          <button
            type="button"
            class="hidden items-center gap-1.5 rounded-full bg-white px-4 py-2 text-sm font-bold text-slateink shadow-sm transition hover:bg-slate-100 md:inline-flex"
            @click="handleLoginClick"
          >
            <UserIcon class="h-4 w-4 text-alpine-700" />
            <span>Sign in</span>
          </button>
        </template>

        <!-- Mobile menu hamburger -->
        <button
          class="rounded-full p-2 text-white md:hidden"
          type="button"
          :aria-expanded="menuOpen"
          aria-label="Menu"
          @click="menuOpen = !menuOpen"
        >
          <X v-if="menuOpen" class="h-5 w-5" />
          <Menu v-else class="h-5 w-5" />
        </button>
      </div>
    </div>

    <!-- Mobile menu -->
    <nav
      v-if="menuOpen"
      class="mx-4 mt-2 rounded-3xl bg-white p-5 text-slateink shadow-float ring-1 ring-slate-900/10 md:hidden"
    >
      <!-- Mobile Auth Section -->
      <div class="mb-4 border-b border-slate-100 pb-4">
        <template v-if="isAuthenticated">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
              <span class="flex h-9 w-9 items-center justify-center rounded-full bg-alpine-600 text-sm font-bold text-white">
                {{ user?.name ? user.name.charAt(0).toUpperCase() : 'U' }}
              </span>
              <div>
                <p class="text-sm font-bold text-slateink">{{ user?.name }}</p>
                <p class="text-xs text-slate-500">{{ user?.email }}</p>
              </div>
            </div>
            <button
              type="button"
              class="flex items-center gap-1 rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-200"
              @click="handleLogoutClick"
            >
              <LogOut class="h-3.5 w-3.5" />
              <span>Log out</span>
            </button>
          </div>
          <a
            :href="accountPage.href"
            class="mt-3 flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-100 py-2.5 text-sm font-semibold text-slate-800 hover:bg-slate-200"
          >
            <component :is="isOwner ? Store : CalendarDays" class="h-4 w-4" />
            <span>{{ accountPage.label }}</span>
          </a>
        </template>
        <template v-else>
          <button
            type="button"
            class="flex w-full items-center justify-center gap-2 rounded-2xl bg-alpine-600 py-3 text-sm font-bold text-white shadow-md shadow-alpine-600/20"
            @click="handleLoginClick"
          >
            <UserIcon class="h-4 w-4" />
            <span>Sign in / Register</span>
          </button>
        </template>
      </div>

      <a
        v-for="link in links"
        :key="link.href"
        :href="link.href"
        class="block rounded-xl px-3 py-2.5 text-sm font-semibold hover:bg-slate-50"
        @click.prevent="go(link.href)"
      >
        {{ link.label }}
      </a>
      <button
        type="button"
        class="block w-full rounded-xl px-3 py-2.5 text-left text-sm font-semibold hover:bg-slate-50"
        @click="openSizing"
      >
        Sizing guide
      </button>
    </nav>
  </header>
</template>
