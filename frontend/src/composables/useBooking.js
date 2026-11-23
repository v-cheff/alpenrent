import { computed, reactive } from 'vue'
import { createBooking } from '../api/bookings.js'
import { getRentalPoints } from '../api/rentalPoints.js'
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

// Bookings can't start in the past (the API rejects them too).
const todayISO = toISODate(today)

const state = reactive({
  startDate: todayISO,
  endDate: addDays(todayISO, 2),
  // 'full_day' | 'half_day' (half day = a single date, from noon)
  rentalType: 'full_day',
  // Pickup location filter; null = every rental point
  rentalPointId: null,
  insurance: false,
  selected: null,
  selectedItemData: null,
  selectedSize: '',
  sizingOpen: false,
  sizePickerOpen: false,
  bookingSuccess: false,
  bookingSubmitting: false,
  bookingError: '',
  // Bumped after each booking so the catalog refetches free sizes.
  catalogVersion: 0,
})

// Rental points owners have created — the pickup locations on the home page.
const rentalPoints = reactive({
  items: [],
  isLoading: false,
  loaded: false,
})

async function loadRentalPoints() {
  if (rentalPoints.isLoading) return
  rentalPoints.isLoading = true
  try {
    const data = await getRentalPoints()
    rentalPoints.items = Array.isArray(data) ? data : []
    rentalPoints.loaded = true
    if (!rentalPoints.items.some((point) => point.id === state.rentalPointId)) {
      state.rentalPointId = null
    }
  } catch {
    rentalPoints.items = []
  } finally {
    rentalPoints.isLoading = false
  }
}

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
  // Selected pickup location, or null for "all rental points".
  const location = computed(
    () => rentalPoints.items.find((point) => point.id === state.rentalPointId) ?? null,
  )
  const selectedItem = computed(() =>
    state.selectedItemData && state.selectedItemData.id === state.selected
      ? state.selectedItemData
      : null,
  )

  // Sizes free for the selected dates (the catalog sends `availableSizes`); all sizes otherwise.
  const freeSizes = computed(() => {
    const item = selectedItem.value
    if (!item) return []
    return Array.isArray(item.availableSizes) ? item.availableSizes : item.sizes ?? []
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

    state.selectedSize = freeSizes.value[0] ?? ''
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
      state.catalogVersion++
      return true
    } catch (error) {
      state.bookingError = error.message || 'Could not complete the booking. Please try again.'
      return false
    } finally {
      state.bookingSubmitting = false
    }
  }

  // Applies the hero search (dates + pickup point) to the catalog and scrolls to it.
  // The catalog watches these fields and refetches free sizes for the new period.
  function checkAvailability(filters = null) {
    if (filters) {
      setDates(filters.startDate, filters.endDate)
      state.rentalPointId = filters.rentalPointId ?? null
      state.catalogVersion++
    }
    const catalog = document.getElementById('catalog')
    catalog?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }

  return {
    state,
    today: todayISO,
    rentalDaysBetween,
    rentalPoints,
    loadRentalPoints,
    durationDays,
    location,
    selectedItem,
    freeSizes,
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
