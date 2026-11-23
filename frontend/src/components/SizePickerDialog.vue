<script setup>
import { computed } from 'vue'
import { X } from 'lucide-vue-next'
import { useBooking } from '../composables/useBooking'
import { useAuth } from '../composables/useAuth'

const {
  state,
  selectedItem,
  freeSizes,
  durationDays,
  halfDayAvailable,
  effectiveRentalType,
  equipmentTotal,
  insuranceTotal,
  grandTotal,
  deposit,
  confirmBooking,
} = useBooking()

const { isAuthenticated } = useAuth()

// The booking modal strictly does not open if the user is not authenticated
const open = computed(() => state.sizePickerOpen && !!selectedItem.value && isAuthenticated.value)

function close() {
  if (state.bookingSubmitting) return

  state.sizePickerOpen = false
}

function selectSize(size) {
  state.selectedSize = size
  state.bookingError = ''
}

async function confirm() {
  await confirmBooking()
}
</script>

<template>
  <Teleport to="body">
    <div v-if="open" class="fixed inset-0 z-50 flex items-end justify-center sm:items-center">
      <button class="absolute inset-0 bg-slateink/50" type="button" aria-label="Close dialog" @click="close" />
      <div
        role="dialog"
        aria-modal="true"
        class="relative z-10 w-full max-w-lg rounded-t-3xl bg-white p-6 shadow-float sm:rounded-3xl"
      >
        <div class="mb-4 flex items-start justify-between gap-4">
          <div>
            <p class="text-xs font-bold uppercase tracking-wider text-alpine-700">Select size & confirm</p>
            <h3 class="mt-1 text-xl font-extrabold text-slateink">{{ selectedItem.name }}</h3>
          </div>
          <button type="button" class="rounded-full p-2 hover:bg-slate-100" aria-label="Close" @click="close">
            <X class="h-5 w-5" />
          </button>
        </div>

        <div class="flex flex-wrap gap-2">
          <button
            v-for="size in selectedItem.sizes"
            :key="size"
            type="button"
            class="min-w-[3.5rem] rounded-xl px-3 py-2 text-sm font-bold ring-1 transition"
            :class="
              !freeSizes.includes(size)
                ? 'cursor-not-allowed bg-slate-50 text-slate-300 line-through ring-slate-100'
                : state.selectedSize === size
                  ? 'bg-slateink text-white ring-slateink'
                  : 'bg-slate-50 text-slate-700 ring-slate-200 hover:bg-white'
            "
            :disabled="state.bookingSubmitting || !freeSizes.includes(size)"
            :title="freeSizes.includes(size) ? '' : 'Already booked for these dates'"
            @click="selectSize(size)"
          >
            {{ size }}
          </button>
        </div>
        <p v-if="selectedItem.rentalPoint" class="mt-3 text-xs text-slate-500">
          Pickup: <span class="font-semibold text-slate-700">{{ selectedItem.rentalPoint.name }}</span>
          <template v-if="selectedItem.rentalPoint.address"> · {{ selectedItem.rentalPoint.address }}</template>
          <template v-if="selectedItem.rentalPoint.openingHours"> · {{ selectedItem.rentalPoint.openingHours }}</template>
        </p>

        <button
          type="button"
          class="mt-4 text-sm font-semibold text-alpine-700 underline-offset-2 hover:underline"
          @click="state.sizingOpen = true"
        >
          Open rider height vs. ski length guide
        </button>

        <fieldset v-if="halfDayAvailable" class="mt-5">
          <legend class="text-sm font-bold text-slateink">Rental time</legend>
          <div class="mt-2 grid grid-cols-2 gap-2">
            <label
              v-for="option in [
                { id: 'full_day', label: 'Full day', price: selectedItem.dailyRate, hint: 'Opening to closing' },
                { id: 'half_day', label: 'Half day', price: selectedItem.halfDayRate, hint: 'From 12:00' },
              ]"
              :key="option.id"
              class="cursor-pointer rounded-2xl border p-3 transition"
              :class="effectiveRentalType === option.id
                ? 'border-alpine-600 bg-alpine-50 ring-4 ring-alpine-100'
                : 'border-slate-200 bg-slate-50 hover:border-slate-300'"
            >
              <input
                v-model="state.rentalType"
                type="radio"
                name="rentalType"
                :value="option.id"
                class="sr-only"
                :disabled="state.bookingSubmitting"
              />
              <span class="flex items-baseline justify-between gap-2">
                <span class="text-sm font-bold text-slateink">{{ option.label }}</span>
                <span class="text-sm font-bold text-alpine-700">€{{ option.price }}</span>
              </span>
              <span class="mt-0.5 block text-xs text-slate-500">{{ option.hint }}</span>
            </label>
          </div>
        </fieldset>

        <label class="mt-5 flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
          <input v-model="state.insurance" type="checkbox" class="mt-1 h-4 w-4 accent-alpine-600" />
          <span>
            <span class="block text-sm font-bold text-slateink">Add damages & theft insurance</span>
            <span class="text-xs text-slate-500">
              +€3 / day · {{ durationDays }} {{ durationDays === 1 ? 'day' : 'days' }} = €{{ durationDays * 3 }}
            </span>
          </span>
        </label>

        <dl class="mt-5 space-y-2 text-sm">
          <div class="flex justify-between text-slate-600">
            <dt>
              Rental ({{
                effectiveRentalType === 'half_day'
                  ? 'half day'
                  : `${durationDays} ${durationDays === 1 ? 'day' : 'days'}`
              }}, MwSt. 20% incl.)
            </dt>
            <dd class="font-semibold">€{{ equipmentTotal }}</dd>
          </div>
          <div class="flex justify-between text-slate-600">
            <dt>Insurance</dt>
            <dd class="font-semibold">€{{ insuranceTotal }}</dd>
          </div>
          <div class="flex justify-between text-slate-600">
            <dt>Refundable deposit</dt>
            <dd class="font-semibold">€{{ deposit }}</dd>
          </div>
          <div class="flex justify-between border-t border-slate-200 pt-2 text-base font-extrabold text-slateink">
            <dt>Due now</dt>
            <dd>€{{ grandTotal }}</dd>
          </div>
        </dl>

        <p
          v-if="state.bookingError"
          class="mt-4 rounded-2xl bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700"
          role="alert"
        >
          {{ state.bookingError }}
        </p>

        <button
          type="button"
          class="mt-6 w-full rounded-2xl bg-alpine-600 py-3 text-sm font-bold text-white hover:bg-alpine-700 disabled:cursor-not-allowed disabled:opacity-60"
          :disabled="state.bookingSubmitting || !state.selectedSize"
          @click="confirm"
        >
          {{ state.bookingSubmitting ? 'Booking...' : `Confirm size ${state.selectedSize} & continue` }}
        </button>
      </div>
    </div>
  </Teleport>
</template>
