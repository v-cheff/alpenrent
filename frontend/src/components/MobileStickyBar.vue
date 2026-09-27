<script setup>
import { computed } from 'vue'
import { useBooking } from '../composables/useBooking'

const { state, selectedItem, durationDays, grandTotal, location } = useBooking()

const visible = computed(() => !!selectedItem.value)
</script>

<template>
  <div
    v-if="visible"
    class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 p-3 shadow-[0_-12px_40px_rgba(15,23,42,0.12)] backdrop-blur md:hidden"
  >
    <div class="flex items-center justify-between gap-3">
      <div class="min-w-0">
        <p class="truncate text-sm font-extrabold text-slateink">{{ selectedItem.name }} · {{ state.selectedSize }}</p>
        <p class="truncate text-xs text-slate-500">
          {{ durationDays }} days · {{ location.label }} · €{{ grandTotal }} (VAT incl.)
        </p>
      </div>
      <button
        type="button"
        class="shrink-0 rounded-xl bg-alpine-600 px-4 py-2.5 text-sm font-bold text-white"
        @click="state.sizePickerOpen = true"
      >
        €{{ grandTotal }}
      </button>
    </div>
  </div>
</template>
