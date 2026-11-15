<script setup>
import { Clock3, MapPin, Pencil, Plus, Store, Trash2 } from 'lucide-vue-next'

const activeId = defineModel('activeId', { default: null })

defineProps({
  points: { type: Array, required: true },
  totalEquipment: { type: Number, default: 0 },
})

const emit = defineEmits(['add', 'edit', 'delete'])

function cardClass(isActive) {
  return isActive
    ? 'border-cyan-400 bg-cyan-50/60 ring-4 ring-cyan-100'
    : 'border-slate-200 bg-white hover:border-cyan-200'
}
</script>

<template>
  <section aria-labelledby="rental-points-heading">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h2 id="rental-points-heading" class="text-lg font-bold text-slate-900">Rental points</h2>
        <p class="text-sm text-slate-500">Shops and pickup spots where renters collect your equipment.</p>
      </div>
      <button
        type="button"
        class="inline-flex items-center gap-1.5 rounded-full border border-cyan-200 bg-white px-4 py-2 text-sm font-semibold text-cyan-700 transition hover:bg-cyan-50"
        @click="emit('add')"
      >
        <Plus class="h-4 w-4" />
        Add rental point
      </button>
    </div>

    <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
      <button
        type="button"
        class="flex flex-col rounded-2xl border p-4 text-left transition"
        :class="cardClass(activeId === null)"
        :aria-pressed="activeId === null"
        @click="activeId = null"
      >
        <span class="flex items-center gap-2 text-sm font-bold text-slate-900">
          <Store class="h-4 w-4 text-cyan-600" />
          All rental points
        </span>
        <span class="mt-2 text-xs text-slate-500">{{ points.length }} points · {{ totalEquipment }} items</span>
      </button>

      <div
        v-for="point in points"
        :key="point.id"
        class="relative flex flex-col rounded-2xl border transition"
        :class="cardClass(activeId === point.id)"
      >
        <button
          type="button"
          class="flex flex-1 flex-col p-4 pr-20 text-left"
          :aria-pressed="activeId === point.id"
          @click="activeId = point.id"
        >
          <span class="truncate text-sm font-bold text-slate-900">{{ point.name }}</span>
          <span v-if="point.address" class="mt-1.5 flex items-start gap-1 text-xs text-slate-500">
            <MapPin class="mt-0.5 h-3.5 w-3.5 shrink-0" />
            <span class="line-clamp-2">{{ point.address }}</span>
          </span>
          <span v-if="point.openingHours" class="mt-1 flex items-center gap-1 text-xs text-slate-500">
            <Clock3 class="h-3.5 w-3.5 shrink-0" />
            {{ point.openingHours }}
          </span>
          <span class="mt-2 text-xs font-semibold text-cyan-700">{{ point.equipmentCount ?? 0 }} items</span>
        </button>

        <div class="absolute right-2 top-2 flex gap-1">
          <button
            type="button"
            class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-cyan-700"
            :aria-label="`Edit ${point.name}`"
            @click="emit('edit', point)"
          >
            <Pencil class="h-4 w-4" />
          </button>
          <button
            type="button"
            class="rounded-lg p-1.5 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600"
            :aria-label="`Delete ${point.name}`"
            @click="emit('delete', point)"
          >
            <Trash2 class="h-4 w-4" />
          </button>
        </div>
      </div>
    </div>
  </section>
</template>
