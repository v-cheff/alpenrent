<script setup>
import { computed, reactive, ref } from 'vue'
import { CheckCircle2, Store, X } from 'lucide-vue-next'
import AccountLayout from '../components/AccountLayout.vue'
import { useEquipmentList } from './composables/useEquipmentList.js'
import { useEquipmentForm } from './composables/useEquipmentForm.js'
import { useRentalPoints } from './composables/useRentalPoints.js'
import { useIncomingBookings } from './composables/useIncomingBookings.js'
import EquipmentStats from './components/EquipmentStats.vue'
import EquipmentToolbar from './components/EquipmentToolbar.vue'
import MyEquipmentCard from './components/MyEquipmentCard.vue'
import EquipmentFormModal from './components/EquipmentFormModal.vue'
import DeleteConfirmModal from './components/DeleteConfirmModal.vue'
import RentalPointsBar from './components/RentalPointsBar.vue'
import RentalPointFormModal from './components/RentalPointFormModal.vue'
import IncomingBookingsList from './components/IncomingBookingsList.vue'

const activeSection = ref('equipment') // 'equipment' | 'bookings'

const {
  list,
  filteredItems,
  stats: equipmentStats,
  load: loadEquipment,
  prepend,
  updateItem,
  deleteEquipmentItem,
} = useEquipmentList()

const { points, load: loadPoints, save: savePoint, remove: removePoint, adjustEquipmentCount } =
  useRentalPoints()

const { bookings, openCount, load: loadBookings } = useIncomingBookings()

const hasPoints = computed(() => points.items.length > 0)
const isLoadingData = computed(() => points.isLoading || list.isLoading)
const stats = computed(() => ({ ...equipmentStats.value, openBookings: openCount.value }))

function loadAll() {
  return Promise.all([loadPoints(), loadEquipment(), loadBookings()])
}

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

// --- Equipment ---------------------------------------------------------------

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
    adjustEquipmentCount(item.rentalPointId, 1)
    notify('Equipment added successfully.')
  },
  onUpdated: (item) => {
    const previous = list.items.find((existing) => existing.id === item.id)
    if (previous && previous.rentalPointId !== item.rentalPointId) {
      adjustEquipmentCount(previous.rentalPointId, -1)
      adjustEquipmentCount(item.rentalPointId, 1)
    }
    updateItem(item)
    notify('Equipment updated successfully.')
  },
})

function addEquipment() {
  if (!hasPoints.value) {
    openPointForm()
    notify('Create a rental point first — equipment is always listed at a rental point.')
    return
  }

  openCreate(list.activeRentalPointId ?? (points.items.length === 1 ? points.items[0].id : null))
}

// --- Rental points -----------------------------------------------------------

const pointForm = reactive({
  isOpen: false,
  point: null,
  isSaving: false,
  error: '',
})

function openPointForm(point = null) {
  pointForm.point = point
  pointForm.error = ''
  pointForm.isOpen = true
}

function closePointForm() {
  if (pointForm.isSaving) return
  pointForm.isOpen = false
  pointForm.point = null
}

async function submitPointForm(data) {
  pointForm.isSaving = true
  pointForm.error = ''

  try {
    const isEdit = Boolean(pointForm.point)
    const saved = await savePoint(pointForm.point?.id, data)

    // Keep the point name shown on equipment cards in sync.
    list.items
      .filter((item) => item.rentalPointId === saved.id)
      .forEach((item) => {
        item.rentalPoint = { ...saved }
      })

    pointForm.isSaving = false
    closePointForm()
    notify(isEdit ? 'Rental point updated.' : `Rental point "${saved.name}" added.`)
  } catch (error) {
    pointForm.error = error.message || 'Could not save the rental point.'
  } finally {
    pointForm.isSaving = false
  }
}

// --- Deletion (equipment or rental point) ------------------------------------

const deleteDialog = reactive({
  isOpen: false,
  kind: 'equipment', // 'equipment' | 'point'
  item: null,
  isDeleting: false,
  error: '',
})

const deleteCopy = computed(() =>
  deleteDialog.kind === 'point'
    ? {
        title: 'Delete rental point',
        description: 'Renters will no longer see this rental point. Only empty rental points can be deleted.',
        confirmLabel: 'Delete rental point',
      }
    : {
        title: 'Delete equipment',
        description:
          'This action will permanently remove the equipment, its bookings and any stored image.',
        confirmLabel: 'Delete equipment',
      },
)

function requestDelete(kind, item) {
  deleteDialog.kind = kind
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
  const { item, kind } = deleteDialog
  if (!item) return

  deleteDialog.isDeleting = true
  deleteDialog.error = ''

  try {
    if (kind === 'point') {
      await removePoint(item.id)
      if (list.activeRentalPointId === item.id) list.activeRentalPointId = null
      notify(`Rental point "${item.name}" deleted.`)
    } else {
      await deleteEquipmentItem(item.id)
      adjustEquipmentCount(item.rentalPointId, -1)
      // Bookings for the deleted equipment are removed on the server as well.
      bookings.items = bookings.items.filter((booking) => booking.equipmentId !== item.id)
      notify(`Equipment "${item.name}" deleted successfully.`)
    }
    deleteDialog.isDeleting = false
    closeDeleteDialog()
  } catch (err) {
    deleteDialog.error = err.message || 'Could not delete.'
  } finally {
    deleteDialog.isDeleting = false
  }
}

