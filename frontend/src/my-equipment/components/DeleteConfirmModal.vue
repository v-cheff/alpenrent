<script setup>
import { AlertTriangle, Trash2 } from 'lucide-vue-next'

defineProps({
  isOpen: { type: Boolean, default: false },
  title: { type: String, default: 'Delete equipment' },
  itemName: { type: String, default: '' },
  description: {
    type: String,
    default: 'This action will permanently remove the equipment from the database and delete any stored image.',
  },
  confirmLabel: { type: String, default: 'Delete equipment' },
  isDeleting: { type: Boolean, default: false },
  error: { type: String, default: '' },
})

const emit = defineEmits(['close', 'confirm'])
</script>

<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm"
    @click.self="!isDeleting && emit('close')"
  >
    <div class="w-full max-w-md overflow-hidden rounded-3xl bg-white p-6 shadow-2xl transition-all">
      <div class="flex items-start gap-4">
        <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
          <AlertTriangle class="h-6 w-6" />
        </div>

        <div class="flex-1">
          <h3 class="text-lg font-bold text-slate-900">{{ title }}</h3>
          <p class="mt-1 text-sm text-slate-500">
            Are you sure you want to delete <span class="font-semibold text-slate-800">"{{ itemName }}"</span>?
          </p>
          <p class="mt-2 text-xs text-slate-400">{{ description }}</p>
        </div>
      </div>

      <div v-if="error" class="mt-4 rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert">
        {{ error }}
      </div>

      <div class="mt-6 flex items-center justify-end gap-3">
        <button
          type="button"
          :disabled="isDeleting"
          class="rounded-full border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 disabled:opacity-50"
          @click="emit('close')"
        >
          Cancel
        </button>

        <button
          type="button"
          :disabled="isDeleting"
          class="flex items-center gap-2 rounded-full bg-rose-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rose-600/20 transition hover:bg-rose-500 disabled:opacity-50"
          @click="emit('confirm')"
        >
          <Trash2 v-if="!isDeleting" class="h-4 w-4" />
          <svg
            v-else
            class="h-4 w-4 animate-spin text-white"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
          >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path
              class="opacity-75"
              fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            ></path>
          </svg>
          {{ isDeleting ? 'Deleting…' : confirmLabel }}
        </button>
      </div>
    </div>
  </div>
</template>
