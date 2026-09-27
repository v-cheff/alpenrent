<script setup>
import { ref } from 'vue'
import { Mountain, Menu, Phone, X } from 'lucide-vue-next'
import { useBooking } from '../composables/useBooking'

const { state } = useBooking()
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

      <div class="flex items-center gap-2">
        <a
          href="tel:+43512123456"
          class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-2 text-sm font-semibold text-white ring-1 ring-white/20 backdrop-blur transition hover:bg-white/20"
        >
          <Phone class="h-4 w-4" />
          <span class="hidden sm:inline">+43 512 123 456</span>
        </a>
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

    <nav
      v-if="menuOpen"
      class="mx-4 rounded-2xl bg-white p-4 text-slateink shadow-float md:hidden"
    >
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
