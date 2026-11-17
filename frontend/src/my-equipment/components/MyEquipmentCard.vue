<script setup>
import { ref } from 'vue'
import { CalendarDays, ChevronDown, MapPin, Pencil, Trash2 } from 'lucide-vue-next'
import { badgeToneClass } from '../equipmentConstants.js'
import EquipmentAvailabilityCalendar from './EquipmentAvailabilityCalendar.vue'

const props = defineProps({
  item: { type: Object, required: true },
})

const emit = defineEmits(['edit', 'delete'])
const optionsMenu = ref(null)
const isOptionsOpen = ref(false)
const isCalendarOpen = ref(false)

function runAction(action) {
  emit(action, props.item)
  optionsMenu.value.open = false
}
</script>

<template>
  <article
    class="relative rounded-3xl border border-slate-200 bg-slate-50 shadow-sm transition hover:-translate-y-1 hover:shadow-md"
    :class="isOptionsOpen ? 'z-30' : 'z-0'"
  >
    <div class="relative h-52 overflow-hidden rounded-t-3xl">
      <img :src="item.image" :alt="item.name" class="h-full w-full object-cover" />
      <div class="absolute left-3 top-3 flex items-center gap-2">
        <span
          :class="[
            'rounded-full px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.14em]',
            badgeToneClass[item.badgeTone] || badgeToneClass.info,
          ]"
        >
          {{ item.badge }}
        </span>
      </div>
      <div class="absolute right-3 top-3 rounded-full bg-white/90 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-700">
        {{ item.category }}
      </div>
    </div>

    <div class="p-4">
      <div class="flex items-start justify-between gap-3">
        <div>
          <p class="text-xs uppercase tracking-[0.16em] text-slate-400">{{ item.categoryLabel }}</p>
          <h3 class="mt-2 text-lg font-bold text-slate-900">{{ item.name }}</h3>
          <p v-if="item.rentalPoint" class="mt-1 flex items-center gap-1 text-xs text-slate-500">
            <MapPin class="h-3.5 w-3.5 shrink-0 text-cyan-600" />
            <span class="truncate">{{ item.rentalPoint.name }}</span>
          </p>
        </div>
        <span
          :class="[
            'rounded-full px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.14em]',
            item.availableNow ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700',
          ]"
        >
          {{ item.availableNow ? 'Available' : 'Booked' }}
        </span>
      </div>

      <div class="mt-4 flex items-center justify-between border-t border-slate-200 pt-3">
        <div>
          <p class="text-xs uppercase tracking-[0.14em] text-slate-400">Daily rate</p>
          <p class="mt-1 text-xl font-bold text-cyan-700">€{{ item.dailyRate }}</p>
          <p v-if="item.halfDayRate != null" class="text-xs text-slate-500">Half day €{{ item.halfDayRate }}</p>
        </div>
        <div class="text-right">
          <p class="text-xs uppercase tracking-[0.14em] text-slate-400">Deposit</p>
          <p class="mt-1 text-base font-semibold text-slate-900">€{{ item.deposit }}</p>
        </div>
      </div>

      <div class="mt-4 flex flex-wrap gap-2">
        <span
          v-for="size in item.sizes.slice(0, 4)"
          :key="`${item.id}-${size}`"
          class="rounded-full border border-slate-200 bg-white px-2 py-1 text-[10px] font-medium text-slate-700"
        >
          {{ size }}
        </span>
      </div>

      <div class="mt-4 flex items-stretch gap-2 border-t border-slate-200 pt-3">
        <button
          type="button"
          class="flex min-w-0 flex-1 items-center justify-center gap-2 rounded-xl bg-cyan-600 px-3 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-cyan-500 active:scale-[0.98]"
          @click="isCalendarOpen = true"
        >
          <CalendarDays class="h-4 w-4 shrink-0" />
          View availability calendar
        </button>
        <details
          ref="optionsMenu"
          class="relative shrink-0"
          @toggle="isOptionsOpen = optionsMenu.open"
        >
          <summary
            class="flex h-full cursor-pointer list-none items-center justify-center gap-1 rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 [&::-webkit-details-marker]:hidden"
          >
            Options
            <ChevronDown class="h-4 w-4" />
          </summary>
          <div class="absolute right-0 top-full z-20 mt-2 w-40 overflow-hidden rounded-xl border border-slate-200 bg-white p-1 shadow-xl">
            <button
              type="button"
              class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm font-medium text-slate-700 hover:bg-slate-100"
              @click="runAction('edit')"
            >
              <Pencil class="h-4 w-4 text-cyan-600" />
              Edit
            </button>
            <button
              type="button"
              class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm font-medium text-rose-600 hover:bg-rose-50"
              @click="runAction('delete')"
            >
              <Trash2 class="h-4 w-4" />
              Delete
            </button>
          </div>
        </details>
      </div>
    </div>

    <EquipmentAvailabilityCalendar
      v-if="isCalendarOpen"
      :item="item"
      @close="isCalendarOpen = false"
    />
  </article>
</template>
