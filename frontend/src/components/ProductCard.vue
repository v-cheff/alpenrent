<script setup>
import { computed } from 'vue'
import { MapPin, ShieldCheck } from 'lucide-vue-next'
import { useBooking } from '../composables/useBooking'

const props = defineProps({
  item: { type: Object, required: true },
})

const { selectItem } = useBooking()

// `availableSizes` comes with the catalog when dates are selected.
const freeSizes = computed(() =>
  Array.isArray(props.item.availableSizes) ? props.item.availableSizes : props.item.sizes ?? [],
)
const isBookedOut = computed(() => freeSizes.value.length === 0)

const badgeClass = {
  new: 'bg-sky-600 text-white',
  waxed: 'bg-emerald-600 text-white',
  info: 'bg-slateink text-white',
  kids: 'bg-amber-500 text-white',
}
</script>

<template>
  <article class="group flex flex-col overflow-hidden rounded-3xl bg-white shadow-card ring-1 ring-slate-200/80">
    <div class="relative aspect-[4/3] overflow-hidden bg-slate-200">
      <img
        :src="item.image"
        :alt="item.name"
        class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
      />
      <span
        class="absolute left-3 top-3 rounded-full px-2.5 py-1 text-[11px] font-bold shadow-sm"
        :class="badgeClass[item.badgeTone] || badgeClass.info"
      >
        {{ item.badge }}
      </span>
    </div>

    <div class="flex flex-1 flex-col p-5">
      <p class="text-xs font-semibold uppercase tracking-wider text-alpine-700">{{ item.categoryLabel }}</p>
      <h3 class="mt-1 text-lg font-extrabold tracking-tight text-slateink">{{ item.name }}</h3>
      <p v-if="item.rentalPoint" class="mt-1 flex items-center gap-1 text-xs font-medium text-slate-500">
        <MapPin class="h-3.5 w-3.5 shrink-0 text-alpine-600" />
        <span class="truncate">{{ item.rentalPoint.name }}</span>
      </p>
      <ul class="mt-3 space-y-1 text-sm text-slate-600">
        <li v-for="spec in item.specs" :key="spec" class="flex items-center gap-2">
          <span class="h-1 w-1 rounded-full bg-slate-400" />
          {{ spec }}
        </li>
      </ul>

      <div class="mt-4 flex items-end justify-between gap-3">
        <div>
          <p class="text-xl font-extrabold text-slateink">
            €{{ item.dailyRate }}
            <span class="text-sm font-semibold text-slate-500">/ day</span>
          </p>
          <p v-if="item.halfDayRate != null" class="text-xs font-medium text-slate-500">
            Half day €{{ item.halfDayRate }}
          </p>
          <p class="text-xs font-medium text-slate-500">+ €{{ item.deposit }} deposit</p>
        </div>
        <p class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-500">
          <ShieldCheck class="h-3.5 w-3.5 text-emerald-600" />
          MwSt. 20% incl.
        </p>
      </div>

      <p
        v-if="!isBookedOut && freeSizes.length < (item.sizes?.length ?? 0)"
        class="mt-4 text-xs font-medium text-amber-700"
      >
        Free for your dates: {{ freeSizes.join(', ') }}
      </p>

      <button
        type="button"
        class="mt-5 w-full rounded-2xl px-4 py-3 text-sm font-bold transition"
        :class="isBookedOut
          ? 'cursor-not-allowed bg-slate-100 text-slate-400'
          : 'bg-slateink text-white hover:bg-slate-800'"
        :disabled="isBookedOut"
        @click="selectItem(item.id, item)"
      >
        {{ isBookedOut ? 'Booked out for these dates' : 'Select Size & Rent' }}
      </button>
    </div>
  </article>
</template>
