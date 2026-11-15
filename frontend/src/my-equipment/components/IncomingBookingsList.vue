<script setup>
import { computed, ref } from 'vue'
import { CalendarDays, MapPin, Mail } from 'lucide-vue-next'
import {
  bookingDays as days,
  bookingStatus,
  formatBookingDate as formatDate,
} from '../../utils/bookings.js'

const props = defineProps({
  bookings: { type: Array, required: true },
  activeRentalPointId: { type: Number, default: null },
})

const filters = [
  { id: 'open', label: 'Upcoming & active' },
  { id: 'completed', label: 'Completed' },
  { id: 'all', label: 'All' },
]
const filter = ref('open')

const statusStyle = {
  upcoming: { label: 'Upcoming', class: 'bg-cyan-50 text-cyan-700' },
  active: { label: 'In rental', class: 'bg-emerald-50 text-emerald-700' },
  completed: { label: 'Completed', class: 'bg-slate-100 text-slate-500' },
}

const rows = computed(() =>
  props.bookings
    .filter(
      (booking) =>
        props.activeRentalPointId === null ||
        booking.equipment?.rentalPointId === props.activeRentalPointId,
    )
    .map((booking) => ({ ...booking, status: bookingStatus(booking) }))
    .filter((booking) => {
      if (filter.value === 'all') return true
      if (filter.value === 'completed') return booking.status === 'completed'
      return booking.status !== 'completed'
    }),
)

</script>

<template>
  <div>
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

    <div
      v-if="rows.length === 0"
      class="mt-6 flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 py-16 text-center"
    >
      <CalendarDays class="h-8 w-8 text-slate-300" />
      <p class="mt-3 text-base font-semibold text-slate-700">No bookings here yet</p>
      <p class="mt-1 text-sm text-slate-400">Bookings renters make for your equipment will show up in this list.</p>
    </div>

    <ul v-else class="mt-6 divide-y divide-slate-100 overflow-hidden rounded-2xl border border-slate-200">
      <li
        v-for="booking in rows"
        :key="booking.id"
        class="grid gap-4 bg-white p-4 sm:grid-cols-[minmax(0,2fr)_minmax(0,1.3fr)_minmax(0,1.3fr)_auto] sm:items-center"
      >
        <div class="flex min-w-0 items-center gap-3">
          <img
            v-if="booking.equipment?.image"
            :src="booking.equipment.image"
            :alt="booking.equipment.name"
            class="h-14 w-14 shrink-0 rounded-xl object-cover"
          />
          <div class="min-w-0">
            <p class="truncate font-semibold text-slate-900">{{ booking.equipment?.name ?? booking.equipmentId }}</p>
            <p class="text-xs text-slate-500">Size {{ booking.size }}</p>
            <p v-if="booking.equipment?.rentalPoint" class="mt-0.5 flex items-center gap-1 text-xs text-slate-500">
              <MapPin class="h-3 w-3 shrink-0" />
              <span class="truncate">{{ booking.equipment.rentalPoint.name }}</span>
            </p>
          </div>
        </div>

        <div class="text-sm">
          <p class="font-semibold text-slate-800">{{ formatDate(booking.startDate) }} → {{ formatDate(booking.endDate) }}</p>
          <p class="text-xs text-slate-500">
            {{ days(booking) }} days
            <template v-if="booking.equipment"> · €{{ booking.equipment.dailyRate * days(booking) }}</template>
          </p>
        </div>

        <div class="min-w-0 text-sm">
          <p class="truncate font-semibold text-slate-800">{{ booking.renter?.name ?? 'Renter' }}</p>
          <a
            v-if="booking.renter?.email"
            :href="`mailto:${booking.renter.email}`"
            class="flex items-center gap-1 truncate text-xs text-cyan-700 hover:underline"
          >
            <Mail class="h-3 w-3 shrink-0" />
            {{ booking.renter.email }}
          </a>
        </div>

        <span
          class="w-fit rounded-full px-2.5 py-1 text-xs font-semibold"
          :class="statusStyle[booking.status].class"
        >
          {{ statusStyle[booking.status].label }}
        </span>
      </li>
    </ul>
  </div>
</template>
