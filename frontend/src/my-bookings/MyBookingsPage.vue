<script setup>
import { computed, reactive, ref } from 'vue'
import { CalendarDays, Clock3, MapPin } from 'lucide-vue-next'
import AccountLayout from '../components/AccountLayout.vue'
import { cancelBooking, getMyBookings } from '../api/bookings.js'
import { bookingDatesLabel, bookingDurationLabel, bookingStatus } from '../utils/bookings.js'

const state = reactive({
  items: [],
  isLoading: true,
  loadError: '',
})

const filters = [
  { id: 'open', label: 'Upcoming & active' },
  { id: 'completed', label: 'Past' },
  { id: 'all', label: 'All' },
]
const filter = ref('open')

// Two-step cancel: first click arms the button, second click cancels.
const confirmingId = ref(null)
const cancellingId = ref(null)
const cancelError = ref('')

const statusStyle = {
  upcoming: { label: 'Upcoming', class: 'bg-cyan-50 text-cyan-700' },
  active: { label: 'In rental', class: 'bg-emerald-50 text-emerald-700' },
  completed: { label: 'Completed', class: 'bg-slate-100 text-slate-500' },
}

const rows = computed(() =>
  state.items
    .map((booking) => ({ ...booking, status: bookingStatus(booking) }))
    .filter((booking) => {
      if (filter.value === 'all') return true
      if (filter.value === 'completed') return booking.status === 'completed'
      return booking.status !== 'completed'
    })
    .sort((a, b) => a.startDate.localeCompare(b.startDate)),
)

const openCount = computed(
  () => state.items.filter((booking) => bookingStatus(booking) !== 'completed').length,
)

async function load() {
  state.isLoading = true
  state.loadError = ''

  try {
    const data = await getMyBookings()
    state.items = Array.isArray(data) ? data : []
  } catch (error) {
    state.items = []
    state.loadError = error.message || 'Could not load your bookings.'
  } finally {
    state.isLoading = false
  }
}

async function cancel(booking) {
  if (confirmingId.value !== booking.id) {
    confirmingId.value = booking.id
    cancelError.value = ''
    return
  }

  cancellingId.value = booking.id
  cancelError.value = ''

  try {
    await cancelBooking(booking.id)
    state.items = state.items.filter((item) => item.id !== booking.id)
    confirmingId.value = null
  } catch (error) {
    cancelError.value = error.message || 'Could not cancel the booking.'
  } finally {
    cancellingId.value = null
  }
}
</script>

<template>
  <AccountLayout
    title="My bookings"
    role="renter"
    :icon="CalendarDays"
    sign-in-text="Sign in with a renter account to see and manage the equipment you have booked."
    @ready="load"
  >
    <template #actions>
      <a
        href="/#catalog"
        class="rounded-full bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-cyan-600/20 transition hover:bg-cyan-500"
      >
        Book equipment
      </a>
    </template>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap gap-2">
          <button
            v-for="option in filters"
            :key="option.id"
            type="button"
            class="rounded-full px-4 py-2 text-sm font-medium transition"
            :class="filter === option.id ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
            @click="filter = option.id"
          >
            {{ option.label }}
          </button>
        </div>
        <p class="text-sm text-slate-500">{{ openCount }} open bookings</p>
      </div>

      <p v-if="state.isLoading" class="mt-6 text-sm text-slate-500">Loading your bookings…</p>
      <p
        v-else-if="state.loadError"
        class="mt-6 rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700"
        role="alert"
      >
        {{ state.loadError }}
      </p>

      <div
        v-else-if="rows.length === 0"
        class="mt-6 flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 py-16 text-center"
      >
        <CalendarDays class="h-8 w-8 text-slate-300" />
        <p class="mt-3 text-base font-semibold text-slate-700">No bookings here</p>
        <p class="mt-1 text-sm text-slate-400">Pick your skis or board in the catalog to book them.</p>
        <a
          href="/#catalog"
          class="mt-4 rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold text-white transition hover:bg-slate-800"
        >
          Browse equipment
        </a>
      </div>

      <template v-else>
        <p
          v-if="cancelError"
          class="mt-6 rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700"
          role="alert"
        >
          {{ cancelError }}
        </p>

        <ul class="mt-6 grid gap-4 lg:grid-cols-2">
          <li
            v-for="booking in rows"
            :key="booking.id"
            class="flex gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4"
          >
            <img
              v-if="booking.equipment?.image"
              :src="booking.equipment.image"
              :alt="booking.equipment.name"
              class="h-24 w-24 shrink-0 rounded-xl object-cover sm:h-28 sm:w-28"
            />

            <div class="flex min-w-0 flex-1 flex-col">
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <p class="truncate font-bold text-slate-900">{{ booking.equipment?.name ?? booking.equipmentId }}</p>
                  <p class="text-xs text-slate-500">Size {{ booking.size }}</p>
                </div>
                <span
                  class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold"
                  :class="statusStyle[booking.status].class"
                >
                  {{ statusStyle[booking.status].label }}
                </span>
              </div>

              <p class="mt-2 text-sm font-semibold text-slate-800">
                {{ bookingDatesLabel(booking) }}
              </p>
              <p class="text-xs text-slate-500">
                {{ bookingDurationLabel(booking) }}
                <template v-if="booking.total !== null && booking.total !== undefined"> · €{{ booking.total }}</template>
                <template v-if="booking.equipment"> · deposit €{{ booking.equipment.deposit }}</template>
              </p>

              <div v-if="booking.equipment?.rentalPoint" class="mt-2 space-y-0.5 text-xs text-slate-500">
                <p class="flex items-start gap-1">
                  <MapPin class="mt-0.5 h-3.5 w-3.5 shrink-0 text-cyan-600" />
                  <span>
                    <span class="font-semibold text-slate-700">{{ booking.equipment.rentalPoint.name }}</span>
                    <template v-if="booking.equipment.rentalPoint.address"> · {{ booking.equipment.rentalPoint.address }}</template>
                  </span>
                </p>
                <p v-if="booking.equipment.rentalPoint.openingHours" class="flex items-center gap-1">
                  <Clock3 class="h-3.5 w-3.5 shrink-0" />
                  {{ booking.equipment.rentalPoint.openingHours }}
                </p>
              </div>

              <div v-if="booking.status === 'upcoming'" class="mt-3 flex justify-end gap-2">
                <button
                  v-if="confirmingId === booking.id"
                  type="button"
                  class="rounded-full px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-200"
                  @click="confirmingId = null"
                >
                  Keep
                </button>
                <button
                  type="button"
                  :disabled="cancellingId === booking.id"
                  class="rounded-full border px-3 py-1.5 text-xs font-semibold transition disabled:opacity-50"
                  :class="confirmingId === booking.id
                    ? 'border-rose-600 bg-rose-600 text-white hover:bg-rose-500'
                    : 'border-slate-200 bg-white text-rose-600 hover:bg-rose-50'"
                  @click="cancel(booking)"
                >
                  {{
                    cancellingId === booking.id
                      ? 'Cancelling…'
                      : confirmingId === booking.id
                        ? 'Yes, cancel booking'
                        : 'Cancel booking'
                  }}
                </button>
              </div>
            </div>
          </li>
        </ul>
      </template>
    </section>
  </AccountLayout>
</template>
