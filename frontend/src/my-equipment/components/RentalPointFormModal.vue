<script setup>
import { reactive, watch } from 'vue'

const props = defineProps({
  point: { type: Object, default: null },
  isSaving: { type: Boolean, default: false },
  error: { type: String, default: '' },
})

const emit = defineEmits(['close', 'submit'])

const form = reactive({ name: '', address: '', openingHours: '' })

watch(
  () => props.point,
  (point) => {
    form.name = point?.name ?? ''
    form.address = point?.address ?? ''
    form.openingHours = point?.openingHours ?? ''
  },
  { immediate: true },
)

function submit() {
  emit('submit', {
    name: form.name.trim(),
    address: form.address.trim() || null,
    openingHours: form.openingHours.trim() || null,
  })
}

const fieldClass =
  'w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-cyan-400 focus:bg-white focus:ring-4 focus:ring-cyan-100'
</script>

<template>
  <div
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm"
    @click.self="!isSaving && emit('close')"
  >
    <form class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl" @submit.prevent="submit">
      <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Rental point</p>
      <h2 class="mt-2 text-2xl font-bold text-slate-900">
        {{ point ? 'Edit rental point' : 'Add rental point' }}
      </h2>

      <div class="mt-6 space-y-4">
        <label class="block">
          <span class="mb-2 block text-sm font-medium text-slate-700">Name</span>
          <input
            v-model="form.name"
            type="text"
            required
            maxlength="255"
            placeholder="Innsbruck Central Store"
            :class="fieldClass"
          />
        </label>

        <label class="block">
          <span class="mb-2 block text-sm font-medium text-slate-700">Address</span>
          <input
            v-model="form.address"
            type="text"
            maxlength="255"
            placeholder="Maria-Theresien-Straße 1, Innsbruck"
            :class="fieldClass"
          />
        </label>

        <label class="block">
          <span class="mb-2 block text-sm font-medium text-slate-700">Opening hours</span>
          <input
            v-model="form.openingHours"
            type="text"
            maxlength="255"
            placeholder="08:00–20:00"
            :class="fieldClass"
          />
        </label>
      </div>

      <p v-if="error" class="mt-4 rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert">
        {{ error }}
      </p>

      <div class="mt-6 flex justify-end gap-3">
        <button
          type="button"
          :disabled="isSaving"
          class="rounded-full border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 disabled:opacity-50"
          @click="emit('close')"
        >
          Cancel
        </button>
        <button
          type="submit"
          :disabled="isSaving || !form.name.trim()"
          class="rounded-full bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-cyan-600/20 transition hover:bg-cyan-500 disabled:opacity-50"
        >
          {{ isSaving ? 'Saving…' : point ? 'Save changes' : 'Add rental point' }}
        </button>
      </div>
    </form>
  </div>
</template>
