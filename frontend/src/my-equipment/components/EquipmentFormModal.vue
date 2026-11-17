<script setup>
import {
  badgeToneOptions,
  equipmentCategoryOptions,
  MAX_SPECS,
} from '../equipmentConstants.js'
import DynamicListField from './DynamicListField.vue'
import EquipmentImageField from './EquipmentImageField.vue'

defineProps({
  editor: { type: Object, required: true },
  title: { type: String, required: true },
  submitLabel: { type: String, required: true },
  rentalPoints: { type: Array, required: true },
})

const emit = defineEmits(['close', 'submit', 'upload', 'url-input'])

const fieldClass =
  'w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-cyan-400 focus:bg-white focus:ring-4 focus:ring-cyan-100'
</script>

<template>
  <div
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm"
    @click.self="emit('close')"
  >
    <div class="max-h-[90vh] w-full max-w-5xl overflow-y-auto rounded-3xl bg-white p-6 shadow-2xl">
      <div class="flex items-center justify-between gap-4">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Equipment</p>
          <h2 class="mt-2 text-2xl font-bold text-slate-900">{{ title }}</h2>
        </div>

        <button
          type="button"
          class="rounded-full border border-slate-200 bg-slate-100 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-200"
          @click="emit('close')"
        >
          Close
        </button>
      </div>

      <form class="mt-6 grid gap-5 lg:grid-cols-2" @submit.prevent="emit('submit')">
        <label class="block">
          <span class="mb-2 block text-sm font-medium text-slate-700">Rental point</span>
          <select v-model="editor.form.rentalPointId" :class="fieldClass" required>
            <option :value="null" disabled>Choose a rental point</option>
            <option v-for="point in rentalPoints" :key="point.id" :value="point.id">
              {{ point.name }}
            </option>
          </select>
        </label>

        <label class="block">
          <span class="mb-2 block text-sm font-medium text-slate-700">Category</span>
          <select v-model="editor.form.category" :class="fieldClass">
            <option
              v-for="category in equipmentCategoryOptions"
              :key="category.id"
              :value="category.id"
            >
              {{ category.id }}
            </option>
          </select>
        </label>

        <label class="block lg:col-span-2">
          <span class="mb-2 block text-sm font-medium text-slate-700">Name</span>
          <input
            v-model="editor.form.name"
            type="text"
            placeholder="Rossignol Experience 86 Basalt"
            :class="fieldClass"
          />
        </label>

        <label class="block">
          <span class="mb-2 block text-sm font-medium text-slate-700">Category label</span>
          <input
            v-model="editor.form.categoryLabel"
            type="text"
            placeholder="All-Mountain Ski Set"
            :class="fieldClass"
          />
        </label>

        <label class="block">
          <span class="mb-2 block text-sm font-medium text-slate-700">Badge</span>
          <input
            v-model="editor.form.badge"
            type="text"
            placeholder="Freshly Waxed"
            :class="fieldClass"
          />
        </label>

        <label class="block">
          <span class="mb-2 block text-sm font-medium text-slate-700">Badge tone</span>
          <select v-model="editor.form.badgeTone" :class="fieldClass">
            <option v-for="tone in badgeToneOptions" :key="tone.id" :value="tone.id">
              {{ tone.label }}
            </option>
          </select>
        </label>

        <label class="block">
          <span class="mb-2 block text-sm font-medium text-slate-700">Daily rate</span>
          <input v-model.number="editor.form.dailyRate" type="number" min="0" step="0.01" :class="fieldClass" />
        </label>

        <label class="block">
          <span class="mb-2 block text-sm font-medium text-slate-700">Half-day rate</span>
          <input
            v-model.number="editor.form.halfDayRate"
            type="number"
            min="0"
            step="0.01"
            placeholder="Leave empty if not offered"
            :class="fieldClass"
          />
          <span class="mt-1 block text-xs text-slate-500">Single date, from 12:00. Leave empty to rent by full days only.</span>
        </label>

        <label class="block">
          <span class="mb-2 block text-sm font-medium text-slate-700">Deposit</span>
          <input v-model.number="editor.form.deposit" type="number" min="0" :class="fieldClass" />
        </label>

        <DynamicListField
          v-model="editor.form.specs"
          title="Specs"
          hint="Add up to 3 short details."
          add-label="Add spec"
          item-label="Specification"
          :maxlength="80"
          :max-items="MAX_SPECS"
          first-placeholder="Radius 16 m"
          placeholder="Another short detail"
          show-counter
        />

        <DynamicListField
          v-model="editor.form.sizes"
          title="Sizes"
          hint="Add each available size separately."
          add-label="Add size"
          item-label="Size"
          layout="chips"
          :maxlength="32"
          placeholder="156"
        />

        <DynamicListField
          v-model="editor.form.availableDates"
          title="Available dates"
          hint="Add dates when this equipment is available."
          add-label="Add date"
          item-label="Available date"
          input-type="date"
        />

        <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 lg:col-span-2">
          <input
            v-model="editor.form.availableNow"
            type="checkbox"
            class="h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500"
          />
          Available now
        </label>

        <EquipmentImageField
          v-model:image="editor.form.image"
          v-model:preview="editor.imagePreview"
          @upload="emit('upload', $event)"
          @url-input="emit('url-input')"
        />

        <p
          v-if="editor.formError"
          class="rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700 lg:col-span-2"
          role="alert"
        >
          {{ editor.formError }}
        </p>

        <div class="flex flex-wrap gap-3 lg:col-span-2">
          <button
            type="submit"
            :disabled="editor.isSaving"
            class="rounded-full bg-cyan-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-600/20 transition hover:bg-cyan-500"
          >
            {{ submitLabel }}
          </button>

          <button
            type="button"
            class="rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
            @click="emit('close')"
          >
            Cancel
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
