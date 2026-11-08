<script setup>
import { badgeToneClass } from '../equipmentConstants.js'

defineProps({
  item: { type: Object, required: true },
})

defineEmits(['edit', 'delete'])
</script>

<template>
  <article class="overflow-hidden rounded-3xl border border-slate-200 bg-slate-50 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
    <div class="relative h-52 overflow-hidden">
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

      <div v-if="$slots.actions" class="mt-4 flex flex-wrap gap-2">
        <slot name="actions" />
      </div>
    </div>
  </article>
</template>
