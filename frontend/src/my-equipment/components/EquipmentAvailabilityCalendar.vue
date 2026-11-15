<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import {
  CalendarDays,
  ChevronLeft,
  ChevronRight,
  Clock3,
  X,
} from 'lucide-vue-next'

const props = defineProps({
  item: { type: Object, required: true },
})

const emit = defineEmits(['close'])

const views = [
  { id: 'month', label: 'Month' },
  { id: 'week', label: 'Week' },
  { id: 'day', label: 'Day' },
]
const weekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
const hours = Array.from({ length: 12 }, (_, index) => index + 8)
const view = ref('month')
const anchorDate = ref(startOfDay(new Date()))
const selectedDate = ref(startOfDay(new Date()))
const slotOverrides = reactive({})
const dialog = ref(null)
let previousFocus = null

function startOfDay(date) {
  const result = new Date(date)
  result.setHours(0, 0, 0, 0)
  return result
}

function addDays(date, amount) {
  const result = new Date(date)
  result.setDate(result.getDate() + amount)
  return result
}

function dateKey(date) {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

function startOfWeek(date) {
  const result = startOfDay(date)
  const mondayOffset = (result.getDay() + 6) % 7
  result.setDate(result.getDate() - mondayOffset)
  return result
}

function formatDate(date, options) {
  return new Intl.DateTimeFormat('en-GB', options).format(date)
}

const monthLabel = computed(() => formatDate(anchorDate.value, { month: 'long', year: 'numeric' }))
const selectedDateLabel = computed(() =>
  formatDate(selectedDate.value, { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }),
)
const periodLabel = computed(() => {
  if (view.value === 'month') return monthLabel.value
  if (view.value === 'day') return selectedDateLabel.value

  const firstDay = startOfWeek(anchorDate.value)
  const lastDay = addDays(firstDay, 6)
  if (firstDay.getMonth() === lastDay.getMonth()) {
    return `${formatDate(firstDay, { day: 'numeric' })}–${formatDate(lastDay, { day: 'numeric', month: 'long', year: 'numeric' })}`
  }
  return `${formatDate(firstDay, { day: 'numeric', month: 'short' })} – ${formatDate(lastDay, { day: 'numeric', month: 'short', year: 'numeric' })}`
})

function dateStatus(date) {
  const key = dateKey(date)
  if ((props.item.availableDates ?? []).includes(key)) return 'available'
  if (key === dateKey(new Date())) return props.item.availableNow ? 'available' : 'unavailable'
  return 'unknown'
}

const monthDays = computed(() => {
  const firstOfMonth = new Date(anchorDate.value.getFullYear(), anchorDate.value.getMonth(), 1)
  const firstCell = startOfWeek(firstOfMonth)
  return Array.from({ length: 42 }, (_, index) => {
    const date = addDays(firstCell, index)
    return {
      date,
      key: dateKey(date),
      inMonth: date.getMonth() === anchorDate.value.getMonth(),
      status: dateStatus(date),
    }
  })
})

const weekDays = computed(() => {
  const firstDay = startOfWeek(anchorDate.value)
  return Array.from({ length: 7 }, (_, index) => {
    const date = addDays(firstDay, index)
    return { date, key: dateKey(date), status: dateStatus(date) }
  })
})

function slotKey(hour) {
  return `${props.item.id}:${dateKey(selectedDate.value)}:${hour}`
}

function defaultSlotStatus(hour) {
  if (dateStatus(selectedDate.value) === 'unavailable') return 'unavailable'

  const seed = [...`${props.item.id}:${dateKey(selectedDate.value)}:${hour}`]
    .reduce((total, character) => total + character.charCodeAt(0), 0)
  return seed % 5 === 0 ? 'reserved' : 'available'
}

function slotStatus(hour) {
  return slotOverrides[slotKey(hour)] ?? defaultSlotStatus(hour)
}

function toggleSlot(hour) {
  const currentStatus = slotStatus(hour)
  slotOverrides[slotKey(hour)] = currentStatus === 'available' ? 'reserved' : 'available'
}

function selectDay(date) {
  selectedDate.value = startOfDay(date)
  anchorDate.value = startOfDay(date)
  view.value = 'day'
}

function selectView(nextView) {
  view.value = nextView
  if (nextView === 'day') selectedDate.value = startOfDay(anchorDate.value)
}

function movePeriod(direction) {
  const next = new Date(anchorDate.value)
  if (view.value === 'month') {
    next.setDate(1)
    next.setMonth(next.getMonth() + direction)
  } else {
    next.setDate(next.getDate() + (view.value === 'week' ? 7 : 1) * direction)
  }
  anchorDate.value = next
  if (view.value === 'day') selectedDate.value = startOfDay(next)
}

function goToToday() {
  const today = startOfDay(new Date())
  anchorDate.value = today
  selectedDate.value = today
}

function handleKeydown(event) {
  if (event.key === 'Escape') {
    emit('close')
    return
  }

  if (event.key !== 'Tab' || !dialog.value) return

  const focusable = [...dialog.value.querySelectorAll(
    'button:not(:disabled), summary, [href], input:not(:disabled), select:not(:disabled), textarea:not(:disabled), [tabindex]:not([tabindex="-1"])',
  )].filter((element) => element.getClientRects().length > 0)
  if (focusable.length === 0) {
    event.preventDefault()
    dialog.value.focus()
    return
  }

  const first = focusable[0]
  const last = focusable[focusable.length - 1]
  if (event.shiftKey && document.activeElement === first) {
    event.preventDefault()
    last.focus()
  } else if (!event.shiftKey && document.activeElement === last) {
    event.preventDefault()
    first.focus()
  }
}

onMounted(() => {
  previousFocus = document.activeElement
  dialog.value?.focus()
  document.addEventListener('keydown', handleKeydown)
})

onBeforeUnmount(() => {
  document.removeEventListener('keydown', handleKeydown)
  previousFocus?.focus?.()
})
</script>

<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/60 p-2 backdrop-blur-sm sm:p-5">
      <section
        ref="dialog"
        role="dialog"
        aria-modal="true"
        :aria-label="`Availability calendar for ${item.name}`"
        tabindex="-1"
        class="flex max-h-[96vh] w-full max-w-[1500px] flex-col overflow-hidden rounded-2xl bg-white shadow-2xl sm:rounded-3xl"
      >
        <header class="flex items-start justify-between gap-4 border-b border-slate-200 px-4 py-4 sm:px-7 sm:py-5">
          <div class="flex min-w-0 items-start gap-3">
            <span class="hidden h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-700 sm:flex">
              <CalendarDays class="h-5 w-5" />
            </span>
            <div class="min-w-0">
              <p class="text-xs font-semibold uppercase tracking-[0.16em] text-cyan-700">Equipment availability</p>
              <h2 class="mt-1 truncate text-lg font-bold text-slate-900 sm:text-2xl">{{ item.name }}</h2>
            </div>
          </div>
          <button
            type="button"
            class="rounded-xl p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-900"
            aria-label="Close calendar"
            @click="emit('close')"
          >
            <X class="h-5 w-5" />
          </button>
        </header>

        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-4 py-3 sm:px-7">
          <div class="flex items-center gap-2">
            <button
              type="button"
              class="rounded-lg border border-slate-200 p-2 text-slate-700 hover:bg-slate-50"
              aria-label="Previous period"
              @click="movePeriod(-1)"
            >
              <ChevronLeft class="h-4 w-4" />
            </button>
            <button
              type="button"
              class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"
              @click="goToToday"
            >
              Today
            </button>
            <button
              type="button"
              class="rounded-lg border border-slate-200 p-2 text-slate-700 hover:bg-slate-50"
              aria-label="Next period"
              @click="movePeriod(1)"
            >
              <ChevronRight class="h-4 w-4" />
            </button>
            <h3 class="ml-1 text-sm font-bold text-slate-900 sm:ml-3 sm:text-lg">{{ periodLabel }}</h3>
          </div>

          <div class="flex rounded-xl bg-slate-100 p-1" role="tablist" aria-label="Calendar view">
            <button
              v-for="option in views"
              :key="option.id"
              type="button"
              role="tab"
              :aria-selected="view === option.id"
              class="rounded-lg px-3 py-1.5 text-xs font-semibold transition sm:px-4"
              :class="view === option.id ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
              @click="selectView(option.id)"
            >
              {{ option.label }}
            </button>
          </div>
        </div>

        <div class="min-h-0 flex-1 overflow-auto p-3 sm:p-6">
          <div class="mb-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-slate-600">
            <span class="inline-flex items-center gap-2">
              <span class="h-2.5 w-2.5 rounded-full bg-emerald-500" />
              Available date
            </span>
            <span class="inline-flex items-center gap-2">
              <span class="h-2.5 w-2.5 rounded-full bg-rose-500" />
              Unavailable today
            </span>
            <span class="inline-flex items-center gap-2">
              <span class="h-2.5 w-2.5 rounded-full bg-slate-300" />
              No date data
            </span>
          </div>

          <div
            v-if="view !== 'day'"
            class="mb-4 flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs leading-relaxed text-amber-900"
          >
            <Clock3 class="mt-0.5 h-4 w-4 shrink-0" />
            <p>Date availability uses the equipment data. Open a date for the hourly demo schedule; hourly changes are local only and are not saved.</p>
          </div>

          <div v-if="view === 'month'" class="min-w-[700px]">
            <div class="grid grid-cols-7 border-b border-slate-200 pb-2">
              <p v-for="day in weekdays" :key="day" class="px-2 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">
                {{ day }}
              </p>
            </div>
            <div class="grid grid-cols-7">
              <button
                v-for="day in monthDays"
                :key="day.key"
                type="button"
                class="group min-h-24 border-b border-r border-slate-100 p-2 text-left transition hover:bg-cyan-50/60 sm:min-h-28 sm:p-3"
                :class="!day.inMonth && 'bg-slate-50/70 text-slate-300'"
                :aria-label="`${formatDate(day.date, { day: 'numeric', month: 'long', year: 'numeric' })}: ${day.status}. Open hourly schedule`"
                @click="selectDay(day.date)"
              >
                <span
                  class="inline-flex h-7 w-7 items-center justify-center rounded-full text-sm font-semibold"
                  :class="day.key === dateKey(new Date()) ? 'bg-cyan-600 text-white' : day.inMonth ? 'text-slate-800 group-hover:text-cyan-800' : 'text-slate-400'"
                >
                  {{ day.date.getDate() }}
                </span>
                <span class="mt-2 flex items-center gap-1.5 text-[10px] font-medium sm:text-xs">
                  <span
                    class="h-2 w-2 shrink-0 rounded-full"
                    :class="{
                      'bg-emerald-500': day.status === 'available',
                      'bg-rose-500': day.status === 'unavailable',
                      'bg-slate-300': day.status === 'unknown',
                    }"
                  />
                  <span :class="day.status === 'unknown' ? 'text-slate-400' : 'text-slate-600'">
                    {{ day.status === 'available' ? 'Available' : day.status === 'unavailable' ? 'Unavailable' : 'No data' }}
                  </span>
                </span>
              </button>
            </div>
          </div>

          <div v-else-if="view === 'week'" class="grid min-w-[800px] grid-cols-7 gap-3">
            <button
              v-for="day in weekDays"
              :key="day.key"
              type="button"
              class="min-h-52 rounded-2xl border p-3 text-left transition hover:-translate-y-0.5 hover:border-cyan-300 hover:shadow-md"
              :class="day.key === dateKey(selectedDate) ? 'border-cyan-400 bg-cyan-50/50' : 'border-slate-200 bg-white'"
              @click="selectDay(day.date)"
            >
              <span class="block text-xs font-semibold uppercase tracking-wide text-slate-500">
                {{ formatDate(day.date, { weekday: 'short' }) }}
              </span>
              <span class="mt-1 block text-2xl font-bold text-slate-900">
                {{ formatDate(day.date, { day: 'numeric' }) }}
              </span>
              <span class="mt-4 inline-flex items-center gap-1.5 text-xs font-medium text-slate-600">
                <span
                  class="h-2.5 w-2.5 rounded-full"
                  :class="{
                    'bg-emerald-500': day.status === 'available',
                    'bg-rose-500': day.status === 'unavailable',
                    'bg-slate-300': day.status === 'unknown',
                  }"
                />
                {{ day.status === 'available' ? 'Available' : day.status === 'unavailable' ? 'Unavailable' : 'No date data' }}
              </span>
              <span class="mt-5 block text-xs font-semibold text-cyan-700">Open day →</span>
            </button>
          </div>

          <div v-else class="mx-auto max-w-5xl">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
              <div>
                <h3 class="text-lg font-bold text-slate-900">{{ selectedDateLabel }}</h3>
                <p class="mt-1 text-sm text-slate-500">Hourly availability · 08:00–20:00</p>
              </div>
              <span
                class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold"
                :class="{
                  'bg-emerald-50 text-emerald-700': dateStatus(selectedDate) === 'available',
                  'bg-rose-50 text-rose-700': dateStatus(selectedDate) === 'unavailable',
                  'bg-slate-100 text-slate-600': dateStatus(selectedDate) === 'unknown',
                }"
              >
                <span
                  class="h-2 w-2 rounded-full"
                  :class="{
                    'bg-emerald-500': dateStatus(selectedDate) === 'available',
                    'bg-rose-500': dateStatus(selectedDate) === 'unavailable',
                    'bg-slate-400': dateStatus(selectedDate) === 'unknown',
                  }"
                />
                {{ dateStatus(selectedDate) === 'available' ? 'Available by date data' : dateStatus(selectedDate) === 'unavailable' ? 'Unavailable by date data' : 'No date data' }}
              </span>
            </div>

            <div class="mb-4 flex flex-wrap items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs leading-relaxed text-amber-900">
              <Clock3 class="mt-0.5 h-4 w-4 shrink-0" />
              <p>Demo only: hourly slots are sample data. Click a slot to toggle its status; changes are not sent to the server or saved.</p>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200">
              <div class="hidden grid-cols-[120px_1fr_150px] bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 sm:grid">
                <span>Time</span>
                <span>Slot</span>
                <span>Status</span>
              </div>
              <div class="divide-y divide-slate-100">
                <button
                  v-for="hour in hours"
                  :key="hour"
                  type="button"
                  class="grid w-full grid-cols-1 items-center gap-2 px-4 py-3 text-left transition hover:bg-slate-50 sm:grid-cols-[120px_1fr_150px] sm:px-5"
                  @click="toggleSlot(hour)"
                >
                  <span class="text-sm font-bold tabular-nums text-slate-800">
                    {{ String(hour).padStart(2, '0') }}:00–{{ String(hour + 1).padStart(2, '0') }}:00
                  </span>
                  <span class="h-3 overflow-hidden rounded-full bg-slate-100">
                    <span
                      class="block h-full rounded-full transition-all"
                      :class="slotStatus(hour) === 'available' ? 'w-full bg-emerald-400' : 'w-full bg-rose-400'"
                    />
                  </span>
                  <span
                    class="inline-flex w-fit items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold"
                    :class="slotStatus(hour) === 'available' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'"
                  >
                    <span class="h-1.5 w-1.5 rounded-full" :class="slotStatus(hour) === 'available' ? 'bg-emerald-500' : 'bg-rose-500'" />
                    {{ slotStatus(hour) === 'available' ? 'Available' : 'Reserved' }}
                  </span>
                </button>
              </div>
            </div>
            <p class="mt-3 text-center text-xs text-slate-400">Click any time slot to switch between available and reserved.</p>
          </div>
        </div>

        <footer class="flex items-center justify-between gap-3 border-t border-slate-200 px-4 py-3 sm:px-7">
          <p class="text-xs text-slate-500">{{ item.categoryLabel }} · {{ item.sizes.length }} sizes</p>
          <button
            type="button"
            class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800"
            @click="emit('close')"
          >
            Close calendar
          </button>
        </footer>
      </section>
    </div>
  </Teleport>
</template>