</script>

<template>
  <AccountLayout
    title="My rental points"
    role="owner"
    :icon="Store"
    sign-in-text="Sign in with an owner account to manage your rental points, list equipment and track bookings from renters."
    @ready="loadAll"
  >
    <template #actions>
      <button
        type="button"
        class="rounded-full bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-cyan-600/20 transition hover:bg-cyan-500"
        @click="addEquipment"
      >
        Add equipment
      </button>
    </template>

    <p v-if="isLoadingData" class="text-sm text-slate-500">Loading your rental points…</p>
    <p
      v-else-if="points.loadError || list.loadError"
      class="rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700"
      role="alert"
    >
      Could not load your data: {{ points.loadError || list.loadError }}
    </p>

    <!-- Onboarding: no rental points yet -->
    <section
      v-else-if="!hasPoints"
      class="mx-auto mt-6 max-w-xl rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center"
    >
      <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-700">
        <Store class="h-7 w-7" />
      </span>
      <h2 class="mt-4 text-xl font-bold text-slate-900">Create your first rental point</h2>
      <p class="mt-2 text-sm text-slate-500">
        A rental point is a shop or pickup spot. Add one or more, then list the equipment available at each.
      </p>
      <button
        type="button"
        class="mt-6 rounded-full bg-cyan-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-600/20 transition hover:bg-cyan-500"
        @click="openPointForm()"
      >
        Add rental point
      </button>
    </section>

    <template v-else>
      <RentalPointsBar
        v-model:active-id="list.activeRentalPointId"
        :points="points.items"
        :total-equipment="list.items.length"
        @add="openPointForm()"
        @edit="openPointForm"
        @delete="requestDelete('point', $event)"
      />

      <div class="mt-8">
        <EquipmentStats :stats="stats" />
      </div>

      <section class="mt-8 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-5 flex gap-1 rounded-xl bg-slate-100 p-1 sm:w-fit" role="tablist" aria-label="Section">
          <button
            type="button"
            role="tab"
            :aria-selected="activeSection === 'equipment'"
            class="flex-1 rounded-lg px-4 py-2 text-sm font-semibold transition"
            :class="activeSection === 'equipment' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
            @click="activeSection = 'equipment'"
          >
            Equipment
          </button>
          <button
            type="button"
            role="tab"
            :aria-selected="activeSection === 'bookings'"
            class="flex-1 rounded-lg px-4 py-2 text-sm font-semibold transition"
            :class="activeSection === 'bookings' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
            @click="activeSection = 'bookings'"
          >
            Incoming bookings
            <span
              v-if="openCount"
              class="ml-1 rounded-full bg-violet-100 px-1.5 py-0.5 text-[11px] font-bold text-violet-700"
            >
              {{ openCount }}
            </span>
          </button>
        </div>

        <template v-if="activeSection === 'equipment'">
          <EquipmentToolbar
            v-model:active-tab="list.activeTab"
            v-model:availability-mode="list.availabilityMode"
            v-model:selected-date="list.selectedDate"
          />

          <div
            v-if="filteredItems.length === 0"
            class="mt-8 flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 py-16 text-center"
          >
            <p class="text-base font-semibold text-slate-700">No equipment found</p>
            <p class="mt-1 text-sm text-slate-400">Try changing your filters or add new equipment.</p>
            <button
              type="button"
              class="mt-4 rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold text-white transition hover:bg-slate-800"
              @click="addEquipment"
            >
              Add equipment
            </button>
          </div>

          <div v-else class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
            <MyEquipmentCard
              v-for="item in filteredItems"
              :key="item.id"
              :item="item"
              @edit="openEdit"
              @delete="requestDelete('equipment', $event)"
            />
          </div>
        </template>

        <template v-else>
          <p v-if="bookings.isLoading" class="text-sm text-slate-500">Loading bookings…</p>
          <p
            v-else-if="bookings.loadError"
            class="rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700"
            role="alert"
          >
            Could not load bookings: {{ bookings.loadError }}
          </p>
          <IncomingBookingsList
            v-else
            :bookings="bookings.items"
            :active-rental-point-id="list.activeRentalPointId"
          />
        </template>
      </section>
    </template>

    <EquipmentFormModal
      v-if="editor.isOpen"
      :editor="editor"
      :title="title"
      :submit-label="submitLabel"
      :rental-points="points.items"
      @close="close"
      @submit="submit"
      @upload="handleImageUpload"
      @url-input="handleImageUrlInput"
    />

    <RentalPointFormModal
      v-if="pointForm.isOpen"
      :point="pointForm.point"
      :is-saving="pointForm.isSaving"
      :error="pointForm.error"
      @close="closePointForm"
      @submit="submitPointForm"
    />

    <DeleteConfirmModal
      :is-open="deleteDialog.isOpen && Boolean(deleteDialog.item)"
      :title="deleteCopy.title"
      :item-name="deleteDialog.item?.name ?? ''"
      :description="deleteCopy.description"
      :confirm-label="deleteCopy.confirmLabel"
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
        <CheckCircle2 class="h-5 w-5 flex-shrink-0 text-emerald-600" />
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
  </AccountLayout>
</template>
