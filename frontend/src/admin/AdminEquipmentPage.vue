<script setup>
import { onMounted } from 'vue'
import { useEquipmentList } from './composables/useEquipmentList.js'
import { useEquipmentForm } from './composables/useEquipmentForm.js'
import EquipmentStats from './components/EquipmentStats.vue'
import EquipmentToolbar from './components/EquipmentToolbar.vue'
import AdminEquipmentCard from './components/AdminEquipmentCard.vue'
import EquipmentFormModal from './components/EquipmentFormModal.vue'

const { list, filteredItems, stats, load, prepend } = useEquipmentList()
const {
  editor,
  title,
  submitLabel,
  openCreate,
  close,
  handleImageUpload,
  handleImageUrlInput,
  submit,
} = useEquipmentForm({ onCreated: prepend })

onMounted(load)
</script>

<template>
  <div class="min-h-screen bg-slate-100 text-slate-900">
    <header class="border-b border-slate-200 bg-white/90 backdrop-blur">
      <div class="mx-auto flex max-w-[1600px] items-center justify-between gap-4 px-5 py-5 lg:px-8">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.24em] text-cyan-600">Alpenrent</p>
          <h1 class="mt-1 text-2xl font-bold text-slate-900">Admin equipment dashboard</h1>
        </div>

        <div class="flex items-center gap-3">
          <div class="flex items-center gap-3 rounded-full border border-slate-200 bg-slate-50 px-4 py-2">
            <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
            <span class="text-sm font-medium text-slate-700">System online</span>
          </div>

          <button
            type="button"
            class="rounded-full bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-cyan-600/20 transition hover:bg-cyan-500"
            @click="openCreate"
          >
            Add equipment
          </button>
        </div>
      </div>
    </header>

    <main class="mx-auto max-w-[1600px] px-5 py-8 lg:px-8">
      <EquipmentStats :stats="stats" />

      <section class="mt-8 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <EquipmentToolbar
          v-model:active-tab="list.activeTab"
          v-model:availability-mode="list.availabilityMode"
          v-model:selected-date="list.selectedDate"
        />

        <p v-if="list.isLoading" class="mt-6 text-sm text-slate-500">Loading equipment…</p>
        <p
          v-else-if="list.loadError"
          class="mt-6 rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700"
          role="alert"
        >
          Could not load equipment: {{ list.loadError }}
        </p>

        <div class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
          <AdminEquipmentCard v-for="item in filteredItems" :key="item.id" :item="item" />
        </div>
      </section>
    </main>

    <EquipmentFormModal
      v-if="editor.isOpen"
      :editor="editor"
      :title="title"
      :submit-label="submitLabel"
      @close="close"
      @submit="submit"
      @upload="handleImageUpload"
      @url-input="handleImageUrlInput"
    />
  </div>
</template>
