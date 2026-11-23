<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import { CalendarRange, ChevronDown, MapPin, Search, Snowflake } from 'lucide-vue-next'
import { useBooking } from '../composables/useBooking'

const { state, today, rentalDaysBetween, rentalPoints, loadRentalPoints, checkAvailability } =
  useBooking()

// Search draft: the catalog changes only when "Check Availability" is pressed.
const draft = reactive({
  startDate: state.startDate,
  endDate: state.endDate,
  rentalPointId: state.rentalPointId,
})

const locationOpen = ref(false)
const locationBox = ref(null)

const durationDays = computed(() => rentalDaysBetween(draft.startDate, draft.endDate))
const location = computed(
  () => rentalPoints.items.find((point) => point.id === draft.rentalPointId) ?? null,
)

function onStartChange(event) {
  const start = event.target.value || today
  draft.startDate = start < today ? today : start
  if (!draft.endDate || draft.endDate < draft.startDate) draft.endDate = draft.startDate
}

function onEndChange(event) {
  const end = event.target.value
  draft.endDate = !end || end < draft.startDate ? draft.startDate : end
}

function pickLocation(id) {
  draft.rentalPointId = id
  locationOpen.value = false
}

function submit() {
  locationOpen.value = false
  checkAvailability({ ...draft })
}

function onDocumentClick(event) {
  if (locationOpen.value && !locationBox.value?.contains(event.target)) {
    locationOpen.value = false
  }
}

function pointHint(point) {
  return [point.address, point.openingHours].filter(Boolean).join(' · ')
}

onMounted(async () => {
  document.addEventListener('click', onDocumentClick)
  if (!rentalPoints.loaded) await loadRentalPoints()
  // loadRentalPoints resets an unknown point; keep the draft in sync with it.
  draft.rentalPointId = state.rentalPointId
})

onBeforeUnmount(() => document.removeEventListener('click', onDocumentClick))
</script>

<template>
  <!-- No overflow-hidden on the section: it would clip the pickup-location dropdown.
       Only the background layer is clipped. The catalog below is z-10 with -mt-6, so the hero
       goes above it only while the dropdown is open. -->
  <section
    id="top"
    class="relative isolate min-h-[100svh] bg-slateink"
    :class="locationOpen ? 'z-20' : 'z-0'"
  >
    <div class="absolute inset-0 -z-10 overflow-hidden" aria-hidden="true">
      <img
        src="/images/hero-mountains.jpg"
        alt=""
        class="absolute inset-0 h-full w-full object-cover"
      />
      <div class="absolute inset-0 bg-gradient-to-b from-slateink/70 via-slateink/45 to-slate-50" />
      <div class="absolute inset-x-0 top-0 h-40 bg-gradient-to-b from-slateink/80 to-transparent" />
    </div>

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
        @submit.prevent="submit"
      >
        <div class="grid gap-3 lg:grid-cols-[1.35fr_1.25fr_auto]">
          <label class="relative flex min-h-[4.75rem] flex-col justify-center rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
            <span class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
              <CalendarRange class="h-3.5 w-3.5 text-alpine-600" />
              Rental dates
            </span>
            <div class="mt-1 flex flex-wrap items-center gap-2">
              <input
                :value="draft.startDate"
                type="date"
                class="w-[9.5rem] bg-transparent text-sm font-semibold text-slateink"
                :min="today"
                @change="onStartChange"
              />
              <span class="text-slate-400">→</span>
              <input
                :value="draft.endDate"
                type="date"
                class="w-[9.5rem] bg-transparent text-sm font-semibold text-slateink"
                :min="draft.startDate"
                @change="onEndChange"
              />
              <span class="rounded-full bg-alpine-50 px-2.5 py-0.5 text-xs font-bold text-alpine-700">
                {{ durationDays }} {{ durationDays === 1 ? 'day' : 'days' }}
              </span>
            </div>
          </label>

          <div ref="locationBox" class="relative" :class="locationOpen ? 'z-30' : 'z-0'">
            <button
              type="button"
              class="flex min-h-[4.75rem] w-full items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-left"
              :aria-expanded="locationOpen"
              @click="locationOpen = !locationOpen"
            >
              <span class="min-w-0">
                <span class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                  <MapPin class="h-3.5 w-3.5 text-alpine-600" />
                  Pickup location
                </span>
                <span class="mt-1 block truncate text-sm font-semibold text-slateink">
                  {{ location ? location.name : 'All rental points' }}
                </span>
                <span class="block truncate text-xs text-slate-500">
                  {{
                    location
                      ? pointHint(location)
                      : rentalPoints.isLoading
                        ? 'Loading rental points…'
                        : `${rentalPoints.items.length} rental points in Tyrol`
                  }}
                </span>
              </span>
              <ChevronDown class="h-4 w-4 shrink-0 text-slate-400" />
            </button>
            <ul
              v-if="locationOpen"
              class="absolute z-40 mt-2 max-h-80 w-full overflow-y-auto rounded-2xl border border-slate-200 bg-white py-1 shadow-card"
            >
              <li>
                <button
                  type="button"
                  class="flex w-full flex-col px-4 py-2.5 text-left hover:bg-slate-50"
                  :class="draft.rentalPointId === null && 'bg-alpine-50'"
                  @click="pickLocation(null)"
                >
                  <span class="text-sm font-semibold">All rental points</span>
                  <span class="text-xs text-slate-500">Show equipment from every store</span>
                </button>
              </li>
              <li v-for="point in rentalPoints.items" :key="point.id">
                <button
                  type="button"
                  class="flex w-full flex-col px-4 py-2.5 text-left hover:bg-slate-50"
                  :class="draft.rentalPointId === point.id && 'bg-alpine-50'"
                  @click="pickLocation(point.id)"
                >
                  <span class="text-sm font-semibold">{{ point.name }}</span>
                  <span class="text-xs text-slate-500">{{ pointHint(point) }}</span>
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
