import { computed, reactive } from 'vue'
import { equipment, locations } from '../data/catalog'

const today = new Date()
const pad = (n) => String(n).padStart(2, '0')
const toISODate = (d) =>
  `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`

const addDays = (iso, days) => {
  const d = new Date(`${iso}T12:00:00`)
  d.setDate(d.getDate() + days)
  return toISODate(d)
}

const state = reactive({
  startDate: toISODate(today),
  endDate: addDays(toISODate(today), 3),
  locationId: 'innsbruck',
  skill: 'intermediate',
  insurance: false,
  selected: null,
  selectedSize: '',
  availabilityChecked: false,
  sizingOpen: false,
  sizePickerOpen: false,
})

function nightsBetween(start, end) {
  const a = new Date(`${start}T12:00:00`)
  const b = new Date(`${end}T12:00:00`)
  const diff = Math.round((b - a) / 86400000)
  return Math.max(diff, 1)
}

export function useBooking() {
  const durationDays = computed(() => nightsBetween(state.startDate, state.endDate))
  const location = computed(
    () => locations.find((l) => l.id === state.locationId) ?? locations[0],
  )
  const selectedItem = computed(
    () => equipment.find((item) => item.id === state.selected) ?? null,
  )

  const equipmentTotal = computed(() => {
    if (!selectedItem.value) return 0
    return selectedItem.value.dailyRate * durationDays.value
  })

  const insuranceTotal = computed(() =>
    state.insurance ? 3 * durationDays.value : 0,
  )

  const grandTotal = computed(() => equipmentTotal.value + insuranceTotal.value)
  const deposit = computed(() => selectedItem.value?.deposit ?? 0)

  function setDates(start, end) {
    state.startDate = start
    if (!end || end <= start) {
      state.endDate = addDays(start, 1)
    } else {
      state.endDate = end
    }
  }

  function selectItem(id) {
    state.selected = id
    const item = equipment.find((e) => e.id === id)
    state.selectedSize = item?.sizes?.[0] ?? ''
    state.sizePickerOpen = true
  }

  function checkAvailability() {
    state.availabilityChecked = true
    const catalog = document.getElementById('catalog')
    catalog?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }

  return {
    state,
    durationDays,
    location,
    selectedItem,
    equipmentTotal,
    insuranceTotal,
    grandTotal,
    deposit,
    setDates,
    selectItem,
    checkAvailability,
  }
}
