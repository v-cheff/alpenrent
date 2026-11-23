<script setup>
import { computed, ref, watch } from 'vue'
import { catalogTabs } from '../data/catalog'
import { useBooking } from '../composables/useBooking'
import { getEquipment } from '../api/equipment'
import { bookingDatesLabel } from '../utils/bookings.js'
import ProductCard from './ProductCard.vue'
import Spinner from './Spinner.vue'

const { state, location } = useBooking()

const activeTab = ref('all')
const equipment = ref([])
const isLoading = ref(false)
const error = ref('')
let requestId = 0

// The API loader stays at least this long so the grid doesn't flash/jump on fast responses.
// Category tabs and "show booked out" filter already-loaded data, so they switch instantly.
const MIN_LOADER_MS = 450
const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms))

const isBusy = isLoading

// Live data: equipment owners listed, with sizes still free for the selected dates and store.
const loadEquipment = async () => {
  const current = ++requestId
  const startedAt = Date.now()
  isLoading.value = true
  error.value = ''

  try {
    const data = await getEquipment({
      rentalPointId: state.rentalPointId,
      startDate: state.startDate,
      endDate: state.endDate,
    })

    if (!Array.isArray(data)) {
      throw new Error('Invalid equipment data format')
    }

    await wait(MIN_LOADER_MS - (Date.now() - startedAt))

    // Ignore responses that arrive after a newer request (fast date changes).
    if (current === requestId) equipment.value = data
  } catch (err) {
    console.error('Failed to load equipment:', err)
    if (current === requestId) error.value = 'Unable to load products. Please try again.'
  } finally {
    if (current === requestId) isLoading.value = false
  }
}

const showBookedOut = ref(false)

const isFree = (item) =>
  !Array.isArray(item.availableSizes) || item.availableSizes.length > 0

const inTab = computed(() =>
  activeTab.value === 'all'
    ? equipment.value
    : equipment.value.filter((item) => item.category === activeTab.value),
)

// Items with no free size for the selected dates are hidden unless asked for.
const bookedOutCount = computed(() => inTab.value.filter((item) => !isFree(item)).length)
const availableCount = computed(() => inTab.value.length - bookedOutCount.value)

const filtered = computed(() =>
  showBookedOut.value ? inTab.value : inTab.value.filter(isFree),
)

const periodLabel = computed(() =>
  bookingDatesLabel({ startDate: state.startDate, endDate: state.endDate }),
)

watch(
  () => [state.startDate, state.endDate, state.rentalPointId, state.catalogVersion],
  loadEquipment,
  { immediate: true },
)
</script>

<template>
  <section
    id="catalog"
    class="relative z-10 -mt-6 bg-slate-50 pb-20 pt-4 sm:pt-8"
  >
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div
        class="mb-8 flex flex-col gap-4 sm:flex-row
               sm:items-end sm:justify-between"
      >
        <div>
          <p
            class="text-xs font-bold uppercase tracking-[0.2em]
                   text-alpine-700"
          >
            Interactive catalog
          </p>

          <h2
            class="mt-2 text-3xl font-extrabold tracking-tight
                   text-slateink sm:text-4xl"
          >
            Tuned gear, ready to ride
          </h2>

          <p class="mt-2 text-sm font-medium text-emerald-700">
            {{ availableCount }} available for {{ periodLabel }} ·
            {{ location ? location.name : 'all rental points' }}
          </p>
          <button
            v-if="bookedOutCount"
            type="button"
            class="mt-1 text-sm font-semibold text-slate-500 underline-offset-2 hover:text-slateink hover:underline"
            @click="showBookedOut = !showBookedOut"
          >
            {{ showBookedOut ? 'Hide' : 'Show' }} {{ bookedOutCount }} booked out
          </button>
        </div>

        <p class="max-w-sm text-sm leading-relaxed text-slate-500">
          Daily rates shown include 20% Austrian VAT (MwSt.).
          Deposits are authorized, not charged unless damaged.
        </p>
      </div>

      <div
        class="mb-8 flex gap-2 overflow-x-auto
               no-scrollbar pb-1"
      >
        <button
          v-for="tab in catalogTabs"
          :key="tab.id"
          type="button"
          :aria-pressed="activeTab === tab.id"
          class="whitespace-nowrap rounded-full px-4 py-2
                 text-sm font-semibold transition"
          :class="
            activeTab === tab.id
              ? 'bg-slateink text-white shadow-sm'
              : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-100'
          "
          @click="activeTab = tab.id"
        >
          {{ tab.label }}
        </button>
      </div>

      <div class="relative min-h-[16rem]" :aria-busy="isBusy">
      <div
        v-if="isLoading && !equipment.length"
        class="flex justify-center py-12"
        role="status"
        aria-label="Loading products"
      >
        <Spinner />
      </div>

      <div
        v-else-if="error"
        class="flex flex-col items-center gap-4 py-20 text-center"
        role="alert"
      >
        <p class="text-lg font-semibold text-slateink">
          {{ error }}
        </p>

        <button
          type="button"
          class="rounded-full bg-slateink px-5 py-2
                 text-sm font-semibold text-white
                 transition hover:opacity-90"
          @click="loadEquipment"
        >
          Try again
        </button>
      </div>

      <div
        v-else-if="filtered.length"
        class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3"
      >
        <ProductCard
          v-for="item in filtered"
          :key="item.id"
          :item="item"
        />
      </div>

      <div
        v-else
        class="flex flex-col items-center justify-center
               gap-4 py-20 text-center"
      >
        <p class="text-lg font-semibold text-slateink">
          {{ bookedOutCount ? 'Everything here is booked out for these dates.' : 'No equipment here yet.' }}
        </p>

        <p class="max-w-sm text-sm leading-relaxed text-slate-500">
          Try other dates, another category or pickup location.
        </p>
      </div>

      <!-- Refresh loader over the previous results: keeps the layout in place, no jump. -->
      <Transition
        enter-active-class="transition-opacity duration-200"
        leave-active-class="transition-opacity duration-300"
        enter-from-class="opacity-0"
        leave-to-class="opacity-0"
      >
        <div
          v-if="isBusy && equipment.length && !error"
          class="absolute inset-0 z-10 rounded-3xl bg-slate-50/70 backdrop-blur-[2px]"
          role="status"
          aria-label="Updating products"
        >
          <div class="sticky top-1/3 flex justify-center py-12">
            <Spinner />
          </div>
        </div>
      </Transition>
      </div>
    </div>
  </section>
</template>
