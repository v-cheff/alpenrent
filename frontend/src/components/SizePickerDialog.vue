<script setup>
import { computed } from 'vue'
import { X } from 'lucide-vue-next'
import { useBooking } from '../composables/useBooking'

const { state, selectedItem, durationDays, equipmentTotal, insuranceTotal, grandTotal, deposit } = useBooking()

const open = computed(() => state.sizePickerOpen && !!selectedItem.value)

function close() {
  state.sizePickerOpen = false
}

function confirm() {
  state.sizePickerOpen = false
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
              state.selectedSize === size
                ? 'bg-slateink text-white ring-slateink'
                : 'bg-slate-50 text-slate-700 ring-slate-200 hover:bg-white'
            "
            @click="state.selectedSize = size"
          >
            {{ size }}
          </button>
        </div>

        <button
          type="button"
          class="mt-4 text-sm font-semibold text-alpine-700 underline-offset-2 hover:underline"
          @click="state.sizingOpen = true"
        >
          Open rider height vs. ski length guide
        </button>

        <label class="mt-5 flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
          <input v-model="state.insurance" type="checkbox" class="mt-1 h-4 w-4 accent-alpine-600" />
          <span>
            <span class="block text-sm font-bold text-slateink">Add damages & theft insurance</span>
            <span class="text-xs text-slate-500">+€3 / day · {{ durationDays }} days = €{{ durationDays * 3 }}</span>
          </span>
        </label>

        <dl class="mt-5 space-y-2 text-sm">
          <div class="flex justify-between text-slate-600">
            <dt>Rental ({{ durationDays }} days, MwSt. 20% incl.)</dt>
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

        <button
          type="button"
          class="mt-6 w-full rounded-2xl bg-alpine-600 py-3 text-sm font-bold text-white hover:bg-alpine-700"
          @click="confirm"
        >
          Confirm size {{ state.selectedSize }} & continue
        </button>
      </div>
    </div>
  </Teleport>
</template>
