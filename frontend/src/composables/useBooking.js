import { computed, reactive } from 'vue'
import { equipment, locations } from '../data/catalog'
import { createBooking } from '../api/bookings.js'
import { useAuth } from './useAuth'

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
  selectedItemData: null,
  selectedSize: '',
  availabilityChecked: false,
  sizingOpen: false,
  sizePickerOpen: false,
  bookingSuccess: false,
  bookingSubmitting: false,
  bookingError: '',
})

function nightsBetween(start, end) {
  const a = new Date(`${start}T12:00:00`)
  const b = new Date(`${end}T12:00:00`)
  const diff = Math.round((b - a) / 86400000)
  return Math.max(diff, 1)
}

export function useBooking() {
  const { isAuthenticated, openLogin } = useAuth()

  const durationDays = computed(() => nightsBetween(state.startDate, state.endDate))
  const location = computed(
    () => locations.find((l) => l.id === state.locationId) ?? locations[0],
  )
  const selectedItem = computed(() => {
    if (state.selectedItemData && state.selectedItemData.id === state.selected) {
      return state.selectedItemData
    }
    return equipment.find((item) => item.id === state.selected) ?? null
  })

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

  function selectItem(id, itemData = null) {
    if (itemData) {
      state.selectedItemData = itemData
    }
    state.selected = id

    if (!isAuthenticated.value) {
      // Functional requirement: booking modal MUST NOT open for unauthorized users
      state.sizePickerOpen = false
      openLogin(
        'Please sign in or register to book equipment.',
        () => {
          // Automatically resume booking upon successful authorization
          state.sizePickerOpen = true
        },
      )
      return false
    }

    const item = selectedItem.value
    state.selectedSize = item?.sizes?.[0] ?? ''
    state.bookingError = ''
    state.sizePickerOpen = true
    return true
  }

  function openSizePicker() {
    if (!isAuthenticated.value) {
      state.sizePickerOpen = false
      openLogin(
        'Please sign in or register to book equipment.',
        () => {
          if (selectedItem.value) {
            state.sizePickerOpen = true
          }
        },
      )
      return false
    }

    if (selectedItem.value) {
      state.bookingError = ''
      state.sizePickerOpen = true
    }
    return true
  }

  async function confirmBooking() {
    if (state.bookingSubmitting) {
      return false
    }

    if (!isAuthenticated.value) {
      state.sizePickerOpen = false
      openLogin('Please sign in or register to complete your booking.')
      return false
    }

    if (!selectedItem.value || !state.selectedSize) {
      state.bookingError = 'Please select an equipment size before continuing.'
      return false
    }

    state.bookingSubmitting = true
    state.bookingError = ''

    try {
      await createBooking({
        equipmentId: selectedItem.value.id,
        size: state.selectedSize,
        startDate: state.startDate,
        endDate: state.endDate,
      })

      state.sizePickerOpen = false
      state.bookingSuccess = true
      return true
    } catch (error) {
      state.bookingError = error.message || 'Could not complete the booking. Please try again.'
      return false
    } finally {
      state.bookingSubmitting = false
    }
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
    openSizePicker,
    confirmBooking,
    checkAvailability,
  }
}
