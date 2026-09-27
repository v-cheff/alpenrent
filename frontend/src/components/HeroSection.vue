<script setup>
import { computed, ref } from 'vue'
import { CalendarRange, ChevronDown, MapPin, Search, Snowflake } from 'lucide-vue-next'
import { locations, skillLevels } from '../data/catalog'
import { useBooking } from '../composables/useBooking'

const { state, durationDays, setDates, checkAvailability } = useBooking()

const locationOpen = ref(false)
const skillOpen = ref(false)

const selectedLocation = computed(
  () => locations.find((l) => l.id === state.locationId) ?? locations[0],
)
const selectedSkill = computed(
  () => skillLevels.find((s) => s.id === state.skill) ?? skillLevels[1],
)

function onStartChange(event) {
  setDates(event.target.value, state.endDate)
}

function onEndChange(event) {
  setDates(state.startDate, event.target.value)
}

function pickLocation(id) {
  state.locationId = id
  locationOpen.value = false
}

function pickSkill(id) {
  state.skill = id
  skillOpen.value = false
}
</script>

<template>
  <section id="top" class="relative isolate min-h-[100svh] overflow-hidden bg-slateink">
    <img
      src="https://images.unsplash.com/photo-1483728642387-6c3bdd6c93e5?auto=format&fit=crop&w=2000&q=80"
      alt="Sunlit alpine ski resort above a Tyrolean valley"
      class="absolute inset-0 h-full w-full object-cover"
    />
    <div class="absolute inset-0 bg-gradient-to-b from-slateink/70 via-slateink/45 to-slate-50" />
    <div class="absolute inset-x-0 top-0 h-40 bg-gradient-to-b from-slateink/80 to-transparent" />

    <div class="relative mx-auto flex min-h-[100svh] max-w-7xl flex-col justify-end px-4 pb-10 pt-28 sm:px-6 lg:justify-center lg:px-8 lg:pb-24 lg:pt-32">
      <div class="max-w-3xl text-white">
        <p class="mb-4 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-sky-100 ring-1 ring-white/20 backdrop-blur">
          <Snowflake class="h-3.5 w-3.5" />
          Tyrol · Austria
        </p>
        <h1 class="text-4xl font-extrabold leading-[1.08] tracking-tight sm:text-5xl lg:text-6xl">
          Premium Ski & Snowboard Rental in Tyrol — Ready on the Slopes
        </h1>
        <p class="mt-5 max-w-xl text-base leading-relaxed text-slate-100/90 sm:text-lg">
          Skip the lines. Guaranteed equipment tuning, free cancellation up to 24 hours.
        </p>
      </div>

      <form
        class="mt-8 w-full rounded-3xl bg-white p-3 shadow-float ring-1 ring-slate-200/80 sm:p-4"
        @submit.prevent="checkAvailability"
      >
        <div class="grid gap-3 lg:grid-cols-[1.35fr_1.1fr_1fr_auto]">
          <label class="relative flex min-h-[4.75rem] flex-col justify-center rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
            <span class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
              <CalendarRange class="h-3.5 w-3.5 text-alpine-600" />
              Rental dates
            </span>
            <div class="mt-1 flex flex-wrap items-center gap-2">
              <input
                v-model="state.startDate"
                type="date"
                class="w-[9.5rem] bg-transparent text-sm font-semibold text-slateink"
                :max="state.endDate"
                @change="onStartChange"
              />
              <span class="text-slate-400">→</span>
              <input
                v-model="state.endDate"
                type="date"
                class="w-[9.5rem] bg-transparent text-sm font-semibold text-slateink"
                :min="state.startDate"
                @change="onEndChange"
              />
              <span class="rounded-full bg-alpine-50 px-2.5 py-0.5 text-xs font-bold text-alpine-700">
                {{ durationDays }} {{ durationDays === 1 ? 'day' : 'days' }}
              </span>
            </div>
          </label>

          <div class="relative">
            <button
              type="button"
              class="flex min-h-[4.75rem] w-full items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-left"
              :aria-expanded="locationOpen"
              @click="locationOpen = !locationOpen; skillOpen = false"
            >
              <span>
                <span class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                  <MapPin class="h-3.5 w-3.5 text-alpine-600" />
                  Pickup location
                </span>
                <span class="mt-1 block text-sm font-semibold text-slateink">{{ selectedLocation.label }}</span>
                <span class="text-xs text-slate-500">{{ selectedLocation.hint }}</span>
              </span>
              <ChevronDown class="h-4 w-4 text-slate-400" />
            </button>
            <ul
              v-if="locationOpen"
              class="absolute z-20 mt-2 w-full overflow-hidden rounded-2xl border border-slate-200 bg-white py-1 shadow-card"
            >
              <li v-for="loc in locations" :key="loc.id">
                <button
                  type="button"
                  class="flex w-full flex-col px-4 py-2.5 text-left hover:bg-slate-50"
                  @click="pickLocation(loc.id)"
                >
                  <span class="text-sm font-semibold">{{ loc.label }}</span>
                  <span class="text-xs text-slate-500">{{ loc.hint }}</span>
                </button>
              </li>
            </ul>
          </div>

          <div class="relative">
            <button
              type="button"
              class="flex min-h-[4.75rem] w-full items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-left"
              :aria-expanded="skillOpen"
              @click="skillOpen = !skillOpen; locationOpen = false"
            >
              <span>
                <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Skier skill</span>
                <span class="mt-1 block text-sm font-semibold text-slateink">{{ selectedSkill.label }}</span>
                <span class="text-xs text-slate-500">{{ selectedSkill.description }}</span>
              </span>
              <ChevronDown class="h-4 w-4 text-slate-400" />
            </button>
            <ul
              v-if="skillOpen"
              class="absolute z-20 mt-2 w-full overflow-hidden rounded-2xl border border-slate-200 bg-white py-1 shadow-card"
            >
              <li v-for="level in skillLevels" :key="level.id">
                <button
                  type="button"
                  class="flex w-full flex-col px-4 py-2.5 text-left hover:bg-slate-50"
                  @click="pickSkill(level.id)"
                >
                  <span class="text-sm font-semibold">{{ level.label }}</span>
                  <span class="text-xs text-slate-500">{{ level.description }}</span>
                </button>
              </li>
            </ul>
          </div>

          <button
            type="submit"
            class="inline-flex min-h-[4.75rem] items-center justify-center gap-2 rounded-2xl bg-alpine-600 px-6 text-sm font-bold text-white shadow-lg shadow-sky-900/20 transition hover:bg-alpine-700"
          >
            <Search class="h-4 w-4" />
            Check Availability
          </button>
        </div>
      </form>
    </div>
  </section>
</template>
