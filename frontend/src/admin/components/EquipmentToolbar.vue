<script setup>
import { catalogTabs } from '../../data/catalog.js'
import { availabilityModeOptions } from '../equipmentConstants.js'

const activeTab = defineModel('activeTab', { type: String, required: true })
const availabilityMode = defineModel('availabilityMode', { type: String, required: true })
const selectedDate = defineModel('selectedDate', { type: String, required: true })
</script>

<template>
  <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
    <div class="flex flex-wrap gap-2">
      <button
        v-for="tab in catalogTabs"
        :key="tab.id"
        type="button"
        class="rounded-full px-4 py-2 text-sm font-medium transition"
        :class="
          activeTab === tab.id
            ? 'bg-slate-900 text-white shadow-sm'
            : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
        "
        @click="activeTab = tab.id"
      >
        {{ tab.label }}
      </button>
    </div>

    <div class="flex flex-wrap items-center gap-3">
      <label class="flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">
        <span>Availability</span>
        <select v-model="availabilityMode" class="bg-transparent outline-none">
          <option v-for="mode in availabilityModeOptions" :key="mode.id" :value="mode.id">
            {{ mode.label }}
          </option>
        </select>
      </label>

      <label
        v-if="availabilityMode === 'selected'"
        class="flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700"
      >
        <span>Period</span>
        <input v-model="selectedDate" type="date" class="bg-transparent outline-none" />
      </label>
    </div>
  </div>
</template>
