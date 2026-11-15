import { computed, reactive } from 'vue'
import { getIncomingBookings } from '../../api/bookings.js'
import { bookingStatus } from '../../utils/bookings.js'

export function useIncomingBookings() {
  const bookings = reactive({
    items: [],
    isLoading: true,
    loadError: '',
  })

  const openCount = computed(
    () => bookings.items.filter((booking) => bookingStatus(booking) !== 'completed').length,
  )

  async function load() {
    bookings.isLoading = true
    bookings.loadError = ''

    try {
      const data = await getIncomingBookings()
      bookings.items = Array.isArray(data) ? data : []
    } catch (error) {
      bookings.items = []
      bookings.loadError = error.message || 'Could not load bookings.'
    } finally {
      bookings.isLoading = false
    }
  }

  return {
    bookings,
    openCount,
    load,
  }
}
