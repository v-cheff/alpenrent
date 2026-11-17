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
  endDate: addDays(toISODate(today), 2),
  // 'full_day' | 'half_day' (half day = a single date, from noon)
  rentalType: 'full_day',
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

// Ski rentals bill each calendar day, both ends inclusive: 1–4 Dec = 4 days, 1–1 Dec = 1 day.
function rentalDaysBetween(start, end) {
  const a = new Date(`${start}T12:00:00`)
  const b = new Date(`${end}T12:00:00`)
  const diff = Math.round((b - a) / 86400000)
  return Math.max(diff + 1, 1)
}

export function useBooking() {
  const { isAuthenticated, isOwner, openLogin } = useAuth()

  const OWNER_CANNOT_BOOK =
    'You are signed in with a rental point owner account. Sign in as a renter to book equipment.'

  const durationDays = computed(() => rentalDaysBetween(state.startDate, state.endDate))
  const location = computed(
    () => locations.find((l) => l.id === state.locationId) ?? locations[0],
  )
  const selectedItem = computed(() => {
    if (state.selectedItemData && state.selectedItemData.id === state.selected) {
      return state.selectedItemData
    }
    return equipment.find((item) => item.id === state.selected) ?? null
  })

  // Half day is offered only for a single date and only if the owner set a half-day rate.
  const halfDayAvailable = computed(
    () =>
      durationDays.value === 1 &&
      selectedItem.value?.halfDayRate !== null &&
      selectedItem.value?.halfDayRate !== undefined,
  )

  const effectiveRentalType = computed(() =>
    halfDayAvailable.value && state.rentalType === 'half_day' ? 'half_day' : 'full_day',
  )

  const equipmentTotal = computed(() => {
    if (!selectedItem.value) return 0
    if (effectiveRentalType.value === 'half_day') return selectedItem.value.halfDayRate
    return selectedItem.value.dailyRate * durationDays.value
  })

  const insuranceTotal = computed(() =>
    state.insurance ? 3 * durationDays.value : 0,
  )

  const grandTotal = computed(() => equipmentTotal.value + insuranceTotal.value)
  const deposit = computed(() => selectedItem.value?.deposit ?? 0)

  function setDates(start, end) {
    state.startDate = start
    if (!end || end < start) {
      state.endDate = start
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
    state.rentalType = 'full_day'
    state.bookingError = isOwner.value ? OWNER_CANNOT_BOOK : ''
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

    if (isOwner.value) {
      state.bookingError = OWNER_CANNOT_BOOK
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
        rentalType: effectiveRentalType.value,
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
    halfDayAvailable,
    effectiveRentalType,
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
