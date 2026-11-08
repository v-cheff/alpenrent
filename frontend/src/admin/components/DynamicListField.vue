<script setup>
import { computed } from 'vue'

const items = defineModel({ type: Array, required: true })

const props = defineProps({
  title: { type: String, required: true },
  hint: { type: String, default: '' },
  addLabel: { type: String, required: true },
  itemLabel: { type: String, required: true },
  inputType: { type: String, default: 'text' },
  maxlength: { type: Number, default: undefined },
  maxItems: { type: Number, default: Infinity },
  layout: { type: String, default: 'stack' },
  placeholder: { type: String, default: '' },
  firstPlaceholder: { type: String, default: '' },
  showCounter: { type: Boolean, default: false },
})

const filledCount = computed(() => items.value.filter((value) => String(value).trim()).length)
const canAdd = computed(() => items.value.length < props.maxItems)

function placeholderFor(index) {
  return index === 0 && props.firstPlaceholder ? props.firstPlaceholder : props.placeholder
}

function add() {
  if (canAdd.value) {
    items.value.push('')
  }
}

function remove(index) {
  items.value.splice(index, 1)
}
</script>

<template>
  <section class="rounded-2xl border border-slate-200 bg-slate-50 p-4 lg:col-span-2">
    <div class="mb-3 flex items-center justify-between gap-3">
      <div>
        <h3 class="text-sm font-semibold text-slate-700">{{ title }}</h3>
        <p v-if="hint" class="mt-1 text-xs text-slate-500">{{ hint }}</p>
      </div>
      <span v-if="showCounter" class="text-xs font-medium text-slate-500">
        {{ filledCount }}/{{ maxItems }}
      </span>
    </div>

    <div v-if="layout === 'chips'" class="flex flex-wrap gap-2">
      <div
        v-for="(_item, index) in items"
        :key="`${itemLabel}-${index}`"
        class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white p-1"
      >
        <input
          v-model="items[index]"
          :type="inputType"
          :maxlength="maxlength"
          :aria-label="`${itemLabel} ${index + 1}`"
          :placeholder="placeholderFor(index)"
          class="w-20 rounded-lg px-2 py-1.5 text-center text-sm text-slate-900 outline-none focus:ring-2 focus:ring-cyan-100"
        />
        <button
          type="button"
          :aria-label="`Remove ${itemLabel.toLowerCase()} ${index + 1}`"
          class="flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-600"
          @click="remove(index)"
        >
          −
        </button>
      </div>
    </div>

    <div v-else class="space-y-2">
      <div
        v-for="(_item, index) in items"
        :key="`${itemLabel}-${index}`"
        class="flex items-center gap-2"
      >
        <input
          v-model="items[index]"
          :type="inputType"
          :maxlength="maxlength"
          :aria-label="`${itemLabel} ${index + 1}`"
          :placeholder="placeholderFor(index)"
          class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-cyan-400 focus:ring-4 focus:ring-cyan-100"
        />
        <button
          type="button"
          :aria-label="`Remove ${itemLabel.toLowerCase()} ${index + 1}`"
          class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-lg text-slate-500 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600"
          @click="remove(index)"
        >
          −
        </button>
      </div>
    </div>

    <button
      v-if="canAdd"
      type="button"
      class="mt-3 inline-flex items-center gap-2 rounded-full border border-cyan-200 bg-white px-3 py-2 text-sm font-medium text-cyan-700 transition hover:bg-cyan-50"
      @click="add"
    >
      <span aria-hidden="true" class="text-base leading-none">+</span>
      {{ addLabel }}
    </button>
  </section>
</template>
