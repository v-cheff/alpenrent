<script setup>
import { onMounted, reactive } from 'vue'
import { CheckCircle2, X } from 'lucide-vue-next'
import { useEquipmentList } from './composables/useEquipmentList.js'
import { useEquipmentForm } from './composables/useEquipmentForm.js'
import EquipmentStats from './components/EquipmentStats.vue'
import EquipmentToolbar from './components/EquipmentToolbar.vue'
import AdminEquipmentCard from './components/AdminEquipmentCard.vue'
import EquipmentFormModal from './components/EquipmentFormModal.vue'
import DeleteConfirmModal from './components/DeleteConfirmModal.vue'

const {
  list,
  filteredItems,
  stats,
  load,
  prepend,
  updateItem,
  deleteEquipmentItem,
} = useEquipmentList()

const toast = reactive({
  message: '',
  timer: null,
})

function notify(message) {
  toast.message = message
  if (toast.timer) clearTimeout(toast.timer)
  toast.timer = setTimeout(() => {
    toast.message = ''
  }, 3500)
}

const {
  editor,
  title,
  submitLabel,
  openCreate,
  openEdit,
  close,
  handleImageUpload,
  handleImageUrlInput,
  submit,
} = useEquipmentForm({
  onCreated: (item) => {
    prepend(item)
    notify('Equipment added successfully.')
  },
  onUpdated: (item) => {
    updateItem(item)
    notify('Equipment updated successfully.')
  },
})

const deleteDialog = reactive({
  isOpen: false,
  item: null,
  isDeleting: false,
  error: '',
})

function requestDelete(item) {
  deleteDialog.item = item
  deleteDialog.error = ''
  deleteDialog.isDeleting = false
  deleteDialog.isOpen = true
}

function closeDeleteDialog() {
  if (deleteDialog.isDeleting) return
  deleteDialog.isOpen = false
  deleteDialog.item = null
  deleteDialog.error = ''
}

async function confirmDelete() {
  if (!deleteDialog.item) return

  deleteDialog.isDeleting = true
  deleteDialog.error = ''

  try {
    const itemName = deleteDialog.item.name
    await deleteEquipmentItem(deleteDialog.item.id)
    notify(`Equipment "${itemName}" deleted successfully.`)
    deleteDialog.isDeleting = false
    closeDeleteDialog()
  } catch (err) {
    deleteDialog.error = err.message || 'Could not delete equipment.'
  } finally {
    deleteDialog.isDeleting = false
  }
}

onMounted(load)
</script>

<template>
  <div class="min-h-screen bg-slate-100 text-slate-900">
    <header class="border-b border-slate-200 bg-white/90 backdrop-blur sticky top-0 z-40">
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

        <div
          v-else-if="filteredItems.length === 0"
          class="mt-8 flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 py-16 text-center"
        >
          <p class="text-base font-semibold text-slate-700">No equipment found</p>
          <p class="mt-1 text-sm text-slate-400">Try changing your filters or add new equipment.</p>
          <button
            type="button"
            class="mt-4 rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold text-white transition hover:bg-slate-800"
            @click="openCreate"
          >
            Add equipment
          </button>
        </div>

        <div v-else class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
          <AdminEquipmentCard
            v-for="item in filteredItems"
            :key="item.id"
            :item="item"
            @edit="openEdit"
            @delete="requestDelete"
          />
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

    <DeleteConfirmModal
      :is-open="deleteDialog.isOpen"
      :item="deleteDialog.item"
      :is-deleting="deleteDialog.isDeleting"
      :error="deleteDialog.error"
      @close="closeDeleteDialog"
      @confirm="confirmDelete"
    />

    <!-- Notification Toast -->
    <transition
      enter-active-class="transform ease-out duration-300 transition"
      enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
      enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="toast.message"
        class="fixed bottom-6 right-6 z-50 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3.5 text-sm font-medium text-emerald-800 shadow-xl"
        role="status"
      >
        <CheckCircle2 class="h-5 w-5 text-emerald-600 flex-shrink-0" />
        <span>{{ toast.message }}</span>
        <button
          type="button"
          class="ml-2 rounded-lg p-1 text-emerald-600 transition hover:bg-emerald-100"
          @click="toast.message = ''"
        >
          <X class="h-4 w-4" />
        </button>
      </div>
    </transition>
  </div>
</template>
